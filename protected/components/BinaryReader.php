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
	 * Read a char from the binary data and remove it
	 * @param integer $count
	 * @return array(char)
	 */
	public function char($count = 1)
	{
		$f = 'c'.(int)$count;
		$char = unpack($f, $this->data);

		$this->data = substr($this->data, 1*$count);

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

		$f .= (int)$count;
		$ulong = unpack($f, $this->data);

		$this->data = substr($this->data, 4*$count);
		
		return $this->formatResults($ulong);
	}

	/**
	 * Read a double from the binary data and remove it
	 * @param integer $count
	 * @return array(double)
	 */
	public function double($count = 1)
	{
		$f = 'd'.(int)$count;
		$double = unpack($f, $this->data);

		$this->data = substr($this->data, 8*$count);

		return $this->formatResults($double);
	}
}
