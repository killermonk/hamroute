<?php

/**
 * Class to handle all the generic functionality and to define a common interface for the logical units
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class AbstractUnit
{
	/**
	 * Holds the current transaction that is executing. Only one transaction can be
	 * running at a time, but it doesn't hurt to open up multiple transactions
	 * @var CDbTransaction
	 */
	protected static $currentTransaction = null;

	/**
	 * Begin a transaction (know your tables, this only works with InnoDB)
	 */
	public static function beginTransaction()
	{
		if (self::$currentTransaction === null)
			self::$currentTransaction = Yii::app()->db->beginTransaction();
	}

	/**
	 * Commit a transaction (know your tables, this only works with InnoDB)
	 *   If no transaction is currently in progress, this is ignored
	 */
	public static function commitTransaction()
	{
		if (self::$currentTransaction !== null)
		{
			self::$currentTransaction->commit();
			self::$currentTransaction = null;
		}
	}

	/**
	 * Rollback a transaction (know your tables, this only works with InnoDB)
	 *   If no transaction is currently in progress, this is ignored
	 */
	public static function rollbackTransaction()
	{
		if (self::$currentTransaction !== null)
		{
			self::$currentTransaction->rollback();
			self::$currentTransaction = null;
		}
	}
}
