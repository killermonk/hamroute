<?php

/**
 * This command is used for configuring the web application
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class ConfigureCommand extends CConsoleCommand
{
	/**
	 * Whether or not we want to be verbose about what we are doing
	 * @var boolean
	 */
	public $verbose = false;

	/**
	 * The default action will be to configure all entries
	 * @var string
	 */
	public $defaultAction = 'all';

	/**
	 * Variable to cache to super user password
	 * @var CDbConnector
	 */
	protected $superCon = null;

	/**
	 * Get a super user connection to the database
	 * @param string $user
	 * @param string $database
	 * @return CDbConnector
	 */
	protected function getSuperUserConnection($user, $database=null)
	{
		if ($this->superCon == null)
		{
			// Read the super user password from the user
			echo "Super user password (not hidden): ";
			$password = trim(fgetss(STDIN));

			$dbHost = Yii::app()->params['dbHost'];
			$this->superCon = new CDbConnection("mysql:host={$dbHost}", $user, $password);
		}

		if (!empty($database))
			$this->superCon->createCommand('USE '.mysql_escape_string($database))->execute();

		return $this->superCon;
	}

	/**
	 * The main action. Configure everything
	 * @param string $user - the sueper user username
	 */
	public function actionAll($user='root')
	{
		// configure everything
		$this->actionDatabase($user, true, true);
	}

	/**
	 * Create the database, and - optionally - the default tables and users as well
	 * @param string $user - the super user username
	 * @param boolean $tables - true to create the tables
	 * @param boolean $users - true to create the users
	 */
	public function actionDatabase($user='root', $tables=true, $users=true)
	{
		// Read variables we need from the app
		$params = Yii::app()->params;

		try
		{
			// Manual connection to mysql
			$db = $this->getSuperUserConnection($user);

			// Create our Database
			$dbName = mysql_escape_string($params['dbName']);
			$db->createCommand("CREATE DATABASE IF NOT EXISTS $dbName")->execute();

			// Create the user for the database
			$dbUser = mysql_escape_string($params['dbUser']);
			$dbHost = $params['dbHost'] == 'localhost' ? 'localhost' : '%';
			$dbPass = mysql_escape_string($params['dbPass']);
			$db->createCommand("GRANT INSERT, SELECT, UPDATE, DELETE ON {$dbName}.* TO '$dbUser'@'$dbHost' IDENTIFIED BY '$dbPass'")->execute();

			// If we want to create our tables, do so
			if ($tables)
			{
				$this->actionTables($users);
			}
			// If we want to create our default user, do so
			elseif ($users)
			{
				$this->actionUsers();
			}
		}
		catch (CDbException $e)
		{
			fputs(STDERR, "\nUnable to configure the database completely: {$e->getMessage()}\n");
		}
	}

	/**
	 * Create all of our tables for the database (destroys all existing data)
	 * @param string $user - the name of the super user
	 * @param boolean $users - true to create our default users as well
	 */
	public function actionTables($user='root', $users=true)
	{
		// Confirm
		echo "This will erase all data. Are you sure? (y/n): ";
		$input = trim(fgetss(STDIN)); // answer plus newline
		if (strtolower($input) != 'y') return;
		echo "Are you sure you're sure? (y/n): ";
		$input = trim(fgetss(STDIN)); // answer plus newline
		if (strtolower($input) != 'y') return;

		// Get our data from the file
		$sqlFile = realpath( Yii::app()->basePath.'/data/schema.mysql.sql' );
		if (empty($sqlFile))
		{
			fputs(STDERR, "\nUnable to configure the tables completely: SQL file does not exist");
			return;
		}

		// Create all of our tables
		try
		{
			$db = $this->getSuperUserConnection($user, Yii::app()->params['dbName']);

			// Turn off our foreign key constraints
			$db->createCommand("SET foreign_key_checks = 0")->execute();

			$sql = file_get_contents($sqlFile);
			$queries = explode(';', $sql);

			foreach ($queries as $q)
			{
				$q = trim($q);
				if (!empty($q))
					$db->createCommand($q)->execute();
			}
		}
		catch (CDbException $e)
		{
			fputs(STDERR, "\nUnable to configure the tables completely: {$e->getMessage()}\n");
		}

		$db->createCommand("SET foreign_key_checks = 1")->execute();
	}

	/**
	 * Create an administrative user in the system for testing everything out
	 */
	public function actionUsers()
	{
		// We don't have administrative users yet, so this just creates a new user
		// This allows us to skip the registration process

		// TODO implement
	}

	/**
	 * Get the actuall help text to make this command useful
	 */
	public function getHelp() {
		return <<<HELP
USAGE
  {$this->getCommandRunner()->getScriptName()} {$this->getName()} <action> [args]

DESCRIPTION
  This command configures the application's back-end so that it will be
  useable. It is mainly used for the initial set up, but can be used
  multiple times in development to get a clean copy of the database, etc.

ACTIONS
  all [--user=root]
    perform all configuration options.
    - user : the database super user to use

  database [--user=root] [--tables=true] [--users=true]
    create and configure the database
    - user : the database super user to use
    - tables : true to also configure the tables
    - users : true to also configure an administrative user

  tables [--user=root] [--users=true]
    create and configure the database tables (destroying all current data)
    - user : the database super user to use
    - users : true to also configure an administrative user

  users
    create an administrative user
HELP;
	}
}
