<?php
/**
 * @copyright (C) 15winks  - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * @author [x]cube LABS Web service Team, January 2014
 * @version : 1.0
 * @brief This Api Controller performs operations(<b>encryptSha1</b>) related to Activity Log.
 */
class CAPISecurityManager {
	/**
	 * @brief This function returns sha1 data
	 * 
	 * @param string $data        	
	 * @param string $key        	
	 * @return string
	 */
	public static function encryptSha1($data, $key) {
		$data = $key . "&" . $data;
		// echo sha1 ( $data );exit;
		Yii::log ( 'data : ' . $data, 'info', 'system.*' );
		Yii::log ( 'SHA1-data -: ' . sha1 ( $data ), 'info', 'system.*' );
		return sha1 ( $data );
	}
}