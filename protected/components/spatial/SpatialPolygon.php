<?php

Yii::import('application.components.spatial.SpatialAbstract');

/**
 * Class to handle being a Spatial polygon
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SpatialPolygon extends SpatialAbstract
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
	 * Add points to the polygon as a bounding box. Please don't call this function more than once for
	 *   the same group unless you really know what you are doing. It can make for some weird results
	 * @param double $lat1
	 * @param double $lon1
	 * @param double $lat2
	 * @param double $lon2
	 * @param integer $group
	 */
	public function setBounds($lat1, $lon1, $lat2, $lon2, $group)
	{
		$this->addCoord(array($lat1, $lon1, $group));
		$this->addCoord(array($lat1, $lon2, $group));
		$this->addCoord(array($lat2, $lon2, $group));
		$this->addCoord(array($lat2, $lon1, $group));
		$this->addCoord(array($lat1, $lon1, $group)); // Close it off
	}

	/**
	 * Add a coordinate to the polygon
	 * @param array $coord - the description of a single coordinate to be added to the polygon
	 *		array(
	 *			0/'lat' => $lat,
	 *			1/'lon' => $lon,
	 *			3/'group' => $group, // Since polygons can have multiple point groups
	 *		)
	 */
	public function addCoord(array $coord)
	{
		if (count($coord) != 2 && count($coord) != 3)
			throw new Exception("Spatial Polygon coordinate requires two or three values. ".(count($coord))." found.");

		// lat, lon, group
		$lat = (double)(isset($coord['lat']) ? $coord['lat'] : $coord[0]);
		$lon = (double)(isset($coord['lon']) ? $coord['lon'] : $coord[1]);
		if (!isset($coord['group']))
			$group = (isset($coord[2])) ? $coord[2] : 0; // Default to the first group
		else
			$group = (int)$coord['group'];

		if (!isset($this->coords[$group]))
			$this->coords[$group] = array();

		$this->coords[$group][] = compact('lat', 'lon');
	}
}
