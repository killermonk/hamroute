<?php

/**
 * This is the model class for table "user_searches".
 *
 * The followings are the available columns in table 'user_searches':
 * @property string $search_id
 * @property string $user_id
 * @property string $name
 * @property integer $temporary
 * @property string $search_data
 *
 * The followings are the available model relations:
 * @property Users $user
 */
class UserSearches extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return UserSearches the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_searches';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, name, search_data', 'required'),
			array('temporary', 'numerical', 'integerOnly'=>true),
			array('user_id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>50),
			// The following rule is used by search().
			array('search_id, user_id, temporary', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'search_id' => 'Search',
			'user_id' => 'User',
			'name' => 'Name',
			'temporary' => 'Temporary',
			'search_data' => 'Search Data',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('search_id',$this->search_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('temporary',$this->temporary);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}