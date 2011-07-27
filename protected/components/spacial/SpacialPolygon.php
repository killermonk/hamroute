<?php

Yii::import('protected.compontents.spacial.SpacialAbstract');

/**
 * Class to handle being a spacial polygon
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SpacialPolygon extends SpacialAbstract
{
	/**
	 * Convert to Well-Known Text format
	 * @return string
	 */
	public function toWKT()
	{
		$wkt = 'POLYGON(( ';

		$points = $this->getPoints();
		if (!empty($points))
		{
			// TODO implement groups
			foreach ($points as $point)
				$wkt .= "{$point['lat']} {$point['lon']}, ";

			$lastPoint = end($points);
			$firstPoint = reset($points);

			// If the end point does not equal the start point, connect it back to itself
			if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lon'] != $lastPoint['lon'])
				$wkt .= "{$firstPoint['lat']} {$firstPoint['lon']}";
		}

		$wkt .= ' ))';
		return $wkt;
	}

	/**
	 * Parse the Well-Known Binary to create the point
	 * @param binary $binary
	 */
	public function parseWKB($binary)
	{
		//<num-polys> <num-points-1> <points1..n> <num-points-2> <points1..n> .. <num-points-n> <points1..n>
		throw new Exception("parseWKB not yet implemented: ".bin2hex($binary));
	}

	/**
	 * Add a point to the spacial point
	 */
	public function addPoint($point)
	{
		// lat, lon, group
	}
}
