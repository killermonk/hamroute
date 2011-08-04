<?php

/**
 * Class to handle common functionality for models that have GeoSpatial columns
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
abstract class AbstractSpatialModel extends CActiveRecord
{
	/**
	 * Add criteria to symbolize that a given column needs to intersect a GeoSpatial object
	 * @param string $column - the column we want to make sure is contained
	 * @param SpatialAbstract $Spatial - the geospatial item we are comparing against
	 * @return AbstractSpatialModel
	 */
	protected function intersects($column, SpatialAbstract $Spatial)
	{
		// Create our criteria
		$criteria = new CDbCriteria();
		$criteria->addCondition("MBRIntersects(`{$column}`, GeomFromText('{$Spatial->toWKT()}'))");

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	/**
	 * Add criteria to symbolize that a given column is within a GeoSpatial object
	 * @param string $column - the column we want ot make sure is within the spatial object
	 * @param SpatialAbstract $Spatial - the geospatial item we are comparing against
	 * @return AbstractSpatialModel
	 */
	protected function within($column, SpatialAbstract $Spatial)
	{
		// Create our criteria
		$criteria = new CDbCriteria();
		$criteria->addCondition("MBRWithin(`{$column}`, GeomFromText('{$Spatial->toWKT()}'))");

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	/**
	 * Add criteria to symbolize that a give column contains a GeoSpatial object
	 * @param string $column
	 * @param SpatialAbstract $Spatial
	 * @return AbstractSpatialModel
	 */
	protected function contains($column, SpatialAbstract $Spatial)
	{
		// Create our criteria
		$criteria = new CDbCriteria();
		$criteria->addCondition("MBRContains(`{$column}`, GeomFromText('{$Spatial->toWKT()}'))");

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
}
