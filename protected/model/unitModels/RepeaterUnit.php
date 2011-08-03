<?php

/**
 * Class to handle all of our interactions with the Repeater tables
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class RepeaterUnit extends AbstractUnit
{
	/**
	 * Get all the repeaters that are near (intersect with) a spacial object
	 * @param SpacialAbstract $spacial
	 * @param array $criteria - any additional filter criteria
	 * @return array(Repeaters)
	 */
	public function getNearSpatial(SpacialAbstract $spacial, array $criteria=null)
	{

	}
}
