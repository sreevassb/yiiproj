<?php

class User extends EMongoDocument // Notice: We extend EMongoDocument class instead of CActiveRecord
{
	public $personal_no;
	public $login;
	public $first_name;
	public $last_name;
	public $email;

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
				//array('personal_no', 'numeric', 'integerOnly' => true),
				array('email', 'email'),
		);
	}


	/**
	 * This returns attribute labels for each public variable that will be stored
	 * as key in the database. Is defined just as normal with mysql
	 *
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
				'username'			=> 'UserName',
				'email'				=> 'EMail',
				'personal_number'	=> 'PN',
				'first_name'		=> 'First Name',
				'last_name'			=> 'Last Name',
		);
	}
	
	/**
	 * This method have to be defined in every model, like with normal CActiveRecord
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}
	
	
	/**
	 * This method should return simple array that will define field names for embedded
	 * documents, and class to use for them
	 */
	public function embeddedDocuments()
	{
		return array(
				// property field name => class name to use for this embedded document
				'address' => 'UserAddress',
		);
	}
	
}