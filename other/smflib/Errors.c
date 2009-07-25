/****
/**********************************************************************************
* Errors.c                                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC2                                         *
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

// Based on Errors.php CVS 1.96


#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"

#include "smflib_function_calls.h"
#include "php_smflib.h"

// For PS-global
#include "ext/session/php_session.h"



PHP_FUNCTION(smflib_log_error)
{
	// Input parameters.
	zval *error_message, *file = NULL, *line = NULL;

	// Global variables.
	zval **modSettings, **_SERVER, **txt, **ID_MEMBER, **user_info = NULL;
	zval **_POST, **_GET, **db_prefix, **sc;

	// Global hash variables.
	zval **enableErrorLogging, **ip, **QUERY_STRING, **_POST_board;
	zval **_GET_board;

	// Local variables.
	zval *html_convert_array, *input_str, *query_string, *_error_message;
	zval *retval;
	zval **dummy;

	// prototype: zval = log_error(error_message, file = null, line = null);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|zz", &error_message, &file, &line) == FAILURE)
		RETURN_NULL();

	// Grab some global variables.
	if (!SMFLIB_GET_GLOBAL_Z(modSettings))
		php_error(E_ERROR, "smflib_log_error(): $modSettings not set");
	if (!SMFLIB_GET_GLOBAL_Z(_SERVER))
		php_error(E_ERROR, "smflib_log_error(): $_SERVER not set");

	// PHP: if (empty($modSettings['enableErrorLogging']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, enableErrorLogging) || SMFLIB_EMPTY_PP(enableErrorLogging))
	{
		// return $error_message;
		RETURN_ZVAL(error_message, 1, 0);
	}

	// Create a local copy of $error_message.
	ALLOC_INIT_ZVAL(_error_message);
	ZVAL_STRINGL(_error_message, Z_STRVAL_P(error_message), Z_STRLEN_P(error_message), 1);

	// PHP: $error_message = strtr($error_message, array('<' => '&lt;', '>' => '&gt;', '"' => '&quot;'));
	ALLOC_INIT_ZVAL(html_convert_array);
	array_init(html_convert_array);
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "<", "&lt;");
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, ">", "&gt;");
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "\"", "&quot;");
	input_str = _error_message;
	SMFLIB_CALL_FUNCTION_ZZ("strtr", input_str, html_convert_array, _error_message);
	zval_ptr_dtor(&input_str);

	// PHP: $error_message = strtr($error_message, array('&lt;br /&gt;' => '<br />', '&lt;b&gt;' => '<strong>', '&lt;/b&gt;' => '</strong>', "\n" => '<br />'));
	SMFLIB_EMPTY_ARR_Z(html_convert_array);
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "&lt;br /&gt;", "<br />");
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "&lt;b&gt;", "<strong>");
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "&lt;/b&gt", "</strong>");
	SMFLIB_SET_KEY_VAL_ZCC(html_convert_array, "\n", "<br />");
	input_str = _error_message;
	SMFLIB_CALL_FUNCTION_ZZ("strtr", input_str, html_convert_array, _error_message);
	zval_ptr_dtor(&input_str);
	zval_ptr_dtor(&html_convert_array);

	// if ($file != null)
	if (file && Z_TYPE_P(file) != IS_NULL)
	{
		zval **txt_1003;

		// PHP: $error_message .= '<br />' . (isset($txt[1003]) ? $txt[1003] . ': ' : '') . $file;
		if (SMFLIB_GET_GLOBAL_Z(txt) && SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1003, txt_1003))
		{
			input_str = _error_message;
			SMFLIB_CALL_FUNCTION_SZZZ("sprintf", SMFLIB_FORMAT_ERROR_FILE_TXT, sizeof(SMFLIB_FORMAT_ERROR_FILE_TXT) - 1, input_str, *txt_1003, file, _error_message);
			zval_ptr_dtor(&input_str);
		}
		else
		{
			input_str = _error_message;
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_FORMAT_ERROR_FILE, sizeof(SMFLIB_FORMAT_ERROR_FILE) - 1, input_str, file, _error_message);
			zval_ptr_dtor(&input_str);
		}
	}

	// PHP: if ($line != null)
	if (line && Z_TYPE_P(line) != IS_NULL)
	{
		zval **txt_1004;

		// PHP: $error_message .= '<br />' . (isset($txt[1004]) ? $txt[1004] . ': ' : '') . $line;
		if (SMFLIB_GET_GLOBAL_Z(txt) && SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1004, txt_1004))
		{
			input_str = _error_message;
			SMFLIB_CALL_FUNCTION_SZZZ("sprintf", SMFLIB_FORMAT_ERROR_LINE_TXT, sizeof(SMFLIB_FORMAT_ERROR_FILE_TXT) - 1, input_str, *txt_1004, line, _error_message);
			zval_ptr_dtor(&input_str);
		}
		else
		{
			input_str = _error_message;
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_FORMAT_ERROR_LINE, sizeof(SMFLIB_FORMAT_ERROR_LINE) - 1, input_str, line, _error_message);
			zval_ptr_dtor(&input_str);
		}
	}

	// PHP: if (empty($ID_MEMBER))
	if (!SMFLIB_GET_GLOBAL_Z(ID_MEMBER) || SMFLIB_EMPTY_PP(ID_MEMBER))
	{
		// PHP: $ID_MEMBER = 0;
		SMFLIB_SET_GLOBAL_CLZ("ID_MEMBER", 0, ID_MEMBER);
	}

	// PHP: if (empty($user_info['ip']))
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, ip) || SMFLIB_EMPTY_PP(ip))
	{
		zval *empty_var;

		// PHP: $user_info['ip'] = '';
		if (!user_info)
		{
			ALLOC_INIT_ZVAL(empty_var);
			array_init(empty_var);
			SMFLIB_SET_GLOBAL_CZZ("user_info", empty_var, user_info);
		}
		SMFLIB_SET_KEY_VAL_ZCCZ(*user_info, "ip", "", ip);
	}

	// PHP: $query_string = empty($_SERVER['QUERY_STRING']) ? '' : addslashes(htmlspecialchars('?' . preg_replace(array('~;sesc=[^&;]+~', '~' . session_name() . '=' . session_id() . '[&;]~'), array(';sesc', ''), $_SERVER['QUERY_STRING'])));
	if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, QUERY_STRING))
	{
		ALLOC_INIT_ZVAL(query_string);
		ZVAL_EMPTY_STRING(query_string);
	}
	else
	{
		zval *search_array, *session_search, *replace_array, *session_id;
		zval *session_name;

		ALLOC_INIT_ZVAL(search_array);
		array_init(search_array);
		add_next_index_stringl(search_array, "~;sesc=[^&;]+~", sizeof("~;sesc=[^&;]+~") - 1, 1);
		ALLOC_INIT_ZVAL(session_id);
		ZVAL_STRING(session_id, PS(id) ? PS(id) : "", 1);
		ALLOC_INIT_ZVAL(session_name);
		ZVAL_STRING(session_name, PS(session_name) ? PS(session_name) : "", 1);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", "~%1$s=%2$s[&;]~", sizeof("~%1$s=%2$s[&;]~") - 1, session_name, session_id, session_search);
		zval_ptr_dtor(&session_name);
		zval_ptr_dtor(&session_id);
		add_next_index_zval(search_array, session_search);

		ALLOC_INIT_ZVAL(replace_array);
		array_init(replace_array);
		add_next_index_stringl(replace_array, ";sesc", sizeof(";sesc") - 1, 1);
		add_next_index_stringl(replace_array, "", sizeof("") - 1, 1);

		SMFLIB_CALL_FUNCTION_ZZZ("preg_replace", search_array, replace_array, *QUERY_STRING, query_string);
		zval_ptr_dtor(&search_array);
		zval_ptr_dtor(&replace_array);

		input_str = query_string;
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "?%1$s", sizeof("?%1$s") - 1, input_str, query_string);
		zval_ptr_dtor(&input_str);

		input_str = query_string;
		SMFLIB_CALL_FUNCTION_Z("htmlspecialchars", input_str, query_string);
		zval_ptr_dtor(&input_str);

		input_str = query_string;
		SMFLIB_CALL_FUNCTION_Z("addslashes", input_str, query_string);
		zval_ptr_dtor(&input_str);
	}

	// PHP: if (isset($_POST['board']) && !isset($_GET['board']))
	if (SMFLIB_GET_GLOBAL_Z(_POST) && SMFLIB_GET_KEY_VAL_ZCZ(*_POST, "board", _POST_board) && (!SMFLIB_GET_GLOBAL_Z(_GET) || !SMFLIB_GET_KEY_VAL_ZCZ(*_GET, "board", _GET_board)))
	{
		// PHP: $query_string .= ($query_string == '' ? 'board=' : ';board=') . $_POST['board'];
		input_str = query_string;
		SMFLIB_CALL_FUNCTION_SZSZ("sprintf", "%1$s%2$sboard=%3$d", sizeof("%1$s%2$sboard=%3$d") - 1, input_str, Z_STRLEN_P(input_str) == 0 ? ";" : "", Z_STRLEN_P(input_str) == 0 ? sizeof(";") - 1 : sizeof("") - 1, *_POST_board, query_string);
		zval_ptr_dtor(&input_str);
	}

	/* PHP: db_query("
		INSERT INTO {$db_prefix}log_errors
			(ID_MEMBER, logTime, ip, url, message, session)
		VALUES ($ID_MEMBER, " . time() . ", '$user_info[ip]', '$query_string', '" . addslashes($error_message) . "', '$sc')", false, false) or die($error_message);*/
	if (SMFLIB_GET_GLOBAL_Z(db_prefix) && SMFLIB_GET_GLOBAL_Z(sc) && zend_hash_find(CG(function_table), "db_query", sizeof("db_query"), (void **) &dummy) == SUCCESS)
	{
		zval *cur_time, *error_message_slashed, *SQL_query;

		SMFLIB_CALL_FUNCTION("time", cur_time);
		SMFLIB_CALL_FUNCTION_Z("addslashes", _error_message, error_message_slashed);
		SMFLIB_CALL_FUNCTION_SZZZZZZZ("sprintf", SMFLIB_QUERY_LOG_ERROR, sizeof(SMFLIB_QUERY_LOG_ERROR) - 1, *db_prefix, *ID_MEMBER, cur_time, *ip, query_string, error_message_slashed, *sc, SQL_query);
		zval_ptr_dtor(&cur_time);
		zval_ptr_dtor(&query_string);
		zval_ptr_dtor(&error_message_slashed);

		SMFLIB_CALL_FUNCTION_ZBB("db_query", SQL_query, 0, 0, retval);
		zval_ptr_dtor(&SQL_query);
		if (Z_TYPE_P(retval) != IS_BOOL || Z_LVAL_P(retval) != 0)
		{
			// PHP: return $error_message;
			zval_ptr_dtor(&retval);
			RETURN_ZVAL(_error_message, 1, 1);
		}
		zval_ptr_dtor(&retval);
	}
	// PHP: die($error_message);
	zval_ptr_dtor(&query_string);
	zend_print_zval(_error_message, 0);
	zval_ptr_dtor(&_error_message);
	zend_bailout();
}

PHP_FUNCTION(smflib_db_error)
{
	// Input parameters.
	zval *db_string, *file, *line;

	// Global variables.
	zval **txt = NULL, **context, **sourcedir, **webmaster_email, **modSettings;
	zval **forum_version, **db_connection, **db_last_error, **db_persist;
	zval **db_server, **db_user, **db_passwd, **db_name, **db_show_debug;

	// Global hash variables.
	zval **txt_1001, **txt_1002, **txt_1003, **txt_1004, **txt_1005;
	zval **autoFixDatabase, **cache_enable = NULL, **smfVersion;
	zval **error_message, **database_error_versions;

	// Local variables.
	zval *query_error, *retval, *input_str;
	zend_bool can_admin_forum;

	// prototype: $db_resource = db_error($db_string, $file, $line);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zzz", &db_string, &file, &line) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(db_connection))
		php_error(E_ERROR, "db_error(): $db_connection not set");
	if (!SMFLIB_GET_GLOBAL_Z(txt))
		SMFLIB_ARR_INIT_GLOBAL_Z(txt);
	if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1001, txt_1001))
		SMFLIB_SET_KEY_VAL_ZLCZ(*txt, 1001, "Database Error", txt_1001);
	if (!SMFLIB_GET_GLOBAL_Z(modSettings))
		php_error(E_ERROR, "db_error(): $modSettings not set");

	// PHP: $query_error = mysql_error($db_connection);
	// !!! Debugging!
	//SMFLIB_CALL_FUNCTION_Z("mysql_error", *db_connection, query_error);
	SMFLIB_CALL_FUNCTION_Z("mysql_error_debug", *db_connection, query_error);

	// There should be an error string, if not stop here.
	if (Z_TYPE_P(query_error) != IS_STRING)
		php_error(E_ERROR, "db_error(): unexpected result of mysql_error() call");

	// PHP: if (strpos($query_error, 'Deadlock found when trying to get lock') === false && strpos($query_error, 'Lock wait timeout exceeded;') === false)
	if (!SMFLIB_STRPOS_ZC(query_error, "Deadlock found when trying to get lock") && !SMFLIB_STRPOS_ZC(query_error, "Lock wait timeout exceeded;"))
	{
		zval *compiled_error_msg;

		// PHP: log_error($txt[1001] . ': ' . $query_error, $file, $line);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s: %2$s", sizeof("%1$s: %2$s"), *txt_1001, query_error, compiled_error_msg);
		SMFLIB_CALL_FUNCTION_ZZZ("log_error", compiled_error_msg, file, line, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&compiled_error_msg);
	}

	// PHP: if (!isset($modSettings['autoFixDatabase']) || $modSettings['autoFixDatabase'] == '1')
	if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, autoFixDatabase) || SMFLIB_Z_TO_L(*autoFixDatabase) == 1)
	{
		zval *old_cache, *temp, *cur_time, *fix_tables = NULL, *input_arr;
		zval *_db_last_error, *ret;
		zval **table;
		HashPosition pos;

		// Calculate the numeric value of $db_last_error (it might not be set).
		ALLOC_INIT_ZVAL(_db_last_error);
		if (SMFLIB_GET_GLOBAL_Z(db_last_error))
		{
			ZVAL_ZVAL(_db_last_error, *db_last_error, 1, 0);
			convert_to_long(_db_last_error);
		}
		else
		{
			ZVAL_LONG(_db_last_error, 0);
		}

		// Grab the current Unix time.
		SMFLIB_CALL_FUNCTION("time", cur_time);

		// PHP: $old_cache = @$modSettings['cache_enable'];
		ALLOC_INIT_ZVAL(old_cache);
		if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, cache_enable))
		{
			ZVAL_ZVAL(old_cache, *cache_enable, 1, 0);
		}

		// PHP: $modSettings['cache_enable'] = '1';
		SMFLIB_SET_KEY_VAL_ZCC(*modSettings, "cache_enable", "1");

		// PHP: if (($temp = cache_get_data('db_last_error', 600)) !== null)
		SMFLIB_CALL_FUNCTION_SL("cache_get_data", "db_last_error", sizeof("db_last_error") - 1, 600, temp);
		if (Z_TYPE_P(temp) != IS_NULL)
		{
			// PHP: $db_last_error = max(@$db_last_error, $temp);
			SET_VAR_LONG("db_last_error", MAX(Z_LVAL_P(_db_last_error), Z_LVAL_P(temp)));
		}
		zval_ptr_dtor(&temp);

		// PHP: if (@$db_last_error < time() - 3600 * 24 * 3)
		if (Z_LVAL_P(_db_last_error) < Z_LVAL_P(cur_time) - 3600 * 24 * 3)
		{
			zval *ticked_table, *match, *found_it;
			zval **match_1;

			// PHP: if (strpos($query_error, 'Got error 127 from table handler') !== false)
			if (SMFLIB_STRPOS_ZC(query_error, "Got error 127 from table handler"))
			{
				zval *matches, *num_matches;
				zval **matches_1;

				// PHP: preg_match_all('~(?:[\n\r]|^)[^\']+?(?:FROM|JOIN|UPDATE|TABLE) ((?:[^\n\r(]+?(?:, )?)*)~s', $db_string, $matches);
				ALLOC_INIT_ZVAL(matches);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match_all", SMFLIB_PREG_GET_TABLE_LIST, sizeof(SMFLIB_PREG_GET_TABLE_LIST), db_string, matches, num_matches)

				// PHP: $fix_tables = array();
				ALLOC_INIT_ZVAL(fix_tables);
				array_init(fix_tables);

				// PHP: foreach ($matches[1] as $tables)
				if (Z_LVAL_P(num_matches) != 0 && SMFLIB_GET_KEY_VAL_ZLZ(matches, 1, matches_1))
				{
					zval *replace_backtick_arr;
					zval **tables;

					// preload array('`' => '').
					ALLOC_INIT_ZVAL(replace_backtick_arr);
					array_init(replace_backtick_arr);
					SMFLIB_SET_KEY_VAL_ZCC(replace_backtick_arr, "`", "");

					zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(matches_1), &pos);
					while (zend_hash_get_current_data_ex(Z_ARRVAL_PP(matches_1), (void**) &tables, &pos) == SUCCESS)
					{
						zval *exploded_tables, *unique_tables;
						HashPosition pos2;

						// PHP: $tables = array_unique(explode(',', $tables));
						SMFLIB_CALL_FUNCTION_SZ("explode", ",", sizeof(",") - 1, *tables, exploded_tables);
						SMFLIB_CALL_FUNCTION_Z("array_unique", exploded_tables, unique_tables);
						zval_ptr_dtor(&exploded_tables);

						// PHP: foreach ($tables as $table)
						zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(unique_tables), &pos2);
						while (zend_hash_get_current_data_ex(Z_ARRVAL_P(unique_tables), (void**) &table, &pos2) == SUCCESS)
						{
							zval *trimmed_table;

							// PHP: if (trim($table) != '')
							SMFLIB_CALL_FUNCTION_Z("trim", *table, trimmed_table);
							if (Z_STRLEN_P(trimmed_table) != 0)
							{
								zval *backtick_free_table;

								// PHP: $fix_tables[] = '`' . strtr(trim($table), array('`' => '')) . '`';
								SMFLIB_CALL_FUNCTION_ZZ("strtr", trimmed_table, replace_backtick_arr, backtick_free_table);
								SMFLIB_CALL_FUNCTION_SZ("sprintf", "`%1$s`", sizeof("`%1$s`") - 1, backtick_free_table, ticked_table);
								zval_ptr_dtor(&backtick_free_table);
								add_next_index_stringl(fix_tables, Z_STRVAL_P(ticked_table), Z_STRLEN_P(ticked_table), 1);
								zval_ptr_dtor(&ticked_table);
							}
							zval_ptr_dtor(&trimmed_table);

							zend_hash_move_forward_ex(Z_ARRVAL_P(unique_tables), &pos2);
						}
						zval_ptr_dtor(&unique_tables);
						zend_hash_move_forward_ex(Z_ARRVAL_PP(matches_1), &pos);
					}
					zval_ptr_dtor(&replace_backtick_arr);
				}
				zval_ptr_dtor(&num_matches);
				zval_ptr_dtor(&matches);

				// PHP: $fix_tables = array_unique($fix_tables);
				input_arr = fix_tables;
				SMFLIB_CALL_FUNCTION_Z("array_unique", input_arr, fix_tables);
				zval_ptr_dtor(&input_arr);
			}

			// PHP: elseif (strpos($query_error, 'Can\'t open file') !== false)
			else if (SMFLIB_STRPOS_ZC(query_error, "Can't open file"))
			{
				// PHP: preg_match('/^Can\'t open file:\s*[\']?([^\.]+?)\./', $query_error, $match);
				ALLOC_INIT_ZVAL(match);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_CANT_OPEN_FILE, sizeof(SMFLIB_PREG_CANT_OPEN_FILE) - 1, query_error, match, found_it);

				// PHP: $fix_tables = array('`' . $match[1] . '`');
				if (Z_LVAL_P(found_it) == 1 && SMFLIB_GET_KEY_VAL_ZLZ(match, 1, match_1))
				{
					SMFLIB_CALL_FUNCTION_SZ("sprintf", "`%1$s`", sizeof("`%1$s`") - 1, *match_1, ticked_table);
					ALLOC_INIT_ZVAL(fix_tables);
					array_init(fix_tables);
					add_next_index_stringl(fix_tables, Z_STRVAL_P(ticked_table), Z_STRLEN_P(ticked_table), 1);
					zval_ptr_dtor(&ticked_table);
				}
				zval_ptr_dtor(&found_it);
				zval_ptr_dtor(&match);
			}

			// PHP: elseif (strpos($query_error, 'Incorrect key file') !== false)
			else if (SMFLIB_STRPOS_ZC(query_error, "Incorrect key file"))
			{
				// PHP: preg_match('/^Incorrect key file for table:\s*[\']?([^\']+?)\'\./', $query_error, $match);
				ALLOC_INIT_ZVAL(match);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_CANT_OPEN_KEY_FILE, sizeof(SMFLIB_PREG_CANT_OPEN_KEY_FILE) - 1, query_error, match, found_it);

				// PHP: $fix_tables = array('`' . $match[1] . '`');
				if (Z_LVAL_P(found_it) == 1 && SMFLIB_GET_KEY_VAL_ZLZ(match, 1, match_1))
				{
					SMFLIB_CALL_FUNCTION_SZ("sprintf", "`%1$s`", sizeof("`%1$s`") - 1, *match_1, ticked_table);
					ALLOC_INIT_ZVAL(fix_tables);
					array_init(fix_tables);
					add_next_index_stringl(fix_tables, Z_STRVAL_P(ticked_table), Z_STRLEN_P(ticked_table), 1);
					zval_ptr_dtor(&ticked_table);
				}
				zval_ptr_dtor(&found_it);
				zval_ptr_dtor(&match);
			}
		}
		zval_ptr_dtor(&_db_last_error);

		// PHP: if (!empty($fix_tables))
		if (!SMFLIB_EMPTY_P(fix_tables))
		{
			zval *admin_php, *subs_post_php;

			if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
				php_error(E_ERROR, "db_error(): $sourcedir not set");

			// PHP: require_once($sourcedir . '/Admin.php');
			SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Admin.php", sizeof("%1$s/Admin.php") - 1, *sourcedir, admin_php);
			smflib_require_once(Z_STRVAL_P(admin_php), Z_STRLEN_P(admin_php) TSRMLS_CC);
			zval_ptr_dtor(&admin_php);

			// PHP: require_once($sourcedir . '/Subs-Post.php');
			SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Subs-Post.php", sizeof("%1$s/Subs-Post.php") - 1, *sourcedir, subs_post_php);
			smflib_require_once(Z_STRVAL_P(subs_post_php), Z_STRLEN_P(subs_post_php) TSRMLS_CC);
			zval_ptr_dtor(&subs_post_php);

			// PHP: cache_put_data('db_last_error', time(), 600);
			SMFLIB_CALL_FUNCTION_SZL("cache_put_data", "db_last_error", sizeof("db_last_error") - 1, cur_time, 600, retval);
			zval_ptr_dtor(&retval);

			// PHP: if (($temp = cache_get_data('db_last_error', 600)) === null)
			SMFLIB_CALL_FUNCTION_SL("cache_get_data", "db_last_error", sizeof("db_last_error") - 1, 600, temp);
			if (Z_TYPE_P(temp))
			{
				zval *update_settings;

				// PHP: updateSettingsFile(array('db_last_error' => time()));
				ALLOC_INIT_ZVAL(update_settings);
				array_init(update_settings);
				SMFLIB_SET_KEY_VAL_ZCL(update_settings, "db_last_error", Z_LVAL_P(cur_time));
				SMFLIB_CALL_FUNCTION_Z("updateSettingsFile", update_settings, retval);
				zval_ptr_dtor(&retval);
				zval_ptr_dtor(&update_settings);
			}
			zval_ptr_dtor(&temp);

			// PHP: foreach ($fix_tables as $table)
			zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(fix_tables), &pos);
			while (zend_hash_get_current_data_ex(Z_ARRVAL_P(fix_tables), (void**) &table, &pos) == SUCCESS)
			{
				zval *SQL_query;

				/* PHP: db_query("
					REPAIR TABLE $table", false, false);*/
				SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_REPAIR_TABLE, sizeof(SMFLIB_QUERY_REPAIR_TABLE) - 1, *table, SQL_query);
				SMFLIB_CALL_FUNCTION_ZBB("db_query", SQL_query, 0, 0, retval);
				zval_ptr_dtor(&retval);
				zval_ptr_dtor(&SQL_query);

				zend_hash_move_forward_ex(Z_ARRVAL_P(fix_tables), &pos);
			}

			// PHP: sendmail($webmaster_email, $txt[1001], $txt[1005]);
			if (SMFLIB_GET_GLOBAL_Z(webmaster_email) && SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1005, txt_1005))
			{
				SMFLIB_CALL_FUNCTION_ZZZ("sendmail", *webmaster_email, *txt_1001, *txt_1005, retval);
				zval_ptr_dtor(&retval);
			}

			// PHP: $modSettings['cache_enable'] = $old_cache;
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*modSettings, "cache_enable", old_cache);

			// PHP: $ret = db_query($db_string, false, false);
			SMFLIB_CALL_FUNCTION_ZBB("db_query", db_string, 0, 0, ret);

			// PHP: if ($ret !== false)
			if (Z_TYPE_P(ret) == IS_BOOL && Z_LVAL_P(ret) == 0)
			{
				// PHP: return $ret;
				zval_ptr_dtor(&cur_time);
				zval_ptr_dtor(&fix_tables);
				zval_ptr_dtor(&query_error);
				RETURN_ZVAL(ret, 1, 1);
			}
			zval_ptr_dtor(&ret);
		}
		// PHP: else
		else
		{
			// PHP: $modSettings['cache_enable'] = $old_cache;
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*modSettings, "cache_enable", old_cache);
		}
		zval_ptr_dtor(&cur_time);
		if (fix_tables)
			zval_ptr_dtor(&fix_tables);

		// PHP: if (strpos($query_error, 'Lost connection') !== false || strpos($query_error, 'Unable to save result set') !== false || strpos($query_error, 'Deadlock found when trying to get lock') !== false || strpos($query_error, 'Lock wait timeout exceeded;') !== false)
		// PHP: if (strpos($query_error, 'Lost connection') !== false || strpos($query_error, 'server has gone away') !== false || strpos($query_error, 'Unable to save result set') !== false || strpos($query_error, 'Deadlock found when trying to get lock') !== false || strpos($query_error, 'Lock wait timeout exceeded;') !== false)
		if (SMFLIB_STRPOS_ZC(query_error, "Lost connection") || SMFLIB_STRPOS_ZC(query_error, "server has gone away") || SMFLIB_STRPOS_ZC(query_error, "Unable to save result set") || SMFLIB_STRPOS_ZC(query_error, "Deadlock found when trying to get lock") || SMFLIB_STRPOS_ZC(query_error, "Lock wait timeout exceeded;"))
		{
			// PHP: if (strpos($query_error, 'Lost connection') !== false || strpos($query_error, 'server has gone away') !== false)
			if (SMFLIB_STRPOS_ZC(query_error, "Lost connection") || SMFLIB_STRPOS_ZC(query_error, "server has gone away"))
			{
				if (!SMFLIB_GET_GLOBAL_Z(db_server) || !SMFLIB_GET_GLOBAL_Z(db_user) || !SMFLIB_GET_GLOBAL_Z(db_passwd) || !SMFLIB_GET_GLOBAL_Z(db_name))
					php_error(E_ERROR, "smflib_db_error(): unable to retrieve database settings");

				//php_set_error_handling(EH_SUPPRESS, NULL TSRMLS_CC);

				// PHP: if (empty($db_persist))
				if (SMFLIB_GET_GLOBAL_Z(db_persist) && !SMFLIB_EMPTY_PP(db_persist))
				{
					// PHP: $db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
					SMFLIB_CALL_FUNCTION_ZZZ("mysql_connect", *db_server, *db_user, *db_passwd, retval);
					SMFLIB_SET_GLOBAL_CZZ("db_connection", retval, db_connection);
				}
				else
				{
					// PHP: $db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
					SMFLIB_CALL_FUNCTION_ZZZ("mysql_pconnect", *db_server, *db_user, *db_passwd, retval);
					SMFLIB_SET_GLOBAL_CZZ("db_connection", retval, db_connection);
				}

				// PHP: if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
				if (Z_TYPE_PP(db_connection) == IS_RESOURCE)
				{
					zval *db_select_result;

					SMFLIB_CALL_FUNCTION_ZZ("mysql_select_db", *db_name, *db_connection, db_select_result);
					if (Z_LVAL_P(db_select_result) != 0)
					{
						// PHP: $db_connection = false;
						SMFLIB_SET_GLOBAL_CBZ("db_connection", 0, db_connection);
					}
					zval_ptr_dtor(&db_select_result);
				}
				//php_std_error_handling();
			}
			// PHP: if ($db_connection)
			if (Z_TYPE_PP(db_connection) == IS_RESOURCE)
			{
				// PHP: $ret = db_query($db_string, false, false);
				SMFLIB_CALL_FUNCTION_ZBB("db_query", db_string, 0, 0, ret);

				// PHP: if ($ret !== false)
				if (Z_TYPE_P(ret) != IS_BOOL || Z_LVAL_P(ret) != 0)
				{
					// PHP: return $ret;
					zval_ptr_dtor(&query_error);
					RETURN_ZVAL(ret, 1, 1);
				}
			}
		}

		// PHP: elseif (strpos($query_error, 'error -1 from table') !== false || strpos($query_error, 'error 28 from table') !== false || strpos($query_error, 'error 12 from table') !== false)
		else if (SMFLIB_STRPOS_ZC(query_error, "error -1 from table") || SMFLIB_STRPOS_ZC(query_error, "error 28 from table") || SMFLIB_STRPOS_ZC(query_error, "error 12 from table"))
		{
			// PHP: if (!isset($txt))
			if (!SMFLIB_GET_GLOBAL_Z(txt))
			{
				// PHP: $query_error .= ' - check database storage space.';
				input_str = query_error;
				SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_CHECK_SPACE, sizeof(SMFLIB_FORMAT_CHECK_SPACE) - 1, input_str, query_error);
				zval_ptr_dtor(&input_str);
			}
			// PHP: else
			else
			{
				zval **mysql_error_space;

				// PHP: if (!isset($txt['mysql_error_space']))
				if (!SMFLIB_GET_KEY_VAL_ZZ(*txt, mysql_error_space))
				{
					// PHP: loadLanguage('Errors');
					SMFLIB_CALL_FUNCTION_S("loadLanguage", "Errors", sizeof("Errors") - 1, retval);
					zval_ptr_dtor(&retval);
				}
				// PHP: $query_error .= !isset($txt['mysql_error_space']) ? ' - check database storage space.' : $txt['mysql_error_space'];
				input_str = query_error;
				if (SMFLIB_GET_KEY_VAL_ZZ(*txt, mysql_error_space))
				{
					SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s%1$s", sizeof("%1$s%1$s") - 1, input_str, *mysql_error_space, query_error);
				}
				else
				{
					SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_CHECK_SPACE, sizeof(SMFLIB_FORMAT_CHECK_SPACE) - 1, input_str, query_error);
				}
				zval_ptr_dtor(&input_str);
			}
		}
	}

	// PHP: if (empty($context) || empty($txt))
	if (!SMFLIB_GET_GLOBAL_Z(context) || SMFLIB_EMPTY_PP(context) || !SMFLIB_GET_GLOBAL_Z(txt) || SMFLIB_EMPTY_PP(txt))
	{
		// PHP: die($query_error);
		zend_print_zval(query_error, 0);
		zval_ptr_dtor(&query_error);
		zend_bailout();
	}

	// PHP: $context['error_title'] = $txt[1001];
	SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "error_title", *txt_1001);

	// PHP: if (allowedTo('admin_forum'))
	SMFLIB_CALL_FUNCTION_S("allowedTo", "admin_forum", sizeof("admin_forum") - 1, retval);
	can_admin_forum = Z_TYPE_P(retval) == IS_BOOL && Z_LVAL_P(retval);
	zval_ptr_dtor(&retval);
	if (can_admin_forum)
	{
		zval *nl2br_query_error, *compiled_error_msg;

		if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1003, txt_1003))
			php_error(E_ERROR, "db_error(): $txt[1003] not set");
		if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1004, txt_1004))
			php_error(E_ERROR, "db_error(): $txt[1004] not set");

		// PHP: $context['error_message'] = nl2br($query_error) . '<br />' . $txt[1003] . ': ' . $file . '<br />' . $txt[1004] . ': ' . $line;
		SMFLIB_CALL_FUNCTION_Z("nl2br", query_error, nl2br_query_error);
		SMFLIB_CALL_FUNCTION_SZZZZZ("sprintf", SMFLIB_FORMAT_ERROR_CONTEXT, sizeof(SMFLIB_FORMAT_ERROR_CONTEXT) - 1, nl2br_query_error, *txt_1003, file, *txt_1004, line, compiled_error_msg);
		zval_ptr_dtor(&nl2br_query_error);
		SMFLIB_SET_KEY_VAL_ZCZ(*context, "error_message", compiled_error_msg);
	}
	// PHP: else
	else
	{
		// PHP: $context['error_message'] = $txt[1002];
		if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 1002, txt_1002))
			php_error(E_ERROR, "db_error(): $txt[1002] not set");
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "error_message", *txt_1002);
	}
	zval_ptr_dtor(&query_error);

	if (!SMFLIB_GET_KEY_VAL_ZZ(*context, error_message))
		php_error(E_ERROR, "db_error(): Unable to set $context['error_message']");

	// PHP: if (allowedTo('admin_forum') && !empty($forum_version) && $forum_version != 'SMF ' . @$modSettings['smfVersion'] && strpos($forum_version, 'Demo') === false && strpos($forum_version, 'CVS') === false)
	if (can_admin_forum && SMFLIB_GET_GLOBAL_Z(forum_version) && !SMFLIB_EMPTY_PP(forum_version) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, smfVersion) && Z_STRLEN_PP(forum_version) > 4 && SMFLIB_CMP_NOT_EQ_SZ(Z_STRVAL_PP(forum_version) + 4, Z_STRLEN_PP(forum_version) - 4, *smfVersion) && !SMFLIB_STRPOS_ZC(*forum_version, "Demo") && !SMFLIB_STRPOS_ZC(*forum_version, "CVS"))
	{
		// PHP: $context['error_message'] .= '<br /><br />' . $txt['database_error_versions'];
		//!!! This doesn't do sprintf
		if (SMFLIB_GET_KEY_VAL_ZZ(*txt, database_error_versions))
		{
			input_str = *error_message;
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s<br /><br />%2$s", sizeof("%1$s<br /><br />%2$s") - 1, input_str, *database_error_versions, *error_message);
			zval_ptr_dtor(&input_str);
		}
	}

	// PHP: if (allowedTo('admin_forum') && isset($db_show_debug) && $db_show_debug === true)
	if (can_admin_forum && SMFLIB_GET_GLOBAL_Z(db_show_debug) && Z_TYPE_PP(db_show_debug) == IS_BOOL && Z_LVAL_PP(db_show_debug) != 0)
	{
		zval *nl2br_db_string;

		// PHP: $context['error_message'] .= '<br /><br />' . nl2br($db_string);
		SMFLIB_CALL_FUNCTION_Z("nl2br", db_string, nl2br_db_string);
		input_str = *error_message;
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s<br /><br />%2$s", sizeof("%1$s<br /><br />%2$s") - 1, input_str, nl2br_db_string, *error_message);
		zval_ptr_dtor(&input_str);
		zval_ptr_dtor(&nl2br_db_string);
	}

	// PHP: fatal_error($context['error_message'], false);
	SMFLIB_CALL_FUNCTION_ZB("fatal_error", *error_message, 0, retval);
	zval_ptr_dtor(&retval);
}

PHP_FUNCTION(smflib_fatal_error)
{
	// Input parameters.
	zval *error, *log = NULL;

	// Global variables.
	zval **txt, **context = NULL, **modSettings;

	// Global hash variables.
	zval **error_title, **txt_106, **enableErrorLogging, **page_title;

	// Local variables.
	zval *wireless, *retval;

	// prototype: void fatal_error($error, $log = true);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &error, &log) == FAILURE)
		RETURN_NULL();

	// PHP: if (empty($txt))
	if (!SMFLIB_GET_GLOBAL_Z(txt))
	{
		// PHP: die($error);
		zend_print_zval(error, 0);
		zend_bailout();
	}

	// PHP: if (!isset($context['error_title']))
	if (!SMFLIB_GET_GLOBAL_Z(context) || !SMFLIB_GET_KEY_VAL_ZZ(*context, error_title))
	{
		// PHP: $context['error_title'] = $txt[106];
		if (!context)
		{
			zval *empty_var;

			ALLOC_INIT_ZVAL(empty_var);
			array_init(empty_var);
			SMFLIB_SET_GLOBAL_CZZ("context", empty_var, context);
		}
		if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 106, txt_106))
			php_error(E_ERROR, "fatal_error(): $txt[106] not set");
		SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*context, "error_title", *txt_106, error_title);

		// PHP: $context['error_message'] = $log || (!empty($modSettings['enableErrorLogging']) && $modSettings['enableErrorLogging'] == 2) ? log_error($error) : $error;
		if (!log || (Z_TYPE_P(log) == IS_BOOL && Z_LVAL_P(log) != 0) || (SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, enableErrorLogging) && SMFLIB_Z_TO_L(*enableErrorLogging) == 2))
		{
			SMFLIB_CALL_FUNCTION_Z("log_error", error, retval);
			SMFLIB_SET_KEY_VAL_ZCZ(*context, "error_message", retval);
		}
		else
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "error_message", error);
	}

	// PHP: if (!isset($context['page_title']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*context, page_title))
	{
		// PHP: $context['page_title'] = $context['error_title'];
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "page_title", *error_title);
	}

	// PHP: if (WIRELESS)
	ALLOC_INIT_ZVAL(wireless);
	if (zend_get_constant("WIRELESS", sizeof("WIRELESS") - 1, wireless TSRMLS_CC) && !SMFLIB_EMPTY_P(wireless))
	{
		zval *wireless_protocol, *wireless_template;

		// PHP: $context['sub_template'] = WIRELESS_PROTOCOL . '_error';
		ALLOC_INIT_ZVAL(wireless_protocol);
		if (!zend_get_constant("WIRELESS_PROTOCOL", sizeof("WIRELESS_PROTOCOL") - 1, wireless_protocol TSRMLS_CC))
			php_error(E_ERROR, "fatal_error(): WIRELESS_PROTOCOL not set");
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s_error", sizeof("%1$s_error") - 1, wireless_protocol, wireless_template);
		zval_ptr_dtor(&wireless_protocol);
		SMFLIB_SET_KEY_VAL_ZCZ(*context, "sub_template", wireless_template);
	}
	// PHP: else
	else
	{
		// PHP: loadTemplate('Errors');
		SMFLIB_CALL_FUNCTION_S("loadTemplate", "Errors", sizeof("Errors") - 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: $context['sub_template'] = 'fatal_error';
		SMFLIB_SET_KEY_VAL_ZCC(*context, "sub_template", "fatal_error");
	}
	zval_ptr_dtor(&wireless);

	// PHP: obExit(null, true);
	SMFLIB_CALL_FUNCTION_NB("obExit", 1, retval);
	zval_ptr_dtor(&retval);

	// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
	SMFLIB_CALL_FUNCTION_SL("trigger_error", "Hacking attempt...", sizeof("Hacking attempt...") - 1, E_USER_ERROR, retval);
	zval_ptr_dtor(&retval);
}

PHP_FUNCTION(smflib_fatal_lang_error)
{
	// Input parameters.
	zval *error, *log = NULL, *sprintf = NULL;

	// Global variables.
	zval **txt;

	// Global hash variables.
	zval **txt_error;

	// Local variables.
	zval *retval, *_log;

	// prototype: void fatal_lang_error($error, $log = true, $sprintf = array());
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &error, &log) == FAILURE)
		RETURN_NULL();

	// PHP: loadLanguage('Errors');
	SMFLIB_CALL_FUNCTION_S("loadLanguage", "Errors", sizeof("Errors") - 1, retval);
	zval_ptr_dtor(&retval);

	// Get the proper $txt.
	if (!SMFLIB_GET_GLOBAL_Z(txt))
		php_error(E_ERROR, "fatal_lang_error(): $txt not set");
	if (Z_TYPE_P(error) == IS_LONG && !SMFLIB_GET_KEY_VAL_ZLZ(*txt, Z_LVAL_P(error), txt_error))
		php_error(E_ERROR, "fatal_lang_error(): $txt[%d] not set", Z_LVAL_P(error));
	else if (Z_TYPE_P(error) == IS_STRING && zend_hash_find(Z_ARRVAL_PP(txt), Z_STRVAL_P(error), Z_STRLEN_P(error) + 1, (void**) &txt_error) == FAILURE)
		php_error(E_ERROR, "fatal_lang_error(): $txt['%s'] not set", Z_STRVAL_P(error));

	// $log might not be set. Make sure it is.
	ALLOC_INIT_ZVAL(_log);
	if (log)
	{
		ZVAL_ZVAL(_log, log, 1, 0);
	}

	// PHP: if (empty($sprintf))
	if (!sprintf || SMFLIB_EMPTY_P(sprintf))
	{
		// PHP: fatal_error($txt[$error], $log);
		SMFLIB_CALL_FUNCTION_ZZ("fatal_error", *txt_error, _log, retval);
		zval_ptr_dtor(&retval);
	}
	// PHP: else
	else
	{
		zval *vsprintf_error;

		// PHP: fatal_error(vsprintf($txt[$error], $sprintf), $log);
		SMFLIB_CALL_FUNCTION_ZZ("vsprintf", *txt_error, sprintf, vsprintf_error);
		SMFLIB_CALL_FUNCTION_ZZ("fatal_error", vsprintf_error, _log, retval);
		zval_ptr_dtor(&vsprintf_error);
		zval_ptr_dtor(&retval);
	}
	zval_ptr_dtor(&_log);
}

PHP_FUNCTION(smflib_error_handler)
{
	// Input parameters.
	zval *error_level, *error_string, *file, *line, *errcontext;

	// Global variables.
	zval **settings, **modSettings, **db_show_debug;

	// Global hash variables.
	zval **enableErrorLogging, **current_include_filename;

	// Local variables.
	zval *retval, *e_strict, *_file, *message, *logged_error;

	// prototype: void error_handler($error_level, $error_string, $file, $line);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zzzz|z", &error_level, &error_string, &file, &line, &errcontext) == FAILURE)
		RETURN_NULL();

	// PHP: if (error_reporting() == 0 || (defined('E_STRICT') && $error_level == E_STRICT && (empty($modSettings['enableErrorLogging']) || $modSettings['enableErrorLogging'] != 2)))
	ALLOC_INIT_ZVAL(e_strict);
	if (EG(error_reporting) == 0 || (zend_get_constant("E_STRICT", sizeof("E_STRICT") - 1, e_strict TSRMLS_CC) && Z_LVAL_P(error_level) == E_STRICT && (!SMFLIB_GET_GLOBAL_Z(modSettings) || !SMFLIB_GET_KEY_VAL_ZZ(*modSettings, enableErrorLogging) || SMFLIB_Z_TO_L(*enableErrorLogging) != 2)))
	{
		// PHP: return;
		RETURN_NULL();
	}
	zval_ptr_dtor(&e_strict);

	// PHP: if (strpos($file, 'eval()') !== false && !empty($settings['current_include_filename']))
	if (SMFLIB_STRPOS_ZC(file, "eval()") && SMFLIB_GET_GLOBAL_Z(settings) && SMFLIB_GET_KEY_VAL_ZZ(*settings, current_include_filename) && !SMFLIB_EMPTY_PP(current_include_filename))
	{
		zval *array, *realpath_include_filename;
		zval **dummy, **array_i = NULL, **function, **args, **args_0;
		int array_size, i = 0;

		// Sooner or later, we're gonna need this.
		SMFLIB_CALL_FUNCTION_Z("realpath", *current_include_filename, realpath_include_filename);

		// PHP: if (function_exists('debug_backtrace'))
		if (zend_hash_find(CG(function_table), "debug_backtrace", sizeof("debug_backtrace"), (void**) &dummy) == SUCCESS)
		{
			// PHP: $array = debug_backtrace();
			SMFLIB_CALL_FUNCTION("debug_backtrace", array);

			// PHP: for ($i = 0; $i < count($array); $i++)
			array_size = zend_hash_num_elements(Z_ARRVAL_P(array));
			for (i = 0; i < array_size; i++)
			{
				// PHP: if ($array[$i]['function'] != 'loadSubTemplate')
				if (!SMFLIB_GET_KEY_VAL_ZLZ(array, i, array_i) || !SMFLIB_GET_KEY_VAL_ZZ(*array_i, function) || SMFLIB_CMP_NOT_EQ_ZC(*function, "loadSubTemplate"))
				{
					// PHP: continue;
					continue;
				}

				// PHP: if (empty($array[$i]['args']))
				if (!SMFLIB_GET_KEY_VAL_ZZ(*array_i, args) || SMFLIB_EMPTY_PP(args))
				{
					// PHP: $i++;
					i++;
				}
				// PHP: break;
				break;
			}

			// PHP: if (isset($array[$i]) && !empty($array[$i]['args']))
			if (SMFLIB_GET_KEY_VAL_ZLZ(array, i, array_i) && SMFLIB_GET_KEY_VAL_ZZ(*array_i, args) && !SMFLIB_EMPTY_PP(args) && SMFLIB_GET_KEY_VAL_ZLZ(*args, 0, args_0))
			{
				// PHP: $file = realpath($settings['current_include_filename']) . ' (' . $array[$i]['args'][0] . ' sub template - eval?)';
				SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s (%2$s sub template - eval?)", sizeof("%1$s (%2$s sub template - eval?)") - 1, realpath_include_filename, *args_0, _file);
			}
			// PHP: else
			else
			{
				// PHP: $file = realpath($settings['current_include_filename']) . ' (eval?)';
				SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s (eval?)", sizeof("%1$s (eval?)") - 1, realpath_include_filename, _file);
			}
			zval_ptr_dtor(&array);
		}
		// PHP: else
		else
		{
			// PHP: $file = realpath($settings['current_include_filename']) . ' (eval?)';
			SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s (eval?)", sizeof("%1$s (eval?)") - 1, realpath_include_filename, _file);
		}
		zval_ptr_dtor(&realpath_include_filename);
	}
	else
	{
		// Initialize _file as a copy of input parameter $file.
		ALLOC_INIT_ZVAL(_file);
		ZVAL_STRINGL(_file, Z_STRVAL_P(file), Z_STRLEN_P(file), 1);
	}

	// PHP: if (isset($db_show_debug) && $db_show_debug === true)
	if (SMFLIB_GET_GLOBAL_Z(db_show_debug) && Z_TYPE_PP(db_show_debug) == IS_BOOL && Z_LVAL_PP(db_show_debug) != 0)
	{
		zval *errShown, *compiled_error;

		// PHP: if ($error_level % 255 != E_ERROR)
		if (Z_LVAL_P(error_level) % 255 != E_ERROR)
		{
			zval *temporary;

			// PHP: $temporary = ob_get_contents();
			SMFLIB_CALL_FUNCTION("ob_get_contents", temporary);

			// PHP: if (substr($temporary, -2) == '="')
			if (Z_STRLEN_P(temporary) >= 2 && SMFLIB_CMP_EQ_SC(Z_STRVAL_P(temporary) + Z_STRLEN_P(temporary) - 2, 2, "=\""))
			{
				// PHP: echo '"';
				PHPWRITE("\"", sizeof("\"") - 1);
			}
			zval_ptr_dtor(&temporary);
		}
		/* PHP: echo '<br />
		<strong>', $error_level % 255 == E_ERROR ? 'Error' : ($error_level % 255 == E_WARNING ? 'Warning' : 'Notice'), '</strong>: ', $error_string, ' in <strong>', $file, '</strong> on line <strong>', $line, '</strong><br />';*/
		ALLOC_INIT_ZVAL(errShown);
		if (Z_LVAL_P(error_level) % 255 == E_ERROR)
		{
			ZVAL_STRINGL(errShown, "Error", sizeof("Error") - 1, 1);
		}
		else if (Z_LVAL_P(error_level) % 255 == E_WARNING)
		{
			ZVAL_STRINGL(errShown, "Warning", sizeof("Warning") - 1, 1);
		}
		else
		{
			ZVAL_STRINGL(errShown, "Notice", sizeof("Notice") - 1, 1);
		}
		SMFLIB_CALL_FUNCTION_SZZZZ("sprintf", SMFLIB_FORMAT_ERROR_OR_WARNING, sizeof(SMFLIB_FORMAT_ERROR_OR_WARNING) - 1, errShown, error_string, _file, line, compiled_error)
		zval_ptr_dtor(&errShown);
		PHPWRITE(Z_STRVAL_P(compiled_error), Z_STRLEN_P(compiled_error));
		zval_ptr_dtor(&compiled_error);
	}

	// PHP: $message = log_error($error_level . ': ' . $error_string, $file, $line);
	SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$d: %2$s", sizeof("%1$d: %2$s") - 1, error_level, error_string, logged_error);
	SMFLIB_CALL_FUNCTION_ZZZ("log_error", logged_error, _file, line, message);
	zval_ptr_dtor(&logged_error);

	// Dying on these errors only causes MORE problems (blank pages!)
	// PHP: if ($file == 'Unknown')
	if (SMFLIB_CMP_EQ_ZC(_file, "Unknown"))
	{
		// PHP: return;
		zval_ptr_dtor(&message);
		zval_ptr_dtor(&_file);
		RETURN_NULL();
	}
	zval_ptr_dtor(&_file);

	// If this is an E_ERROR or E_USER_ERROR.... die.  Violently so.
	// PHP: if ($error_level % 255 == E_ERROR)
	if (Z_LVAL_P(error_level) % 255 == E_ERROR)
	{
		// PHP: obExit(false);
		SMFLIB_CALL_FUNCTION_B("obExit", 0, retval);
		zval_ptr_dtor(&retval);
	}
	// PHP: else
	else
	{
		// PHP: return;
		zval_ptr_dtor(&message);
		RETURN_NULL();
	}

	// If this is an E_ERROR, E_USER_ERROR, E_WARNING, or E_USER_WARNING.... die.  Violently so.
	// PHP: if ($error_level % 255 == E_ERROR || $error_level % 255 == E_WARNING)
	if (Z_LVAL_P(error_level) % 255 == E_ERROR || Z_LVAL_P(error_level) % 255 == E_WARNING)
	{
		zval *shown_string;
		// PHP: fatal_error(allowedTo('admin_forum') ? $message : $error_string, false);
		SMFLIB_CALL_FUNCTION_S("allowed_to", "admin_forum", sizeof("admin_forum") - 1, retval);
		shown_string = Z_LVAL_P(retval) != 0 ? message : error_string;
		zval_ptr_dtor(&retval);
		SMFLIB_CALL_FUNCTION_ZB("fatal_error", shown_string, 0, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&shown_string);
	}
	zval_ptr_dtor(&message);

	// PHP: if ($error_level % 255 == E_ERROR)
	if (Z_LVAL_P(error_level) % 255 == E_ERROR)
	{
		// PHP: die('Hacking attempt...');
		PHPWRITE("Hacking attempt...", sizeof("Hacking attempt..."));
		zend_bailout();

	}
}

PHP_FUNCTION(smflib_db_fatal_error)
{
	// Input parameters.
	zval *loadavg = NULL;

	// Global variables.
	zval **sourcedir;

	// Local variables.
	zval *subs_auth_php, *retval;

	// prototype: bool db_fatal_error(bool loadavg = false);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|z", &loadavg) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
		php_error(E_ERROR, "db_fatal_error(): $sourcedir not set");

	// PHP: require_once($sourcedir . '/Subs-Auth.php');
	SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Subs-Auth.php", sizeof("%1$s/Subs-Auth.php") - 1, *sourcedir, subs_auth_php);
	smflib_require_once(Z_STRVAL_P(subs_auth_php), Z_STRLEN_P(subs_auth_php) TSRMLS_CC);
	zval_ptr_dtor(&subs_auth_php);

	// PHP: show_db_error($loadavg);
	SMFLIB_CALL_FUNCTION_Z("show_db_error", loadavg, retval);
	zval_ptr_dtor(&retval);

	// PHP: return false;
	RETURN_FALSE;
}
