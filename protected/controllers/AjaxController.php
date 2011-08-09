<?php

/**
 * Controller to handle our ajax requests
 */
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
	 * @param string $band - the band to use when finding repeaters
	 * @return array - the list of repeaters
	 */
	public function actionGetRepeaters(array $boxes, $band=null)
	{
		$i = 0;
		$logic = new SearchLogic();
		$repeaters = array();
		$usedRepeaters = array(); // Lookup array for duplicates

		// Get all the repeaters that are covered by these bounding boxes
		$result = $logic->getRepeatersByMultiBounds($boxes, $band);
		foreach($result as $repeater)
		{
			$rptrId = $repeater->repeater_id;

			// If we have already used this repeater, skip it
			if (isset($usedRepeaters[$rptrId]))
				continue;

			// Flag this repeater as used
			$usedRepeaters[$rptrId] = true;

			$repeaters[$i] = array(
				'id' => $i,
				'location' => $repeater->getLocationPoint()->getCoords(),
				'coverage' => $repeater->getCoveragePolygon()->getCoords(),
				'band' => $repeater->band,
				'output' => $repeater->output_freq,
				'input' => $repeater->input_freq
			);
			$i++;
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