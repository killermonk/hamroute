<?php

/**
 * This is the model class for table "repeater_links".
 *
 * The followings are the available columns in table 'repeater_links':
 * @property string $source_id
 * @property string $dest_id
 */
class RepeaterLinks extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return RepeaterLinks the static model class
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
		return 'repeater_links';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('source_id, dest_id', 'required'),
			array('source_id, dest_id', 'length', 'max'=>10),
			// The following rule is used by search().
			array('source_id, dest_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'source' => array(self::BELONGS_TO, 'Repeaters', 'repeater_id'),
			'dest' => array(self::BELONGS_TO, 'Repeaters', 'repeater_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'source_id' => 'Source',
			'dest_id' => 'Dest',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('source_id',$this->source_id,true);
		$criteria->compare('dest_id',$this->dest_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}