<?php

Yii::import('protected.compontents.spacial.SpacialAbstract');

/**
 * Class to handle being a special point
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SpacialPoint extends SpacialAbstract
{
	/**
	 * Convert to Well-Known Text format
	 * @return string
	 */
    public function toWKT()
	{
		$wkt = 'POINT(';

		$points = $this->getPoints();
		if (!empty($points))
			$wkt .= "{$points[0]['lat']} {$points[0]['lon']}";

		$wkt .= ')';
		return $wkt;
	}
	
	/**
	 * Parse the Well-Known Binary to create the point
	 * @param binary $binary
	 */
	public function parseWKB($binary)
	{
		$points = unpack("d*", $binary);
		if (count($points) != 2)
			throw new Exception("Unable to parse WKB. Invalid number of points (" . count($points) ."). Must be exactly 2.");

		// Force it to a 0-based array from its 1-based array
		$this->addPoint(array_values($points));
	}

	/**
	 * Add a point to the spacial point
	 */
	public function addPoint($point)
	{
		if (!$this->isEmpty())
			throw new Exception("Spacial Point can only have one point");
		if (!is_array($point))
			throw new Exception("Spacial Point value must be an array");

		$lat = isset($point['lat']) ? $point['lat'] : $point[0];
		$lon = isset($point['lon']) ? $point['lon'] : $point[1];
		$this->points[] = compact('lat', 'lon');
	}
}
