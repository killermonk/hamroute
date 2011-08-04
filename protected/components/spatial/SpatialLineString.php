<?php

Yii::import('application.components.spatial.SpatialAbstract');

/**
 * Class to handle being a Spatial line string
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SpatialLineString extends SpatialAbstract
{
	/**
	 * Convert to Well-Known Text format
	 * @return string
	 */
	public function toWKT()
	{
		$wkt = 'LINESTRING(';

		$coords = $this->getCoords();
		if (!empty($coords))
		{
			// Append all the points
			$coordList = array();
			foreach ($coords as $coord)
				$coordList[] = "{$coord['lat']} {$coord['lon']}";

			// Add the coordinates
			$wkt .= implode(',', $coordList);
		}

		$wkt .= ')';
		return $wkt;
	}

	/**
	 * Add a coordinate to a Line String
	 */
	public function addCoord(array $coord)
	{
		if (count($coord) != 2)
			throw new Exception("Spatial Point coordinate requires two values. ".(count($coord))." found.");

		$lat = (double)(isset($coord['lat']) ? $coord['lat'] : $coord[0]);
		$lon = (double)(isset($coord['lon']) ? $coord['lon'] : $coord[1]);
		$this->coords[] = compact('lat', 'lon');
	}
}
