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
	 * @param array $extra - extra information about the search
	 */
	public function saveRecentSearch($start, $end, array $extra)
	{
		// Clean our data up a bit
		$start = trim($start);
		$end = trim($end);

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
			// This will always be defined, because it is defined by getRecentSearches
			$_SESSION[$this->searchesIndex][] = compact('start', 'end', 'extra');
		}
	}

	/**
	 * Get a list of the recent searches for the currently active user
	 * @return array - the list of search information
	 */
	public function getRecentSearches()
	{
		if (!isset($_SESSION[$this->searchesIndex]))
			$_SESSION[$this->searchesIndex] = array();
		return $_SESSION[$this->searchesIndex];
	}

	/**
	 * Get all the repeaters who's coverage areas overlap the route and matches
	 *  the given search filters
	 * @param array $routeCoords - an array of points defining the route
	 * @param string $band - the band we want to use (null for all)
	 * @return array(Repeaters)
	 */
	public function getRepeatersAlongRoute(array $routeCoords, $band=null)
	{
		// Create our SpatialPolyLine to represent our route
		Yii::import('application.components.spatial.SpatialAbstract');
		$route = SpatialAbstract::createFromType(SpatialAbstract::LINE_STRING);
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
