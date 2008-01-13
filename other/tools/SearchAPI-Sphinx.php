<?php
/**********************************************************************************
* SearchAPI-Sphinx.php                                                            *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2                                      *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	int searchSort(string $wordA, string $wordB)
		- callback function for usort used to sort the fulltext results.
		- the order of sorting is: large words, small words, large words that
		  are excluded from the search, small words that are excluded.
*/

class sphinx_search
{
	// This is the last version of SMF that this was tested on, to protect against API changes.
	public $version_compatible = 'SMF 2.0 Beta 2';
	// This won't work with versions of SMF less than this.
	public $min_smf_version = 'SMF 2.0 Beta 2';

	// Nothing to do...
	public function __construct()
	{
	}

	// This function compares the length of two strings plus a little.
	public function searchSort($a, $b)
	{
		global $modSettings, $excludedWords;
	
		$x = strlen($a) - (in_array($a, $excludedWords) ? 1000 : 0);
		$y = strlen($b) - (in_array($b, $excludedWords) ? 1000 : 0);

		return $x < $y ? 1 : ($x > $y ? -1 : 0);
	}

	// Do we have to do some work with the words we are searching for to prepare them?
	public function prepareIndexes($word, &$wordsSearch, &$wordsExclude, $isExcluded)
	{
		global $modSettings;

		$subwords = text2words($word, null, false);

		$fulltextWord = count($subwords) === 1 ? $word : '"' . $word . '"';
		$wordsSearch['indexed_words'][] = $fulltextWord;
		if ($isExcluded)
			$wordsExclude[] = $fulltextWord;
	}

	// This has it's own custom search.
	public function searchQuery($search_params, $search_words, $excluded_words, &$participants, &$search_results)
	{
		global $user_info, $context, $sourcedir, $modSettings;

		// Only request the results if they haven't been cached yet.
		if (($cached_results = cache_get_data('search_results_' . md5($user_info['query_see_board'] . '_' . $context['params']))) === null)
		{
			//!!! Should this not be in here?
			// The API communicating with the search deamon.
			require_once($sourcedir . '/sphinxapi.php');

			// Create an instance of the sphinx client and set a few options.
			$mySphinx = new SphinxClient();
			$mySphinx->SetServer($modSettings['sphinx_searchd_server'], (int) $modSettings['sphinx_searchd_port']);
			$mySphinx->SetLimits(0, (int) $modSettings['sphinx_max_results']);
			$mySphinx->SetMatchMode(SPH_MATCH_BOOLEAN);
			$mySphinx->SetGroupBy('id_topic', SPH_GROUPBY_ATTR);
			$mySphinx->SetSortMode($search_params['sort_dir'] === 'asc' ? SPH_SORT_ATTR_ASC : SPH_SORT_ATTR_DESC, $search_params['sort'] === 'id_msg' ? 'id_topic' : $search_params['sort']);

			// Set the limits based on the search parameters.
			if (!empty($search_params['min_msg_id']) || !empty($search_params['max_msg_id']))
				$mySphinx->SetIDRange($search_params['min_msg_id'], empty($search_params['max_msg_id']) ? (int) $modSettings['maxMsgID'] : $search_params['max_msg_id']);
			if (!empty($search_params['topic']))
				$mySphinx->SetFilter('id_topic', array((int) $search_params['topic']));
			if (!empty($search_params['brd']))
				$mySphinx->SetFilter('id_board', $search_params['brd']);
			if (!empty($search_params['memberlist']))
				$mySphinx->SetFilter('id_member', $search_params['memberlist']);
			
			// Construct the (binary mode) query.
			$orResults = array();
			foreach ($search_words as $orIndex => $words)
			{
				$andResult = '';
				foreach ($words['indexed_words'] as $sphinxWord)
					$andResult .= (in_array($sphinxWord, $excluded_words) ? '-' : '') . $sphinxWord . ' & ';
				$orResults[] = substr($andResult, 0, -3);
			}
			$query = count($orResults) === 1 ? $orResults[0]  : '(' . implode(') | (', $orResults) . ')';

			// Execute the search query.
			$request = $mySphinx->Query($query, 'smf_index');

			// Can a connection to the deamon be made?
			if ($request === false)
				fatal_lang_error('error_no_search_deamon');

			// Get the relevant information from the search results.
			$cached_results = array(
				'matches' => array(),
				'num_results' => $request['total'],
			);
			if (isset($request['matches']))
				foreach ($request['matches'] as $msgID => $match)
					$cached_results['matches'][$msgID] = array(
						'id' => $match['attrs']['id_topic'],
						'relevance' => round($match['attrs']['relevance'] / 10000, 1) . '%',
						'num_matches' => $match['attrs']['@count'],
						'matches' => array(),
					);

			// Store the search results in the cache.
			cache_put_data('search_results_' . md5($user_info['query_see_board'] . '_' . $context['params']), $cached_results, 600);
		}

		$participants = array();
		foreach (array_slice(array_keys($cached_results['matches']), $_REQUEST['start'], $modSettings['search_results_per_page']) as $msgID)
		{
			$context['topics'][$msgID] = $cached_results['matches'][$msgID];
			$participants[$cached_results['matches'][$msgID]['id']] = false;
		}

		// Sentences need to be broken up in words for proper highlighting.
		$search_results = array();
		foreach ($search_words as $orIndex => $words)
			$search_results = array_merge($search_results, $search_words[$orIndex]['subject_words']);

		return $cached_results['num_results'];
	}
}

?>