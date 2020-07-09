<?php

namespace PHPTDD;

class ListTest extends BaseTestCase
{
	public function test() : void
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/Subs-List.php');

		$listOptions = array(
			'id' => 'a',
			'width' => '100%',
			'items_per_page' => 4,
			'no_items_label' => 'n',
			'no_items_align' => 'left',
			'title' => 't',
			'base_href' => '?action=profile;area=showposts;sa=attach;u=',
			'default_sort_col' => 'a',
			'get_items' => array(
				'file' => __DIR__ . '/IntegrationFixtures.php',
				'function' => 'get',
				'params' => array(
					'dummy',
				),
			),
			'get_count' => array(
				'file' => __DIR__ . '/IntegrationFixtures.php',
				'function' => 'num',
				'params' => array(
					'dummy',
				),
			),
			'data_check' => array(
				'class' => function($data)
				{
					return $data['a'] ? '' : 'approvebg';
				}
			),
			'columns' => array(
				'a' => array(
					'header' => array(
						'value' => 'h',
						'class' => 'lefttext',
						'style' => 'width: 25%;',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a topic=%1$d">%2$s</a>',
							'params' => array(
								'a' => false,
								'b' => true,
							),
						),
					),
					'sort' => array(
						'default' => 'a',
						'reverse' => 'a DESC',
					),
				),
				'd' => array(
					'header' => array(
					),
					'data' => array(
						'db' => 'd',
						'comma_format' => true,
					),
					'sort' => array(
						'default' => 'd',
						'reverse' => 'd DESC',
					),
				),
				'e' => array(
					'header' => array(
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'e',
						'timeformat' => true,
					),
					'sort' => array(
						'default' => 'e',
						'reverse' => 'e DESC',
					),
				),
			),
		);

		createList($listOptions);
		$this->assertEquals('a', $context['a']['sort']['id']);
		$this->assertFalse($context['a']['sort']['desc']);
		$this->assertEquals(5, $context['a']['total_num_items']);
		$this->assertEquals(4, $context['a']['items_per_page']);
		$this->assertCount(1, $context['a']['rows']);
		$this->assertEquals('<a topic=1">j&amp;j</a>', $context['a']['rows'][0]['data']['a']['value']);
		$this->assertEquals('123,456.78', $context['a']['rows'][0]['data']['d']['value']);
		$this->assertEquals('May 23, 1970, 09:21 PM', $context['a']['rows'][0]['data']['e']['value']);
	}

	public function test2() : void
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/Subs-List.php');

		$listOptions = array(
			'id' => 'a',
			'items_per_page' => 4,
			'base_href' => '?action=profile;area=showposts;sa=attach;u=',
			'get_items' => array(
				'value' => array(
					array(
						'a' => 'dummy',
					),
				),
			),
			'get_count' => array(
				'value' => '2'
			),
			'columns' => array(
				'a' => array(
					'data' => array(
						'db' => 'a',
					),
				),
			),
		);

		createList($listOptions);
		$this->assertEquals(2, $context['a']['total_num_items']);
		$this->assertCount(1, $context['a']['rows']);
		$this->assertEquals('dummy', $context['a']['rows'][0]['data']['a']['value']);
	}
}