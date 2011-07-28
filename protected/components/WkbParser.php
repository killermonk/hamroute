<?php

/**
 * Wkb Parser is a Parser for the MySQL Well-Known Binary format
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class WkbParser
{
	/**
	 * Parse binary data and return our Wkb class which it represents
	 * @param binary $binary
	 * @return WkbAbstract
	 */
	public static function parse($binary)
	{
		// Import our Spacial classes
		Yii::import('application.components.spacial.SpacialAbstract');

		$headerStr = 'Lbuffer/Cendian';

		$info = unpack($headerStr, $binary);
		$littleEndian = ($info['endian'] == 1);

		// Get all the rest of the information from the string
		$data = substr($binary, 5);
		$typeUnpack = ($littleEndian) ? 'V' : 'N';
		$info = unpack("{$typeUnpack}type", $data);

		/* @@ */
		var_dump('BinInfo', $info);
		var_dump('BinData', bin2hex($data));
		/* ## */
		$obj = SpacialAbstract::createFromType($info['type']);

		$pointData = substr($data, 4);
		var_dump('PointData', bin2hex($pointData));

		$obj->parseWKB($pointData, $littleEndian);

		return $obj;
	}
}
