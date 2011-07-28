<?php

// For autoloading our sub classes
Yii::import('application.components.spacial.*');

/**
 * The abstract class to define a common interface to the OpenGIS spacial classes
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
abstract class SpacialAbstract
{
	// Constants for out type of objects
	const POINT = 1;
	const LINE_STRING = 2;
	const POLYGON = 3;
	const MULTI_POINT = 4;
	const MULTI_LINE_STRING = 5;
	const MULTI_POLYGON = 6;
	const GEOMETRY_COLLECTION = 7;

	/**
	 * The array of points for this object
	 * @var array
	 */
	protected $coords = array();

	/**
	 * Convert this object to Well-Known Text
	 */
	abstract function toWKT();

	/**
	 * Add a coordinate to this object
	 * @param array $coord - the coordinate to add to the object
	 */
	abstract public function addCoord(array $coord);

	/**
	 * Convert the object to a string
	 * @return string
	 */
	public function __toString()
	{
		return $this->toWKT();
	}

	/**
	 * Return whether ot not this object is empty
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->coords);
	}

	/**
	 * Return the list of points associated with this object
	 * @return array
	 */
	public function getCoords()
	{
		return $this->coords;
	}

	/**
	 * Factory method for creating a Spatial object based on it's type
	 * @param integer $type
	 * @return SpacialAbstract
	 */
	public static function createFromType($type)
	{
		switch ($type)
		{
			case self::POINT: $obj = new SpacialPoint(); break;
			case self::LINE_STRING: $obj = new SpacialLineString(); break;
			case self::POLYGON: $obj = new SpacialPolygon(); break;
			case self::MULTI_POINT: $obj = new SpacialMultiPoint(); break;
			case self::MULTI_LINE_STRING: $obj = new SpacialMultiLineString(); break;
			case self::MULTI_POLYGON: $obj = new SpacialMultiPolygon(); break;
			case self::GEOMETRY_COLLECTION: $obj = new SpacialGeometryCollection(); break;
			default: throw new Exception("Spacial type {$type} was not found");
		}

		return $obj;
	}
}
