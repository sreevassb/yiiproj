 <?php
 class Users extends EMongoDocument // Notice: We extend EMongoDocument class instead of CActiveRecord
 {
 	public $personal_no;
 	public $login;
 	public $first_name;
 	public $last_name;
 	public $email;
 
 	/**
 	 * This method have to be defined in every model, like with normal CActiveRecord
 	 */
 	public static function model($className=__CLASS__)
 	{
 		return parent::model($classname);
 	}
 	
 	/**
 	 * This method have to be defined in every Model
 	 * @return string MongoDB collection name, witch will be used to store documents of this model
 	 */
 	public function getCollectionName()
 	{
 		return 'users';
 	}
 
 	// We can define rules for fields, just like in normal CModel/CActiveRecord classes
 	public function rules()
 	{
 		return array(
 				array('login, email, personal_no', 'required'),
 				array('personal_no', 'numeric', 'integerOnly' => true),
 				array('email', 'email'),
 		);
 	}
 
 	// the same with attribute names
 	public function attributeLabels()
 	{
 		return array(
 				'email' => 'E-Mail Address',
 		);
 	}
 
 
 }