<?php

/**
 * This class is used to import information into the system
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class ImportCommand extends CConsoleCommand
{
	/**
	 * Whether or not we want to be verbose about what we are doing
	 * @var boolean
	 */
	public $verbose = false;

	/**
	 * The radius, in miles, to use for generating our coverage
	 * @var integer
	 */
	public $coverageRadius = 10;

	/**
	 * The number of points to generate for our automatic coverage
	 * @var integer
	 */
	public $coveragePoints = 10;

	/**
	 * The cache for our region data so we aren't continually hitting the database for it
	 * @var array
	 */
	protected $regions = array();

	/**
	 * Print an eror message
	 * @param string $message - the message to print
	 */
	protected function errorMessage($message)
	{
		fputs(STDERR, "{$message}\n");
	}

	/**
	 * Get the region information from that database. If the region doesn't exist,
	 * it is created
	 * @param string $state - the state for the region
	 * @param string $contry - the country for the region
	 * @param string $area - the area description of the region
	 * @return string - the id for the region
	 */
	protected function getRegion($state, $country, $area)
	{
		if (!isset($this->regions[$state][$country][$area]))
		{
			$criteria = new CDbCriteria();
			$criteria->addColumnCondition(array(
				'state' => $state,
				'country' => $country,
				'area_name' => $area,
			));

			$region = RepeaterRegions::model()->find($criteria);

			// Region was not found
			if ($region === null)
			{
				// Create the region
				$region = new RepeaterRegions();
				$region->state = $state;
				$region->country = $country;
				$region->area_name = $area;
				if (!$region->save())
					return false; // Issues...
			}

			// Cache the region id
			$this->regions[$state][$country][$area] = $region->region_id;
		}

		// Return our data
		return $this->regions[$state][$country][$area];
	}

	/**
	 * Get a list of points around the radius of a given lat/lon
	 * @param float $lat - the center latitude
	 * @param float $lon - the center longitude
	 * @param float $radius - the radius in miles
	 * @param integer $number - the number of points we want
	 * @return array( array('lat'=>..,'lon'=>..), .. ) - the list of point
	 */
	protected function pointsOnRadius($lat, $lon, $radius, $number)
	{
		// in utah, each degree of latitude ~ 69 miles
		$latMile = 0.0144927536231884;
		// in utah, each degree of longitude ~ 53 miles
		$lonMile = 0.0188679245283019;

		$points = array();

		$angle = 360 / $number;
		for ($i=0; $i<$number; $i++)
		{
			$rot = $angle * $i * M_PI / 180; // Our current rotation in degrees
			// Get our location for this point
			$points[] = array(
				'lat' => $radius * $latMile * sin($rot) + $lat,
				'lon' => $radius * $lonMile * cos($rot) + $lon,
			);
		}

		return $points;
	}
	
	/**
	 * Convert a single point to a MySQL-formatted point
	 * @param array $points - the point
	 * @reutrn string - the MySQL-formatted point
	 */
	protected function pointsToPoint(array $points)
	{
		return "PointFromText('POINT({$points['lat']} {$points['lon']})')";
	}

	/**
	 * Convert an array of points to a MySQL-formatted polygon
	 * @param array $points - the list of points
	 * @return string - the MySQL-formatted polygon
	 */
	protected function pointsToPolygon(array $points)
	{
		$polygon = "PolyFromText('POLYGON(( ";

		if (!empty($points))
		{
			foreach ($points as $point)
				$polygon .= "{$point['lat']} {$point['lon']}, ";

			// Connect it back to itself
			$point = reset($points);
			$polygon .= "{$point['lat']} {$point['lon']}";
		}

		$polygon .= " ))')";
		return $polygon;
	}

	/**
	 * The main action, don't do anything
	 */
	public function actionIndex()
	{
		$this->getCommandRunner()->createCommand('help')->run(array('import'));
	}

	/**
	 * Import repeaters from the utahcvs repeaters file
	 * @param string $inputFile
	 */
	public function actionUtahvhfs($inputFile=null)
	{
		if (empty($inputFile))
			$inputFile = 'http://utahvhfs.org/rptrraw.txt';

		$csvHandle = fopen($inputFile, 'r');
		if ($csvHandle === false)
		{
			$this->errorMessage("Could not open file: $inputFile");
			return;
		}

		// Read the headers
		$headers = fgetcsv($csvHandle);
		if ($headers === false)
		{
			$this->errorMessage("Unable to read CSV headers from: $inputFile");
			return;
		}

		// Lower-case our headers and make our lookup index
		$headers = array_map('strtolower', $headers);
		$index = array_flip($headers);

		// Get each entry in the file and process it
		while (($rptrData = fgetcsv($csvHandle)) !== false)
		{
			// Trim all the data
			$rptrData = array_map('trim', $rptrData);

			// Only care about 2m and 70cm bands
			$band = $rptrData[$index['band']];
			if ($band != '144' && $band != '440')
				continue;

			// Our geo data
			$lat = $rptrData[$index['latitude']];
			$lon = $rptrData[$index['longitude']];

			// Only care about active repeaters
			if ($rptrData[$index['active']] != 'Y' || empty($rptrData[$index['input']]) || empty($lat) || empty($lon))
				continue;

			// Get the region id for this repeater
			$region_id = $this->getRegion($rptrData[$index['state']], 'US', $rptrData[$index['area']]);
			if (empty($region_id))
			{
				$this->errorMessage("Unable to find region for '{$rptrData[$index['state']]}', 'US', '{$rptrData[$index['area']]}'");
				continue;
			}

			// If we are verbose
			if ($this->verbose)
				echo "Generating {$rptrData[$index['output']]} in {$rptrData[$index['area']]}: ";

			$rptr = new Repeaters();
			$rptr->band = $rptrData[$index['band']];
			$rptr->output_freq = $rptrData[$index['output']];
			$rptr->input_freq = $rptrData[$index['input']];
			$rptr->region_id = $region_id;

			// If we have a tone
			if ($rptrData[$index['tone']] == 'Y')
			{
				$ctcss_in = $rptrData[$index['ctcss_in']];
				$rptr->ctcss_in = empty($ctcss_in) ? null : $ctcss_in;
				$ctcss_out = $rptrData[$index['ctcss_out']];
				$rptr->ctcss_out = empty($ctcss_out) ? null : $ctcss_out;
			}

			// If we have DCS
			if ($rptrData[$index['dcs']] == 'Y')
			{
				$dcs_code = $rptrData[$index['dcs_code']];
				$rptr->dcs_code = empty($dcs_code) ? null : $dcs_code;
			}

			$rptr->open = ($rptrData[$index['open']] == 'Y') ? 1 : 0;

			// Don't ask me why every entry doesn't have the right amount of keys, I'm just compensating for it
			$importData = array_combine( array_slice($headers, 0, count($rptrData)), $rptrData);
			$rptr->import_data = json_encode($importData);

			// Create our geo fields
			$center = compact('lat','lon');
			$rptr->geo_location = new CDbExpression($this->pointsToPoint($center));

			$coverage = $this->pointsOnRadius($lat, $lon, $this->coverageRadius, $this->coveragePoints);
			$rptr->geo_coverage = new CDbExpression($this->pointsToPolygon($coverage));

			try
			{
				if (!$rptr->save())
				{
					$this->errorMessage("Unable to create repeater: ".var_export($rptr->getErrors(), true));
					if ($this->verbose)
						$this->errorMessage(var_export($rptr->getAttributes(), true));
				}
			}
			catch (CDbException $ex)
			{
				$this->errorMessage("Unable to create repeater: " . $ex->getMessage());
				if ($this->verbose)
					$this->errorMessage(var_export($rptr->getAttributes(), true));
			}

			if ($this->verbose)
				echo "{$rptr->repeater_id}\n";
		}
	}

	/**
	 * Get the actuall help text to make this command useful
	 */
	public function getHelp() {
		return <<<HELP
USAGE
  {$this->getCommandRunner()->getScriptName()} {$this->getName()} <action> [args]

DESCRIPTION
  This command imports information into the system from a variety of sources.

ACTIONS
  utahvhfs [--inputFile=<path to csv file>]
    import repeater information from the Utah VHF Society
    - inputFile : the path to the csv file from the Utah VHF Society
      if not specified, fetched from the web

GLOBAL ARGUMENTS
  coverageRadius : The radius, in miles, to use for generating our coverage
  coveragePoints : The number of points to generate for our automatic coverage
HELP;
	}
}
