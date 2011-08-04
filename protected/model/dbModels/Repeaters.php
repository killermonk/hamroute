<?php

/**
 * This is the model class for table "repeaters".
 *
 * The followings are the available columns in table 'repeaters':
 * @property string $repeater_id
 * @property string $band
 * @property string $output_freq
 * @property string $input_freq
 * @property string $ctcss_in
 * @property string $ctcss_out
 * @property string $dcs_code
 * @property integer $region_id
 * @property integer $open
 * @property string $geo_location
 * @property string $geo_coverage
 * @property string $import_data
 */
class Repeaters extends AbstractSpatialModel
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Repeaters the static model class
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
		return 'repeaters';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('band, output_freq, input_freq, region_id, geo_location, geo_coverage', 'required'),
			array('region_id, open', 'numerical', 'integerOnly'=>true),
			array('band', 'length', 'max'=>5),
			array('output_freq, input_freq', 'length', 'max'=>10),
			array('ctcss_in, ctcss_out', 'length', 'max'=>5),
			array('dcs_code', 'length', 'max'=>6),
			array('import_data', 'safe'),
			// The following rule is used by search().
			array('repeater_id, band, region_id, open', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'region' => array(self::BELONGS_TO, 'RepeaterRegions', 'region_id'),
			'links' => array(self::HAS_MANY, 'RepeaterLinks', 'source_id'), // Only a link to the source, not the dest
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'repeater_id' => 'Repeater',
			'band' => 'Band',
			'output_freq' => 'Output Freq',
			'input_freq' => 'Input Freq',
			'ctcss_in' => 'Ctcss In',
			'ctcss_out' => 'Ctcss Out',
			'dcs_code' => 'Dcs Code',
			'region_id' => 'Region',
			'open' => 'Open',
			'geo_location' => 'Geo Location',
			'geo_coverage' => 'Geo Coverage',
			'import_data' => 'Import Data',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('repeater_id',$this->repeater_id,true);
		$criteria->compare('band',$this->band,true);
		$criteria->compare('region_id',$this->region_id);
		$criteria->compare('open',$this->open);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Filter the repeaters down to only those who's coverage area is contained by the given spatial object
	 * @param SpatialAbstract $spatial
	 * @return Repeaters
	 */
	public function covers(SpatialAbstract $spatial)
	{
		return $this->intersects('geo_coverage', $spatial);
	}

	/**
	 * Filter the repeaters down to only those who are located within the spatial object
	 * @param SpatialAbstract $spatial
	 * @return Repeaters
	 */
	public function locatedIn(SpatialAbstract $spatial)
	{
		return $this->within('geo_location', $spatial);
	}
}