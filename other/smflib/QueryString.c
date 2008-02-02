/****
/**********************************************************************************
* QueryString.c                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2.1                                    *
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

// Based on QueryString.php CVS 1.66

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"

#include "smflib_function_calls.h"
#include "php_smflib.h"

// For ENT_QUOTES
#include "ext/standard/html.h"

// For php_version_compare().
#include "ext/standard/php_versioning.h"


PHP_FUNCTION(smflib_cleanRequest)
{
	// Global variables.
	zval **boardurl, **modSettings, **_SERVER, **_GET, **_ENV, **_POST;
	zval **_COOKIE, **_FILES, **_REQUEST, **threadid, **board, **topic;
	zval **scripturl;

	// Global hash variables.
	zval **QUERY_STRING, **integrate_magic_quotes, **REQUEST_URI = NULL;
	zval **_REQUEST_board, **_REQUEST_start, **_REQUEST_topic;
	zval **REMOTE_ADDR = NULL, **HTTP_X_FORWARDED_FOR = NULL;
	zval **HTTP_CLIENT_IP = NULL, **HTTP_USER_AGENT;

	// Local variables.
	zval *retval, *str_input, *tmp_copy, *exploded_str, *reversed_array;
	char *arg_seperator = INI_STR("arg_separator.input");
	zval **entry, **list_result;
	zend_bool integrate_mq, bad_client_ip, bad_remote_addr;
	HashPosition pos;

	// Some globals should be set or results might be unpredictable.
	if (!SMFLIB_GET_GLOBAL_Z(boardurl) || Z_TYPE_PP(boardurl) != IS_STRING)
		php_error(E_ERROR, "cleanRequest(): $boardurl was not set or invalid");
	if (!SMFLIB_GET_GLOBAL_Z(modSettings) || Z_TYPE_PP(modSettings) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $modSettings was not set or invalid");

	// Eventhough the superglobals might be empty, they should all be set.
	if (!SMFLIB_GET_GLOBAL_Z(_SERVER) || Z_TYPE_PP(_SERVER) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_SERVER was not set or invalid");
	if (!SMFLIB_GET_GLOBAL_Z(_ENV) || Z_TYPE_PP(_ENV) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_ENV not set");
	if (!SMFLIB_GET_GLOBAL_Z(_POST) || Z_TYPE_PP(_POST) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_POST not set");
	if (!SMFLIB_GET_GLOBAL_Z(_COOKIE) || Z_TYPE_PP(_COOKIE) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_COOKIE not set");
	if (!SMFLIB_GET_GLOBAL_Z(_GET) || Z_TYPE_PP(_GET) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_GET not set");
	if (!SMFLIB_GET_GLOBAL_Z(_FILES) || Z_TYPE_PP(_FILES) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_FILES not set");
	if (!SMFLIB_GET_GLOBAL_Z(_REQUEST) || Z_TYPE_PP(_REQUEST) != IS_ARRAY)
		php_error(E_ERROR, "cleanRequest(): $_REQUEST not set");

	integrate_mq = SMFLIB_GET_KEY_VAL_ZZ(*modSettings, integrate_magic_quotes) && !SMFLIB_EMPTY_PP(integrate_magic_quotes);

	// PHP: $scripturl = $boardurl . '/index.php';
	SMFLIB_CALL_FUNCTION_SZ("sprintf", "%s/index.php", sizeof("%s/index.php") - 1, *boardurl, retval);
	SMFLIB_SET_GLOBAL_CZ("scripturl", retval);
	SMFLIB_GET_GLOBAL_Z(scripturl);

	// PHP: unset($GLOBALS['HTTP_POST_VARS']);
	//zend_hash_del(&EG(symbol_table), "HTTP_POST_VARS", sizeof("HTTP_POST_VARS"));
	SMFLIB_UNSET_GLOBAL_C("HTTP_POST_VARS");

	// PHP: unset($GLOBALS['HTTP_POST_FILES']);
	//zend_hash_del(&EG(symbol_table), "HTTP_POST_FILES", sizeof("HTTP_POST_FILES"));
	SMFLIB_UNSET_GLOBAL_C("HTTP_POST_FILES");

	// PHP: if (!isset($_SERVER['QUERY_STRING']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, QUERY_STRING))
	{
		zval *env_query_string;

		//PHP: $_SERVER['QUERY_STRING'] = getenv('QUERY_STRING');
		SMFLIB_CALL_FUNCTION_S("getenv", "QUERY_STRING", sizeof("QUERY_STRING") - 1, env_query_string);
		SMFLIB_SET_KEY_VAL_ZCZZ(*_SERVER, "QUERY_STRING", env_query_string, QUERY_STRING);
	}

	//php_set_error_handling(EH_SUPPRESS, NULL TSRMLS_CC);
	//php_std_error_handling();

	// PHP: if ((strpos(@ini_get('arg_separator.input'), ';') === false || @version_compare(PHP_VERSION, '4.2.0') == -1) && !empty($_SERVER['QUERY_STRING']))
	if ((!SMFLIB_STRPOS_SC(arg_seperator, strlen(arg_seperator), ";") || php_version_compare(PHP_VERSION, "4.2.0") == -1) && !SMFLIB_EMPTY_PP(QUERY_STRING))
	{
		zval *ampersand_QUERY_STRING, *parsable_QUERY_STRING, *decoded_URL;
		zval **REDIRECT_QUERY_STRING;

		// PHP: $_GET = array();
		SMFLIB_EMPTY_ARR_Z(*_GET);

		// PHP: $_SERVER['QUERY_STRING'] = urldecode(substr($_SERVER['QUERY_STRING'], 0, 5) == 'url=/' ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING']);
		if (Z_STRLEN_PP(QUERY_STRING) >= 5 && SMFLIB_CMP_EQ_SC(Z_STRVAL_PP(QUERY_STRING), 5, "url=/"))
		{
			if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, REDIRECT_QUERY_STRING))
				php_error(E_ERROR, "cleanRequest(): unable to find $_SERVER['REDIRECT_QUERY_STRING']");
			SMFLIB_CALL_FUNCTION_Z("urldecode", *REDIRECT_QUERY_STRING, decoded_URL);
		}
		else
		{
			SMFLIB_CALL_FUNCTION_Z("urldecode", *QUERY_STRING, decoded_URL);
		}
		SMFLIB_SET_KEY_VAL_ZCZZ(*_SERVER, "QUERY_STRING", decoded_URL, QUERY_STRING);


		// PHP: if (get_magic_quotes_gpc() != 0 && empty($modSettings['integrate_magic_quotes']))
		if (PG(magic_quotes_gpc) && !integrate_mq)
		{
			// PHP: $_SERVER['QUERY_STRING'] = stripslashes($_SERVER['QUERY_STRING']);
			SMFLIB_CALL_FUNCTION_Z("stripslashes", *QUERY_STRING, retval);
			SMFLIB_SET_KEY_VAL_ZCZZ(*_SERVER, "QUERY_STRING", retval, QUERY_STRING);
		}

		// PHP: parse_str(preg_replace('/&(\w+)(?=&|$)/', '&$1=', strtr($_SERVER['QUERY_STRING'], ';', '&')), $_GET);
		SMFLIB_CALL_FUNCTION_ZSS("strtr", *QUERY_STRING, ";", sizeof(";") - 1, "&", sizeof("&") - 1, ampersand_QUERY_STRING);
		SMFLIB_CALL_FUNCTION_SSZ("preg_replace", "/&(\\w+)(?=&|$)/", sizeof("/&(\\w+)(?=&|$)/") - 1, "&$1=", sizeof("&$1=") - 1, ampersand_QUERY_STRING, parsable_QUERY_STRING);
		zval_ptr_dtor(&ampersand_QUERY_STRING);
		SMFLIB_CALL_FUNCTION_ZZ("parse_str", parsable_QUERY_STRING, *_GET, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&parsable_QUERY_STRING);
	}

	// PHP: elseif (strpos(@ini_get('arg_separator.input'), ';') !== false)
	else if (SMFLIB_STRPOS_SC(arg_seperator, strlen(arg_seperator), ";"))
	{
		// PHP: $_GET = urldecode__recursive($_GET);
		SMFLIB_CALL_FUNCTION_Z("urldecode__recursive", *_GET, retval);
		SMFLIB_SET_GLOBAL_CZZ("_GET", retval, _GET);

		// PHP: if (get_magic_quotes_gpc() != 0 && empty($modSettings['integrate_magic_quotes']))
		if (PG(magic_quotes_gpc) && !integrate_mq)
		{
			// PHP: $_GET = stripslashes__recursive($_GET);
			SMFLIB_CALL_FUNCTION_Z("stripslashes__recursive", *_GET, retval);
			SMFLIB_SET_GLOBAL_CZZ("_GET", retval, _GET);
		}
	}

	// PHP: if (!empty($_SERVER['REQUEST_URI']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, REQUEST_URI) && !SMFLIB_EMPTY_PP(REQUEST_URI))
	{
		zval *scripturl_basename, *scripturl_addendum, *file_ext, *temp;
		zval *dot_pos;

		// PHP: if (get_magic_quotes_gpc() != 0 && empty($modSettings['integrate_magic_quotes']))
		if (!PG(magic_quotes_gpc) && !integrate_mq)
		{
			// PHP: $_SERVER['REQUEST_URI'] = stripslashes($_SERVER['REQUEST_URI']);
			SMFLIB_CALL_FUNCTION_Z("stripslashes", *REQUEST_URI, retval);
			SMFLIB_SET_KEY_VAL_ZCZZ(*_SERVER, "REQUEST_URI", retval, REQUEST_URI);
		}

		// PHP: if (substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '.'), 4) == '.htm')
		SMFLIB_CALL_FUNCTION_ZS("strrpos", *REQUEST_URI, ".", sizeof(".") - 1, dot_pos);
		SMFLIB_CALL_FUNCTION_ZZL("substr", *REQUEST_URI, dot_pos, 4, file_ext);
		if (SMFLIB_CMP_EQ_ZC(file_ext, ".htm"))
		{
			// PHP: $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '.'));
			SMFLIB_CALL_FUNCTION_ZLZ("substr", *REQUEST_URI, 0, dot_pos, retval);
			SMFLIB_SET_KEY_VAL_ZCZZ(*_SERVER, "REQUEST_URI", retval, REQUEST_URI);
		}
		zval_ptr_dtor(&dot_pos);
		zval_ptr_dtor(&file_ext);

		// PHP: parse_str(substr(preg_replace('/&(\w+)(?=&|$)/', '&$1=', strtr(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], basename($scripturl)) + strlen(basename($scripturl))), '/,', '&=')), 1), $temp);
		SMFLIB_CALL_FUNCTION_Z("basename", *scripturl, scripturl_basename);
		SMFLIB_CALL_FUNCTION_ZL("substr", *REQUEST_URI, SMFLIB_REL_STRPOS_ZZ(*REQUEST_URI, scripturl_basename) + Z_STRLEN_P(scripturl_basename), scripturl_addendum);
		zval_ptr_dtor(&scripturl_basename);
		str_input = scripturl_addendum;
		SMFLIB_CALL_FUNCTION_ZSS("strtr", str_input, "/,", sizeof("/,") - 1, "&=", sizeof("&=") - 1, scripturl_addendum);
		zval_ptr_dtor(&str_input);
		str_input = scripturl_addendum;
		SMFLIB_CALL_FUNCTION_SSZ("preg_replace", "/&(\\w+)(?=&|$)/", sizeof("/&(\\w+)(?=&|$)/") - 1, "&$1=", sizeof("&$1=") - 1, str_input, scripturl_addendum);
		zval_ptr_dtor(&str_input);
		str_input = scripturl_addendum;
		SMFLIB_CALL_FUNCTION_ZL("substr", str_input, 1, scripturl_addendum);
		zval_ptr_dtor(&str_input);
		ALLOC_INIT_ZVAL(temp);
		SMFLIB_CALL_FUNCTION_ZZ("parse_str", scripturl_addendum, temp, retval);
		zval_ptr_dtor(&retval);

		// PHP: $_GET += $temp;
		zend_hash_merge(Z_ARRVAL_PP(_GET), Z_ARRVAL_P(temp), (void (*)(void *pData)) zval_add_ref, (void *) &tmp_copy, sizeof(zval *), 0);
		zval_ptr_dtor(&temp);
		zval_ptr_dtor(&scripturl_addendum);
	}

	// PHP: $_GET = htmlspecialchars__recursive($_GET);
	SMFLIB_CALL_FUNCTION_Z("htmlspecialchars__recursive", *_GET, retval);
	SMFLIB_SET_GLOBAL_CZZ("_GET", retval, _GET);

	// PHP: if (get_magic_quotes_gpc() == 0 && empty($modSettings['integrate_magic_quotes']))
	if (!PG(magic_quotes_gpc) && !integrate_mq)
	{
		zval **file, **file_name;

		// PHP: $_ENV = addslashes__recursive($_ENV);
		SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *_ENV, retval);
		SMFLIB_SET_GLOBAL_CZ("_ENV", retval);

		// PHP: $_POST = addslashes__recursive($_POST);
		SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *_POST, retval);
		SMFLIB_SET_GLOBAL_CZZ("_POST", retval, _POST);

		// PHP: $_COOKIE = addslashes__recursive($_COOKIE);
		SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *_COOKIE, retval);
		SMFLIB_SET_GLOBAL_CZ("_COOKIE", retval);

		// PHP: $_SERVER = addslashes__recursive($_SERVER);
		SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *_SERVER, retval);
		SMFLIB_SET_GLOBAL_CZZ("_SERVER", retval, _SERVER);

		// PHP: foreach ($_FILES as $k => $dummy)
		zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(_FILES), &pos);
		while (zend_hash_get_current_data_ex(Z_ARRVAL_PP(_FILES), (void**) &file, &pos) == SUCCESS)
		{
			// PHP: $_FILES[$k]['name'] = addslashes__recursive($_FILES[$k]['name']);
			if (SMFLIB_GET_KEY_VAL_ZCZ(*file, "name", file_name))
			{
				SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *file_name, retval);
				SMFLIB_SET_KEY_VAL_ZCZ(*file, "name", retval);
			}
			zend_hash_move_forward_ex(Z_ARRVAL_PP(_FILES), &pos);
		}
	}

	// PHP: $_REQUEST = $_POST + $_GET;
	SMFLIB_SET_GLOBAL_CZZ_CPY("_REQUEST", *_POST, _REQUEST);
	zend_hash_merge(Z_ARRVAL_PP(_REQUEST), Z_ARRVAL_PP(_GET), (void (*)(void *pData)) zval_add_ref, (void *) &tmp_copy, sizeof(zval *), 0);

	// PHP: if (isset($_REQUEST['board']))
	if (SMFLIB_GET_KEY_VAL_ZCZ(*_REQUEST, "board", _REQUEST_board))
	{
		// Make sure $_REQUEST['board'] is a string value.
		if (Z_TYPE_PP(_REQUEST_board) != IS_STRING)
			convert_to_string(*_REQUEST_board);

		// PHP: if (strpos($_REQUEST['board'], '/') !== false)
		if (SMFLIB_STRPOS_ZC(*_REQUEST_board, "/"))
		{
			// PHP: list ($_REQUEST['board'], $_REQUEST['start']) = explode('/', $_REQUEST['board']);
			SMFLIB_CALL_FUNCTION_SZ("explode", "/", sizeof("/") - 1, *_REQUEST_board, exploded_str);
			if (SMFLIB_GET_KEY_VAL_ZLZ(exploded_str, 0, list_result))
				SMFLIB_SET_KEY_VAL_ZCSZ(*_REQUEST, "board", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result), _REQUEST_board);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 1, list_result))
				SMFLIB_SET_KEY_VAL_ZCS(*_REQUEST, "start", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result));
			zval_ptr_dtor(&exploded_str);
		}
		// PHP: elseif (strpos($_REQUEST['board'], '.') !== false)
		else if (SMFLIB_STRPOS_ZC(*_REQUEST_board, "."))
		{
			// PHP: list ($_REQUEST['board'], $_REQUEST['start']) = explode('.', $_REQUEST['board']);
			SMFLIB_CALL_FUNCTION_SZ("explode", ".", sizeof(".") - 1, *_REQUEST_board, exploded_str);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 0, list_result))
				SMFLIB_SET_KEY_VAL_ZCSZ(*_REQUEST, "board", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result), _REQUEST_board);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 1, list_result))
				SMFLIB_SET_KEY_VAL_ZCS(*_REQUEST, "start", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result));
			zval_ptr_dtor(&exploded_str);
		}

		// PHP: $board = (int) $_REQUEST['board'];
		SMFLIB_SET_GLOBAL_CLZ("board", strtol(Z_STRVAL_PP(_REQUEST_board), NULL, 10), board);

		// PHP: $_GET['board'] = $board;
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_GET, "board", *board);
	}
	else
	{
		// PHP: $board = 0;
		SMFLIB_SET_GLOBAL_CL("board", 0);
	}

	// PHP:if (isset($_REQUEST['threadid']) && !isset($_REQUEST['topic']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_REQUEST, threadid) && !SMFLIB_GET_KEY_VAL_ZCZ(*_REQUEST, "topic", _REQUEST_topic))
	{
		// PHP: $_REQUEST['topic'] = $_REQUEST['threadid'];
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_REQUEST, "topic", *threadid);
	}

	// PHP: if (isset($_REQUEST['topic']))
	if (SMFLIB_GET_KEY_VAL_ZCZ(*_REQUEST, "topic", _REQUEST_topic))
	{
		// Make sure $_REQUEST['topic'] is a string value.
		if (Z_TYPE_PP(_REQUEST_topic) != IS_STRING)
			convert_to_string(*_REQUEST_topic);

		// PHP: if (strpos($_REQUEST['topic'], '/') !== false)
		if (SMFLIB_STRPOS_ZC(*_REQUEST_topic, "/"))
		{
			// PHP: list ($_REQUEST['topic'], $_REQUEST['start']) = explode('/', $_REQUEST['topic']);
			SMFLIB_CALL_FUNCTION_SZ("explode", "/", sizeof("/") - 1, *_REQUEST_topic, exploded_str);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 0, list_result))
				SMFLIB_SET_KEY_VAL_ZCSZ(*_REQUEST, "topic", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result), _REQUEST_topic);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 1, list_result))
				SMFLIB_SET_KEY_VAL_ZCS(*_REQUEST, "start", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result));
			zval_ptr_dtor(&exploded_str);
		}
		// PHP: elseif (strpos($_REQUEST['topic'], '.') !== false)
		else if (SMFLIB_STRPOS_ZC(*_REQUEST_topic, "."))
		{
			// PHP: list ($_REQUEST['topic'], $_REQUEST['start']) = explode('.', $_REQUEST['topic']);
			SMFLIB_CALL_FUNCTION_SZ("explode", ".", sizeof(".") - 1, *_REQUEST_topic, exploded_str);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 0, list_result))
				SMFLIB_SET_KEY_VAL_ZCSZ(*_REQUEST, "topic", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result), _REQUEST_topic);
			if (SMFLIB_GET_KEY_VAL_ZLZ (exploded_str, 1, list_result))
				SMFLIB_SET_KEY_VAL_ZCS(*_REQUEST, "start", Z_STRVAL_PP(list_result), Z_STRLEN_PP(list_result));
			zval_ptr_dtor(&exploded_str);
		}

		// PHP: $topic = (int) $_REQUEST['topic'];
		SMFLIB_SET_GLOBAL_CLZ("topic", strtol(Z_STRVAL_PP(_REQUEST_topic), NULL, 10), topic);

		// PHP: $_GET['topic'] = $topic;
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_GET, "topic", *topic);
	}

	// PHP: if (empty($_REQUEST['start']) || $_REQUEST['start'] < 0)
	if (!SMFLIB_GET_KEY_VAL_ZCZ(*_REQUEST, "start", _REQUEST_start) || SMFLIB_EMPTY_PP(_REQUEST_start) || SMFLIB_Z_TO_L(*_REQUEST_start) < 0)
	{
		// PHP: $_REQUEST['start'] = 0;
		SMFLIB_SET_KEY_VAL_ZCL(*_REQUEST, "start", 0);
	}

	// Do some work in advance for the following if statements.
	if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, REMOTE_ADDR))
		SMFLIB_SET_KEY_VAL_ZCCZ(*_SERVER, "REMOTE_ADDR", "", REMOTE_ADDR);

	if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_CLIENT_IP))
	{
		SMFLIB_CALL_FUNCTION_SZ("preg_match", SMFLIB_PREG_BAD_IP, sizeof(SMFLIB_PREG_BAD_IP) - 1, *HTTP_CLIENT_IP, retval);
		bad_client_ip = Z_LVAL_P(retval) == 1;
		zval_ptr_dtor(&retval);
	}
	SMFLIB_CALL_FUNCTION_SZ("preg_match", SMFLIB_PREG_BAD_IP, sizeof(SMFLIB_PREG_BAD_IP) - 1, *REMOTE_ADDR, retval);
	bad_remote_addr = Z_LVAL_P(retval) == 1;
	zval_ptr_dtor(&retval);

	SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_X_FORWARDED_FOR);

	// PHP: if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	if (!SMFLIB_EMPTY_PP(HTTP_X_FORWARDED_FOR) && !SMFLIB_EMPTY_PP(HTTP_CLIENT_IP) && (bad_client_ip == 0 || bad_remote_addr != 0))
	{
		zval *strtok_x_forwarded_for, *strtok_client_ip, *str_x_forwarded_for;
		zval *str_client_ip;
		zend_bool bad_x_forwared_for, remote_addr_set = 0;

		// PHP: if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
		SMFLIB_CALL_FUNCTION_ZS("strtok", *HTTP_X_FORWARDED_FOR, ".", sizeof(".") - 1, strtok_x_forwarded_for);
		SMFLIB_CALL_FUNCTION_ZS("strtok", *HTTP_CLIENT_IP, ".", sizeof(".") - 1, strtok_client_ip);
		if (SMFLIB_CMP_NOT_EQ_ZZ(strtok_x_forwarded_for, strtok_client_ip))
		{
			// First condition met, let's check the second.
			SMFLIB_CALL_FUNCTION_SZ("sprintf", ".%s", sizeof(".%s") - 1, strtok_x_forwarded_for, str_x_forwarded_for);
			SMFLIB_CALL_FUNCTION_ZS("strrchr", *HTTP_CLIENT_IP, ".", sizeof(".") - 1, str_client_ip);
			if (SMFLIB_CMP_EQ_ZZ(str_x_forwarded_for, str_client_ip))
			{
				SMFLIB_CALL_FUNCTION_SZ("preg_match", SMFLIB_PREG_BAD_IP, sizeof(SMFLIB_PREG_BAD_IP) - 1, *HTTP_X_FORWARDED_FOR, retval);
				bad_x_forwared_for = Z_LVAL_P(retval) == 1;
				zval_ptr_dtor(&retval);
				if (bad_x_forwared_for == 0 || bad_remote_addr != 0)
				{
					// PHP: $_SERVER['REMOTE_ADDR'] = implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
					SMFLIB_CALL_FUNCTION_SZ("explode", ".", sizeof(".") - 1, *HTTP_CLIENT_IP, exploded_str);
					SMFLIB_CALL_FUNCTION_Z("array_reverse", exploded_str, reversed_array);
					zval_ptr_dtor(&exploded_str);
					SMFLIB_CALL_FUNCTION_SZ("implode", ".", sizeof(".") - 1, reversed_array, *REMOTE_ADDR);
					zval_ptr_dtor(&reversed_array);
					// We went through all if's. No more else.
					remote_addr_set = 1;

				}
			}
			zval_ptr_dtor(&str_x_forwarded_for);
			zval_ptr_dtor(&str_client_ip);
		}
		zval_ptr_dtor(&strtok_x_forwarded_for);
		zval_ptr_dtor(&strtok_client_ip);
		// PHP: else
		if (!remote_addr_set)
		{
			// PHP: $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
			SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*_SERVER, "REMOTE_ADDR", *HTTP_CLIENT_IP, REMOTE_ADDR);
		}
	}

	// PHP: if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	if (!SMFLIB_EMPTY_PP(HTTP_CLIENT_IP) && (bad_client_ip == 0 || bad_remote_addr != 0))
	{
		zval *strtok_x_forwarded_for, *strtok_remote_addr;

		// PHP: if (strtok($_SERVER['REMOTE_ADDR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.'))
		SMFLIB_CALL_FUNCTION_ZS("strtok", *REMOTE_ADDR, ".", sizeof(".") - 1, strtok_x_forwarded_for);
		SMFLIB_CALL_FUNCTION_ZS("strtok", *HTTP_CLIENT_IP, ".", sizeof(".") - 1, strtok_remote_addr);
		if (SMFLIB_CMP_NOT_EQ_ZZ(strtok_remote_addr, strtok_x_forwarded_for))
		{
			// PHP: $_SERVER['REMOTE_ADDR'] = implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
			SMFLIB_CALL_FUNCTION_SZ("explode", ".", sizeof(".") - 1, *HTTP_CLIENT_IP, exploded_str);
			SMFLIB_CALL_FUNCTION_Z("array_reverse", exploded_str, reversed_array);
			zval_ptr_dtor(&exploded_str);
			SMFLIB_CALL_FUNCTION_SZ("implode", ".", sizeof(".") - 1, reversed_array, *REMOTE_ADDR);
			zval_ptr_dtor(&reversed_array);
		}
		else
		{
			// PHP: $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
			SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*_SERVER, "REMOTE_ADDR", *HTTP_CLIENT_IP, REMOTE_ADDR);
		}
		zval_ptr_dtor(&strtok_x_forwarded_for);
		zval_ptr_dtor(&strtok_remote_addr);
	}
	// PHP: elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	else if (!SMFLIB_EMPTY_PP(HTTP_X_FORWARDED_FOR))
	{
		zval *ip_array, *comma_pos;
		zend_bool bad_x_forwarded_for, bad_ip;

		SMFLIB_CALL_FUNCTION_SZ("preg_match", SMFLIB_PREG_BAD_IP, sizeof(SMFLIB_PREG_BAD_IP) - 1, *HTTP_X_FORWARDED_FOR, retval);
		bad_x_forwarded_for = Z_LVAL_P(retval) == 1;
		zval_ptr_dtor(&retval);

		// PHP: if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false)
		if (SMFLIB_STRPOS_ZC(*HTTP_X_FORWARDED_FOR, ","))
		{
			// PHP: $ips = array_reverse(explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']));
			SMFLIB_CALL_FUNCTION_SZ("explode", ",", sizeof(",") - 1, *HTTP_X_FORWARDED_FOR, exploded_str);
			SMFLIB_CALL_FUNCTION_Z("array_reverse", exploded_str, ip_array);
			zval_ptr_dtor(&exploded_str);

			// PHP: foreach ($ips as $i => $ip)
			zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(ip_array), &pos);
			while (zend_hash_get_current_data_ex(Z_ARRVAL_P(ip_array), (void**) &entry, &pos) == SUCCESS)
			{
				// PHP: if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
				SMFLIB_CALL_FUNCTION_SZ("preg_match", SMFLIB_PREG_BAD_IP, sizeof(SMFLIB_PREG_BAD_IP) - 1, *entry, retval);
				bad_ip = Z_LVAL_P(retval) == 1;
				zval_ptr_dtor(&retval);
				if (bad_ip && !bad_remote_addr)
				{
					// PHP: continue;
					zend_hash_move_forward_ex(Z_ARRVAL_P(ip_array), &pos);
					continue;
				}

				// PHP: $_SERVER['REMOTE_ADDR'] = trim($ip);
				SMFLIB_CALL_FUNCTION_Z("trim", *entry, *REMOTE_ADDR);

				// PHP: break;
				break;
			}
			zval_ptr_dtor(&ip_array);
		}
		// PHP: elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
		else if (!bad_x_forwarded_for || bad_remote_addr)
		{
			// PHP: $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			SMFLIB_SET_KEY_VAL_ZCZZ_CPY(*_SERVER, "REMOTE_ADDR", *HTTP_X_FORWARDED_FOR, REMOTE_ADDR);
		}
		zval_ptr_dtor(&comma_pos);
	}
	// [Already done above]
	// PHP: elseif (!isset($_SERVER['REMOTE_ADDR']))
	// PHP: $_SERVER['REMOTE_ADDR'] = '';
	// [/Already done above]

	// PHP: if (empty($_SERVER['REQUEST_URI']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, REQUEST_URI) || SMFLIB_EMPTY_PP(REQUEST_URI))
	{
		// PHP: $_SERVER['REQUEST_URL'] = $scripturl . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
		if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, QUERY_STRING) || SMFLIB_EMPTY_PP(QUERY_STRING))
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SERVER, "REQUEST_URL", *scripturl)
		else
		{
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s?%2$s", sizeof("%1$s?%2$s") - 1, *scripturl, *QUERY_STRING, retval);
			SMFLIB_SET_KEY_VAL_ZCZ(*_SERVER, "REQUEST_URL", retval);
		}
	}
	// PHP: elseif (preg_match('~^([^/]+//[^/]+)~', $scripturl, $match) == 1)
	else
	{
		zval *match, *found_it;
		zval **match_1;

		ALLOC_INIT_ZVAL(match);
		SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_URL_ROOT, sizeof(SMFLIB_PREG_URL_ROOT) - 1, *scripturl, match, found_it);
		if (Z_LVAL_P(found_it) == 1 && SMFLIB_GET_KEY_VAL_ZLZ(match, 1, match_1))
		{
			// PHP: $_SERVER['REQUEST_URL'] = $match[1] . $_SERVER['REQUEST_URI'];
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s%2$s", sizeof("%1$s%2$s") - 1, *match_1, *REQUEST_URI, retval);
			SMFLIB_SET_KEY_VAL_ZCZ(*_SERVER, "REQUEST_URL", retval);
		}
		// PHP: else
		else
		{
			// PHP: $_SERVER['REQUEST_URL'] = $_SERVER['REQUEST_URI'];
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SERVER, "REQUEST_URL", *REQUEST_URI)
		}
		zval_ptr_dtor(&found_it);
		zval_ptr_dtor(&match);
	}

	// PHP: if (empty($_SERVER['HTTP_USER_AGENT']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_USER_AGENT) || SMFLIB_EMPTY_PP(HTTP_USER_AGENT))
	{
		// PHP: $_SERVER['HTTP_USER_AGENT'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_SERVER, "HTTP_USER_AGENT", "");
	}
}

PHP_FUNCTION(smflib_addslashes__recursive)
{
	// Input parameters.
	zval *var;

	// Local variables.
	zval *slashed_zval, *slashed_key;
	zval **entry;
	char *key;
	uint key_len;
	ulong index;
	HashPosition pos;


	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &var) == FAILURE)
		RETURN_NULL();

	// PHP: if (!is_array($var))
	if (Z_TYPE_P(var) != IS_ARRAY)
	{
		// PHP: return addslashes($var);
		SMFLIB_CALL_FUNCTION_Z("addslashes", var, slashed_zval);
		RETURN_ZVAL(slashed_zval, 1, 1);
	}

	// PHP: $new_var = array();
	array_init(return_value);

	// PHP: foreach ($var as $k => $v)
	zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(var), &pos);
	while (zend_hash_get_current_data_ex(Z_ARRVAL_P(var), (void**) &entry, &pos) == SUCCESS)
	{
		// PHP: $new_var[addslashes($k)] = addslashes__recursive($v);
		if (Z_TYPE_PP(entry) != IS_ARRAY)
		{
			// A little detour from the PHP-script. No need for recursion here.
			SMFLIB_CALL_FUNCTION_Z("addslashes", *entry, slashed_zval);
		}
		else
		{
			SMFLIB_CALL_FUNCTION_Z("addslashes__recursive", *entry, slashed_zval);
		}

		if (zend_hash_get_current_key_ex(Z_ARRVAL_P(var), &key, &key_len, &index, 0, &pos) == HASH_KEY_IS_STRING)
		{
			// Add slashes to the key.
			SMFLIB_CALL_FUNCTION_S("addslashes", key, key_len - 1, slashed_key);
			SMFLIB_SET_KEY_VAL_ZSZ(return_value, Z_STRVAL_P(slashed_key), Z_STRLEN_P(slashed_key), slashed_zval);
			zval_ptr_dtor(&slashed_key);
		}
		else
			SMFLIB_SET_KEY_VAL_ZLZ(return_value, index, slashed_zval);

		zend_hash_move_forward_ex(Z_ARRVAL_P(var), &pos);
	}

	// PHP: return $new_var;
	return;
}

PHP_FUNCTION(smflib_htmlspecialchars__recursive)
{
	// Input parameters.
	zval *var;

	// Local variables.
	zval *converted_zval;

	// prototype: zval = htmlspecialchars__recursive(zval);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &var) == FAILURE)
		RETURN_NULL();

	// PHP: if (!is_array($var))
	if (Z_TYPE_P(var) != IS_ARRAY)
	{
		// PHP: return htmlspecialchars($var, ENT_QUOTES);
		SMFLIB_CALL_FUNCTION_ZL("htmlspecialchars", var, ENT_QUOTES, converted_zval);
		RETURN_ZVAL(converted_zval, 1, 1);
	}

	// PHP: return array_map('htmlspecialchars__recursive', $var);
	SMFLIB_CALL_FUNCTION_SZ("array_map", "htmlspecialchars__recursive", sizeof("htmlspecialchars__recursive") - 1, var, converted_zval);
	RETURN_ZVAL(converted_zval, 1, 1);
}

PHP_FUNCTION(smflib_urldecode__recursive)
{
	// Input parameters.
	zval *var;

	// Local variables.
	zval *decoded_zval, *decoded_key;
	zval **entry;
	char *key;
	uint key_len;
	ulong index;
	HashPosition pos;

	// prototype: zval = urldecode__recursive(zval);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &var) == FAILURE)
		RETURN_NULL();

	// PHP: if (!is_array($var))
	if (Z_TYPE_P(var) != IS_ARRAY)
	{
		// PHP: return urldecode($var);
		SMFLIB_CALL_FUNCTION_ZL("urldecode", var, ENT_QUOTES, decoded_zval);
		RETURN_ZVAL(decoded_zval, 1, 1);
	}

	// PHP: $new_var = array();
	array_init(return_value);

	// PHP: foreach ($var as $k => $v)
	zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(var), &pos);
	while (zend_hash_get_current_data_ex(Z_ARRVAL_P(var), (void**) &entry, &pos) == SUCCESS)
	{
		// PHP: $new_var[urldecode($k)] = urldecode__recursive($v);
		if (Z_TYPE_PP(entry) != IS_ARRAY)
		{
			// A little detour from the PHP-script. No need for recursion here.
			SMFLIB_CALL_FUNCTION_Z("urldecode", *entry, decoded_zval);
		}
		else
		{
			SMFLIB_CALL_FUNCTION_Z("urldecode__recursive", *entry, decoded_zval);
		}

		if (zend_hash_get_current_key_ex(Z_ARRVAL_P(var), &key, &key_len, &index, 0, &pos) == HASH_KEY_IS_STRING)
		{
			// Add slashes to the key.
			SMFLIB_CALL_FUNCTION_S("urldecode", key, key_len - 1, decoded_key);
			SMFLIB_SET_KEY_VAL_ZSZ(return_value, Z_STRVAL_P(decoded_key), Z_STRLEN_P(decoded_key), decoded_zval);
			zval_ptr_dtor(&decoded_key);
		}
		else
			SMFLIB_SET_KEY_VAL_ZLZ(return_value, index, decoded_zval);

		zend_hash_move_forward_ex(Z_ARRVAL_P(var), &pos);
	}

	// PHP: return $new_var;
	return;
}

PHP_FUNCTION(smflib_stripslashes__recursive)
{
	// Input parameters.
	zval *var;

	// Local variables.
	zval *stripped_zval, *stripped_key;
	zval **entry;
	char *key;
	uint key_len;
	ulong index;
	HashPosition pos;

	// prototype: zval = stripslashes__recursive(zval);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &var) == FAILURE)
		RETURN_NULL();

	// PHP: if (!is_array($var))
	if (Z_TYPE_P(var) != IS_ARRAY)
	{
		// PHP: return stripslashes($var);
		SMFLIB_CALL_FUNCTION_ZL("stripslashes", var, ENT_QUOTES, stripped_zval);
		RETURN_ZVAL(stripped_zval, 1, 1);
	}

	// PHP: $new_var = array();
	array_init(return_value);

	// PHP: foreach ($var as $k => $v)
	zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(var), &pos);
	while (zend_hash_get_current_data_ex(Z_ARRVAL_P(var), (void**) &entry, &pos) == SUCCESS)
	{
		// PHP: $new_var[stripslashes($k)] = stripslashes__recursive($v);
		if (Z_TYPE_PP(entry) != IS_ARRAY)
		{
			// A little detour from the PHP-script. No need for recursion here.
			SMFLIB_CALL_FUNCTION_Z("stripslashes", *entry, stripped_zval);
		}
		else
		{
			SMFLIB_CALL_FUNCTION_Z("stripslashes__recursive", *entry, stripped_zval);
		}

		if (zend_hash_get_current_key_ex(Z_ARRVAL_P(var), &key, &key_len, &index, 0, &pos) == HASH_KEY_IS_STRING)
		{
			// Add slashes to the key.
			SMFLIB_CALL_FUNCTION_S("stripslashes", key, key_len - 1, stripped_key);
			SMFLIB_SET_KEY_VAL_ZSZ(return_value, Z_STRVAL_P(stripped_key), Z_STRLEN_P(stripped_key), stripped_zval);
			zval_ptr_dtor(&stripped_key);
		}
		else
			SMFLIB_SET_KEY_VAL_ZLZ(return_value, index, stripped_zval);

		zend_hash_move_forward_ex(Z_ARRVAL_P(var), &pos);
	}

	// PHP: return $new_var;
	return;
}

PHP_FUNCTION(smflib_htmltrim__recursive)
{
	// Input parameters.
	zval *var;

	// Local variables.
	zval *trimmed_zval;

	// prototype: zval = htmltrim__recursive(zval);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &var) == FAILURE)
		RETURN_NULL();

	// PHP: if (!is_array($var))
	if (Z_TYPE_P(var) != IS_ARRAY)
	{
		// PHP: return trim($var, " \t\n\r\x0B\0\xA0");
		SMFLIB_CALL_FUNCTION_ZS("trim", var, SMFLIB_HTML_SPACES, sizeof(SMFLIB_HTML_SPACES) - 1, trimmed_zval);
		RETURN_ZVAL(trimmed_zval, 1, 1);
	}

	// PHP: return array_map('trim__recursive', $var);
	SMFLIB_CALL_FUNCTION_SZ("array_map", "htmltrim__recursive", sizeof("htmltrim__recursive") - 1, var, trimmed_zval);
	RETURN_ZVAL(trimmed_zval, 1, 1);
}

PHP_FUNCTION(smflib_ob_sessrewrite)
{
	// Input parameters.
	char *buffer;
	int buffer_len;
	long mode;

	// Global variables.
	zval **scripturl, **_COOKIE, **user_info, **_SERVER, **modSettings;
	zval **context, **_GET;

	// Global hash variables.
	zval **is_guest, **HTTP_USER_AGENT, **queryless_urls, **server, **is_cgi;
	zval **is_apache, **debug;

	// Local variables.
	zval *sid, *_buffer;
	long fix_pathinfo = INI_INT("cgi.fix_pathinfo");

	// prototype: zval = htmltrim__recursive(string);
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &buffer, &buffer_len, &mode) == FAILURE)
		RETURN_NULL();

	// Make a copy of $buffer.
	ALLOC_INIT_ZVAL(_buffer);
	ZVAL_STRINGL(_buffer, buffer, buffer_len, 1);

	// PHP: if ($scripturl == '' || !defined('SID'))
	ALLOC_INIT_ZVAL(sid);
	if (!SMFLIB_GET_GLOBAL_Z(scripturl) || Z_STRLEN_PP(scripturl) == 0 || !zend_get_constant("SID", 3, sid TSRMLS_CC))
	{
		// PHP: return $buffer;
		zval_ptr_dtor(&sid);
		RETURN_ZVAL(_buffer, 0, 1);
		return;

	}

	// Some basics.
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_guest))
		php_error(E_ERROR, "smflib_ob_sessrewrite(): unable to retrieve the value of $user_info['is_guest']");
	if (!SMFLIB_GET_GLOBAL_Z(_SERVER) || !SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_USER_AGENT))
		php_error(E_ERROR, "smflib_ob_sessrewrite(): unable to retrieve the value of $_SERVER['HTTP_USER_AGENT']");
	if (!SMFLIB_GET_GLOBAL_Z(context) || !SMFLIB_GET_KEY_VAL_ZZ(*context, server) || !SMFLIB_GET_KEY_VAL_ZZ(*server, is_cgi))
		php_error(E_ERROR, "smflib_ob_sessrewrite(): unable to retrieve the value of $context['server']['is_cgi']");
	if (!SMFLIB_GET_KEY_VAL_ZZ(*server, is_apache))
		php_error(E_ERROR, "smflib_ob_sessrewrite(): unable to retrieve the value of $context['server']['is_apache']");


	// PHP: if (empty($_COOKIE) && SID != '' && (!$user_info['is_guest'] || (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false)) && @version_compare(PHP_VERSION, '4.3.0') != -1)
	if ((!SMFLIB_GET_GLOBAL_Z(_COOKIE) || SMFLIB_EMPTY_PP(_COOKIE)) && Z_STRLEN_P(sid) != 0 && (!Z_LVAL_PP(is_guest) || (SMFLIB_STRPOS_ZC(*HTTP_USER_AGENT, "Mozilla") || SMFLIB_STRPOS_ZC(*HTTP_USER_AGENT, "Opera"))) && php_version_compare(PHP_VERSION, "4.3.0") != -1)
	{
		zval *preg_search, *pregged_scripturl, *pregged_sid, *preg_replace;
		zval *str_input;

		// PHP: $buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '(?!\?' . preg_quote(SID, '/') . ')(\?)?/', '"' . $scripturl . '?' . SID . ';', $buffer);
		SMFLIB_CALL_FUNCTION_ZS("preg_quote", *scripturl, "/", sizeof("/") - 1, pregged_scripturl);
		SMFLIB_CALL_FUNCTION_ZS("preg_quote", sid, "/", sizeof("/") - 1, pregged_sid);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_PREG_SEARCH_SESSIONLESS_URL, sizeof(SMFLIB_PREG_SEARCH_SESSIONLESS_URL) - 1,  pregged_scripturl, pregged_sid, preg_search);
		zval_ptr_dtor(&pregged_scripturl);
		zval_ptr_dtor(&pregged_sid);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_PREG_REPLACE_SESSIONLESS_URL, sizeof(SMFLIB_PREG_REPLACE_SESSIONLESS_URL) - 1, *scripturl, sid, preg_replace);
		str_input = _buffer;
		SMFLIB_CALL_FUNCTION_ZZZ("preg_replace", preg_search, preg_replace, str_input, _buffer);
		zval_ptr_dtor(&str_input);
		zval_ptr_dtor(&preg_search);
		zval_ptr_dtor(&preg_replace);
	}
	// PHP: elseif (isset($_GET['debug']))
	else if (SMFLIB_GET_GLOBAL_Z(_GET) && SMFLIB_GET_KEY_VAL_ZZ(*_GET, debug))
	{
		zval *preg_search, *pregged_scripturl, *preg_replace;
		zval *str_input;

		// PHP: $buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '(\?)?/', '"' . $scripturl . '?debug;', $buffer);
		SMFLIB_CALL_FUNCTION_ZS("preg_quote", *scripturl, "/", sizeof("/") - 1, pregged_scripturl);
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_PREG_SEARCH_DEBUG_URL, sizeof(SMFLIB_PREG_SEARCH_DEBUG_URL) - 1,  pregged_scripturl, preg_search);
		zval_ptr_dtor(&pregged_scripturl);
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_PREG_REPLACE_DEBUG_URL, sizeof(SMFLIB_PREG_REPLACE_DEBUG_URL) - 1, *scripturl, preg_replace);
		str_input = _buffer;
		SMFLIB_CALL_FUNCTION_ZZZ("preg_replace", preg_search, preg_replace, str_input, _buffer);
		zval_ptr_dtor(&str_input);
		zval_ptr_dtor(&preg_search);
		zval_ptr_dtor(&preg_replace);
	}

	// PHP: 	if (!empty($modSettings['queryless_urls']) && (!$context['server']['is_cgi'] || @ini_get('cgi.fix_pathinfo') == 1) && $context['server']['is_apache'])
	if (SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, queryless_urls) && !SMFLIB_EMPTY_PP(queryless_urls) && (!Z_LVAL_PP(is_cgi) || fix_pathinfo == 1) && Z_LVAL_PP(is_apache))
	{
		zval *pregged_scripturl, *preg_search, *str_input;

		// We're gonna need this anyway.
		SMFLIB_CALL_FUNCTION_ZS("preg_quote", *scripturl, "/", sizeof("/") - 1, pregged_scripturl);

		// PHP: if (defined('SID') && SID != '')
		// if SID were not defined, the function would've already returned.
		if (Z_STRLEN_P(sid) != 0)
		{
			// PHP: $buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?(?:' . SID . ';)((?:board|topic)=[^#"]+?)(#[^"]*?)?"/e', "'\"' . \$scripturl . '/' . strtr('\$1', '&;=', '//,') . '.html?' . SID . '\$2\"'", $buffer);
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_PREG_SEARCH_QUERYLESS_URL_SESS, sizeof(SMFLIB_PREG_SEARCH_QUERYLESS_URL_SESS) - 1,  pregged_scripturl, sid, preg_search);
			str_input = _buffer;
			SMFLIB_CALL_FUNCTION_ZSZ("preg_replace", preg_search, SMFLIB_PREG_REPLACE_QUERYLESS_URL_SESS, sizeof(SMFLIB_PREG_REPLACE_QUERYLESS_URL_SESS) - 1, str_input, _buffer);
			zval_ptr_dtor(&str_input);
			zval_ptr_dtor(&preg_search);
		}
		// PHP: else
		else
		{
			// PHP: $buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?((?:board|topic)=[^#"]+?)(#[^"]*?)?"/e', "'\"' . \$scripturl . '/' . strtr('\$1', '&;=', '//,') . '.html\$2\"'", $buffer);
			SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_PREG_SEARCH_QUERYLESS_URL, sizeof(SMFLIB_PREG_SEARCH_QUERYLESS_URL) - 1,  pregged_scripturl, preg_search);
			str_input = _buffer;
			SMFLIB_CALL_FUNCTION_ZSZ("preg_replace", preg_search, SMFLIB_PREG_REPLACE_QUERYLESS_URL, sizeof(SMFLIB_PREG_REPLACE_QUERYLESS_URL) - 1, str_input, _buffer);
			zval_ptr_dtor(&str_input);
			zval_ptr_dtor(&preg_search);
		}
		zval_ptr_dtor(&pregged_scripturl);
	}

	zval_ptr_dtor(&sid);

	// PHP: return $buffer;
	RETURN_ZVAL(_buffer, 0, 1);
}

static char invalid_unicode_chars[32] = {1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1};

// On call, length should be the length of input, on return it will be the length of output.
// Caller must deallocate input and output!!
static char *validate_unicode(char *input, int *length)
{
	char *output, *p, *input_end;

	output = emalloc(*length);
	input_end = input + *length;

	for (p = output; input < input_end; )
	{
		if (*input < 32 && invalid_unicode_chars[*input])
			*input++;
		else if (*input < 192)
			*p++ = *input++;
		else
		{
			*p++ = *input++;
			*p++ = *input++;

			if (*input >= 224)
				*p++ = *input++;
			if (*input >= 240)
				*p++ = *input++;
			if (*input >= 248)
				*p++ = *input++;
			if (*input >= 252)
				*p++ = *input++;
		}
	}

	*length = p - output;
	return output;
}