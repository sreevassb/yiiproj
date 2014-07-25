<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for apis in this application should extend from this base class.
 */
class ApiController extends CController {
	/**
	 *
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 *      meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '//layouts/column1';
	/**
	 *
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu = array ();
	/**
	 *
	 * @var array the breadcrumbs of the current page. The value of this property will
	 *      be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 *      for more details on how to specify this property.
	 */
	public $breadcrumbs = array ();
	private $userId = null;
	public $extension = 'json';
	public $errors = array ();
	
	/**
	 * Constructs model node with given or model attributeNodes
	 * 
	 * @param CActiveRecord $model        	
	 * @param array $attributeNodes        	
	 * @return array
	 */
	protected function createNode($model, $attributeNodes = array(), $with = array()) {
		$nodeNames = count ( $attributeNodes ) > 0 ? $attributeNodes : $model->attributeNodes ();
		$attributeLabels = $model->attributeLabels ();
		
		$node = array ();
		foreach ( $nodeNames as $attr => $nodeName ) {
			if (is_array ( $nodeName )) {
				$node [$nodeName ['name']] = eval ( $nodeName ['value'] );
			} else {
				if (isset ( $attributeLabels [$attr] ))
					$node [$nodeName] = $model->$attr;
			}
		}
		
		// Build with relations
		if (count ( $with ) > 0) {
			foreach ( $with as $relation ) {
				$nodeKey = empty ( $nodeNames [$relation] ) ? $relation : $nodeNames [$relation];
				$node [$nodeKey] = $this->createNodes ( $model->$relation );
			}
		}
		
		return $node;
	}
	
	/**
	 *
	 *
	 * Constructs model(s) node with given or model attributeNodes
	 * 
	 * @param mixed $models        	
	 * @param array $attributeNodes        	
	 * @return array
	 */
	public function createNodes($models, $attributeNodes = array(), $with = array()) {
		if (is_array ( $models )) {
			$op = array ();
			foreach ( $models as $model ) {
				$op [] = $this->createNode ( $model, $attributeNodes, $with );
			}
			return $op;
		} else {
			return $this->createNode ( $models, $attributeNodes, $with );
		}
	}
	private function getContentType($extension) {
		$contentType = 'text/html';
		switch ($extension) {
			case 'plist' :
			case 'xml' :
				$contentType = 'text/xml';
				break;
			case 'json' :
				$contentType = 'application/json';
				break;
		}
	}
	private function getStatusCodeMessage($status) {
		$codes = Array (
				200 => 'OK',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				500 => 'Internal Server Error' 
		);
		return (isset ( $codes [$status] )) ? $codes [$status] : '';
	}
	
	/**
	 * Converts an array to xml string
	 * 
	 * @param array $arr        	
	 * @param boolean $root        	
	 * @return string
	 */
	private function toXml($arr, $root = true) {
		$str = '';
		if ($root === true) {
			$str = '<?xml version="1.0" encoding="UTF-8" ?>';
			$str .= '<root>';
		}
		foreach ( $arr as $key => $val ) {
			if (is_array ( $val )) {
				$str .= is_numeric ( $key ) ? '<node>' : '<' . $key . '>';
				$str .= $this->toXml ( $val, false );
				$str .= is_numeric ( $key ) ? '</node>' : '</' . $key . '>';
			} else {
				if (is_numeric ( $key )) {
					$key = 'node';
				}
				if ($val === false) {
					$val = 0;
				}
				if (is_numeric ( $val ) || empty ( $val )) {
					$str .= '<' . $key . '>' . $val . '</' . $key . '>';
				} else {
					$str .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
				}
			}
		}
		if ($root === true) {
			$str .= '</root>';
		}
		return $str;
	}
	
	/**
	 * Returns standard apple's plist format
	 * Responsibilities: add datatypes (integer, real, bool(true, false) ) for respective datatypes
	 * 
	 * @param array $arr        	
	 * @param boolean $root        	
	 * @return string
	 */
	private function toPlist($arr, $root = true) {
		$str = '';
		if ($root === true) {
			$str = '<?xml version="1.0" encoding="utf-8" ?>';
			$str .= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';
			$str .= '<plist version="1.0">';
		}
		if (count ( $arr ) > 0) {
			if (isset ( $arr [0] )) {
				$str .= '<array>';
			} else {
				$str .= '<dict>';
			}
		} else if (is_array ( $arr ) && count ( $arr ) == 0) {
			$str .= '<array>';
		}
		
		foreach ( $arr as $key => $val ) {
			if (is_array ( $val )) {
				if (! is_numeric ( $key )) {
					$str .= '<key>' . $key . '</key>';
				}
				$str .= $this->toPlist ( $val, false );
			} else {
				$addAnonymousNode = is_numeric ( $key ) ? true : false;
				if ($addAnonymousNode) {
					// throw new Exception('Error constructing XML: Invalid name value pair');
					$str .= '<dict>';
					$key = 'node';
				}
				
				$str .= '<key>' . $key . '</key>';
				
				/**
				 * Set respective datatypes
				 */
				if (is_bool ( $val )) {
					$str .= $val ? '<true/>' : '<false/>';
				} else if (is_int ( $val )) {
					$str .= '<integer>' . $val . '</integer>';
				} else if (is_float ( $val )) {
					$str .= '<real>' . $val . '</real>';
				} else {
					if (empty ( $val )) {
						$str .= '<string>' . $val . '</string>';
					} else {
						$str .= '<string><![CDATA[' . $val . ']]></string>';
					}
				}
				if ($addAnonymousNode) {
					$str .= '</dict>';
				}
			}
		}
		if (count ( $arr ) > 0) {
			if (isset ( $arr [0] )) {
				$str .= '</array>';
			} else {
				$str .= '</dict>';
			}
		} else if (is_array ( $arr ) && count ( $arr ) == 0) {
			$str .= '</array>';
		}
		if ($root === true) {
			
			$str .= '</plist>';
		}
		return $str;
	}
	
	/**
	 * Sends the data to browser in REST Api(xml, json, plist) format
	 * 
	 * @param $mixed $data        	
	 * @param integer $status        	
	 * @return void
	 */
	protected function sendResponse($data, $status = 200) {
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->getStatusCodeMessage ( $status );
		
		header ( $status_header );
		header ( 'Content-type: ' . $this->getContentType ( $this->extension ) );
		
		$body = null;
		if (is_string ( $data )) {
			echo $data;
			Yii::app ()->end ();
		}
		
		switch ($this->extension) {
			case 'json' :
				$body = CJSON::encode ( $data );
				break;
			case 'xml' :
				$body = $this->toXml ( $data, true );
				break;
			case 'plist' :
				$body = $this->toPlist ( $data, true );
				break;
		}
		echo $body;
		Yii::app ()->end ();
	}
	protected function checkAuth() {
		$user = $this->getAuthCredentials ();
		$username = $user ['username'];
		$password = $user ['password'];
		
		$record = Staff::model ()->findByAttributes ( array (
				'email' => $username 
		) );
		
		if ($record === null)
			$this->sendResponse ( 'Error: User Name is invalid', 401 );
		else if ($record->password !== md5 ( $password ))
			$this->sendResponse ( 'Error: User Password is invalid', 401 );
		else if ($record->status !== Staff::STATUS_ACTIVE)
			$this->sendResponse ( 'Error: User Name is invalid', 401 );
		
		$this->userId = $record->id;
		return true;
	}
	public function getUserId() {
		return $this->userId;
	}
	private function getAuthCredentials($authType = 'customHeader') {
		switch ($authType) {
			case 'customHeader' :
				if (! (isset ( $_SERVER ['HTTP_X_USERNAME'] ) && isset ( $_SERVER ['HTTP_X_PASSWORD'] ))) {
					$this->sendResponse ( 'Invalid user', 401 );
				}
				$username = $_SERVER ['HTTP_X_USERNAME'];
				$password = $_SERVER ['HTTP_X_PASSWORD'];
				break;
			
			case 'Basic' :
				if (isset ( $_SERVER ['PHP_AUTH_USER'] ) && isset ( $_SERVER ['PHP_AUTH_PW'] )) {
					$username = $_SERVER ['PHP_AUTH_USER'];
					$password = $_SERVER ['PHP_AUTH_PW'];
				} else {
					header ( 'WWW-Authenticate: Basic realm="' . Yii::app ()->name . '"' );
				}
				break;
		}
		return array (
				'username' => $username,
				'password' => $password 
		);
	}
	
	/**
	 * hasValidationErrors checks for validation errors of api
	 * 
	 * @param array $inputs
	 *        	is the returned array of filter functions (filter_var_array, filter_input_array).
	 * @param array $optional
	 *        	is the array of optional parameters.
	 * @param array $multi
	 *        	is the array of array which has list of parameters where all the parameters should not be empty.
	 * @return boolean
	 */
	public function hasValidationErrors($inputs, $optional = array(), $multi = array()) {
		// Check for input filter errors here and generate error messages.
		$hasErrors = false;
		$errors = array ();
		foreach ( $inputs as $key => $value ) {
			if ($value === false) {
				$hasErrors = true;
				$errors [] = $key . ' is invalid ';
			} else if (trim ( $value ) == '' && ! in_array ( $key, $optional )) {
				$hasErrors = true;
				$errors [] = $key . ' is required ';
			}
		}
		
		// Check for multi options
		$multiError = false;
		for($i = 0, $cnt = count ( $multi ); $i < $cnt; ++ $i) {
			$multiError = true;
			$vars = array ();
			
			for($j = 0, $cnt2 = count ( $multi [$i] ); $j < $cnt2; ++ $j) {
				if (empty ( $inputs [$multi [$i] [$j]] )) {
					$vars [] = $multi [$i] [$j];
				} else {
					$multiError = false;
				}
			}
			
			if ($multiError) {
				$errors [] = 'Any one of ' . implode ( ', ', $vars ) . ' is required ';
			}
			$vars = array ();
			$multiError = true;
		}
		
		$this->errors = $errors;
		
		return $hasErrors || $multiError;
	}
	
	/**
	 * Returns the errors in array
	 * 
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
	public function run($actionID) {
		$this->calculateHash ();
		
		try {
			
			parent::run ( $actionID );
		} catch ( CApiException $e ) {
			$op ['status'] = 'failed';
			$op ['statusCode'] = $e->getStatusCode ();
			$op ['error'] = $e->getMessage ();
			$op ['errorMessages'] = $e->getErrors ();
			
			$this->sendResponse ( $op, $e->getStatusCode () );
		}
	}
	
	/**
	 * Calculates the hash
	 */
	protected function calculateHash() {
		if (! Yii::app ()->params ['use-signature'])
			return true;
		
		switch (Yii::app ()->request->getRequestType ()) {
			case 'GET' :
				$requestData = $_GET;
				break;
			case 'POST' :
				$requestData = $_POST;
				break;
		}
		
		if (empty ( $requestData ['signature'] ) && Yii::app ()->params ['inDevelopment'])
			return true;
			
			// check for params
		if (empty ( $requestData ['signature'] ) || empty ( $requestData ['ts'] ) || empty ( $requestData ['nonce'] ) || empty ( $requestData ['accessKey'] )) {
			$data = array (
					'error' => 'Bad Request: signature, ts, nonce, accessKey are required' 
			);
			$this->sendResponse ( $data, 400 );
		}
		
		if (Yii::app ()->params ['public-key'] != $requestData ['accessKey']) {
			$data = array (
					'error' => 'Bad Request: unknown accessKey' 
			);
			$this->sendResponse ( $data, 400 );
		}
		
		$inputSignature = $requestData ['signature'];
		
		// Check timestamp
		$t = time ();
		$delta = Yii::app ()->params ['timestamp-window'];
		$lt = $t - $delta;
		$rt = $t + $delta;
		if ($requestData ['ts'] < $lt || $requestData ['ts'] > $rt) {
			$data = array (
					'error' => 'Bad Request, time window doesnot match' 
			);
			$this->sendResponse ( $data, 400 );
		}
		
		unset ( $requestData ['signature'] );
		unset ( $requestData ['extension'] );
		
		$qStr = $this->getNVP ( $requestData ) . Yii::app ()->params ['private-key'];
		$calcSignature = md5 ( $qStr );
		
		if ($calcSignature != $inputSignature) {
			$data = array (
					'error' => 'Bad Request, incorrect signature' 
			);
			$this->sendResponse ( $data, 400 );
		}
		
		// Using nonce using Apc
		$checkNonce = Yii::app ()->params ['prevent-duplicates'];
		
		if ($checkNonce && Yii::app ()->cache) {
			if (Yii::app ()->cache->get ( 'nonce-' . $requestData ['nonce'] )) {
				$data = array (
						'error' => 'Forbidden, Duplicate' 
				);
				$this->sendResponse ( $data, 403 );
			} else {
				Yii::app ()->cache->set ( 'nonce-' . $requestData ['nonce'], 1, $delta * 2 );
			}
		}
	}
	
	/**
	 * Returns array as a name value pair
	 * 
	 * @param array $array        	
	 * @return string
	 */
	private static function getNVP($array) {
		$op = '';
		foreach ( $array as $n => $v )
			$op .= $n . '=' . $v . '&';
		return rtrim ( $op, '&' );
	}
	
	/**
	 * @brief Validates post data coming from api call.
	 * <br>Used in most of the Api actions where there is post data validation with hash key.
	 */
	public function customAuthenticate($nonce = null) {
		$nonceList = array();
		$nonceList = json_decode(Yii::app()->cache->get('nonce'));
		if(Yii::app()->cache->get('nonce_' . $nonce) == $nonce){
			return false;
		}
		Yii::app()->cache->set('nonce_' . $nonce, $nonce, 259200);
						
		$secretKey = yii::app ()->params ['secretKey'];			
		// data checking
		$postHash = $_REQUEST ['hashdata'];
		unset ( $_REQUEST ['hashdata'] );
		unset ( $_REQUEST ['nonce'] );
		unset ( $_REQUEST ['extension'] );
		$postStr = '';
		foreach ( $_REQUEST as $key => $value ) {
			$postStr .= $key . "=" . $value . "&";
		}
		$postStr = rtrim ( $postStr, '&' );
		if ($postStr == '') {
			return true;
		}
		
		if (CAPISecurityManager::encryptSha1 ( $postStr, $secretKey ) != rawurldecode ( $postHash )) {
			return false;
		}
		return true;
		
	}
}