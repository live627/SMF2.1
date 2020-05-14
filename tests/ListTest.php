<?php

namespace PHPTDD;

class ListTest extends BaseTestCase
{
	protected $test_areas;
	protected $test_options;

	public function test() : void
	{
		global $context, $sourcedir, $user_info;

		require_once($sourcedir . '/Subs-List.php');

		// This is all the information required to list attachments.
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
								'b' => true,
								'a' => false,
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
		var_dump($context['a']);
	}
}