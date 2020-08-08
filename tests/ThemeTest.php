<?php

namespace PHPTDD;

class ThemeTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Package.php');
		require_once($sourcedir . '/Subs-Themes.php');
		require_once($sourcedir . '/Themes.php');
	}

	public function testThemeInfo()
	{
		$xml_data = get_theme_info(__DIR__ . '/boxes');
		$this->assertEquals('1.0', $xml_data['version']);
		$this->assertStringContainsString('fake/dir', $xml_data['images_url']);
	}

	public function testGetSingleTheme()
	{
		$single = get_single_theme(1);
		$this->assertIsInt($single['id']);
		$this->assertEquals(1, $single['id']);
		$this->assertStringContainsString('Themes/default', $single['theme_url']);
		$this->assertStringContainsString('Themes/default/images', $single['images_url']);
		$this->assertStringContainsString('Themes/default', strtr($single['theme_dir'], '\\', '/'));
		$this->assertTrue($single['valid_path']);
		$this->assertTrue($single['known']);
		$this->assertTrue($single['enable']);
		$this->assertStringContainsString('Default', $single['name']);
	}

	public function testInstallCopy()
	{
	global $sourcedir, $txt, $context, $boarddir, $boardurl;
	global $themedir, $themeurl, $smcFunc;

	$themedir = $boarddir . '/Themes';
	$themeurl = $boardurl . '/Themes';

		$_REQUEST['copy'] = '123';
		$installed = InstallCopy();
		unset($_REQUEST['copy']);
		$this->assertIsInt($installed['id']);
		$this->assertStringContainsString('Themes/123', $installed['theme_url']);
		$this->assertStringContainsString('Themes/123/images', $installed['images_url']);
		$this->assertStringContainsString('Themes/123', strtr($installed['theme_dir'], '\\', '/'));
		$this->assertStringContainsString('123', $installed['name']);

		$single = get_single_theme($installed['id']);
		$this->assertEquals($installed['id'], $single['id']);
		$this->assertEquals('123', $single['name']);
	}

	public function testRemoveTheme()
	{
		global $context;

		get_all_themes();
		$single = current(array_filter($context['themes'], function($th)
		{
			return $th['name'] == '123';
		}));
		$this->assertEquals('123', $single['name']);
		remove_theme($single['id']);
		remove_dir($single['theme_dir']);
	}

	public function testGetAllThemes()
	{
		global $context;

		get_all_themes();
		$this->assertArrayHasKey(1, $context['themes']);
		$this->assertIsInt($context['themes'][1]['id']);
		$this->assertEquals(1, $context['themes'][1]['id']);
		$this->assertStringContainsString('Themes/default', $context['themes'][1]['theme_url']);
		$this->assertStringContainsString('Themes/default/images', $context['themes'][1]['images_url']);
		$this->assertStringContainsString('Themes/default', strtr($context['themes'][1]['theme_dir'], '\\', '/'));
		$this->assertTrue($context['themes'][1]['valid_path']);
		$this->assertTrue($context['themes'][1]['known']);
		$this->assertTrue($context['themes'][1]['enable']);
		$this->assertStringContainsString('Default', $context['themes'][1]['name']);
	}

	public function testGetInstalledThemes()
	{
		global $context;

		get_installed_themes();
		$this->assertArrayHasKey(1, $context['themes']);
		$this->assertIsInt($context['themes'][1]['id']);
		$this->assertEquals(1, $context['themes'][1]['id']);
		$this->assertStringContainsString('Themes/default', $context['themes'][1]['theme_url']);
		$this->assertStringContainsString('Themes/default/images', $context['themes'][1]['images_url']);
		$this->assertStringContainsString('Themes/default', strtr($context['themes'][1]['theme_dir'], '\\', '/'));
		$this->assertTrue($context['themes'][1]['valid_path']);
		$this->assertTrue($context['themes'][1]['known']);
		$this->assertTrue($context['themes'][1]['enable']);
		$this->assertStringContainsString('Default', $context['themes'][1]['name']);
	}
}