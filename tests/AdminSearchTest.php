<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class AdminSearchTest extends TestCase
{
	public function testSearchSettings(): void
	{
		global $context, $scripturl;

		global $context, $sourcedir, $user_info;

		loadLanguage('Admin');
		$context['search_term'] = 'enable';
		require_once __DIR__ . '/../Sources/Admin.php';
		$context['admin_menu_name'] = 'admin_menu';
		$context['admin_menu']['sections'] = [];
		AdminSearchInternal();
		$results = array_filter(
			array_map(
				fn($result) => [$result['url'], strtolower($search_result['name'])],
				$context['search_results']
			),
			fn($result) => stripos($result[1], $context['search_term']) !== false
		);
		foreach ($results as [$url, $name])
		{
			$this->assertStringContainsString($scripturl, $url);
			$this->assertStringContainsString($context['search_term'], $name);
		}
	}
}
