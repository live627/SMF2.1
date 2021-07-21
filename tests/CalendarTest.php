<?php

namespace PHPTDD;

class CalendarTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Calendar.php');
		require_once($sourcedir . '/Subs-Calendar.php');
	}

	public function timeformatProvider(): array
	{
		return [
			['%b %d, %Y, %I:%M:%S %p'],
			['%b %d, %Y, %I:%M %p']
		];
	}

	/**
	 * @dataProvider timeformatProvider
	 */
	public function testCalendarPost(string $timeformat): void
	{
		global $user_info, $context;

		$user_info['time_format'] = $timeformat;
		CalendarPost();

		$this->assertRegExp('/[0-9]{2}:[0-9]{2} [A-Z]{2}/', $context['event']['start_time']);
	}

	public function dateToEnglishProvider(): array
	{
		return [
			['Enero', 'Ene', 'January', 'Jan'],
			['Febrero', 'Feb', 'February', 'Feb'],
			['Marzo', 'Mar', 'March', 'Mar'],
			['Abril', 'Abr', 'April', 'Apr'],
			['Mayo', 'May', 'May', 'May'],
			['Junio', 'Jun', 'June', 'Jun'],
			['Julio', 'Jul', 'July', 'Jul'],
			['Agosto', 'Ago', 'August', 'Aug'],
			['Septiembre', 'Sep', 'September', 'Sep'],
			['Octubre', 'Oct', 'October', 'Oct'],
			['Noviembre', 'Nov', 'November', 'Nov'],
			['Diciembre', 'Dic', 'December', 'Dec'],
		];
	}

	/**
	 * @dataProvider dateToEnglishProvider
	 */
	public function testDateToEnglish(string $test, string $test_short, string $expected, string $expected_short): void
	{
		global $txt, $context;

		$context['user']['language'] = 'x';
		$txt['months_titles'] = [
			1 => 'Enero',
			'Febrero',
			'Marzo',
			'Abril',
			'Mayo',
			'Junio',
			'Julio',
			'Agosto',
			'Septiembre',
			'Octubre',
			'Noviembre',
			'Diciembre',
		];
		$txt['months_short'] =
			[1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
		$this->assertEquals($expected, convertDateToEnglish($test));
		$this->assertEquals($expected_short, convertDateToEnglish($test_short));
		$context['user']['language'] = 'english';
	}

	public function testNewEventProperties(): void
	{
		$_POST = ['year' => '2021', 'month' => '04', 'day' => '02', 'hour' => '03', 'minute' => '47', 'second' => '18'];
		$eventProperties = getNewEventDatetimes();

		$this->assertEquals('2021', $eventProperties['year']);
		$this->assertEquals('4', $eventProperties['month']);
		$this->assertEquals('2', $eventProperties['day']);
		$this->assertEquals('3', $eventProperties['hour']);
		$this->assertEquals('47', $eventProperties['minute']);
		$this->assertEquals('18', $eventProperties['second']);
		$this->assertEquals('2021-04-02', $eventProperties['start_date']);
		$this->assertEquals('Apr 02, 2021', $eventProperties['start_date_local']);
		$this->assertEquals('Apr 02, 2021', $eventProperties['start_date_orig']);
		$this->assertEquals('03:47:18', $eventProperties['start_time']);
		$this->assertEquals('03:47 AM', $eventProperties['start_time_local']);
		$this->assertEquals('03:47 AM', $eventProperties['start_time_orig']);
		$this->assertEquals('1617335238', $eventProperties['start_timestamp']);
		$this->assertEquals('2021-04-02 03:47:18', $eventProperties['start_datetime']);
		$this->assertEquals('2021-04-02T03:47:18+00:00', $eventProperties['start_iso_gmdate']);
		$this->assertEquals('2021', $eventProperties['end_year']);
		$this->assertEquals('4', $eventProperties['end_month']);
		$this->assertEquals('2', $eventProperties['end_day']);
		$this->assertEquals('4', $eventProperties['end_hour']);
		$this->assertEquals('47', $eventProperties['end_minute']);
		$this->assertEquals('18', $eventProperties['end_second']);
		$this->assertEquals('2021-04-02', $eventProperties['end_date']);
		$this->assertEquals('Apr 02, 2021', $eventProperties['end_date_local']);
		$this->assertEquals('Apr 02, 2021', $eventProperties['end_date_orig']);
		$this->assertEquals('04:47:18', $eventProperties['end_time']);
		$this->assertEquals('04:47 AM', $eventProperties['end_time_local']);
		$this->assertEquals('04:47 AM', $eventProperties['end_time_orig']);
		$this->assertEquals('1617338838', $eventProperties['end_timestamp']);
		$this->assertEquals('2021-04-02 03:47:18', $eventProperties['end_datetime']);
		$this->assertEquals('2021-04-02T04:47:18+00:00', $eventProperties['end_iso_gmdate']);
		$this->assertFalse($eventProperties['allday']);
		$this->assertEquals('UTC', $eventProperties['tz']);
		$this->assertEquals('UTC', $eventProperties['tz_abbrev']);
		$this->assertEquals('1', $eventProperties['span']);
	}

	public function gridProvider(): array
	{
		global $modSettings;

		return [
			[
				[
					'start_day' => 0,
					'show_birthdays' => in_array($modSettings['cal_showbdays'], [1, 2]),
					'show_events' => in_array($modSettings['cal_showevents'], [1, 2]),
					'show_holidays' => in_array($modSettings['cal_showholidays'], [1, 2]),
					'show_week_num' => true,
					'short_day_titles' => !empty($modSettings['cal_short_days']),
					'short_month_titles' => !empty($modSettings['cal_short_months']),
					'show_next_prev' => !empty($modSettings['cal_prev_next_links']),
					'show_week_links' => isset($modSettings['cal_week_links']) ? $modSettings['cal_week_links'] : 0,
				],
			],
		];
	}

	/**
	 * @dataProvider gridProvider
	 */
	public function testGrid(array $calendarOptions): void
	{
		$calendarGrid = getCalendarGrid('2021-04-02', $calendarOptions);
		$this->assertCount(5, $calendarGrid['weeks']);
		$this->assertCount(7, $calendarGrid['weeks'][0]['days']);
		$this->assertEquals('2021', $calendarGrid['previous_calendar']['year']);
		$this->assertEquals('3', $calendarGrid['previous_calendar']['month']);
		$this->assertEquals('02', $calendarGrid['previous_calendar']['day']);
		$this->assertEquals('2021-03-02', $calendarGrid['previous_calendar']['start_date']);
		$this->assertEquals('2021', $calendarGrid['next_calendar']['year']);
		$this->assertEquals('5', $calendarGrid['next_calendar']['month']);
		$this->assertEquals('02', $calendarGrid['next_calendar']['day']);
		$this->assertEquals('2021-05-02', $calendarGrid['next_calendar']['start_date']);
		$this->assertEquals('Apr 02, 2021', $calendarGrid['start_date']);
	}
}