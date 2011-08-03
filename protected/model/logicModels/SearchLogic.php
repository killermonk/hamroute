<?php

/**
 * This class handles all the Business Logic for Search functionality
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class SearchLogic extends AbstractLogic
{
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
		$route = new SpatialPolyLine();
		foreach ($routeCoords as $coords)
			$route->addCoords($coords);

		// Create our search criteria for the repeater
		$criteria = array();
		if (!empty($band))
			$criteria['band'] = $band;

		// Find all of our repeaters
		$unit = new RepeaterUnit();
		$repeaters = $unit->getNearSpatial($route, $criteria);
	}
}
