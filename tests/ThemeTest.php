<?php

namespace PHPTDD;

use Exception;

class ThemeTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $boarddir, $boardurl, $sourcedir, $themedir, $themeurl;

		$themedir = $boarddir . '/Themes';
		$themeurl = $boardurl . '/Themes';

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
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}themes
			WHERE id_theme != 1');
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

	public function testGetSingleThemeNotExists()
	{
		$single = get_single_theme(11);
		$this->assertCount(3, $single);
		$this->assertFalse($single['known']);
		$this->assertFalse($single['enable']);
	}

	public function testInstallCopy()
	{
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
		$this->assertStringContainsString('Themes/123', $single['theme_url']);
	}

	/**
	 * @depends testInstallCopy
	 */
	public function testInstallDuplicateCopy()
	{
		global $context;

		try
		{
			$_REQUEST['copy'] = '123';
			InstallCopy();
		}
		catch (Exception $e)
		{
			$this->assertInstanceOf(Exception::class, $e);
			$this->assertEquals('theme_install_already_dir', $e->getMessage());
		}
		finally
		{
			unset($_REQUEST['copy']);
			get_all_themes();
			$this->assertCount(2, $context['themes']);
		}
	}

	/**
	 * @depends testInstallCopy
	 */
	public function testInstallCopyWithNoName()
	{
		global $context;

		try
		{
			InstallCopy();
		}
		catch (Exception $e)
		{
			$this->assertInstanceOf(Exception::class, $e);
			$this->assertEquals('theme_install_error_title', $e->getMessage());
		}
		finally
		{
			get_all_themes();
			$this->assertCount(2, $context['themes']);
		}
	}

	/**
	 * @depends testThemeInfo
	 * @depends testInstallCopy
	 */
	public function testInstallDependentTheme()
	{
		global $context, $themedir, $themeurl;

		$installed_id = theme_install(
			array(
				'theme_dir' => $themedir . '/boxes',
				'theme_url' => $themeurl . '/boxes',
				'name' => 'boxes',
			) + get_theme_info(__DIR__ . '/boxes')
		);

		$single = get_single_theme($installed_id);
		$this->assertEquals($installed_id, $single['id']);
		$this->assertEquals('boxes', $single['name']);

		get_all_themes();
		$this->assertCount(3, $context['themes']);
	}

	/**
	 * @depends testInstallDependentTheme
	 */
	public function testUpdateDependentTheme()
	{
		global $context, $themedir, $themeurl;

		$installed_id = theme_install(array_merge(
			array(
				'theme_dir' => $themedir . '/boxes',
				'theme_url' => $themeurl . '/boxes',
				'name' => 'boxes',
			), get_theme_info(__DIR__ . '/boxes'), array('version' => '1.1')
		));

		$single = get_single_theme($installed_id);
		$this->assertEquals($installed_id, $single['id']);
		$this->assertEquals('boxes', $single['name']);

		get_all_themes();
		$this->assertCount(3, $context['themes']);
	}

	/**
	 * @depends testInstallDependentTheme
	 */
	public function testRemoveDependentTheme()
	{
		global $context;

		get_all_themes();
		$single = current(array_filter($context['themes'], function($th)
		{
			return $th['name'] == 'boxes';
		}));
		$this->assertEquals('boxes', $single['name']);
		remove_theme($single['id']);

		get_all_themes();
		$this->assertCount(2, $context['themes']);
	}

	/**
	 * @depends testInstallCopy
	 */
	public function testRemoveTheme()
	{
		global $context;

		get_all_themes();
		$this->assertCount(2, $context['themes']);
		$single = current(array_filter($context['themes'], function($th)
		{
			return $th['name'] == '123';
		}));
		$this->assertEquals('123', $single['name']);
		remove_theme($single['id']);
		remove_dir($single['theme_dir']);

		get_all_themes();
		$this->assertCount(1, $context['themes']);
	}

	public function testGetAllThemes()
	{
		global $context;

		get_all_themes();
		$this->assertCount(1, $context['themes']);
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
		$this->assertCount(1, $context['themes']);
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