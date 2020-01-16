<?php

namespace PHPTDD;

class MenuTest extends BaseTestCase
{
	protected $test_areas;
	protected $test_options;

	protected function setUp()
	{
		global $context, $sourcedir, $user_info;

		require_once($sourcedir . '/Subs-Menu.php');

		$this->test_areas = array(
		/*	'section1' => array(
				'title' => 'One',
				'permission' => array('admin_forum'),
				'areas' => array(
					'area1' => array(
						'label' => 'Area1 Label',
						'function' => 'action_area1',
						'icon' => 'transparent.png',
						'class' => 'test_img_area1',
					),
				),
			),
			'section2' => array(
				'title' => 'Two',
				'permission' => array('admin_forum'),
				'areas' => array(
					'area2' => array(
						'label' => 'Area2 Label',
						'function' => 'action_area2',
						'icon' => 'transparent.png',
						'class' => 'test_img_area2',
							'select' => 'area3',
						'custom_url' => 'custom_url',
						'hidden' => true,
					),
					'area3' => array(
						'permission' => 'area3 permission',
						'label' => 'Area3 Label',
						'function' => 'action_area3',
						'icon' => 'transparent.png',
						'class' => 'test_img_area3',
							'subsections' => [
								'sub1' => ['Sub One', ['admin_forum']],
								'sub2' => ['Sub Two', ['admin_forum'], true],
								'sub3' => ['Sub Three', ['admin_forum'], 'enabled' => false],
								'sub4' => ['Sub Four', ['admin_forum'], 'active' => ['sub1'], 'amt' => 'c'],
							],
					),
					'area4' => array(
						'label' => 'Area4 Label',
						'function' => 'action_area4',
						'icon' => 'transparent.png',
						'class' => 'test_img_area4',
						'enabled' => false,
					),
				),
			),
			'section3' => array(
				'title' => 'Three',
				'permission' => array('admin_forum'),
				'enabled' => false,
				'areas' => array(
					'area5' => array(
						'label' => 'Area5 Label',
						'function' => 'action_area5',
						'icon' => 'transparent.png',
						'class' => 'test_img_area5',
					),
				),
			)*/
			'section1' =>
				[
					'title' => 'One',
					'permission' => ['admin_forum'],
					'areas' => [
						'area1' => [
							'label' => 'Area1 Label',
							'function' => function()
							{
							},
						],
					],
				],
			'section2' =>
				[
					'title' => 'Two',
					'permission' => ['admin_forum'],
					'areas' => [
						'area2' => [
							'label' => 'Area2 Label',
							'function' => function()
							{
							},
							'select' => 'area3',
							'hidden' => true,
						],
						'area3' => [
							'permission' => 'area3 permission',
							'label' => 'Area3 Label',
							'custom_url' => 'some url',
							'function' => function()
							{
							},
							'subsections' => [
								'sub1' => ['Sub One', ['admin_forum']],
								'sub2' => ['Sub Two', ['admin_forum'], true],
								'sub3' => ['Sub Three', ['admin_forum'], 'enabled' => false],
								'sub4' => ['Sub Four', ['admin_forum'], 'active' => ['sub1'], 'counter' => 'c'],
							],
						],
						'area4' => [
							'label' => 'Area4 Label',
							'function' => function()
							{
							},
							'enabled' => false,
						],
					],
				],
			'section3' =>
				[
					'title' => 'Three',
					'permission' => ['admin_forum'],
					'enabled' => false,
					'areas' => [
						'area5' => [
							'label' => 'Area5 Label',
							'function' => function()
							{
							},
							'icon' => 'transparent.png',
							'class' => 'admin_img_support',
						],
					],
				],
			'section4' =>
				[
					'title' => 'Four',
					'permission' => ['admin_forum'],
					'areas' => [
						'area6' => [
							'label' => 'Area6 Label',
							'function' => function()
							{
							},
						],
						'area7' => [
							'label' => 'Area7 Label',
							'function' => function()
							{
							},
						],
					],
				],
		);
		$this->test_options = array(
			'extra_url_parameters' => array('extra' => 'param')
		);
		$context['session_var'] = 'abcde';
		$context['session_id'] = '123456789';
		$context['right_to_left'] = false;
		$user_info['is_admin'] = true;
		$context['current_action'] = 'bubbh';
	}

	protected function tearDown()
	{
		global $context;

		destroyMenu('last');
		$context['max_menu_id']--;
	}

	public function addOptions($menuOptions)
	{
		$this->test_options = array_merge($this->test_options, $menuOptions);
	}
	public function testMenu()
	{
		global $context;
		createMenu($this->test_areas, $this->test_options);
		// These are long-ass arrays, y'all!
		$result = $context['menu_data_' . $context['max_menu_id']];
		$this->assertArrayNotHasKey('section3', $result['sections']);
		$this->assertCount(3, $result['sections']);
		$this->assertCount(1, $result['sections']['section2']['areas']);
		$this->assertCount(4, $result['sections']['section2']['areas']['area3']['subsections']);
		$this->assertStringStartsWith(';extra=param', $result['extra_parameters']);
		$this->assertArrayNotHasKey('section3', $result['sections']);
		$this->assertTrue($result['sections']['section2']['areas']['area3']['subsections']['sub3']['disabled']);
		$this->assertEquals('some url', $result['sections']['section2']['areas']['area3']['url']);
		$this->assertArrayNotHasKey('area2', $result['sections']['section2']['areas']);
		$this->assertTrue($result['sections']['section2']['areas']['area3']['subsections']['sub1']['is_first']);
	}
	/**
	 * @dataProvider optionsProvider
	 */
	public function testOptions($expectedKey, $expectedVal)
	{
		global $context;
		createMenu($this->test_areas, $this->test_options);
		$result = $context['menu_data_' . $context['max_menu_id']];
		parse_str(strtr($result['extra_parameters'], ';', '&'), $result);
		$this->assertArrayHasKey($expectedKey, $result);
		$this->assertContains($expectedVal, $result);
	}
	public function optionsProvider()
	{
		return [
			['abcde', '123456789'],
			['extra', 'param'],
		];
	}
	public function testCurrentlySelected()
	{
		global $context;
		createMenu($this->test_areas, $this->test_options);
		$result = $context['menu_data_' . $context['max_menu_id']];
		$this->assertSame(1, $context['max_menu_id']);
		$this->assertSame('bubbh', $result['current_action']);
		$this->assertSame('section1', $result['current_section']);
		$this->assertSame('area1', $result['current_area']);
		$this->addOptions(['action' => 'section2', 'current_area' => 'area3']);
		createMenu($this->test_areas, $this->test_options);
		$result = $context['menu_data_' . $context['max_menu_id']];
		$this->assertSame(2, $context['max_menu_id']);
		$this->assertSame('section2', $result['current_action']);
		$this->assertSame('area3', $result['current_area']);
		$this->assertSame('sub2', $result['current_subsection']);
		$this->assertTrue(isset($context['menu_data_2']));
		$this->assertContains('generic_menu_dropdown', $context['template_layers']);
		$this->tearDown();
		$this->assertTrue(isset($context['menu_data_1']));
		$this->assertSame(1, $context['max_menu_id']);
		$this->assertContains('generic_menu_dropdown', $context['template_layers']);
		$this->tearDown();
		$this->assertFalse(isset($context['menu_data_1']));
		$this->assertSame(0, $context['max_menu_id']);
		$this->assertNotContains('generic_menu_dropdown', $context['template_layers']);
	}
	public function testAreaSelect()
	{
		global $context;
		$this->addOptions(['current_area' => 'area2']);
		createMenu($this->test_areas, $this->test_options);
		$result = $context['menu_data_' . $context['max_menu_id']];
		$this->assertSame('area3', $result['current_area']);
		$this->assertSame('sub2', $result['current_subsection']);
	}
	public function testSaDefault()
	{
		global $context;
		$this->addOptions(['current_area' => 'area2']);
		createMenu($this->test_areas, $this->test_options);
		$result = $context['menu_data_' . $context['max_menu_id']];
		$this->assertSame('area3', $result['current_area']);
		$this->assertSame('sub2', $result['current_subsection']);
	}
}