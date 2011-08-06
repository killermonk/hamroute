<?php

class AjaxController extends Controller
{
	public $layout = 'empty'; // Use our empty layout, since we will be returning json

	/**
	 * The array of data to be json_encoded in the template
	 * @var array
	 */
	protected $json_data = array();
	
	/**
	 * Whether we should automatically run the code to auto render the json data
	 * @var boolean
	 */
	protected $autoRender = true;
	
	/*****************
	 * Overrides
	 *****************/

	/**
	 * Get the parameter array to be used for automagically assinging values to function arguments
	 * @return array
	 */
	public function getActionParams()
	{
		$args = parent::getActionParams();

		// Add in the $_POST array, giving it precedence
		return array_merge($args, $_POST);
	}

	/**
	 * Define the filters to be applied to the actions
	 * @return array - the filter configuration
	 */
	public function filters()
	{
		// Force all methods to be called as ajax
		return array(
			'ajaxOnly',
		);
	}

	/**
	 * After the action executes, run this. It will auto render our data
	 * @param CAction $action
	 */
	protected function afterAction($action)
	{
		parent::afterAction($action);

		// Auto render our template
		if ($this->autoRender)
		{
			$this->render('json', array(
				'json_data' => $this->json_data,
			));
		}
	}

	/*****************
	 * Helpers
	 *****************/

	/**
	 * Set the data we are going to json encode
	 * @param array $data
	 */
	protected function setData(array $data)
	{
		$this->json_data = $data;
	}

	/**
	 * Set whether or not to auto render the content
	 * @param boolean $render - true to auto render, false to not
	 */
	protected function setAutoRender($render)
	{
		$this->autoRender = $render;
	}

	/*****************
	 * Actions
	 *****************/

	/**
	 * Get a list of repeaters that cross the cover the given polyline
	 * @param array $boxes - the array of boxes to find repeaters along
	 * @return array - the list of repeaters
	 */
	public function actionGetRepeaters(array $boxes)
	{
		$i = 0;
		$logic = new SearchLogic();
		$repeaters = array();
		foreach($boxes as $box)
		{
			$result = $logic->getRepeatersAlongRoute($box);
			foreach($result as $repeater)
			{
				// Every time this method is called, it re-parses the binary data.
				//   cache the result so we don't do it multiple times
				$locationCoords = $repeater->getLocationPoint()->getCoords();

				// NOTE: This is a bad way of checking uniqueness. It will kind of work
				//  but you can have multiple repeaters running from the same two, just with
				//  different antennas, so it can be a different repeaters on a different
				//  frequency at the same location...
				$unique = true;
				foreach($repeaters as $rpt)
				{
					if($rpt['location'] == $locationCoords)
					{
						$unique = false;
						break; // This repeater is not unique, stop checking
					}
				}

				if($unique)
				{
					$repeaters[$i]['id'] = $i;
					$repeaters[$i]['location'] = $locationCoords;
					$repeaters[$i]['coverage'] = $repeater->getCoveragePolygon()->getCoords();
					$i++;
				}
			}
		}
		$this->setData($repeaters);
	}

	/**
	 * Log that the user has performed a search
	 * @param string $start
	 * @param string $end
	 * @param array $extra
	 * @return array(success => $status) (true or false)
	 */
	public function actionLogSearch($start, $end, array $extra=null)
	{
		// Clean data
		$start = trim($start);
		$end = trim($end);

		if ($extra === null)
			$extra = array();

		// Validation
		if (empty($start))
			throw new CHttpException(400, "Start cannot be empty");
		if (empty($end))
			throw new CHttpException(400, "End cannot be empty");

		$logic = new SearchLogic();
		$logic->saveRecentSearch($start, $end, $extra);

		$this->setData(array(
			'success' => true,
			'searches' => $logic->getRecentSearches(),
		));
	}

	/**
	 * Get the information about our recently searches
	 * @return array - the list of saved searches
	 */
	public function actionGetRecentSearches()
	{
		$logic = new SearchLogic();
		$searches = $logic->getRecentSearches();

		$this->setData($searches);
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		$error=Yii::app()->errorHandler->error;
		if($error)
		{
			$this->setAutoRender(false);
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

}