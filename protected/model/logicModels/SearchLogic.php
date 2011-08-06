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

		// Determine whether or not we should add this search
		$addSearch = true;
		foreach ($searches as $search)
		{
			if ($search['start'] == $start && $search['end'] == $end)
			{
				$addSearch = false;
				break;
			}
		}

		// Add the search if we want to
		if ($addSearch)
		{
			$session = new CHttpSession();
			$session->open();

			// This will always be defined, because it is defined by getRecentSearches
			// Push the new item onto the front of the array, to enforce ordering
			array_unshift($searches, compact('start', 'end', 'extra'));

			// If we have too many searches, trim off the old ones
			$max = Yii::app()->params['maxRecentSearches'];
			if (count($searches) > $max)
				array_splice($searches, $max);

			$session[$this->searchesIndex] = $searches;
			$session->close();
		}
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
	 * Get all the repeaters who's coverage areas overlap the route and matches
	 *  the given search filters
	 * @param array $routeCoords - an array of points defining routeBox
	 * @param string $band - the band we want to use (null for all)
	 * @return array(Repeaters)
	 */
	public function getRepeatersAlongRoute(array $routeCoords, $band=null)
	{
		// Create our SpatialPolyLine to represent our route
		Yii::import('application.components.spatial.SpatialAbstract');
		$route = SpatialAbstract::createFromType(SpatialAbstract::POLYGON);
		foreach ($routeCoords as $coords)
			$route->addCoord($coords);

		// Create our search criteria for the repeater
		$criteria = array();
		if (!empty($band))
			$criteria['band'] = $band;

		// Find all of our repeaters
		$unit = new RepeaterUnit();
		$repeaters = $unit->getNearSpatial($route, $criteria);

		// TODO do any other processing on the repeaters

		return $repeaters;
	}
}
