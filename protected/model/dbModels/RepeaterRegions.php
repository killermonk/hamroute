<?php

/**
 * This is the model class for table "repeater_regions".
 *
 * The followings are the available columns in table 'repeater_regions':
 * @property string $region_id
 * @property string $state
 * @property string $country
 * @property string $location_name
 * @property string $area_name
 */
class RepeaterRegions extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return RepeaterRegions the static model class
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
		return 'repeater_regions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('region_id', 'required'),
			array('region_id', 'length', 'max'=>10),
			array('state', 'length', 'max'=>5),
			array('country', 'length', 'max'=>2),
			array('location_name, area_name', 'length', 'max'=>25),
			// The following rule is used by search().
			array('region_id, state, country, area_name', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'repeaters' => array(self::HAS_MANY, 'Repeaters', 'region_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'region_id' => 'Region',
			'state' => 'State',
			'country' => 'Country',
			'location_name' => 'Location Name',
			'area_name' => 'Area Name',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('region_id',$this->region_id,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('area_name',$this->area_name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}