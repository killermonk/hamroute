<?php

/**
 * Class to handle all of our interactions with the Repeater tables
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class RepeaterUnit extends AbstractUnit
{
	/**
	 * Get all the repeaters that are near (intersect with) a Spatial object
	 * @param SpatialAbstract $spatial
	 * @param array $criteria - any additional filter criteria
	 * @return array(Repeaters)
	 */
	public function getNearSpatial(SpatialAbstract $spatial, array $criteria=null)
	{
		// Initialize to check that the repeater covers a given region
		$model = Repeaters::model()->covers($spatial);

		// Add the rest of our criteria
		if (!empty($criteria))
		{
			$conditions = array();
			if (isset($criteria['band']))
				$conditions['band'] = $criteria['band'];

			if (!empty($conditions))
			{
				$dbCriteria = new CDbCriteria();
				$dbCriteria->addColumnCondition($conditions);
				$model->getDbCriteria()->mergeWith($dbCriteria);
			}
		}

		// Find all the repeaters
		return $model->findAll();
	}
}
