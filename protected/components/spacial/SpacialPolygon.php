<?php

Yii::import('application.components.spacial.SpacialAbstract');

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
		$wkt = 'POLYGON(';

		$coordGroups = $this->getCoords();
		if (!empty($coordGroups))
		{
			foreach ($coordGroups as $coords)
			{
				// Append all the points
				$coordList = array();
				foreach ($coords as $coord)
					$coordList[] = "{$coord['lat']} {$coord['lon']}";

				$lastCoord = end($coords);
				$firstCoord = reset($coords);

				// If the end coord does not equal the start coord, connect it back to itself
				if ($firstCoord['lat'] != $lastCoord['lat'] || $firstCoord['lon'] != $lastCoord['lon'])
					$coordList[] = "{$firstCoord['lat']} {$firstCoord['lon']}";

				// Create the group
				$groups[] = '('.implode(',', $coordList).')';
			}

			// Add the groups
			$wkt .= implode(',', $groups);
		}

		$wkt .= ')';
		return $wkt;
	}

	/**
	 * Add a coordinate to the polygon
	 */
	public function addCoord(array $coord)
	{
		if (count($coord) != 3)
			throw new Exception("Spacial Polygon coordinate requires three values. ".(count($coord))." found.");

		// lat, lon, group
		$lat = (double)(isset($coord['lat']) ? $coord['lat'] : $coord[0]);
		$lon = (double)(isset($coord['lon']) ? $coord['lon'] : $coord[1]);
		$group = (int)(isset($coord['group']) ? $coord['group'] : $coord[2]);

		if (!isset($this->coords[$group]))
			$this->coords[$group] = array();

		$this->coords[$group][] = compact('lat', 'lon');
	}
}
