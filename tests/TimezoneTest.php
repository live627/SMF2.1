<?php

namespace PHPTDD;

class TimezoneTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Timezones.php');
	}

	public function testMetazones(): void
	{
		$tzid_metazones = get_tzid_metazones();
		$this->assertArrayHasKey('America/Phoenix', $tzid_metazones);
		$this->assertContains('North_America_Mountain', $tzid_metazones);
	}

	public function testGroupedTimezones(): void
	{
		$country_tzids = get_sorted_tzids_for_country('??');
		$this->assertContains('UTC', $country_tzids);
	}

	public function testGroupedTimezones2(): void
	{
		$country_tzids = get_sorted_tzids_for_country('wot');
		$this->assertIsArray($country_tzids);
		$this->assertEmpty($country_tzids);
	}

	public function countryCodesProvider(): array
	{
		return [
			['us,gb', 'US,GB', ['US', 'GB']],
			['usa,gbr', '', []],
			['840,826', '', []]
		];
	}

	/**
	 * @dataProvider countryCodesProvider
	 */
	public function testValidCountryCodes(string $test, string $expected, array $expected2): void
	{
		$result = validate_iso_country_codes($test, true);
		$this->assertEquals($expected, $result);
		$result = validate_iso_country_codes($test);
		$this->assertEquals($expected2, $result);
	}
}