/****
/**********************************************************************************
* Load.c                                                                          *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
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

// Based on Load.php CVS 1.257

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"

#include "smflib_function_calls.h"
#include "php_smflib.h"

PHP_FUNCTION(smflib_reloadSettings)
{
	// Global variables.
	zval **modSettings, **db_prefix, **boarddir, **mysql_set_mode;

	// Global hash variables.
	zval **defaultMaxTopics, **defaultMaxMessages, **defaultMaxMembers;
	zval **cache_enable, **loadavg_enable, **load_average, **loadavg_forum;
	zval **integrate_pre_include, **integrate_pre_load, **autoOptDatabase;
	zval **loadavg_auto_opt, **autoOptMaxOnline, **autoOptLastOpt;

	// Local variables.
	zval *retval, *SQL_query, *request, *row;
	zval *smf_integration_settings, *cur_time, *smf, *match, *has_match;
	zval *escaped_prefix, *tables, *new_settings;
	zval **row_0, **row_1, **func_ptr, **match_1, **match_2, **table;

	if (!SMFLIB_GET_GLOBAL_Z(db_prefix))
		php_error(E_ERROR, "smflib_reloadSettings(): $db_prefix not set");

	// PHP: if (isset($GLOBALS['mysql_set_mode']) && $GLOBALS['mysql_set_mode'] === true)
	if (SMFLIB_GET_GLOBAL_Z(mysql_set_mode) && Z_TYPE_PP(mysql_set_mode) == IS_BOOL && Z_LVAL_PP(mysql_set_mode))
	{
		// PHP: db_query("SET sql_mode='', AUTOCOMMIT=1", false, false);
		SMFLIB_CALL_FUNCTION_SBB("db_query", SMFLIB_QUERY_SET_MODE, sizeof(SMFLIB_QUERY_SET_MODE) - 1, 0, 0, retval);
		zval_ptr_dtor(&retval);
	}

	// PHP: if (($modSettings = cache_get_data('modSettings', 90)) == null)
	SMFLIB_CALL_FUNCTION_SL("cache_get_data", "modSettings", sizeof("modSettings") - 1, 90, retval);
	SMFLIB_SET_GLOBAL_CZZ("modSettings", retval, modSettings);
	if (Z_TYPE_PP(modSettings) != IS_ARRAY)
	{
		/* PHP: $request = db_query("
			SELECT variable, value
			FROM {$db_prefix}settings", false, false);*/
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_LOAD_SETTINGS, sizeof(SMFLIB_QUERY_LOAD_SETTINGS) - 1, *db_prefix, SQL_query);
		SMFLIB_CALL_FUNCTION_ZBB("db_query", SQL_query, 0, 0, request);
		zval_ptr_dtor(&SQL_query);

		// PHP: $modSettings = array();
		SMFLIB_ARR_INIT_GLOBAL_Z(modSettings);

		// PHP: if (!$request)
		if (Z_TYPE_P(request) == IS_BOOL && !Z_LVAL_P(request))
		{
			// PHP: db_fatal_error();
			SMFLIB_CALL_FUNCTION("db_fatal_error", retval);
			zval_ptr_dtor(&retval);
		}
		// PHP: while ($row = mysql_fetch_row($request))
		SMFLIB_MYSQL_FETCH_ROW_BEGIN(request, row)
		{
			// PHP: $modSettings[$row[0]] = $row[1];
			SMFLIB_GET_KEY_VAL_ZLZ(row, 0, row_0);
			SMFLIB_GET_KEY_VAL_ZLZ(row, 1, row_1);
			SMFLIB_SET_KEY_VAL_ZZZ_CPY(*modSettings, *row_0, *row_1);
		}
		SMFLIB_MYSQL_FETCH_ROW_END(request, row);

		// PHP: mysql_free_result($request);
		SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&request);

		// PHP: if (empty($modSettings['defaultMaxTopics']) || $modSettings['defaultMaxTopics'] <= 0 || $modSettings['defaultMaxTopics'] > 999)
		if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, defaultMaxTopics) || SMFLIB_EMPTY_PP(defaultMaxTopics) || SMFLIB_Z_TO_L(*defaultMaxTopics) < 0 || SMFLIB_Z_TO_L(*defaultMaxTopics) > 999)
		{
			// PHP: $modSettings['defaultMaxTopics'] = 20;
			SMFLIB_SET_KEY_VAL_ZCL(*modSettings, "defaultMaxTopics", 20);
		}
		// PHP: if (empty($modSettings['defaultMaxMessages']) || $modSettings['defaultMaxMessages'] <= 0 || $modSettings['defaultMaxMessages'] > 999)
		if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, defaultMaxMessages) || SMFLIB_EMPTY_PP(defaultMaxMessages) || SMFLIB_Z_TO_L(*defaultMaxMessages) <= 0 || SMFLIB_Z_TO_L(*defaultMaxMessages) > 999)
		{
			// PHP: $modSettings['defaultMaxMessages'] = 15;
			SMFLIB_SET_KEY_VAL_ZCL(*modSettings, "defaultMaxMessages", 20);
		}
		// PHP: if (empty($modSettings['defaultMaxMembers']) || $modSettings['defaultMaxMembers'] <= 0 || $modSettings['defaultMaxMembers'] > 999)
		if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, defaultMaxMembers) || SMFLIB_EMPTY_PP(defaultMaxMembers) || SMFLIB_Z_TO_L(*defaultMaxMembers) < 0 || SMFLIB_Z_TO_L(*defaultMaxMembers) > 999)
		{
			// PHP: $modSettings['defaultMaxMembers'] = 30;
			SMFLIB_SET_KEY_VAL_ZCL(*modSettings, "defaultMaxMembers", 20);
		}

		// PHP: if (!empty($modSettings['cache_enable']))
		if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, cache_enable) && !SMFLIB_EMPTY_PP(cache_enable))
		{
			zval **v;

			// PHP: foreach ($modSettings as $k => $v)
			SMFLIB_FOREACH_BEGIN_ZZ(*modSettings, v)
			{
				// PHP: if (strlen($v) != 0 && is_numeric($v))
				if (Z_TYPE_PP(v) == IS_STRING && Z_STRLEN_PP(v) && SMFLIB_IS_NUMERIC(*v))
				{
					// PHP: $modSettings[$k] = strpos($v, '.') === false ? (int) $v : (float) $v;
					if (SMFLIB_STRPOS_ZC(*v, "."))
					{
						convert_to_double_ex(v);
					}
					else
					{
						convert_to_long_ex(v);
					}
				}
			}
			SMFLIB_FOREACH_END_ZZ(*modSettings, z_val);

			// PHP: cache_put_data('modSettings', $modSettings, 90);
			SMFLIB_CALL_FUNCTION_SZL("cache_put_data", "modSettings", sizeof("modSettings") - 1, *modSettings, 90, retval);
			zval_ptr_dtor(&retval);
		}
	}

	// Check the load averages?
	// PHP: if (!empty($modSettings['loadavg_enable']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, loadavg_enable) && !SMFLIB_EMPTY_PP(loadavg_enable))
	{
		// PHP: if (($modSettings['load_average'] = cache_get_data('loadavg', 90)) == null)
		SMFLIB_CALL_FUNCTION_SL("cache_get_data", "loadavg", sizeof("loadavg") - 1, 90, retval);
		SMFLIB_SET_KEY_VAL_ZCZZ(*modSettings, "load_average", retval, load_average);
		if (Z_TYPE_PP(load_average) == IS_NULL)
		{
			zval *file_contents = NULL, *matches, *preg_result;
			zval **matches_1;

			// PHP: $modSettings['load_average'] = @file_get_contents('/proc/loadavg'));
			// !!! What about the @?
			SMFLIB_CALL_FUNCTION_S("file_get_contents", "/proc/loadavg", sizeof("/proc/loadavg"), file_contents);
			SMFLIB_SET_KEY_VAL_ZCZZ(*modSettings, "load_average", file_contents, load_average);

			// PHP: if (!empty($modSettings['load_average']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $modSettings['load_average'], $matches) != 0)
			if (!SMFLIB_EMPTY_PP(load_average))
			{
				ALLOC_INIT_ZVAL(matches);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_LOAD_AVG_COLUMNS, sizeof(SMFLIB_PREG_LOAD_AVG_COLUMNS) - 1, *load_average, matches, preg_result);
				if (Z_LVAL_P(preg_result));
				{
					// PHP: $modSettings['load_average'] = (float) $matches[1];
					SMFLIB_GET_KEY_VAL_ZLZ(matches, 1, matches_1);
					SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*modSettings, "load_average", *matches_1, load_average);
					convert_to_double(*load_average);
				}
				zval_ptr_dtor(&preg_result);
				zval_ptr_dtor(&matches);
			}
			// PHP: elseif (($modSettings['load_average'] = @`uptime`) != null && preg_match('~load average[s]?: (\d+\.\d+), (\d+\.\d+), (\d+\.\d+)~i', $modSettings['load_average'], $matches) != 0)
			else
			{
				zend_bool do_else = 0;

				SMFLIB_CALL_FUNCTION_S("shell_exec", "uptime", sizeof("uptime"), retval);
				SMFLIB_SET_KEY_VAL_ZCZZ(*modSettings, "load_average", retval, load_average);
				if (Z_TYPE_PP(load_average) != IS_NULL)
				{

					ALLOC_INIT_ZVAL(matches);
					SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_LOAD_AVG_UPTIME, sizeof(SMFLIB_PREG_LOAD_AVG_UPTIME) - 1, *load_average, matches, preg_result);
					if (Z_LVAL_P(preg_result))
					{
						// PHP: $modSettings['load_average'] = (float) $matches[1];
						SMFLIB_GET_KEY_VAL_ZLZ(matches, 1, matches_1);
						SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*modSettings, "load_average", *matches_1, load_average);
						convert_to_double(*load_average);
					}
					else
						do_else = 1;
				}
				else
					do_else = 1;

				// PHP: else
				if (do_else)
				{
					// PHP: unset($modSettings['load_average']);
					SMFLIB_UNSET_KEY_VAL_ZC(*modSettings, "load_average");
				}
			}

			// PHP: if (!empty($modSettings['load_average']))
			if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, load_average) && !SMFLIB_EMPTY_PP(load_average))
			{
				// PHP: cache_put_data('loadavg', $modSettings['load_average'], 90);
				SMFLIB_CALL_FUNCTION_SZL("cache_put_data", "loadavg", sizeof("loadavg") - 1, *load_average, 90, retval);
				zval_ptr_dtor(&retval);
			}
		}

		// PHP: if (!empty($modSettings['loadavg_forum']) && !empty($modSettings['load_average']) && $modSettings['load_average'] >= $modSettings['loadavg_forum'])
		if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, loadavg_forum) && !SMFLIB_EMPTY_PP(loadavg_forum) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, load_average) && !SMFLIB_EMPTY_PP(load_average) && SMFLIB_Z_TO_L(*load_average) >= SMFLIB_Z_TO_L(*loadavg_forum))
		{
			// PHP: db_fatal_error(true);
			SMFLIB_CALL_FUNCTION_B("db_fatal_error", 1, retval);
			zval_ptr_dtor(&retval);
		}
	}

	// PHP: if (isset($modSettings['integrate_pre_include']) && file_exists(strtr($modSettings['integrate_pre_include'], array('$boarddir' => $boarddir))))
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, integrate_pre_include))
	{
		zval *boarddir_replace, *require_file;

		if (!SMFLIB_GET_GLOBAL_Z(boarddir))
			php_error(E_ERROR, "smflib_reloadSettings(): $boarddir not set");

		// PHP: require_once(strtr($modSettings['integrate_pre_include'], array('$boarddir' => $boarddir)));
		ALLOC_INIT_ZVAL(boarddir_replace);
		array_init(boarddir_replace);
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(boarddir_replace, "$boarddir", *boarddir);
		SMFLIB_CALL_FUNCTION_ZZ("strtr", *integrate_pre_include, boarddir_replace, require_file);
		zval_ptr_dtor(&boarddir_replace);
		smflib_require_once(Z_STRVAL_P(require_file), Z_STRLEN_P(require_file) TSRMLS_CC);
		zval_ptr_dtor(&require_file);
	}
	// PHP: if (defined('SMF_INTEGRATION_SETTINGS'))
	ALLOC_INIT_ZVAL(smf_integration_settings);
	if (zend_get_constant("SMF_INTEGRATION_SETTINGS", sizeof("SMF_INTEGRATION_SETTINGS") - 1, smf_integration_settings TSRMLS_CC))
	{
		zval *tmp_copy;

		// !!! unserialize SMF_INTEGRATION_SETTINGS first.

		// PHP: $modSettings = SMF_INTEGRATION_SETTINGS + $modSettings;
		// smf_integration_settings overwrites $modsettings.
		zend_hash_merge(Z_ARRVAL_PP(modSettings), Z_ARRVAL_P(smf_integration_settings), (void (*)(void *pData)) zval_add_ref, (void *) &tmp_copy, sizeof(zval *), 1);
	}
	zval_ptr_dtor(&smf_integration_settings);

	// PHP: if (isset($modSettings['integrate_pre_load']) && function_exists($modSettings['integrate_pre_load']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, integrate_pre_load) && SMFLIB_FUNCTION_EXISTS_ZZ(*integrate_pre_load, func_ptr))
	{
		// PHP: call_user_func($modSettings['integrate_pre_load']);
		SMFLIB_CALL_USER_FUNCTION(Z_STRVAL_PP(integrate_pre_load), Z_STRLEN_PP(integrate_pre_load), retval);
		zval_ptr_dtor(&retval);
	}

	// PHP: if (empty($modSettings['autoOptDatabase']) || $modSettings['autoOptLastOpt'] + $modSettings['autoOptDatabase'] * 3600 * 24 >= time() || SMF == 'SSI')
	SMFLIB_CALL_FUNCTION("time", cur_time);
	ALLOC_INIT_ZVAL(smf);
	SMFLIB_GET_KEY_VAL_ZZ(*modSettings, autoOptDatabase);
	if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, autoOptDatabase) || SMFLIB_EMPTY_PP(autoOptDatabase) || (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, autoOptLastOpt) ? SMFLIB_Z_TO_L(*autoOptLastOpt) : 0) + SMFLIB_Z_TO_L(*autoOptDatabase) * 3600 * 24 >= Z_LVAL_P(cur_time) || (zend_get_constant("SMF", sizeof("SMF") - 1, smf TSRMLS_CC) && Z_TYPE_P(smf) == IS_STRING && SMFLIB_CMP_EQ_ZC(smf, "SSI")))
	{
		// PHP: return;
		zval_ptr_dtor(&cur_time);
		RETURN_NULL();
	}
	zval_ptr_dtor(&smf);

	// PHP: if (!empty($modSettings['load_average']) && !empty($modSettings['loadavg_auto_opt']) && $modSettings['load_average'] >= $modSettings['loadavg_auto_opt'])
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, load_average) && !SMFLIB_EMPTY_PP(load_average) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, loadavg_auto_opt) && !SMFLIB_EMPTY_PP(loadavg_auto_opt) && SMFLIB_Z_TO_L(*load_average) >= SMFLIB_Z_TO_L(*loadavg_auto_opt))
	{
		// PHP: return;
		zval_ptr_dtor(&cur_time);
		RETURN_NULL();
	}

	// PHP: if (!empty($modSettings['autoOptMaxOnline']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, autoOptMaxOnline) && !SMFLIB_EMPTY_PP(autoOptMaxOnline))
	{
		zval **dont_do_it;

		/* PHP: $request = db_query("
			SELECT COUNT(session)
			FROM {$db_prefix}log_online", __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_NUM_ONLINE, sizeof(SMFLIB_QUERY_NUM_ONLINE) - 1, *db_prefix, SQL_query);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
		zval_ptr_dtor(&SQL_query);

		// PHP: list ($dont_do_it) = mysql_fetch_row($request);
		SMFLIB_CALL_FUNCTION_Z("mysql_fetch_row", request, row);
		SMFLIB_GET_KEY_VAL_ZLZ(row, 0, dont_do_it);

		// PHP: mysql_free_result($request);
		SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&request);

		// PHP: if ($dont_do_it > $modSettings['autoOptMaxOnline'])
		if (SMFLIB_Z_TO_L(*dont_do_it) > SMFLIB_Z_TO_L(*autoOptMaxOnline))
		{
			// PHP: return;
			zval_ptr_dtor(&row);
			zval_ptr_dtor(&cur_time);
			RETURN_NULL();
		}
		zval_ptr_dtor(&row);
	}

	// PHP: if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
	ALLOC_INIT_ZVAL(match);
	SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_GET_BACKTICKED_DB, sizeof(SMFLIB_PREG_GET_BACKTICKED_DB) - 1, *db_prefix, match, has_match)
	if (Z_LVAL_P(has_match) && SMFLIB_GET_KEY_VAL_ZLZ(match, 1, match_1) && SMFLIB_GET_KEY_VAL_ZLZ(match, 2, match_2))
	{
		zval *remove_backticks, *backtick_free_table;

		/* PHP: $request = db_query("
			SHOW TABLES
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'", __FILE__, __LINE__);*/
		ALLOC_INIT_ZVAL(remove_backticks);
		array_init(remove_backticks);
		SMFLIB_SET_KEY_VAL_ZCC(remove_backticks, "`", "");
		SMFLIB_CALL_FUNCTION_ZZ("strtr", *match_1, remove_backticks, backtick_free_table);
		zval_ptr_dtor(&remove_backticks);
		SMFLIB_CALL_FUNCTION_SSZ("str_replace", "_", sizeof("_") - 1, "\\_", sizeof("\\_") - 1, *match_2, escaped_prefix);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_QUERY_SHOW_TABLES_BACKTICK, sizeof(SMFLIB_QUERY_SHOW_TABLES_BACKTICK) - 1, backtick_free_table, escaped_prefix, SQL_query);
		zval_ptr_dtor(&backtick_free_table);
		zval_ptr_dtor(&escaped_prefix);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
		zval_ptr_dtor(&SQL_query);
	}
	// PHP: else
	else
	{
		/* PHP: $request = db_query("
			SHOW TABLES
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'", __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SSZ("str_replace", "_", sizeof("_") - 1, "\\_", sizeof("\\_") - 1, *db_prefix, escaped_prefix);
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_SHOW_TABLES, sizeof(SMFLIB_QUERY_SHOW_TABLES) - 1, escaped_prefix, SQL_query);
		zval_ptr_dtor(&escaped_prefix);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
		zval_ptr_dtor(&SQL_query);
	}
	zval_ptr_dtor(&has_match);
	zval_ptr_dtor(&match);


	// PHP: $tables = array();
	ALLOC_INIT_ZVAL(tables);
	array_init(tables);

	// PHP: while ($row = mysql_fetch_row($request))
	SMFLIB_MYSQL_FETCH_ROW_BEGIN(request, row)
	{
		// PHP: $tables[] = $row[0];
		SMFLIB_GET_KEY_VAL_ZLZ(row, 0, row_0);
		SMFLIB_ADD_INDEX_ZZ_CPY(tables, *row_0);
	}
	SMFLIB_MYSQL_FETCH_ROW_END(request, row);

	// PHP: mysql_free_result($request);
	SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&request);

	// PHP: updateSettings(array('autoOptLastOpt' => time()));
	ALLOC_INIT_ZVAL(new_settings);
	array_init(new_settings);
	SMFLIB_SET_KEY_VAL_ZCZ_CPY(new_settings, "autoOptLastOpt", cur_time);
	SMFLIB_CALL_FUNCTION_Z("updateSettings", new_settings, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&cur_time);
	zval_ptr_dtor(&new_settings);

	// PHP: ignore_user_abort(true);
	SMFLIB_CALL_FUNCTION_B("ignore_user_abort", 1, retval);
	zval_ptr_dtor(&retval);

	// PHP: foreach ($tables as $table)
	SMFLIB_FOREACH_BEGIN_ZZ(tables, table)
	{
		/* PHP: db_query("
			OPTIMIZE TABLE `$table`", __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_OPTIMIZE_TABLE, sizeof(SMFLIB_QUERY_OPTIMIZE_TABLE) - 1, *table, SQL_query);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&SQL_query);
	}
	SMFLIB_FOREACH_END_ZZ(tables, table)
	zval_ptr_dtor(&tables);
}
