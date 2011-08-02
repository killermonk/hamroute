<?php

class AjaxController extends Controller
{

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionGetRepeaters()
	{
		$result = Repeaters::model()->findByPk(1);
		//$repeater['location'] = WkbParser::parse($result->geo_location)->getCoords();
		$repeater['location'] = WkbParser::parse($result->geo_location)->getCoords();
		$repeater['coverage'] = WkbParser::parse($result->geo_coverage)->getCoords();

		echo json_encode($repeater['location']);
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