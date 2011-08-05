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
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 * @param array $polyline - the list of points to find repeaters along
	 */
	public function actionGetRepeaters(array $polyline)
	{
		$logic = new SearchLogic();
		$result = $logic->getRepeatersAlongRoute($polyline);

		$repeaters = array();
		foreach($result as $key => $repeater)
		{
			$repeaters[$key]['id'] = $key;
			$repeaters[$key]['location'] = $repeater->getLocationPoint()->getCoords();
			$repeaters[$key]['coverage'] = $repeater->getCoveragePolygon()->getCoords();
		}

		$this->setData($repeaters);
	}

	/**
	 * Log that the user has performed a search
	 * @param string $start
	 * @param string $end
	 * @param array $extra
	 */
	public function actionLogSearch($start, $end, array $extra=null)
	{
		// Clean data
		$start = trim($start);
		$end = trim($end);

		// Validation
		if (empty($start))
			throw new CHttpException(400, "Start cannot be empty");
		if (empty($end))
			throw new CHttpException(400, "End cannot be empty");

		$logic = new SearchLogic();
		$logic->saveRecentSearch($start, $end, $extra);
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
			$this->setAutoRender(false);
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

}