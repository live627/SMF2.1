<?php

namespace PHPTDD;

class AdminSearchTest extends BaseTestCase
{
	public function testSearchSettings()
	{
		global $context, $scripturl;

		foreach ($this->settingsProvider() as list ($url, $name))
		{
			$this->assertStringContainsString($scripturl, $url);
			$this->assertStringContainsString($context['search_term'], $name);
		}
	}

	public function settingsProvider()
	{
		global $context, $sourcedir, $user_info;

		loadLanguage('Admin');
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
