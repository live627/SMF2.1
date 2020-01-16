<?php

namespace PHPTDD;

class TestAdminSearch extends BaseTestCase
{
	public function __destruct()
	{
		global $user_info;

		$user_info['permissions'] = array();
	}

	/**
	 * Hacky solution to generate coverage for the internal search methods
	 * since calling the function within the data provider doesn't seem
	 * to indicate any extra coverage generation.
	 */
	public function testBeforeSearchSettings()
	{
		global $context;

		$this->settingsProvider();
		$this->assertNotEmpty($context['search_results']);
	}

	/**
	 * @dataProvider settingsProvider
	 */
	public function testSearchSettings($url, $name)
	{
		global $context, $scripturl;

		$this->assertContains($scripturl, $url);
		$this->assertContains($context['search_term'], $name);
	}

	public function settingsProvider()
	{
		global $context, $sourcedir, $user_info;

		/*
		 * Forcefully reload language files to combat PHPUnit
		 * messing up globals between tests.
		 */
		loadLanguage('Admin', 'english', true, true);
		$user_info['permissions'][] = 'admin_forum';

		$context['search_term'] = 'enable';
		require_once($sourcedir . '/Admin.php');
		$context['admin_menu_name'] = 'admin_menu';
		$context['admin_menu']['sections'] = [];
		AdminSearchInternal();

		return array_filter(
			array_map(
				function($search_result)
				{
					return array($search_result['url'], strtolower($search_result['name']));
				}, $context['search_results']
			),
			function($search_result) use ($context)
			{
				return stripos($search_result[1], $context['search_term']) !== false;
			}
		);
	}
}
