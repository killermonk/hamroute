<?php

/**
 * This class handles all the Business Logic for Search functionality
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SearchLogic extends AbstractLogic
{
	/**
	 * The key to be used for saving the recent searches
	 * @var string
	 */
	private $searchesIndex = 'RecentSearches';

	/**
	 * Save a recent search for the currently active user
	 * @param string $start - the start locatino of the search
	 * @param string $end - the end location of the search
	 * @param array $extra - [optional] extra information about the search
	 */
	public function saveRecentSearch($start, $end, array $extra)
	{
		// Get our list of recent searches and make sure this doesn't overlap them
		$searches = $this->getRecentSearches();

		// Find the location of this search if it already exists in our list
		$searchIndex = false;
		foreach ($searches as $index => $search)
		{
			// Lowercase searching
			if (strcasecmp($search['start'], $start) == 0 && strcasecmp($search['end'], $end) == 0)
			{
				$searchIndex = $index;
				break;
			}
		}

		$session = new CHttpSession();
		$session->open();

		// If the search already exists, remove it from the list (we'll put it back on the front)
		if ($searchIndex !== false)
			unset($searches[$searchIndex]);

		// Push the new item onto the front of the array, to enforce ordering
		array_unshift($searches, compact('start', 'end', 'extra'));

		// If we have too many searches, trim off the old ones
		$max = Yii::app()->params['maxRecentSearches'];
		if (count($searches) > $max)
			array_splice($searches, $max);

		// Save the search back to the session
		$session[$this->searchesIndex] = $searches;
		$session->close();
	}

	/**
	 * Get a list of the recent searches for the currently active user
	 * @return array - the list of search information
	 */
	public function getRecentSearches()
	{
		$session = new CHttpSession();
		$session->open();

		if (!isset($session[$this->searchesIndex]))
			$session[$this->searchesIndex] = array();

		$searches = $session[$this->searchesIndex];
		$session->close();

		return $searches;
	}

	/**
	 * Get the repeaters that are near a spatial object
	 * @param SpacialAbstract|array $spacial
	 * @param string $band - the band we want to use (null for all)
	 * @return array(Repeaters)
	 */
	protected function getRepeatersNearSpatial($spacial, $band=null)
	{
		// Create our search criteria for the repeater
		$criteria = array();
		if (!empty($band))
			$criteria['band'] = $band;

		// Find all of our repeaters
		$unit = new RepeaterUnit();
		$repeaters = $unit->getNearSpatial($spacial, $criteria);

		// TODO do any other processing on the repeaters

		return $repeaters;
	}

	/**
	 * Get all the repeaters covered by a list of bounding boxes
	 * @param array $boundingBoxes
	 * @param string $band
	 * @return array(Repeaters)
	 */
	public function getRepeatersByMultiBounds(array $boundingBoxes, $band=null)
	{
		// Create our spatial polygons for each of the bounding boxes
		$boxes = array();
		Yii::import('application.components.spatial.SpatialAbstract');
		foreach ($boundingBoxes as $boundingCoords)
		{
			$box = SpatialAbstract::createFromType(SpatialAbstract::POLYGON);
			$box->setBounds($boundingCoords[0], $boundingCoords[1], $boundingCoords[2], $boundingCoords[3], 0);
			$boxes[] = $box;
		}

		// Find all the repeaters
		return $this->getRepeatersNearSpatial($boxes, $band);
	}

	/**
	 * Get all repeaters who's coverage areas overlap the given boudning coordinates
	 * @param array $boundingCoords - an array of 4 points specifying the bounding box
	 * @param string $band - the band we want to use (null for all)
	 * @return array(Repeaters)
	 */
	public function getRepeatersByBounds(array $boundingCoords, $band=null)
	{
		// Create our SpatialPolygon to represent our bounding box
		Yii::import('application.components.spatial.SpatialAbstract');
		$box = SpatialAbstract::createFromType(SpatialAbstract::POLYGON);
		$box->setBounds($boundingCoords[0], $boundingCoords[1], $boundingCoords[2], $boundingCoords[3], 0);

		// Find all the repeaters
		return $this->getRepeatersNearSpatial($box, $band);
	}

	/**
	 * Get all the repeaters who's coverage areas overlap the route and matches
	 *  the given search filters
	 * @param array $routeCoords - an array of points defining routeBox
	 * @param string $band - the band we want to use (null for all)
	 * @return array(Repeaters)
	 */
	public function getRepeatersAlongRoute(array $routeCoords, $band=null)
	{
		// Create our SpatialLineString to represent our route
		Yii::import('application.components.spatial.SpatialAbstract');
		$route = SpatialAbstract::createFromType(SpatialAbstract::LINE_STRING);
		foreach ($routeCoords as $coords)
			$route->addCoord($coords);

		// Find all the repeaters
		return $this->getRepeatersNearSpatial($route, $band);
	}
}
