<?php

/**
 * Test to make sure that our Well-Known Binary parser works like it should
 *
 * @author Brian Armstrong <kf7huf@gmail.com>
 */
class WkbTest extends CTestCase
{
	protected function hex2bin($data)
	{
		return pack('H*', $data);
	}

	public function testPoint()
	{
		$expectedWkt = 'POINT(40.6597 -112.2025)';
		$binary = $this->hex2bin("000000000101000000EA95B20C71544440295C8FC2F50C5CC0");
		$obj = WkbParser::parse($binary);
		$this->assertInstanceOf('SpacialPoint', $obj, "Returned object of wrong type");

		$wkt = $obj->toWKT();
		$this->assertEquals($expectedWkt, $wkt, "Failed to properly parse or format");
	}

	public function testPolygon()
	{
		// Test with multiple groups
		$expectedWkt =
		'POLYGON('.
		  '(40.6597 -112.013820755,40.7448862684 -112.049855284,40.6597 -112.013820755),'.
		  '(40.7975342777 -112.144194907,40.7975342777 -112.260805093,40.7448862684 -112.355144716,40.7975342777 -112.144194907),'.
		  '(40.6597 -112.391179245,40.5745137316 -112.355144716,40.5218657223 -112.260805093,40.6597 -112.391179245),'.
		  '(40.5218657223 -112.144194907,40.5745137316 -112.049855284,40.6597 -112.013820755,40.5218657223 -112.144194907)'.
		')';
		$binary = $this->hex2bin("0000000001030000000400000003000000EA95B20C71544440C9AE7270E2005CC03D02E96E585F4440059437D430035CC0EA95B20C71544440C9AE7270E2005CC0040000008B146C9A156644402574467D3A095CC08B146C9A156644402D44D807B1105CC03D02E96E585F44404D24E7B0BA165CC08B146C9A156644402574467D3A095CC004000000EA95B20C715444408909AC1409195CC097297CAA894944404D24E7B0BA165CC04917F97ECC4244402D44D807B1105CC0EA95B20C715444408909AC1409195CC0040000004917F97ECC4244402574467D3A095CC097297CAA89494440059437D430035CC0EA95B20C71544440C9AE7270E2005CC04917F97ECC4244402574467D3A095CC0");
		$obj = WkbParser::parse($binary);
		$this->assertInstanceOf('SpacialPolygon', $obj, "Returned object of wrong type");

		$wkt = $obj->toWKT();
		$this->assertEquals($expectedWkt, $wkt, "Failed to properly parse or format");

		// Test with single group
		$expectedWkt =
		'POLYGON('.
		  '(40.6597 -112.013820755,40.7448862684 -112.049855284,40.7975342777 -112.144194907,40.7975342777 -112.260805093,40.6597 -112.013820755)'.
		')';
		$binary = $this->hex2bin("0000000001030000000100000005000000EA95B20C71544440C9AE7270E2005CC03D02E96E585F4440059437D430035CC08B146C9A156644402574467D3A095CC08B146C9A156644402D44D807B1105CC0EA95B20C71544440C9AE7270E2005CC0");
		$obj = WkbParser::parse($binary);
		$this->assertInstanceOf('SpacialPolygon', $obj, "Returned object of wrong type");

		$wkt = $obj->toWKT();
		$this->assertEquals($expectedWkt, $wkt, "Failed to properly parse or format");
	}
}
