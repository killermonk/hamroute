<?php

/**
 * Wkb Parser is a Parser for the MySQL Well-Known Binary format
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class WkbParser
{
	/**
	 * The binary reader for this parser
	 * @var BrinaryReader
	 */
	protected $data = null;

	/**
	 * The object produced by parsing the data
	 * @var SpacialAbstract
	 */
	protected $obj = null;

	/**
	 * Parse binary data and return our Wkb class which it represents
	 * @param binary $binary - the data to parse
	 * @return SpacialAbstract
	 */
	public static function parse($binary)
	{
		$parser = new WkbParser($binary);
		return $parser->getObject();
	}

	/**
	 * Initialize the parser
	 * @param binary $data
	 */
	public function __construct($data)
	{
		$this->data = new BinaryReader($data);
	}

	/**
	 * Get the object associated with this parser
	 * @return SpacialAbstract
	 */
	public function getObject()
	{
		if ($this->obj === null)
		{
			$type = $this->readHeader();
			$this->obj = $this->parseObject($type);
		}

		return $this->obj;
	}

	/**
	 * Read the header for this object and return the object type
	 * @return integer - the type of object
	 */
	protected function readHeader()
	{
		// Our header is Big Endian
		$this->data->setBigEndian();

		$this->data->char(); // Strip off the front padding
		list($endian) = $this->data->ulong(); // Read whether we are big or little endian encoded

		// Tell the reader which binary format to use
		if ($endian == 1)
			$this->data->setLittleEndian();

		// Return the type of this element
		list($type) = $this->data->ulong();
		return $type;
	}

	/**
	 * Parse the object as an element of $type
	 * @param integer $type - the spacial type
	 * @return SpacialAbstract
	 */
	protected function parseObject($type)
	{
		// Import our Spacial classes
		Yii::import('application.components.spacial.SpacialAbstract');

		// Create our spacial class
		$obj = SpacialAbstract::createFromType($type);

		// Parse it as needed
		switch ($type)
		{
			case SpacialAbstract::POINT:
				$this->parsePoint($obj);
				break;
			case SpacialAbstract::LINE_STRING:
				$this->parseLineString($obj);
				break;
			case SpacialAbstract::POLYGON:
				$this->parsePolygon($obj);
				break;
			case SpacialAbstract::MULTI_POINT:
				$this->parseMultiPoint($obj);
				break;
			case SpacialAbstract::MULTI_LINE_STRING:
				$this->parseMultiLineString($obj);
				break;
			case SpacialAbstract::MULTI_POLYGON:
				$this->parseMultiPolygon($obj);
				break;
			case SpacialAbstract::GEOMETRY_COLLECTION:
				$this->parseGeometryCollection($obj);
				break;
		}
		
		return $obj;
	}

	/**
	 * Parse a Point
	 * @param SpacialPoint $obj
	 */
	protected function parsePoint(SpacialPoint $obj)
	{
		// Format: <point1> <point2>

		// Read and add the points
		$coords = $this->data->double(2);
		if (count($coords) != 2)
			throw new Exception("Unable to parse WKB for Points. Invalid number of coordinates (" . count($coords) ."). Must be exactly 2.");

		$obj->addCoord($coords);
	}

	protected function parseLineString($obj)
	{
		// Format: <num-points> <points1....n>

		// Read the number of points in the list
		list($numCoords) = $this->data->ulong();

		$coords = $this->data->double($numCoords*2);
		if (count($coords) != $numCoords*2)
			throw new Exception("Unable to parse WKB for Points. Invalid number of coordinates (" . count($coords) ."). Must be exactly ".($numCoords*2));

		// Add the to the line string
		for ($i=0; $i<$numCoords*2; $i+=2)
			$obj->addCoord(array($coords[$i], $coords[$i+1]));
	}

	/**
	 * Parse a Polygon
	 * @param SpacialPolygon $obj
	 */
	protected function parsePolygon(SpacialPolygon $obj)
	{
		// Format: <num-polys> <num-points-1> <points1..n> <num-points-2> <points1..n> .. <num-points-n> <points1..n>

		// Read the number of coordinate groups we have for this polygon
		list($numGroup) = $this->data->ulong();

		for ($groupNum=0; $groupNum<$numGroup; $groupNum++)
		{
			// Get the number of coordinates for this polygon and read them
			list($numCoords) = $this->data->ulong();
			$coords = $this->data->double($numCoords*2);
			if (count($coords) != $numCoords*2)
				throw new Exception("Unable to parse WKB for Points. Invalid number of coordinates in group {$groupNum} (" . count($coords) ."). Must be exactly ".($numCoords*2));

			// Add them to the polygon
			for ($i=0; $i<$numCoords*2; $i+=2)
				$obj->addCoord(array($coords[$i], $coords[$i+1], $groupNum));
		}
	}

	protected function parseMultiPoint($obj)
	{
		throw new Exception("Not Yet Implemented");
	}

	protected function parseMultiLineString($obj)
	{
		throw new Exception("Not Yet Implemented");
	}

	protected function parseMultiPolygon($obj)
	{
		throw new Exception("Not Yet Implemented");
	}

	protected function parseGeometryCollection($obj)
	{
		throw new Exception("Not Yet Implemented");
	}
}
