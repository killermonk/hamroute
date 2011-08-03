<?php

/**
 * Class to handle any generic functionality and defining the common interface for all business logic models
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class AbstractLogic
{
	/**
	 * Start a transaction (know your tables, this only works with InnoDB)
	 */
	protected static function beginTransaction()
	{
		AbstractUnit::beginTransaction();
	}

	/**
	 * Commit a transaction (know your tables, this only works with InnoDB)
	 */
	protected static function commitTransaction()
	{
		AbstractUnit::commitTransaction();
	}

	/**
	 * Rollback a transaction (know your tables, this only works with InnoDB)
	 */
	protected static function rollbackTransaction()
	{
		AbstractUnit::rollbackTransaction();
	}
}
