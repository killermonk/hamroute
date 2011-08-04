<?php

class AjaxController extends Controller
{

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionGetRepeaters()
	{
		$coords = $_POST['polyline'];
		$logic = new SearchLogic();

		$result = $logic->getRepeatersAlongRoute($coords);
		$repeaters = array();

		/*
		 * Legacy code, left for reference
		Yii::import('application.components.spatial.SpatialAbstract');
		$lineObj = SpatialAbstract::createFromType(SpatialAbstract::LINE_STRING);

		$coords = $_POST['polyline'];
		foreach ($coords as $coord)
			$lineObj->addCoord($coord);

		//$polyLine = "GeomFromText('LINESTRING(" .str_replace('|', ',', str_replace(array('(', ')', ','), '', $_POST["polyline"])) . ")')";
		$polyLine = "GeomFromText('" . $lineObj->toWKT() . "')";
		$result = Yii::app()->db->createCommand()
			->select('repeater_id')
			->from('repeaters')
			->where("MBRIntersects({$polyLine},geo_coverage)")
			->queryAll();
		foreach($result as $key => $repeater) {
			$repeaterArray[] = $repeater['repeater_id'];
		}	
		$result = Repeaters::model()->findAllByPk($repeaterArray);
		 */

		foreach($result as $key => $repeater)
		{
			$repeaters[$key]['id'] = $key;
			$repeaters[$key]['location'] = WkbParser::parse($repeater->geo_location)->getCoords();
			$repeaters[$key]['coverage'] = WkbParser::parse($repeater->geo_coverage)->getCoords();
		}
		//echo json_encode($repeaters);
		echo json_encode($repeaters);
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

}