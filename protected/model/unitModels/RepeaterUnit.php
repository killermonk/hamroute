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
	 * @param SpatialAbstract|array $spatial
	 * @param array $criteria - any additional filter criteria
	 * @return array(Repeaters)
	 */
	public function getNearSpatial($spatial, array $criteria=null)
	{
		// Initialize to check that the repeater covers a given region
		$model = Repeaters::model();
		if (is_array($spatial))
		{
			// Only find distinct repeaters
			$model->getDbCriteria()->distinct = true;
			// Add all the spatial objects in
			foreach ($spatial as $obj)
				$model->covers($obj, false);
		}
		else
			$model->covers($spatial);

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
		$loc_col = $model->getTableAlias(true) . '.geo_location';
		return $model->findAll(array(
			'order' => "(X({$loc_col})*360 + Y({$loc_col})) DESC",
		));
	}
}
