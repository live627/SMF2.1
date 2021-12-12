<?php

declare(strict_types=1);

namespace PHPTDD;

class ListTest extends BaseTestCase
{
	public function test() : void
	{
		global $context, $sourcedir;

		require_once $sourcedir . '/Subs-List.php';

		$listOptions = [
			'id' => 'a',
			'width' => '100%',
			'items_per_page' => 4,
			'no_items_label' => 'n',
			'no_items_align' => 'left',
			'title' => 't',
			'base_href' => '?action=profile;area=showposts;sa=attach;u=',
			'default_sort_col' => 'a',
			'get_items' => [
				'file' => __DIR__ . '/IntegrationFixtures.php',
				'function' => 'get',
				'params' => [
					'dummy',
				],
			],
			'get_count' => [
				'file' => __DIR__ . '/IntegrationFixtures.php',
				'function' => 'num',
				'params' => [
					'dummy',
				],
			],
			'data_check' => [
				'class' => function ($data): string
				{
					return $data['a'] ? '' : 'approvebg';
				},
			],
			'columns' => [
				'a' => [
					'header' => [
						'value' => 'h',
						'class' => 'lefttext',
						'style' => 'width: 25%;',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a topic=%1$d">%2$s</a>',
							'params' => [
								'a' => false,
								'b' => true,
							],
						],
					],
					'sort' => [
						'default' => 'a',
						'reverse' => 'a DESC',
					],
				],
				'd' => [
					'header' => [
					],
					'data' => [
						'db' => 'd',
						'comma_format' => true,
					],
					'sort' => [
						'default' => 'd',
						'reverse' => 'd DESC',
					],
				],
				'e' => [
					'header' => [
						'class' => 'lefttext',
					],
					'data' => [
						'db' => 'e',
						'timeformat' => true,
					],
					'sort' => [
						'default' => 'e',
						'reverse' => 'e DESC',
					],
				],
			],
		];

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

	public function data(): array
	{
		return [[0], [2], [32], [65], ['2'], ['65']];
	}

	/**
	 * @dataProvider data
	 */
	public function test2(/*mixed */$count): void
	{
		global $context, $sourcedir;

		require_once $sourcedir . '/Subs-List.php';

		$listOptions = [
			'id' => 'a',
			'items_per_page' => 4,
			'base_href' => '?action=profile;area=showposts;sa=attach;u=',
			'get_items' => [
				'value' => [
					[
						'a' => 'dummy',
					],
				],
			],
			'get_count' => [
				'value' => $count,
			],
			'columns' => [
				'a' => [
					'data' => [
						'db' => 'a',
					],
				],
			],
		];

		createList($listOptions);
		$this->assertEquals($count, $context['a']['total_num_items']);
		$this->assertCount(1, $context['a']['rows']);
		$this->assertEquals('dummy', $context['a']['rows'][0]['data']['a']['value']);
	}
}
