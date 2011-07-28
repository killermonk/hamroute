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
	/**
	 * The array of points for this object
	 * @var array
	 */
	protected $points = array();

	/**
	 * Convert this object to Well-Known Text
	 */
	abstract function toWKT();

	/**
	 * Parse the Well-Known Binary and insert the values into the object
	 * @param binary $binary - just the points section of the WKB
	 * @param boolean $littleEndian - whether we are littleEndian or bigEndian
	 */
	abstract function parseWKB($binary, $littleEndian);

	/**
	 * Add a point to this object
	 * @param mixed $point - the point to add to the object
	 */
	abstract public function addPoint($point);

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
		return empty($this->points);
	}

	/**
	 * Return the list of points associated with this object
	 * @return array
	 */
	public function getPoints()
	{
		return $this->points;
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
			case 1: $obj = new SpacialPoint(); break;
			case 2: $obj = new SpacialLineString(); break;
			case 3: $obj = new SpacialPolygon(); break;
			case 4: $obj = new SpacialMultiPoint(); break;
			case 5: $obj = new SpacialMultiLineString(); break;
			case 6: $obj = new SpacialMultiPolygon(); break;
			case 7: $obj = new SpacialGeometryCollection(); break;
			default: throw new Exception("Spacial type {$type} was not found");
		}

		return $obj;
	}
}
