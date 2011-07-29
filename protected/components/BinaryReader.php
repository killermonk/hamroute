<?php

/**
 * Class to handle reading binary data
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class BinaryReader
{
	/**
	 * The binary data to be read
	 * @var binary
	 */
	private $data = null;

	/**
	 * Track our offset on the data
	 * @var integer
	 */
	private $offset = 0;

	/**
	 * Our flag for whether to use little/big endian (null for system default)
	 * @var integer
	 */
	private $endian = null;

	/**
	 * Initialize the binary reader
	 * @param binary $data
	 */
    public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Set the reader to use big endian format
	 */
	public function setBigEndian()
	{
		$this->endian = 1;
	}

	/**
	 * Set the reader to use little endian format
	 */
	public function setLittleEndian()
	{
		$this->endian = 0;
	}

	/**
	 * Set the read to use the default system endian format
	 */
	public function setDefaultEndian()
	{
		$this->endian = null;
	}

	/**
	 * Format the data for return to the user
	 * @param mixed $data
	 * @return false|array - false failure, array values
	 */
	protected function formatResults($data)
	{
		if ($data === false)
			return false;
		return array_values($data);
	}

	/**
	 * Read a number of bytes from the data string and advance our internal pointer
	 * @param integer $dataSize
	 * @return binary
	 */
	protected function read($dataSize)
	{
		$buffer = substr($this->data, $this->offset, $dataSize);
		// TODO detect invalid data if strlen($buffer) < $dataSize

		// Advance internal offset
		$this->offset += $dataSize;
		return $buffer;
	}

	/**
	 * Read a char from the binary data and remove it
	 * @param integer $count
	 * @return array(char)
	 */
	public function char($count = 1)
	{
		$buffer = $this->read($count);

		$f = 'c'.(int)$count;
		$char = unpack($f, $buffer);

		return $this->formatResults($char);
	}

	/**
	 * Read a long from the binary data and remove it
	 * @param integer $count
	 * @return array(unsigned long)
	 */
	public function ulong($count = 1)
	{
		// Get the format we want
		if ($this->endian == 0)
			$f = 'V';
		elseif ($this->endian == 1)
			$f = 'N';
		else
			$f = 'L';

		$bufferSize = 4*$count;
		$buffer = $this->read($bufferSize);

		$f .= (int)$count;
		$ulong = unpack($f, $buffer);
		
		return $this->formatResults($ulong);
	}

	/**
	 * Read a double from the binary data and remove it
	 * @param integer $count
	 * @return array(double)
	 */
	public function double($count = 1)
	{
		$bufferSize = 8*$count;
		$buffer = $this->read($bufferSize);

		$f = 'd'.(int)$count;
		$double = unpack($f, $buffer);

		return $this->formatResults($double);
	}
}
