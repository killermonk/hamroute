<?php

/**
 * Class to handle common functionality for models that have GeoSpatial columns
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
abstract class AbstractSpacialModel extends CActiveRecord
{
	/**
	 * Add criteria to symbolize that a given column needs to intersect a GeoSpatial object
	 * @param $obj1 - the geospatial item used as our main boundary
	 * @param $obj2 - the geospatial item that touches $obj1
	 * @return AbstractSpacialModel
	 */
	public function intersects($obj1, $obj2)
	{
		// TODO figure out how we want to structure this

		if ($obj1 instanceof SpacialAbstract)
			$obj1 = "GeomFromText('{$obj1->toWKT()}')";
		if ($obj2 instanceof SpacialAbstract)
			$obj2 = "GeomFromText('{$obj1->toWKT()}')";

		// Create our criteria
		$criteria = new CDbCriteria();
		$criteria->addCondition("MBRIntersects(GeomFromText('{$spacial->toWKT()}'), `{$column}`)");

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
}
