<?php

Yii::import('application.components.spatial.SpatialAbstract');

/**
 * Class to handle being a special point
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SpatialPoint extends SpatialAbstract
{
	/**
	 * Convert to Well-Known Text format
	 * @return string
	 */
	public function toWKT()
	{
		$wkt = 'POINT(';

		$coords = $this->getCoords();
		if (!empty($coords))
			$wkt .= "{$coords[0]['lat']} {$coords[0]['lon']}";

		$wkt .= ')';
		return $wkt;
	}
	
	/**
	 * Add a coordinate to a Point
	 */
	public function addCoord(array $coord)
	{
		if (!$this->isEmpty())
			throw new Exception("Spatial Point can only have one coordinate");
		if (count($coord) != 2)
			throw new Exception("Spatial Point coordinate requires two values. ".(count($coord))." found.");

		$lat = (double)(isset($coord['lat']) ? $coord['lat'] : $coord[0]);
		$lon = (double)(isset($coord['lon']) ? $coord['lon'] : $coord[1]);
		$this->coords[] = compact('lat', 'lon');
	}
}
