/****
/**********************************************************************************
* Security.c                                                                      *
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

// Based on Security.php CVS 1.141

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"

#include "smflib_function_calls.h"
#include "php_smflib.h"

PHP_FUNCTION(smflib_is_admin)
{
	// Global variables.
	zval **user_info, **_GET;

	// Global hash variables.
	zval **is_admin;

	// Local variables.
	zval *retval;

	// PHP: if (!$user_info['is_admin'])
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_admin) || Z_LVAL_PP(is_admin) == 0)
	{
		// PHP: $_GET['action'] = '';
		SMFLIB_GET_GLOBAL_Z(_GET);
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "action", "");

		// PHP: writeLog(true);
		SMFLIB_CALL_FUNCTION_B("writeLog", 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: fatal_lang_error(1);
		SMFLIB_CALL_FUNCTION_L("fatal_lang_error", 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
		php_error(E_USER_ERROR, "Hacking attempt...");
	}

	// PHP: validateSession();
	SMFLIB_CALL_FUNCTION("validateSession", retval);
	zval_ptr_dtor(&retval);
}

PHP_FUNCTION(smflib_validateSession)
{
	// Global variables.
	zval **modSettings, **sourcedir, **user_info, **sc, **_SESSION, **_POST;

	// Global hash variables.
	zval **securityDisable, **admin_time, **admin_hash_pass, **username;
	zval **integrate_verify_password, **passwd, **admin_pass;

	// Local variables.
	zval *retval, *cur_time, *_admin_time = NULL, *subs_auth, *verify_result;
	zval *sha_result;
	zval **dummy;
	zend_bool good_password;

	// PHP: is_not_guest();
	SMFLIB_CALL_FUNCTION("is_not_guest", retval);
	zval_ptr_dtor(&retval);

	// Get the current time.
	SMFLIB_CALL_FUNCTION("time", cur_time);

	// Get the numeric value of $_SESSION['admin_time'].
	if (SMFLIB_GET_GLOBAL_Z(_SESSION) && SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, admin_time))
	{
		ALLOC_INIT_ZVAL(_admin_time);
		ZVAL_ZVAL(_admin_time, *admin_time, 1, 0);
		convert_to_long(_admin_time);
	}

	// PHP: if (!empty($modSettings['securityDisable']) || (!empty($_SESSION['admin_time']) && $_SESSION['admin_time'] + 3600 >= time()))
	if ((SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, securityDisable) && !SMFLIB_EMPTY_PP(securityDisable)) || (_admin_time && !SMFLIB_EMPTY_P(_admin_time) && Z_LVAL_P(_admin_time) + 3600 >= Z_LVAL_P(cur_time)))
	{
		// PHP: return;
		RETURN_NULL();
	}

	// PHP: require_once($sourcedir . '/Subs-Auth.php');
	if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
		php_error(E_ERROR, "validateSession(): $sourcedir not set");
	SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Subs-Auth.php", sizeof("%1$s/Subs-Auth.php") - 1, *sourcedir, subs_auth);
	smflib_require_once(Z_STRVAL_P(subs_auth), Z_STRLEN_P(subs_auth) TSRMLS_CC);
	zval_ptr_dtor(&subs_auth);

	// PHP: if (isset($_POST['admin_hash_pass']) && strlen($_POST['admin_hash_pass']) == 40)
	if (SMFLIB_GET_GLOBAL_Z(_POST) && SMFLIB_GET_KEY_VAL_ZZ(*_POST, admin_hash_pass) && Z_TYPE_PP(admin_hash_pass) == IS_STRING && Z_STRLEN_PP(admin_hash_pass) == 40)
	{
		// PHP: checkSession();
		SMFLIB_CALL_FUNCTION("checkSession", retval);
		zval_ptr_dtor(&retval);

		// PHP: $good_password = false;
		good_password = 0;

		// PHP: if (isset($modSettings['integrate_verify_password']) && function_exists($modSettings['integrate_verify_password']))
		if (SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, integrate_verify_password) && SMFLIB_FUNCTION_EXISTS_ZZ(*integrate_verify_password, dummy))
		{
			// PHP: if (call_user_func($modSettings['integrate_verify_password'], $user_info['username'], $_POST['admin_hash_pass'], true) === true)
			if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, username))
				php_error(E_ERROR, "validateSession(): $user_info['username'] not set");
			SMFLIB_CALL_USER_FUNCTION_ZZB(Z_STRVAL_PP(integrate_verify_password), Z_STRLEN_PP(integrate_verify_password), *username, *admin_hash_pass, 1, verify_result);
			if (Z_TYPE_P(verify_result) == IS_BOOL && Z_LVAL_P(verify_result) != 0)
			{
				// PHP: $good_password = true;
				good_password = 1;
			}
			zval_ptr_dtor(&verify_result);
		}

		// PHP: if ($good_password || $_POST['admin_hash_pass'] == sha1($user_info['passwd'] . $sc))
		if (!good_password)
		{
			zval *sha_food;

			if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, passwd))
				php_error(E_ERROR, "validateSession(): $user_info['passwd'] not set");
			if (!SMFLIB_GET_GLOBAL_Z(sc))
				php_error(E_ERROR, "validateSession(): $sc not set");
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s%2$s", sizeof("%1$s%2$s") - 1, *passwd, *sc, sha_food);
			SMFLIB_CALL_FUNCTION_Z("sha1", sha_food, sha_result);
			zval_ptr_dtor(&sha_food);
		}
		if (good_password || SMFLIB_CMP_EQ_ZZ(*admin_hash_pass, sha_result))
		{
			// PHP: $_SESSION['admin_time'] = time();
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SESSION, "admin_time", cur_time);

			// PHP: return;
			RETURN_NULL();
		}
		zval_ptr_dtor(&sha_result);
	}

	// PHP: if (isset($_POST['admin_pass']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_POST, admin_pass))
	{
		// PHP: checkSession();
		SMFLIB_CALL_FUNCTION("checkSession", retval);
		zval_ptr_dtor(&retval);

		// PHP: $good_password = false;
		good_password = 0;

		// PHP: if (isset($modSettings['integrate_verify_password']) && function_exists($modSettings['integrate_verify_password']))
		if (SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, integrate_verify_password) && SMFLIB_FUNCTION_EXISTS_ZZ(*integrate_verify_password, dummy))
		{
			// PHP: if (call_user_func($modSettings['integrate_verify_password'], $user_info['username'], $_POST['admin_pass'], false) === true)
			if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, username))
				php_error(E_ERROR, "validateSession(): $user_info['username'] not set");
			SMFLIB_CALL_USER_FUNCTION_ZZB(Z_STRVAL_PP(integrate_verify_password), Z_STRLEN_PP(integrate_verify_password), *username, *admin_pass, 1, verify_result);
			if (Z_TYPE_P(verify_result) == IS_BOOL && Z_LVAL_P(verify_result) != 0)
			{
				// PHP: $good_password = true;
				good_password = 1;
			}
			zval_ptr_dtor(&verify_result);
		}

		// PHP: if ($good_password || sha1(strtolower($user_info['username']) . $_POST['admin_pass']) == $user_info['passwd'])
		if (!good_password)
		{
			zval *sha_food;
			char *lowercase_username;

			if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, username))
				php_error(E_ERROR, "validateSession(): $user_info['username'] not set");
			if (!SMFLIB_GET_KEY_VAL_ZZ(*user_info, passwd))
				php_error(E_ERROR, "validateSession(): $user_info['passwd'] not set");

			lowercase_username = estrndup(Z_STRVAL_PP(username), Z_STRLEN_PP(username));
			zend_str_tolower(lowercase_username, Z_STRLEN_PP(username));

			SMFLIB_CALL_FUNCTION_SSZ("sprintf", "%1$s%2$s", sizeof("%1$s%2$s") - 1, lowercase_username, Z_STRLEN_PP(username), *admin_pass, sha_food);
			efree(lowercase_username);
			SMFLIB_CALL_FUNCTION_Z("sha1", sha_food, sha_result);
			zval_ptr_dtor(&sha_food);
		}
		if (good_password || SMFLIB_CMP_EQ_ZZ(*admin_hash_pass, sha_result))
		{
			// PHP: $_SESSION['admin_time'] = time();
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SESSION, "admin_time", cur_time);

			// PHP: return;
			RETURN_NULL();
		}
		zval_ptr_dtor(&sha_result);
	}
	zval_ptr_dtor(&cur_time);

	// PHP: adminLogin();
	SMFLIB_CALL_FUNCTION("adminLogin", retval);
	zval_ptr_dtor(&retval);
}

PHP_FUNCTION(smflib_is_not_guest)
{
	// Input parameters.
	zval *message = NULL;

	// Global variables.
	zval **user_info, **txt, **context, **_GET, **_REQUEST;
	zval **_SESSION;

	// Global hash variables.
	zval **is_guest, **xml, **REQUEST_URL, **txt_34;

	// Local variables.
	zval *retval;

	// void is_not_guest($message = '')
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|z", &message) == FAILURE)
		RETURN_NULL();

	// PHP: if (!$user_info['is_guest'])
	if (SMFLIB_GET_GLOBAL_Z(user_info) && SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_guest) && !Z_LVAL_PP(is_guest))
	{
		// PHP: return;
		RETURN_NULL();
	}

	// PHP: $_GET['action'] = '';
	SMFLIB_GET_GLOBAL_Z(_GET);
	SMFLIB_SET_KEY_VAL_ZCC(*_GET, "action", "");

	// PHP: $_GET['board'] = '';
	SMFLIB_SET_KEY_VAL_ZCC(*_GET, "board", "");

	// PHP: $_GET['topic'] = '';
	SMFLIB_SET_KEY_VAL_ZCC(*_GET, "topic", "");

	// PHP: writeLog(true);
	SMFLIB_CALL_FUNCTION_B("writeLog", 1, retval);
	zval_ptr_dtor(&retval);

	// PHP: if (isset($_REQUEST['xml']))
	if (SMFLIB_GET_GLOBAL_Z(_REQUEST) && SMFLIB_GET_KEY_VAL_ZZ(*_REQUEST, xml))
	{
		// PHP: obExit(false);
		SMFLIB_CALL_FUNCTION_B("obExit", 0, retval);
		zval_ptr_dtor(&retval);
	}

	// PHP: $_SESSION['login_url'] = $_SERVER['REQUEST_URL'];
	if (!SMFLIB_GET_GLOBAL_Z(_SESSION) || !SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, REQUEST_URL))
		php_error(E_ERROR, "is_not_guest(): $_SERVER[REQUEST_URL] not set");
	SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SESSION, "login_url", *REQUEST_URL);

	// PHP: loadLanguage('Login');
	SMFLIB_CALL_FUNCTION_S("loadLanguage", "Login", sizeof("Login"), retval);
	zval_ptr_dtor(&retval);

	// PHP: loadTemplate('Login');
	SMFLIB_CALL_FUNCTION_S("loadTemplate", "Login", sizeof("Login"), retval);
	zval_ptr_dtor(&retval);

	// PHP: $context['kick_message'] = $message;
	if (!SMFLIB_GET_GLOBAL_Z(context))
		php_error(E_ERROR, "is_not_guest(): $context not set");
	if (message)
	{
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "kick_message", message);
	}
	else
	{
		SMFLIB_SET_KEY_VAL_ZCC(*context, "kick_message", "");
	}

	// PHP: $context['sub_template'] = 'kick_guest';
	SMFLIB_SET_KEY_VAL_ZCC(*context, "sub_template", "kick_guest");

	// PHP: $context['page_title'] = $txt[34];
	if (!SMFLIB_GET_GLOBAL_Z(txt) || !SMFLIB_GET_KEY_VAL_ZLZ(*txt, 34, txt_34))
		php_error(E_ERROR, "is_not_guest(): $txt[34] not set");
	SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context, "page_title", *txt_34);

	// PHP: obExit();
	SMFLIB_CALL_FUNCTION("obExit", retval);
	zval_ptr_dtor(&retval);

	// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
	php_error(E_USER_ERROR, "Hacking attempt...");
}

PHP_FUNCTION(smflib_is_not_banned)
{
	// Input parameters.
	zval *forceCheck = NULL;

	// Global variables.
	zval **txt, **db_prefix, **ID_MEMBER, **modSettings, **context;
	zval **user_info, **sourcedir, **cookiename, **_SESSION;
	zval **_COOKIE, **_GET;

	// Global hash variables.
	zval **is_admin, **ban, **banLastUpdated, **ban_last_checked;
	zval **ban_ID_MEMBER, **ban_ip, **user_info_ip, **ban_email;
	zval **user_info_email, **disableHostnameLookup, **is_guest;
	zval **localCookies, **globalCookies;

	// Local variables.
	zval *_banLastUpdated = NULL, *cur_time, *retval, *ban_cookiename;
	zval *SQL_query, *request, *row, *subs_auth, *cookie_url, *imploded_ids;
	zval **cannot_access = NULL, **cannot_post = NULL, **ban_cookie;
	zval **cannot_access_ids, **row_reason, **row_ID_BAN, **cookie_url_0;
	zval **cookie_url_1, **cannot_login, **permissions;

	// allowedTo($permission, $boards = null).
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|z", &forceCheck) == FAILURE)
		RETURN_NULL();

	// Just make sure some of the globals are actually set as assumed.
	if (!SMFLIB_GET_GLOBAL_Z(_SESSION))
		php_error(E_ERROR, "smflib_is_not_banned(): $_SESSION not set");
	if (!SMFLIB_GET_GLOBAL_Z(_COOKIE))
		php_error(E_ERROR, "smflib_is_not_banned(): $_COOKIE not set");
	if (!SMFLIB_GET_GLOBAL_Z(modSettings))
		php_error(E_ERROR, "smflib_is_not_banned(): $modSettings not set");
	if (SMFLIB_GET_KEY_VAL_ZZ(*modSettings, banLastUpdated))
	{
		ALLOC_INIT_ZVAL(_banLastUpdated);
		ZVAL_ZVAL(_banLastUpdated, *banLastUpdated, 1, 0);
		convert_to_long(_banLastUpdated);
	}
	if (!SMFLIB_GET_GLOBAL_Z(ID_MEMBER))
		php_error(E_ERROR, "smflib_is_not_banned(): $ID_MEMBER not set");
	if (!SMFLIB_GET_GLOBAL_Z(db_prefix))
		php_error(E_ERROR, "smflib_is_not_banned(): $db_prefix not set");
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "ip", user_info_ip))
		php_error(E_ERROR, "smflib_is_not_banned(): $user_info['ip'] not set");
	if (!SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "email", user_info_email))
		php_error(E_ERROR, "smflib_is_not_banned(): $user_info['email'] not set");
	if (!SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_guest))
		php_error(E_ERROR, "smflib_is_not_banned(): $user_info['is_guest'] not set");
	if (!SMFLIB_GET_GLOBAL_Z(cookiename))
		php_error(E_ERROR, "smflib_is_not_banned(): $cookiename not set");
	if (!SMFLIB_GET_GLOBAL_Z(context))
		php_error(E_ERROR, "smflib_is_not_banned(): $context not set");
	if (!SMFLIB_GET_GLOBAL_Z(_GET))
		php_error(E_ERROR, "smflib_is_not_banned(): $_GET not set");

	// Get the current time, we're gonne need it a few times.
	SMFLIB_CALL_FUNCTION("time", cur_time);

	// PHP: if ($user_info['is_admin'])
	if (SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_admin) && Z_LVAL_PP(is_admin))
	{
		// PHP: return;
		if (_banLastUpdated)
			zval_ptr_dtor(&_banLastUpdated);
		zval_ptr_dtor(&cur_time);
		RETURN_NULL();
	}

	// PHP: if ($forceCheck || !isset($_SESSION['ban']) || empty($modSettings['banLastUpdated']) || ($_SESSION['ban']['last_checked'] < $modSettings['banLastUpdated']) || $_SESSION['ban']['ID_MEMBER'] != $ID_MEMBER || $_SESSION['ban']['ip'] != $user_info['ip']  || (isset($user_info['email'], $_SESSION['ban']['email']) && $_SESSION['ban']['email'] != $user_info['email']))
	if ((forceCheck && Z_LVAL_P(forceCheck)) || !SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban) || !_banLastUpdated || SMFLIB_EMPTY_PP(banLastUpdated) || !SMFLIB_GET_KEY_VAL_ZCZ(*ban, "last_checked", ban_last_checked) || Z_LVAL_PP(ban_last_checked) < Z_LVAL_P(_banLastUpdated) || !SMFLIB_GET_KEY_VAL_ZCZ(*ban, "ID_MEMBER", ban_ID_MEMBER) || Z_LVAL_PP(ban_ID_MEMBER) != Z_LVAL_PP(ID_MEMBER) || !SMFLIB_GET_KEY_VAL_ZCZ(*ban, "ip", ban_ip) || SMFLIB_CMP_NOT_EQ_ZZ(*user_info_ip, *ban_ip) || (SMFLIB_GET_KEY_VAL_ZCZ(*ban, "email", ban_email) && SMFLIB_CMP_NOT_EQ_ZZ(*ban_email, *user_info_email)))
	{
		zval *new_ban, *ban_query, *ip_parts, *preg_result;

		/* PHP: $_SESSION['ban'] = array(
			'last_checked' => time(),
			'ID_MEMBER' => $ID_MEMBER,
			'ip' => $user_info['ip'],
			'email' => $user_info['email'],
		);*/
		ALLOC_INIT_ZVAL(new_ban);
		array_init(new_ban);
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(new_ban, "last_checked", cur_time);
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(new_ban, "ID_MEMBER", *ID_MEMBER);
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(new_ban, "ip", *user_info_ip);
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(new_ban, "email", *user_info_email);
		SMFLIB_SET_KEY_VAL_ZCZZ(*_SESSION, "ban", new_ban, ban);

		// PHP: $ban_query = array();
		ALLOC_INIT_ZVAL(ban_query);
		array_init(ban_query);

		// PHP: if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $user_info['ip'], $ip_parts) == 1)
		ALLOC_INIT_ZVAL(ip_parts);
		SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_REGULAR_IP, sizeof(SMFLIB_PREG_REGULAR_IP) - 1, *user_info_ip, ip_parts, preg_result);
		if (Z_LVAL_P(preg_result) == 1)
		{
			zval *ip_query_part;
			zval **ip_part_1, **ip_part_2, **ip_part_3, **ip_part_4;

			/* PHP: $ban_query[] = "(($ip_parts[1] BETWEEN bi.ip_low1 AND bi.ip_high1)
						AND ($ip_parts[2] BETWEEN bi.ip_low2 AND bi.ip_high2)
						AND ($ip_parts[3] BETWEEN bi.ip_low3 AND bi.ip_high3)
						AND ($ip_parts[4] BETWEEN bi.ip_low4 AND bi.ip_high4))";*/
			SMFLIB_GET_KEY_VAL_ZLZ(ip_parts, 1, ip_part_1);
			SMFLIB_GET_KEY_VAL_ZLZ(ip_parts, 2, ip_part_2);
			SMFLIB_GET_KEY_VAL_ZLZ(ip_parts, 3, ip_part_3);
			SMFLIB_GET_KEY_VAL_ZLZ(ip_parts, 4, ip_part_4);
			SMFLIB_CALL_FUNCTION_SZZZZ("sprintf", SMFLIB_FORMAT_IP_QUERY_PART, sizeof(SMFLIB_FORMAT_IP_QUERY_PART) - 1, *ip_part_1, *ip_part_2, *ip_part_3, *ip_part_4, ip_query_part);
			SMFLIB_ADD_INDEX_ZZ(ban_query, ip_query_part);

			// PHP: if (empty($modSettings['disableHostnameLookup']))
			if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, disableHostnameLookup) || SMFLIB_EMPTY_PP(disableHostnameLookup))
			{
				zval *hostname;

				// PHP: $hostname = host_from_ip($user_info['ip']);
				SMFLIB_CALL_FUNCTION_Z("host_from_ip", *user_info_ip, hostname);

				// PHP: if (strlen($hostname) > 0)
				if (Z_STRLEN_P(hostname) > 0)
				{
					zval *slashed_hostname, *hostname_query_part;

					// PHP: $ban_query[] = "('" . addslashes($hostname) . "' LIKE bi.hostname)";
					SMFLIB_CALL_FUNCTION_Z("addslashes", hostname, slashed_hostname);
					SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_HOSTNAME_QUERY_PART, sizeof(SMFLIB_FORMAT_HOSTNAME_QUERY_PART) - 1, slashed_hostname, hostname_query_part);
					zval_ptr_dtor(&slashed_hostname);
					SMFLIB_ADD_INDEX_ZZ(ban_query, hostname_query_part);
				}
				zval_ptr_dtor(&hostname);
			}
		}

		// PHP: elseif ($user_info['ip'] == 'unknown')
		else if (SMFLIB_CMP_EQ_ZC(*user_info_ip, "unknown"))
		{
			/* PHP: $ban_query[] = "(bi.ip_low1 = 255 AND bi.ip_high1 = 255
						AND bi.ip_low2 = 255 AND bi.ip_high2 = 255
						AND bi.ip_low3 = 255 AND bi.ip_high3 = 255
						AND bi.ip_low4 = 255 AND bi.ip_high4 = 255)";*/
			SMFLIB_ADD_INDEX_ZC(ban_query, SMFLIB_FORMAT_UNKNOWN_IP);
		}
		zval_ptr_dtor(&ip_parts);
		zval_ptr_dtor(&preg_result);

		// PHP: if (strlen($user_info['email']) != 0)
		if (Z_STRLEN_PP(user_info_email) != 0)
		{
			zval *slashed_email, *email_query_part;

			// PHP: $ban_query[] = "('" . addslashes($user_info['email']) . "' LIKE bi.email_address)";
			SMFLIB_CALL_FUNCTION_Z("addslashes", *user_info_email, slashed_email);
			SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_EMAIL_QUERY_PART, sizeof(SMFLIB_FORMAT_EMAIL_QUERY_PART) - 1, slashed_email, email_query_part);
			zval_ptr_dtor(&slashed_email);
			SMFLIB_ADD_INDEX_ZZ(ban_query, email_query_part);
		}

		// PHP: if (!$user_info['is_guest'] && !empty($ID_MEMBER))
		if (!Z_LVAL_PP(is_guest) && !SMFLIB_EMPTY_PP(ID_MEMBER))
		{
			zval *member_query_part;

			// PHP: $ban_query[] = "bi.ID_MEMBER = $ID_MEMBER";
			SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_MEMBER_QUERY_PART, sizeof(SMFLIB_FORMAT_MEMBER_QUERY_PART) - 1, *ID_MEMBER, member_query_part);
			SMFLIB_ADD_INDEX_ZZ(ban_query, member_query_part);
		}

		// PHP: if (!empty($ban_query))
		if (!SMFLIB_EMPTY_P(ban_query))
		{
			zval *restrictions, *imploded_ban_query;
			zval **restriction, **row_restriction;

			/* PHP: $restrictions = array(
				'cannot_access',
				'cannot_login',
				'cannot_post',
				'cannot_register',
			);*/
			ALLOC_INIT_ZVAL(restrictions);
			array_init(restrictions);
			SMFLIB_ADD_INDEX_ZC(restrictions, "cannot_access");
			SMFLIB_ADD_INDEX_ZC(restrictions, "cannot_login");
			SMFLIB_ADD_INDEX_ZC(restrictions, "cannot_post");
			SMFLIB_ADD_INDEX_ZC(restrictions, "cannot_register");

			/* PHP: $request = db_query("
				SELECT bi.ID_BAN, bg.cannot_access, bg.cannot_register, bg.cannot_post, bg.cannot_login, bg.reason
				FROM ({$db_prefix}ban_groups AS bg, {$db_prefix}ban_items AS bi)
				WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP
					AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . ")
					AND (" . implode(' OR ', $ban_query) . ')', __FILE__, __LINE__);*/
			SMFLIB_CALL_FUNCTION_SZ("implode", " OR ", sizeof(" OR ") - 1, ban_query, imploded_ban_query);
			zval_ptr_dtor(&ban_query);
			SMFLIB_CALL_FUNCTION_SZZZ("sprintf", SMFLIB_QUERY_BAN_MAIN, sizeof(SMFLIB_QUERY_BAN_MAIN) - 1, *db_prefix, cur_time, imploded_ban_query, SQL_query);
			zval_ptr_dtor(&imploded_ban_query);
			SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
			zval_ptr_dtor(&SQL_query);

			// PHP: while ($row = mysql_fetch_assoc($request))
			SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)
				// PHP: foreach ($restrictions as $restriction)
				SMFLIB_FOREACH_BEGIN_ZZ(restrictions, restriction)
					// PHP: if (!empty($row[$restriction]))
					if (SMFLIB_GET_KEY_VAL_ZZZ(row, *restriction, row_restriction) && !SMFLIB_EMPTY_PP(row_restriction))
					{
						zval **ban_restriction, **restriction_ids;

						// PHP: $_SESSION['ban'][$restriction]['reason'] = $row['reason'];
						if (!SMFLIB_GET_KEY_VAL_ZCZ(row, "reason", row_reason))
							php_error(E_ERROR, "smflib_is_not_banned(): $row['reason'] not set");
						if (!SMFLIB_GET_KEY_VAL_ZZZ(*ban, *restriction, ban_restriction))
							SMFLIB_ARR_INIT_ZZZ(*ban, *restriction, ban_restriction);
						SMFLIB_SET_KEY_VAL_ZCZ_CPY(*ban_restriction, "reason", *row_reason);

						// PHP: $_SESSION['ban'][$restriction]['ids'][] = $row['ID_BAN'];
						if (!SMFLIB_GET_KEY_VAL_ZCZ(row, "ID_BAN", row_ID_BAN))
							php_error(E_ERROR, "smflib_is_not_banned(): $row['ID_BAN'] not set");
						if (!SMFLIB_GET_KEY_VAL_ZCZ(*ban_restriction, "ids", restriction_ids))
							SMFLIB_ARR_INIT_ZCZ(*ban_restriction, "ids", restriction_ids);
						SMFLIB_ADD_INDEX_ZZ_CPY(*restriction_ids, *row_ID_BAN);
					}
				SMFLIB_FOREACH_END_ZZ(restrictions, restriction)
			SMFLIB_MYSQL_FETCH_ASSOC_END(request, row)
			zval_ptr_dtor(&restrictions);

			// PHP: mysql_free_result($request);
			SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
			zval_ptr_dtor(&retval);
			zval_ptr_dtor(&request);
		}

		// PHP: if (isset($_SESSION['ban']['cannot_access']) || isset($_SESSION['ban']['cannot_post']))
		if (SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access) || SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_post))
		{
			zval **cannot_post_ids;

			// PHP: log_ban(array_merge(isset($_SESSION['ban']['cannot_access']) ? $_SESSION['ban']['cannot_access']['ids'] : array(), isset($_SESSION['ban']['cannot_post']) ? $_SESSION['ban']['cannot_post']['ids'] : array()));
			if (cannot_access && cannot_post && SMFLIB_GET_KEY_VAL_ZCZ(*cannot_access, "ids", cannot_access_ids) && SMFLIB_GET_KEY_VAL_ZCZ(*cannot_post, "ids", cannot_post_ids))
			{
				zval *array_to_log;

				SMFLIB_CALL_FUNCTION_ZZ("array_merge", *cannot_access_ids, *cannot_post_ids, array_to_log);
				SMFLIB_CALL_FUNCTION_Z("log_ban", array_to_log, retval);
				zval_ptr_dtor(&retval);
				zval_ptr_dtor(&array_to_log);
			}
			else if (cannot_access && SMFLIB_GET_KEY_VAL_ZCZ(*cannot_access, "ids", cannot_access_ids))
			{
				SMFLIB_CALL_FUNCTION_Z("log_ban", *cannot_access_ids, retval);
				zval_ptr_dtor(&retval);
			}
			else if (SMFLIB_GET_KEY_VAL_ZCZ(*cannot_post, "ids", cannot_post_ids))
			{
				SMFLIB_CALL_FUNCTION_Z("log_ban", *cannot_post_ids, retval);
				zval_ptr_dtor(&retval);
			}
		}
	}
	if (_banLastUpdated)
		zval_ptr_dtor(&_banLastUpdated);

	// PHP: if (!isset($_SESSION['ban']['cannot_access']) && !empty($_COOKIE[$cookiename . '_']))
	SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s_", sizeof("%1$s_") - 1, *cookiename, ban_cookiename);
	if (!SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access) && SMFLIB_GET_KEY_VAL_ZZZ(*_COOKIE, ban_cookiename, ban_cookie) && !SMFLIB_EMPTY_PP(ban_cookie))
	{
		zval *bans, *imploded_bans;
		zval **value;

		// PHP: $bans = explode(',', $_COOKIE[$cookiename . '_']);
		SMFLIB_CALL_FUNCTION_SZ("explode", ",", sizeof(",") - 1, *ban_cookie, bans);

		// PHP: foreach ($bans as $key => $value)
		SMFLIB_FOREACH_BEGIN_ZZ(bans, value)

			// PHP: $bans[$key] = (int) $value;
			convert_to_long(*value);

		SMFLIB_FOREACH_END_ZZ(bans, value)

		/* PHP: $request = db_query("
			SELECT bi.ID_BAN, bg.reason
			FROM ({$db_prefix}ban_items AS bi, {$db_prefix}ban_groups AS bg)
			WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP
				AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . ")
				AND bg.cannot_access = 1
				AND bi.ID_BAN IN (" . implode(', ', $bans) . ")
			LIMIT " . count($bans), __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, bans, imploded_bans);
		SMFLIB_CALL_FUNCTION_SZZZL("sprintf", SMFLIB_QUERY_BAN_COOKIE, sizeof(SMFLIB_QUERY_BAN_COOKIE) - 1, *db_prefix, cur_time, imploded_bans, SMFLIB_COUNT_Z(bans), SQL_query);
		zval_ptr_dtor(&imploded_bans);
		zval_ptr_dtor(&bans);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
		zval_ptr_dtor(&SQL_query);

		// If the query has a zero row result, $_SESSION['ban']['cannot_access'] won't be set.
		cannot_access = NULL;

		// PHP: while ($row = mysql_fetch_assoc($request))
		SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)
		{
			// $_SESSION['ban']['cannot_access'] isn't set, so let's do that now.
			if (!cannot_access)
			{
				SMFLIB_ARR_INIT_ZCZ(*ban, "cannot_access", cannot_access);

				// Also initialize $_SESSION['ban']['cannot_access']['ids'].
				SMFLIB_ARR_INIT_ZCZ(*cannot_access, "ids", cannot_access_ids);
			}

			// PHP: $_SESSION['ban']['cannot_access']['ids'][] = $row['ID_BAN'];
			SMFLIB_GET_KEY_VAL_ZCZ(row, "ID_BAN", row_ID_BAN);
			SMFLIB_ADD_INDEX_ZZ_CPY(*cannot_access_ids, *row_ID_BAN);

			// PHP: $_SESSION['ban']['cannot_access']['reason'] = $row['reason'];
			SMFLIB_GET_KEY_VAL_ZCZ(row, "ID_BAN", row_ID_BAN);
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*cannot_access, "reason", *row_ID_BAN);
		}
		SMFLIB_MYSQL_FETCH_ASSOC_END(request, row)

		// PHP: mysql_free_result($request);
		SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&request);

		// PHP: if (!isset($_SESSION['ban']['cannot_access']))
		if (!cannot_access)
		{
			zval *cookie_url;

			// PHP: require_once($sourcedir . '/Subs-Auth.php');
			if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
				php_error(E_ERROR, "smflib_is_not_banned(): $sourcedir not set");
			SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Subs-Auth.php", sizeof("%1$s/Subs-Auth.php") - 1, *sourcedir, subs_auth);
			smflib_require_once(Z_STRVAL_P(subs_auth), Z_STRLEN_P(subs_auth) TSRMLS_CC);
			zval_ptr_dtor(&subs_auth);

			// PHP: $cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));
			SMFLIB_CALL_FUNCTION_BB("url_parts", SMFLIB_GET_KEY_VAL_ZZ(*modSettings, localCookies) && !SMFLIB_EMPTY_PP(localCookies), SMFLIB_GET_KEY_VAL_ZZ(*modSettings, globalCookies) && !SMFLIB_EMPTY_PP(globalCookies), cookie_url);

			// PHP: setcookie($cookiename . '_', '', time() - 3600, $cookie_url[1], $cookie_url[0], 0);
			SMFLIB_GET_KEY_VAL_ZLZ(cookie_url, 0, cookie_url_0);
			SMFLIB_GET_KEY_VAL_ZLZ(cookie_url, 1, cookie_url_1);
			SMFLIB_CALL_FUNCTION_ZSLZZL("setcookie", ban_cookiename, "", sizeof("") - 1, Z_LVAL_P(cur_time) - 3600, *cookie_url_1, *cookie_url_0, 0, retval);
			zval_ptr_dtor(&retval);
			zval_ptr_dtor(&cookie_url);
		}
	}

	// PHP: if (isset($_SESSION['ban']['cannot_access']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access))
	{
		zval *old_name, *name_your_banned;
		zval **user_info_name, **txt_28, **context_user, **user_info_language;
		zval **txt_430, **cannot_access_reason;

		if (!SMFLIB_GET_GLOBAL_Z(txt) || !SMFLIB_GET_KEY_VAL_ZLZ(*txt, 28, txt_28))
			php_error(E_ERROR, "is_not_banned(): $txt[28] not set");


		// PHP: if (!$user_info['is_guest'])
		if (!Z_LVAL_PP(is_guest))
		{
			/* PHP: db_query("
				DELETE FROM {$db_prefix}log_online
				WHERE ID_MEMBER = $ID_MEMBER
				LIMIT 1", __FILE__, __LINE__);*/
			SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_BAN_CLEAR_ONLINE, sizeof(SMFLIB_QUERY_BAN_CLEAR_ONLINE) - 1, *ID_MEMBER, SQL_query);
			SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
			zval_ptr_dtor(&SQL_query);
			zval_ptr_dtor(&request);
		}

		// PHP: $old_name = isset($user_info['name']) && $user_info['name'] != '' ? $user_info['name'] : $txt[28];
		ALLOC_INIT_ZVAL(old_name);
		if (SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "name", user_info_name) && Z_TYPE_PP(user_info_name) == IS_STRING && Z_STRLEN_PP(user_info_name) != 0)
		{
			ZVAL_ZVAL(old_name,  *user_info_name, 1, 0);
		}
		else
		{
			ZVAL_ZVAL(old_name,  *txt_28, 1, 0);
		}

		// PHP: $user_info['name'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*user_info, "name", "");

		// PHP: $user_info['username'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*user_info, "username", "");

		// PHP: $user_info['is_guest'] = true;
		SMFLIB_SET_KEY_VAL_ZCB(*user_info, "is_guest", 1);

		// PHP: $user_info['is_admin'] = false;
		SMFLIB_SET_KEY_VAL_ZCB(*user_info, "is_admin", 0);

		// PHP: $user_info['permissions'] = array();
		SMFLIB_ARR_INIT_ZC(*user_info, "permissions");

		// PHP: $ID_MEMBER = 0;
		SMFLIB_SET_GLOBAL_CL("ID_MEMBER", 0);

		/* PHP: $context['user'] = array(
			'id' => 0,
			'username' => '',
			'name' => $txt[28],
			'is_guest' => true,
			'is_logged' => false,
			'is_admin' => false,
			'is_mod' => false,
			'language' => $user_info['language']
		);*/
		SMFLIB_ARR_INIT_ZCZ(*context, "user", context_user);
		SMFLIB_SET_KEY_VAL_ZCL(*context_user, "id", 0);
		SMFLIB_SET_KEY_VAL_ZCC(*context_user, "username", "");
		SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context_user, "name", *txt_28);
		SMFLIB_SET_KEY_VAL_ZCB(*context_user, "is_guest", 1);
		SMFLIB_SET_KEY_VAL_ZCB(*context_user, "is_logged", 0);
		SMFLIB_SET_KEY_VAL_ZCB(*context_user, "is_admin", 0);
		SMFLIB_SET_KEY_VAL_ZCB(*context_user, "is_mod", 0);
		if (SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "language", user_info_language))
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*context_user, "language", *user_info_language);

		// PHP: require_once($sourcedir . '/Subs-Auth.php');
		if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
			php_error(E_ERROR, "smflib_is_not_banned(): $sourcedir not set");
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/Subs-Auth.php", sizeof("%1$s/Subs-Auth.php") - 1, *sourcedir, subs_auth);
		smflib_require_once(Z_STRVAL_P(subs_auth), Z_STRLEN_P(subs_auth) TSRMLS_CC);
		zval_ptr_dtor(&subs_auth);

		// PHP: $cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));
		SMFLIB_CALL_FUNCTION_BB("url_parts", SMFLIB_GET_KEY_VAL_ZZ(*modSettings, localCookies) && !SMFLIB_EMPTY_PP(localCookies), SMFLIB_GET_KEY_VAL_ZZ(*modSettings, globalCookies) && !SMFLIB_EMPTY_PP(globalCookies), cookie_url);

		// PHP: setcookie($cookiename . '_', implode(',', $_SESSION['ban']['cannot_access']['ids']), time() + 3153600, $cookie_url[1], $cookie_url[0], 0);
		SMFLIB_GET_KEY_VAL_ZLZ(cookie_url, 0, cookie_url_0);
		SMFLIB_GET_KEY_VAL_ZLZ(cookie_url, 1, cookie_url_1);
		SMFLIB_GET_KEY_VAL_ZCZ(*cannot_access, "ids", cannot_access_ids);
		SMFLIB_CALL_FUNCTION_SZ("implode", ",", sizeof(",") - 1, *cannot_access_ids, imploded_ids);
		SMFLIB_CALL_FUNCTION_ZZLZZL("setcookie", ban_cookiename, imploded_ids, Z_LVAL_P(cur_time) + 3153600, *cookie_url_1, *cookie_url_0, 0, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&cookie_url);
		zval_ptr_dtor(&imploded_ids);

		// PHP: $_GET['action'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "action", "");

		// PHP: $_GET['board'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "board", "");

		// PHP: $_GET['topic'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "topic", "");

		// PHP: writeLog(true);
		SMFLIB_CALL_FUNCTION_B("writeLog", 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: fatal_error(sprintf($txt[430], $old_name) . (empty($_SESSION['ban']['cannot_access']['reason']) ? '' : '<br />' . $_SESSION['ban']['cannot_access']['reason']));
		SMFLIB_GET_KEY_VAL_ZLZ(*txt, 430, txt_430);
		SMFLIB_CALL_FUNCTION_ZZ("sprintf", *txt_430, old_name, name_your_banned);
		zval_ptr_dtor(&old_name);
		if (SMFLIB_GET_KEY_VAL_ZCZ(*cannot_access, "reason", cannot_access_reason) && !SMFLIB_EMPTY_PP(cannot_access_reason))
		{
			zval *input_str;

			input_str = name_your_banned;
			SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s<br />%2$s", sizeof("%1$s<br />%2$s") - 1, input_str, *cannot_access_reason, name_your_banned);
			zval_ptr_dtor(&input_str);
		}
		SMFLIB_CALL_FUNCTION_Z("fatal_error", name_your_banned, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&name_your_banned);

		// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
		php_error(E_USER_ERROR, "Hacking attempt...");
	}

	// PHP: elseif (isset($_SESSION['ban']['cannot_login']) && !$user_info['is_guest'])
	else if (SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_login) && !Z_LVAL_PP(is_guest))
	{
		zval *logInOut;
		zval **cannot_login_ids;

		/* PHP: db_query("
			UPDATE {$db_prefix}ban_items
			SET hits = hits + 1
			WHERE ID_BAN IN (" . implode(', ', $_SESSION['ban']['cannot_login']['ids']) . ')', __FILE__, __LINE__);*/
		SMFLIB_GET_KEY_VAL_ZCZ(*cannot_login, "ids", cannot_login_ids);
		SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, *cannot_login_ids, imploded_ids);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_QUERY_BAN_UPDATE_HITS, sizeof(SMFLIB_QUERY_BAN_UPDATE_HITS) - 1, *db_prefix, imploded_ids, SQL_query);
		zval_ptr_dtor(&imploded_ids);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&SQL_query);

		/* PHP: db_query("
			INSERT INTO {$db_prefix}log_banned
				(ID_MEMBER, ip, email, logTime)
			VALUES ($ID_MEMBER, '$user_info[ip]', '$user_info[email]', " . time() . ')', __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SZZZZZ("sprintf", SMFLIB_QUERY_BAN_INSERT_LOG, sizeof(SMFLIB_QUERY_BAN_INSERT_LOG) - 1, *db_prefix, *ID_MEMBER, *user_info_ip, *user_info_email, cur_time, SQL_query);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&SQL_query);

		// PHP: $_GET['action'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "action", "");

		// PHP: $_GET['board'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "board", "");

		// PHP: $_GET['topic'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "topic", "");

		// PHP: writeLog(true);
		SMFLIB_CALL_FUNCTION_B("writeLog", 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: require_once($sourcedir . '/LogInOut.php');
		if (!SMFLIB_GET_GLOBAL_Z(sourcedir))
			php_error(E_ERROR, "smflib_is_not_banned(): $sourcedir not set");
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "%1$s/LogInOut.php", sizeof("%1$s/LogInOut.php") - 1, *sourcedir, logInOut);
		smflib_require_once(Z_STRVAL_P(logInOut), Z_STRLEN_P(logInOut) TSRMLS_CC);
		zval_ptr_dtor(&logInOut);

		// PHP: Logout(true);
		SMFLIB_CALL_FUNCTION_B("Logout", 1, retval);
		zval_ptr_dtor(&retval);
	}
	zval_ptr_dtor(&cur_time);
	zval_ptr_dtor(&ban_cookiename);

	// PHP: if (isset($user_info['permissions']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*user_info, permissions))
	{
		// PHP: banPermissions();
		SMFLIB_CALL_FUNCTION("banPermissions", retval);
		zval_ptr_dtor(&retval);
	}
}

PHP_FUNCTION(smflib_banPermissions)
{
	// Global variables.
	zval **user_info, **_SESSION;

	// Global hash variables.
	zval **permissions, **ban, **cannot_access, **cannot_post;

	// Local variables.

	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, permissions))
		php_error(E_ERROR, "smflib_banPermissions(): $user_info['permissions'] not set");
	if (!SMFLIB_GET_GLOBAL_Z(_SESSION))
		php_error(E_ERROR, "smflib_banPermissions(): $_SESSION not set");

	// PHP: if (isset($_SESSION['ban']['cannot_access']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban) && SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access))
	{
		// PHP: $user_info['permissions'] = array();
		SMFLIB_ARR_INIT_ZC(*user_info, "permissions");
	}

	// PHP: elseif (isset($_SESSION['ban']['cannot_post']))
	else if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban) && SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_post))
	{
		zval *denied_permissions, *new_permissions;
		char i;

		/* PHP: $denied_permissions = array(
			'pm_send',
			'calendar_post', 'calendar_edit_own', 'calendar_edit_any',
			'poll_post',
			'poll_add_own', 'poll_add_any',
			'poll_edit_own', 'poll_edit_any',
			'poll_lock_own', 'poll_lock_any',
			'poll_remove_own', 'poll_remove_any',
			'manage_attachments', 'manage_smileys', 'manage_boards', 'admin_forum', 'manage_permissions',
			'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news',
			'profile_identity_any', 'profile_extra_any', 'profile_title_any',
			'post_new', 'post_reply_own', 'post_reply_any',
			'delete_own', 'delete_any', 'delete_replies',
			'make_sticky',
			'merge_any', 'split_any',
			'modify_own', 'modify_any', 'modify_replies',
			'move_any',
			'send_topic',
			'lock_own', 'lock_any',
			'remove_own', 'remove_any',
		);*/
		ALLOC_INIT_ZVAL(denied_permissions);
		array_init(denied_permissions);
		for (i = 0; _denied_permissions[i]; i++)
		{
			SMFLIB_ADD_INDEX_ZS(denied_permissions, _denied_permissions[i], strlen(_denied_permissions[i]));
		}

		// PHP: $user_info['permissions'] = array_diff($user_info['permissions'], $denied_permissions);
		SMFLIB_CALL_FUNCTION_ZZ("array_diff", *permissions, denied_permissions, new_permissions);
		SMFLIB_SET_KEY_VAL_ZCZ(*user_info, "permissions", new_permissions);

		zval_ptr_dtor(&denied_permissions);
	}
}

PHP_FUNCTION(smflib_log_ban)
{
	// Input parameters.
	zval *ban_ids = NULL, *email = NULL;

	// Global variables.
	zval **db_prefix, **user_info, **ID_MEMBER, **_SERVER;

	// Global hash variables.
	zval **HTTP_X_MOZ, **ip, **is_guest, **user_info_email;

	// Local variables.
	zval *SQL_query, *retval, *email_param, *cur_time;

	// log_ban($ban_ids = array(), $email = null)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|zz", &ban_ids, &email) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(_SERVER))
		php_error(E_ERROR, "smflib_log_ban(): $_SERVER not set");
	if (!SMFLIB_GET_GLOBAL_Z(db_prefix))
		php_error(E_ERROR, "smflib_log_ban(): $db_prefix not set");
	if (!SMFLIB_GET_GLOBAL_Z(ID_MEMBER))
		php_error(E_ERROR, "smflib_log_ban(): $ID_MEMBER not set");
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, ip))
		php_error(E_ERROR, "smflib_log_ban(): $user_info['ip'] not set");
	if (!SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_guest))
		php_error(E_ERROR, "smflib_log_ban(): $user_info['is_guest'] not set");



	// PHP: if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_X_MOZ) && SMFLIB_CMP_EQ_ZC(*HTTP_X_MOZ, "prefetch"))
	{
		// PHP: return;
		RETURN_NULL();
	}

	/* PHP: db_query("
		INSERT INTO {$db_prefix}log_banned
			(ID_MEMBER, ip, email, logTime)
		VALUES ($ID_MEMBER, '$user_info[ip]', " . ($email === null ? (!$user_info['is_guest'] ? "'$user_info[email]'" : 'NULL') : "'$email'") . ', ' . time() . ')', __FILE__, __LINE__);*/
	if (email)
	{
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "'%1$s'", sizeof("'%1$s'") - 1, email, email_param);
	}
	else if (!Z_LVAL_PP(is_guest) && SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "email", user_info_email))
	{
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "'%1$s'", sizeof("'%1$s'") - 1, *user_info_email, email_param);
	}
	else
	{
		ALLOC_INIT_ZVAL(email_param);
		ZVAL_STRINGL(email_param, "NULL", sizeof("NULL") - 1, 1);
	}
	SMFLIB_CALL_FUNCTION("time", cur_time);
	SMFLIB_CALL_FUNCTION_SZZZZZ("sprintf", SMFLIB_QUERY_LOG_BAN_MAIN, sizeof(SMFLIB_QUERY_LOG_BAN_MAIN) - 1, *db_prefix, *ID_MEMBER, *ip, email_param, cur_time, SQL_query);
	zval_ptr_dtor(&email_param);
	zval_ptr_dtor(&cur_time);
	SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&SQL_query);

	// PHP: if (!empty($ban_ids))
	if (ban_ids && !SMFLIB_EMPTY_P(ban_ids))
	{
		zval *imploded_ids;

		/* PHP: db_query("
			UPDATE {$db_prefix}ban_items
			SET hits = hits + 1
			WHERE ID_BAN IN (" . implode(', ', $ban_ids) . ')', __FILE__, __LINE__);*/
		SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, ban_ids, imploded_ids);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", SMFLIB_QUERY_LOG_BAN_HITS, sizeof(SMFLIB_QUERY_LOG_BAN_HITS) - 1, *db_prefix, imploded_ids, SQL_query);
		zval_ptr_dtor(&imploded_ids);
		SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&SQL_query);
	}
}

PHP_FUNCTION(smflib_isBannedEmail)
{
	// Input parameters.
	zval *email, *restriction, *error;

	// Global variables.
	zval **db_prefix, **txt, **_SESSION;

	// Global hash variables.
	zval **ban, **ban_restriction, **ids, **reason, **cannot_access, **txt_430;
	zval **txt_28;

	// Local variables.
	zval *ban_ids, *ban_reason, *SQL_query, *request, *retval, *formated_error;
	zval *row;

	// isBannedEmail($email, $restriction, $error)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zzz", &email, &restriction, &error) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(_SESSION))
		php_error(E_ERROR, "smflib_isBannedEmail(): $_SESSION not set");
	if (!SMFLIB_GET_GLOBAL_Z(db_prefix))
		php_error(E_ERROR, "smflib_isBannedEmail(): $db_prefix not set");


	// PHP: if (empty($email) || trim($email) == '')
	if (!SMFLIB_EMPTY_P(email))
	{
		zval *trimmed_email;

		SMFLIB_CALL_FUNCTION_Z("trim", email, trimmed_email);
		if (Z_STRLEN_P(trimmed_email) == 0)
		{
			// PHP: return;
			zval_ptr_dtor(&trimmed_email);
			RETURN_NULL();
		}
		zval_ptr_dtor(&trimmed_email);
	}
	else
		RETURN_NULL();

	// PHP: $ban_ids = isset($_SESSION['ban'][$restriction]) ? $_SESSION['ban'][$restriction]['ids'] : array();
	// PHP: $ban_reason = isset($_SESSION['ban'][$restriction]) ? $_SESSION['ban'][$restriction]['reason'] : '';
	ALLOC_INIT_ZVAL(ban_ids);
	ALLOC_INIT_ZVAL(ban_reason);
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban) && Z_TYPE_PP(ban) == IS_ARRAY && SMFLIB_GET_KEY_VAL_ZZZ(*ban, restriction, ban_restriction) && Z_TYPE_PP(ban_restriction) == IS_ARRAY && SMFLIB_GET_KEY_VAL_ZZ(*ban_restriction, ids) && Z_TYPE_PP(ids) == IS_ARRAY && SMFLIB_GET_KEY_VAL_ZZ(*ban_restriction, reason))
	{
		ZVAL_ZVAL(ban_ids, *ids, 1, 0);
		ZVAL_ZVAL(ban_reason, *reason, 1, 0);
	}
	else
	{
		array_init(ban_ids);
		ZVAL_STRINGL(ban_reason, "", sizeof("") - 1, 1);
	}

	/* PHP: $request = db_query("
		SELECT bi.ID_BAN, bg.$restriction, bg.cannot_access, bg.reason
		FROM ({$db_prefix}ban_items AS bi, {$db_prefix}ban_groups AS bg)
		WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP
			AND '$email' LIKE bi.email_address
			AND (bg.$restriction = 1 OR bg.cannot_access = 1)", __FILE__, __LINE__);*/
	SMFLIB_CALL_FUNCTION_SZZZ("sprintf", SMFLIB_QUERY_BANNED_EMAIL, sizeof(SMFLIB_QUERY_BANNED_EMAIL) - 1, restriction, *db_prefix, email, SQL_query);
	SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
	zval_ptr_dtor(&SQL_query);

	// PHP: while ($row = mysql_fetch_assoc($request))
	SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)
	{
		zval **row_cannot_access, **ID_BAN, **row_reason, **row_restriction;

		// PHP: if (!empty($row['cannot_access']))
		if (SMFLIB_GET_KEY_VAL_ZCZ(row, "cannot_access", row_cannot_access) && !SMFLIB_EMPTY_PP(row_cannot_access))
		{
			// PHP: $_SESSION['ban']['cannot_access']['ids'][] = $row['ID_BAN'];
			if (!SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban))
				SMFLIB_ARR_INIT_ZCZ(*_SESSION, "ban", ban);
			if (!SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access))
				SMFLIB_ARR_INIT_ZCZ(*ban, "cannot_access", cannot_access);
			if (!SMFLIB_GET_KEY_VAL_ZZ(*cannot_access, ids))
				SMFLIB_ARR_INIT_ZCZ(*cannot_access, "ids", ids);
			SMFLIB_GET_KEY_VAL_ZZ(row, ID_BAN);
			SMFLIB_ADD_INDEX_ZZ_CPY(*ids, *ID_BAN);

			// PHP: $_SESSION['ban']['cannot_access']['reason'] = $row['reason'];
			SMFLIB_GET_KEY_VAL_ZCZ(row, "reason", row_reason);
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*cannot_access, "reason", *row_reason);
		}

		// PHP: if (!empty($row[$restriction]))
		if (SMFLIB_GET_KEY_VAL_ZZZ(row, restriction, row_restriction) && !SMFLIB_EMPTY_PP(row_restriction))
		{
			// PHP: $ban_ids[] = $row['ID_BAN'];
			SMFLIB_GET_KEY_VAL_ZZ(row, ID_BAN);
			SMFLIB_ADD_INDEX_ZZ_CPY(ban_ids, *ID_BAN);

			// PHP: $ban_reason = $row['reason'];
			SMFLIB_GET_KEY_VAL_ZCZ(row, "reason", row_reason);
			ZVAL_ZVAL(ban_reason, *row_reason, 1, 0);
		}
	}
	SMFLIB_MYSQL_FETCH_ASSOC_END(request, row);

	// PHP: mysql_free_result($request);
	SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&request);

	// PHP: if (isset($_SESSION['ban']['cannot_access']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, ban) && SMFLIB_GET_KEY_VAL_ZZ(*ban, cannot_access))
	{
		zval *input_str;

		// PHP: log_ban($_SESSION['ban']['cannot_access']['ids']);
		SMFLIB_GET_KEY_VAL_ZZ(*cannot_access, ids);
		SMFLIB_CALL_FUNCTION_Z("log_ban", *ids, retval);
		zval_ptr_dtor(&retval);

		// PHP: $_SESSION['ban']['last_checked'] = time();
		SMFLIB_CALL_FUNCTION("time", retval);
		SMFLIB_SET_KEY_VAL_ZCZ(*ban, "last_checked", retval);

		// PHP: fatal_error(sprintf($txt[430], $txt[28]) . $_SESSION['ban']['cannot_access']['reason'], false);
		if (!SMFLIB_GET_GLOBAL_Z(txt) || !SMFLIB_GET_KEY_VAL_ZLZ(*txt, 430, txt_430))
			php_error(E_ERROR, "smflib_isBannedEmail(): $txt[430] not set");
		if (!SMFLIB_GET_KEY_VAL_ZLZ(*txt, 28, txt_28))
			php_error(E_ERROR, "smflib_isBannedEmail(): $txt[28] not set");
		SMFLIB_CALL_FUNCTION_ZZ("sprintf", *txt_430, *txt_28, formated_error);
		input_str = formated_error;
		SMFLIB_GET_KEY_VAL_ZZ(*cannot_access, reason);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s%2$s", sizeof("%1$s%2$s") - 1, input_str, *reason, formated_error);
		zval_ptr_dtor(&input_str);
		SMFLIB_CALL_FUNCTION_ZB("fatal_error", formated_error, 0, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&formated_error);
	}

	// PHP: if (!empty($ban_ids))
	if (!SMFLIB_EMPTY_P(ban_ids))
	{
		// PHP: log_ban($ban_ids, $email);
		SMFLIB_CALL_FUNCTION_ZZ("log_ban", ban_ids, email, retval);
		zval_ptr_dtor(&retval);

		// PHP: fatal_error($error . $ban_reason, false);
		SMFLIB_CALL_FUNCTION_SZZ("sprintf", "%1$s%2$s", sizeof("%1$s%2$s") - 1, error, ban_reason, formated_error);
		SMFLIB_CALL_FUNCTION_ZB("fatal_error", formated_error, 0, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&formated_error);
	}
	zval_ptr_dtor(&ban_ids);
	zval_ptr_dtor(&ban_reason);
}

PHP_FUNCTION(smflib_checkSession)
{
	// Input parameters.
	zval *type = NULL, *from_action = NULL, *is_fatal = NULL;

	// Global variables.
	zval **sc, **modSettings, **boardurl, **_POST, **_GET, **_SESSION;
	zval **_SERVER;

	// Global hash variables.
	zval **_POST_sc, **sesc, **USER_AGENT, **HTTP_USER_AGENT, **disableCheckUA;
	zval **HTTP_X_MOZ, **HTTP_REFERER, **HTTP_HOST, **globalCookies, **old_url;

	// Local variables.
	zval *error = NULL, *retval;
	ulong log_error = 0;

	// checkSession($type = 'post', $from_action = '', $is_fatal = true)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|zzz", &type, &from_action, &is_fatal) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(_POST))
		php_error(E_ERROR, "smflib_checkSession(): $_POST not set");
	if (!SMFLIB_GET_GLOBAL_Z(_GET))
		php_error(E_ERROR, "smflib_checkSession(): $_GET not set");
	if (!SMFLIB_GET_GLOBAL_Z(_SESSION))
		php_error(E_ERROR, "smflib_checkSession(): $_SESSION not set");
	if (!SMFLIB_GET_GLOBAL_Z(_SERVER))
		php_error(E_ERROR, "smflib_checkSession(): $_SERVER not set");
	if (!SMFLIB_GET_GLOBAL_Z(sc))
		php_error(E_ERROR, "smflib_checkSession(): $sc not set");

	// PHP: if ($type == 'post' && (!isset($_POST['sc']) || $_POST['sc'] != $sc))
	if ((!type || SMFLIB_CMP_EQ_ZC(type, "post")) && (!SMFLIB_GET_KEY_VAL_ZCZ(*_POST, "sc", _POST_sc) || SMFLIB_CMP_NOT_EQ_ZZ(*_POST_sc, *sc)))
	{
		// PHP: $error = 'smf304';
		ALLOC_INIT_ZVAL(error);
		ZVAL_STRINGL(error, "smf304", sizeof("smf304") - 1, 1);
	}

	// PHP: elseif ($type == 'get' && (!isset($_GET['sesc']) || $_GET['sesc'] != $sc))
	else if (type && SMFLIB_CMP_EQ_ZC(type, "get") && (!SMFLIB_GET_KEY_VAL_ZZ(*_GET, sesc) || SMFLIB_CMP_NOT_EQ_ZZ(*sesc, *sc)))
	{
		// PHP: $error = 'smf305';
		ALLOC_INIT_ZVAL(error);
		ZVAL_STRINGL(error, "smf305", sizeof("smf305") - 1, 1);
	}

	// PHP: elseif ($type == 'request' && (!isset($_GET['sesc']) || $_GET['sesc'] != $sc) && (!isset($_POST['sc']) || $_POST['sc'] != $sc))
	else if (type && SMFLIB_CMP_EQ_ZC(type, "request") && (!SMFLIB_GET_KEY_VAL_ZZ(*_GET, sesc) || SMFLIB_CMP_NOT_EQ_ZZ(*sesc, *sc)) && (!SMFLIB_GET_KEY_VAL_ZCZ(*_POST, "sc", _POST_sc) || SMFLIB_CMP_NOT_EQ_ZZ(*_POST_sc, *sc)))
	{
		// PHP: $error = 'smf305';
		ALLOC_INIT_ZVAL(error);
		ZVAL_STRINGL(error, "smf306", sizeof("smf306") - 1, 1);
	}

	// PHP: if ((!isset($_SESSION['USER_AGENT']) || $_SESSION['USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) && empty($modSettings['disableCheckUA']))
	if ((!SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, USER_AGENT) || !SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_USER_AGENT) || SMFLIB_CMP_NOT_EQ_ZZ(*USER_AGENT, *HTTP_USER_AGENT)) && (!SMFLIB_GET_GLOBAL_Z(modSettings) || !SMFLIB_GET_KEY_VAL_ZZ(*modSettings, disableCheckUA) || SMFLIB_EMPTY_PP(disableCheckUA)))
	{
		// PHP: $error = 'smf305';
		if (error)
			zval_ptr_dtor(&error);
		ALLOC_INIT_ZVAL(error);
		ZVAL_STRINGL(error, "smf305", sizeof("smf306") - 1, 1);
	}

	// PHP: if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_X_MOZ) && SMFLIB_CMP_EQ_ZC(*HTTP_X_MOZ, "prefetch"))
	{
		// PHP: ob_end_clean();
		SMFLIB_CALL_FUNCTION("ob_end_clean", retval);
		zval_ptr_dtor(&retval);

		// PHP: header('HTTP/1.1 403 Forbidden');
		SMFLIB_CALL_FUNCTION_S("header", "HTTP/1.1 403 Forbidden", sizeof("HTTP/1.1 403 Forbidden") - 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: die;
		zend_bailout();
	}

	// PHP: $referrer = isset($_SERVER['HTTP_REFERER']) ? @parse_url($_SERVER['HTTP_REFERER']) : array();
	// We don't need an empty $referrer, just ignore it.
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_REFERER))
	{
		zval *referrer;
		zval **referrer_host;

		SMFLIB_CALL_FUNCTION_Z("parse_url", *HTTP_REFERER, referrer);

		// PHP: if (!empty($referrer['host']))
		if (SMFLIB_GET_KEY_VAL_ZCZ(referrer, "host", referrer_host) && !SMFLIB_EMPTY_PP(referrer_host))
		{
			zval *real_host, *parsed_url;
			zval **parsed_url_host;
			char *lc_referrer_host, *lc_parsed_url_host, *lc_real_host;

			// PHP: if (strpos($_SERVER['HTTP_HOST'], ':') !== false)
			if (SMFLIB_GET_KEY_VAL_ZZ(*_SERVER, HTTP_HOST) && SMFLIB_STRPOS_ZC(*HTTP_HOST, ":"))
			{
				// PHP: $real_host = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], ':'));
				SMFLIB_CALL_FUNCTION_ZLL("substr", *HTTP_HOST, 0, SMFLIB_REL_STRPOS_ZC(*HTTP_HOST, ":"), real_host);
			}
			// PHP: else
			else
			{
				// PHP: $real_host = $_SERVER['HTTP_HOST'];
				ALLOC_INIT_ZVAL(real_host);
				ZVAL_ZVAL(real_host, *HTTP_HOST, 1, 0);
			}

			// PHP: $parsed_url = parse_url($boardurl);
			if (!SMFLIB_GET_GLOBAL_Z(boardurl))
				php_error(E_ERROR, "smflib_checkSession(): $boardurl not set");
			SMFLIB_CALL_FUNCTION_Z("parse_url", *boardurl, parsed_url);

			// PHP: if (!empty($modSettings['globalCookies']))
			if (SMFLIB_GET_GLOBAL_Z(modSettings) && SMFLIB_GET_KEY_VAL_ZZ(*modSettings, globalCookies) && !SMFLIB_EMPTY_PP(globalCookies))
			{
				zval *parts, *found_it;
				zval **parts_1;

				// PHP: if (preg_match('~(?:[^\.]+\.)?([^\.]{3,}\..+)\z~i', $parsed_url['host'], $parts) == 1)
				SMFLIB_GET_KEY_VAL_ZCZ(parsed_url, "host", parsed_url_host);
				ALLOC_INIT_ZVAL(parts);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_CHECK_SESSION_VALID_IP, sizeof(SMFLIB_PREG_CHECK_SESSION_VALID_IP) - 1, *parsed_url_host, parts, found_it);
				if (Z_LVAL_P(found_it) == 1)
				{
					// PHP: $parsed_url['host'] = $parts[1];
					SMFLIB_GET_KEY_VAL_ZLZ(parts, 1, parts_1);
					SMFLIB_SET_KEY_VAL_ZCZ_CPY(parsed_url, "host", *parts_1);
				}
				zval_ptr_dtor(&found_it);
				zval_ptr_dtor(&parts);

				// PHP: if (preg_match('~(?:[^\.]+\.)?([^\.]{3,}\..+)\z~i', $referrer['host'], $parts) == 1)
				ALLOC_INIT_ZVAL(parts);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_CHECK_SESSION_VALID_IP, sizeof(SMFLIB_PREG_CHECK_SESSION_VALID_IP) - 1, *referrer_host, parts, found_it);
				if (Z_LVAL_P(found_it) == 1)
				{
					// PHP: $referrer['host'] = $parts[1];
					SMFLIB_GET_KEY_VAL_ZLZ(parts, 1, parts_1);
					SMFLIB_SET_KEY_VAL_ZCZ_CPY(referrer, "host", *parts_1);
				}
				zval_ptr_dtor(&found_it);
				zval_ptr_dtor(&parts);

				// PHP: if (preg_match('~(?:[^\.]+\.)?([^\.]{3,}\..+)\z~i', $real_host, $parts) == 1)
				ALLOC_INIT_ZVAL(parts);
				SMFLIB_CALL_FUNCTION_SZZ("preg_match", SMFLIB_PREG_CHECK_SESSION_VALID_IP, sizeof(SMFLIB_PREG_CHECK_SESSION_VALID_IP) - 1, real_host, parts, found_it);
				if (Z_LVAL_P(found_it) == 1)
				{
					// PHP: $real_host = $parts[1];
					SMFLIB_GET_KEY_VAL_ZLZ(parts, 1, parts_1);
					efree(Z_STRVAL_P(real_host));
					ZVAL_ZVAL(real_host, *parts_1, 1, 0);
				}
				zval_ptr_dtor(&found_it);
				zval_ptr_dtor(&parts);
			}

			// PHP: if (strtolower($referrer['host']) != strtolower($parsed_url['host']) && strtolower($referrer['host']) != strtolower($real_host))
			lc_referrer_host = estrndup(Z_STRVAL_PP(referrer_host), Z_STRLEN_PP(referrer_host));
			zend_str_tolower(lc_referrer_host, Z_STRLEN_PP(referrer_host));
			lc_parsed_url_host = estrndup(Z_STRVAL_PP(parsed_url_host), Z_STRLEN_PP(parsed_url_host));
			zend_str_tolower(lc_parsed_url_host, Z_STRLEN_PP(parsed_url_host));
			if (SMFLIB_CMP_NOT_EQ_SS(lc_referrer_host, Z_STRLEN_PP(referrer_host), lc_parsed_url_host, Z_STRLEN_PP(parsed_url_host)))
			{
				lc_real_host = estrndup(Z_STRVAL_P(real_host), Z_STRLEN_P(real_host));
				zend_str_tolower(lc_parsed_url_host, Z_STRLEN_P(real_host));
				if (SMFLIB_CMP_NOT_EQ_SS(lc_referrer_host, Z_STRLEN_PP(referrer_host), lc_parsed_url_host, Z_STRLEN_PP(parsed_url_host)))
				{
					// PHP: $error = 'smf306';
					if (error)
						zval_ptr_dtor(&error);
					ALLOC_INIT_ZVAL(error);
					ZVAL_STRINGL(error, "smf306", sizeof("smf306") - 1, 1);

					// PHP: $log_error = true;
					log_error = 1;
				}
				efree(lc_real_host);
			}
			efree(lc_parsed_url_host);
			efree(lc_referrer_host);
			zval_ptr_dtor(&real_host);
			zval_ptr_dtor(&parsed_url);
		}
		zval_ptr_dtor(&referrer);
	}

	// PHP: if (!empty($from_action) && (!isset($_SESSION['old_url']) || preg_match('~[?;&]action=' . $from_action . '([;&]|$)~', $_SESSION['old_url']) == 0))
	if (from_action && !SMFLIB_EMPTY_P(from_action))
	{
		zend_bool show_error = 0;
		if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, old_url))
		{
			zval *preg_thing;

			SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_PREG_CHECK_SESSION_FROM_ACTION, sizeof(SMFLIB_PREG_CHECK_SESSION_FROM_ACTION) - 1, from_action, preg_thing)
			SMFLIB_CALL_FUNCTION_ZZ("preg_match", preg_thing, *old_url, retval);
			show_error = Z_LVAL_P(retval) == 0 ? 1 : 0;
			zval_ptr_dtor(&retval);
			zval_ptr_dtor(&preg_thing);
		}
		else
			show_error = 1;
		if (show_error)
		{
			// PHP: $error = 'smf306';
			if (error)
				zval_ptr_dtor(&error);
			ALLOC_INIT_ZVAL(error);
			ZVAL_STRINGL(error, "smf306", sizeof("smf306") - 1, 1);

			// PHP: $log_error = true;
			log_error = 1;
		}
	}

	// PHP: if (!isset($error))
	if (!error)
	{
		// PHP: return '';
		RETURN_STRINGL("", sizeof("") - 1, 1);
	}
	// PHP: elseif ($is_fatal)
	else if (!is_fatal || Z_LVAL_P(is_fatal))
	{
		// PHP: fatal_lang_error($error, isset($log_error));
		SMFLIB_CALL_FUNCTION_ZB("fatal_lang_error", error, log_error, retval);
		zval_ptr_dtor(&error);
		zval_ptr_dtor(&retval);
	}
	// PHP: else
	else
	{
		// PHP: return $error;
		RETURN_ZVAL(error, 1, 1);
	}

	// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
	php_error(E_USER_ERROR, "Hacking attempt...");
}

PHP_FUNCTION(smflib_checkSubmitOnce)
{
	// Input parameters.
	zval *action, *is_fatal = NULL;

	// Global variables.
	zval **context, **ID_MEMBER, **_SESSION, **_REQUEST;

	// Global hash variables.
	zval **form_stack_pointer, **form_stack, **seqnum = NULL;

	// Local variables.
	zval *cache_key, *temp, *retval, *getBitResult, *cache_array;

	// checkSubmitOnce($action, $is_fatal = true)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &action, &is_fatal) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(ID_MEMBER))
		php_error(E_ERROR, "smflib_checkSubmitOnce(): $ID_MEMBER not set");
	if (!SMFLIB_GET_GLOBAL_Z(_SESSION))
		php_error(E_ERROR, "smflib_checkSubmitOnce(): $_SESSION not set");
	if (!SMFLIB_GET_GLOBAL_Z(_REQUEST))
		php_error(E_ERROR, "smflib_checkSubmitOnce(): $_REQUEST not set");
	if (!SMFLIB_GET_GLOBAL_Z(context) || Z_TYPE_PP(context) != IS_ARRAY)
		php_error(E_ERROR, "smflib_checkSubmitOnce(): $context not set");

	// PHP: if (($temp = cache_get_data('form_stack-' . $ID_MEMBER, 120)) != null && count($temp) == 2)
	SMFLIB_CALL_FUNCTION_SZ("sprintf", "form-stack-%1$s", sizeof("form-stack-%1$s") - 1, *ID_MEMBER, cache_key);
	SMFLIB_CALL_FUNCTION_ZL("cache_get_data", cache_key, 120, temp);
	if (Z_TYPE_P(temp) == IS_ARRAY && SMFLIB_COUNT_Z(temp) == 2)
	{
		zval **temp_0, **temp_1;

		SMFLIB_GET_KEY_VAL_ZLZ(temp, 0, temp_0);
		SMFLIB_GET_KEY_VAL_ZLZ(temp, 1, temp_1);

		// PHP: if (!isset($_SESSION['form_stack_pointer']) || $temp[1] > $_SESSION['form_stack_pointer'])
		if (!SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack_pointer) || Z_LVAL_PP(temp_1) > Z_LVAL_PP(form_stack_pointer))
		{
			// PHP: $_SESSION['form_stack'] = $temp[0];
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SESSION, "form_stack", *temp_0);

			// PHP: $_SESSION['form_stack_pointer'] = $temp[1];
			SMFLIB_SET_KEY_VAL_ZCZ_CPY(*_SESSION, "form_stack_pointer", *temp_1);
		}
	}
	zval_ptr_dtor(&temp);

	// PHP: if ($action == 'register')
	if (SMFLIB_CMP_EQ_ZC(action, "register"))
	{
		// PHP: if (!isset($_SESSION['form_stack']) || !isset($_SESSION['form_stack_pointer']))
		if (!SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack) || !SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack_pointer))
		{
			// PHP: $_SESSION['form_stack'] = chr(1);
			SMFLIB_SET_KEY_VAL_ZCC(*_SESSION, "form_stack", "\x1");

			// PHP: $_SESSION['form_stack_pointer'] = 0;
			SMFLIB_SET_KEY_VAL_ZCLZ(*_SESSION, "form_stack_pointer", 0, form_stack_pointer);

		}
		// PHP: else
		else
		{
			// PHP: setBit($_SESSION['form_stack'], $_SESSION['form_stack_pointer'], 1);
			SMFLIB_CALL_FUNCTION_ZZL("setBit", *form_stack, *form_stack_pointer, 1, retval);
			zval_ptr_dtor(&retval);
		}

		// PHP: $context['form_sequence_number'] = $_SESSION['form_stack_pointer']++;
		SMFLIB_SET_KEY_VAL_ZCZ(*context, "form_sequence_number", *form_stack_pointer);
		Z_LVAL_PP(form_stack_pointer)++;
	}
	// PHP: elseif ($action == 'check')
	else if (SMFLIB_CMP_EQ_ZC(action, "check"))
	{
		// PHP: if (isset($_REQUEST['seqnum'], $_SESSION['form_stack']) && !getBit($_SESSION['form_stack'], $_REQUEST['seqnum']))
		if (SMFLIB_GET_KEY_VAL_ZZ(*_REQUEST, seqnum) && SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack))
		{
			SMFLIB_CALL_FUNCTION_ZZ("getBit", *form_stack, *seqnum, getBitResult);
			if (!Z_LVAL_P(getBitResult))
			{
				// PHP: if ($is_fatal)
				if (!is_fatal || Z_LVAL_P(is_fatal))
				{
					// PHP: fatal_lang_error('error_form_already_submitted', false);
					SMFLIB_CALL_FUNCTION_SB("fatal_lang_error", "error_form_already_submitted", sizeof("error_form_already_submitted") - 1, 0, retval);
					zval_ptr_dtor(&retval);
				}
				// PHP: else
				else
				{
					// PHP: return false;
					zval_ptr_dtor(&cache_key);
					RETURN_FALSE;
				}
			}
			zval_ptr_dtor(&getBitResult);
		}
		// PHP: elseif (!isset($_REQUEST['seqnum']))
		else if (seqnum)
		{
			// PHP: $_REQUEST['seqnum'] = 0;
			SMFLIB_SET_KEY_VAL_ZCL(*_REQUEST, "sequnum", 0);
		}

		// PHP: setBit($_SESSION['form_stack'], $_REQUEST['seqnum'], 0);
		if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack) && SMFLIB_GET_KEY_VAL_ZZ(*_REQUEST, seqnum))
		{
			SMFLIB_CALL_FUNCTION_ZZL("setBit", *form_stack, *seqnum, 0, retval);
			zval_ptr_dtor(&retval);
		}

		// PHP: if (isset($_SESSION['form_stack'], $_SESSION['form_stack_pointer']))
		if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack) && SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack_pointer))
		{
			// PHP: cache_put_data('form_stack-' . $ID_MEMBER, array($_SESSION['form_stack'], $_SESSION['form_stack_pointer']), 120);
			ALLOC_INIT_ZVAL(cache_array);
			array_init(cache_array);
			SMFLIB_ADD_INDEX_ZZ_CPY(cache_array, *form_stack);
			SMFLIB_ADD_INDEX_ZZ_CPY(cache_array, *form_stack_pointer);
			SMFLIB_CALL_FUNCTION_ZZL("cache_put_data", cache_key, cache_array, 120, retval);
			zval_ptr_dtor(&retval);
			zval_ptr_dtor(&cache_array);
		}
		// PHP: return true;
		zval_ptr_dtor(&cache_key);
		RETURN_TRUE;
	}
	// PHP: elseif ($action == 'free' && !empty($_REQUEST['seqnum']) && getBit($_SESSION['form_stack'], $_REQUEST['seqnum']))
	else if (SMFLIB_CMP_EQ_ZC(action, "free") && SMFLIB_GET_KEY_VAL_ZZ(*_REQUEST, seqnum) && !SMFLIB_EMPTY_PP(seqnum) && SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack))
	{
		SMFLIB_CALL_FUNCTION_ZZ("getBit", *form_stack, *seqnum, getBitResult);
		if (Z_LVAL_P(getBitResult))
		{
			// PHP: setBit($_SESSION['form_stack'], $_REQUEST['seqnum'], 0);
			SMFLIB_CALL_FUNCTION_ZZL("setBit", *form_stack, *seqnum, 0, retval);
			zval_ptr_dtor(&retval);
		}
		zval_ptr_dtor(&getBitResult);
	}
	// PHP: elseif ($action != 'free')
	else if (SMFLIB_CMP_NOT_EQ_ZC(action, "free"))
	{
		// PHP: trigger_error('checkSubmitOnce(): Invalid action \'' . $action . '\'', E_USER_WARNING);
		php_error(E_USER_WARNING, "checkSubmitOnce(): Invalid action '%s'", Z_STRVAL_P(action));
	}

	// PHP: if (isset($_SESSION['form_stack'], $_SESSION['form_stack_pointer']))
	if (SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack) && SMFLIB_GET_KEY_VAL_ZZ(*_SESSION, form_stack_pointer))
	{
		// PHP: cache_put_data('form_stack-' . $ID_MEMBER, array($_SESSION['form_stack'], $_SESSION['form_stack_pointer']), 120);
		ALLOC_INIT_ZVAL(cache_array);
		array_init(cache_array);
		SMFLIB_ADD_INDEX_ZZ_CPY(cache_array, *form_stack);
		SMFLIB_ADD_INDEX_ZZ_CPY(cache_array, *form_stack_pointer);
		SMFLIB_CALL_FUNCTION_ZZL("cache_put_data", cache_key, cache_array, 120, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&cache_array);
	}
	zval_ptr_dtor(&cache_key);
}

PHP_FUNCTION(smflib_setBit)
{
	// Input parameters.
	long position, value;
	zval *string;

	// Local variables.
	long charPos, bitPos;


	// setBit(&$string, $position, $value)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zll", &string, &position, &value) == FAILURE)
		RETURN_NULL();

	// Make sure this is a string.
	if (Z_TYPE_P(string) != IS_STRING)
		convert_to_string(string);

	// PHP: $charPos = $position >> 3;
	charPos = position >> 3;

	// PHP: $bitPos = $position & 7;
	bitPos = position & 7;

	// PHP: if (strlen($string) < $charPos + 1)
	if (Z_STRLEN_P(string) < charPos + 1)
	{
		int i;

		// PHP: $string .= str_repeat(chr(0), $charPos + 1 - strlen($string));
		STR_REALLOC(Z_STRVAL_P(string), charPos + 2);
		for (i = Z_STRLEN_P(string); i < charPos + 2; i++)
			Z_STRVAL_P(string)[i] = 0;
		Z_STRLEN_P(string) = charPos + 1;
	}

	// PHP: if (empty($value))
	if (value == 0)
	{
		// PHP: $string{$charPos} = chr(ord($string{$charPos}) & ~(1 << $bitPos));
		Z_STRVAL_P(string)[charPos] = Z_STRVAL_P(string)[charPos] & ~(1 << bitPos);
	}
	// PHP: else
	else
	{
		// PHP: $string{$charPos} = chr(ord($string{$charPos}) | (1 << $bitPos));
		Z_STRVAL_P(string)[charPos] = Z_STRVAL_P(string)[charPos] | (1 << bitPos);
	}
}

PHP_FUNCTION(smflib_getBit)
{
	// Input parameters.
	char *string;
	int string_len;
	long position;

	// Local variables.
	long charPos;

	// getBit(&$string, $position)
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sl", &string, &string_len, &position) == FAILURE)
		RETURN_NULL();

	// PHP: $charPos = $position >> 3;
	charPos = position >> 3;

	// PHP: if (strlen($string) < $charPos + 1)
	if (string_len < charPos + 1)
	{
		// PHP: return false;
		RETURN_FALSE;
	}

	// PHP: return (ord($string{$charPos}) & (1 << ($position & 7))) > 0;
	if (string[charPos] & (1 << (position & 7)))
	{
		RETURN_TRUE;
	}
	else
	{
		RETURN_FALSE;
	}
}



PHP_FUNCTION(smflib_allowedTo)
{
	// Input parameters.
	zval *permission, *boards = NULL;

	// Global variables.
	zval **user_info, **modSettings, **db_prefix, **ID_MEMBER;

	// Global hash variables.
	zval **is_admin = NULL, **permissions = NULL, **permission_enable_by_board = NULL;
	zval **groups = NULL;

	// Local variables.
	zval *imploded_boards, *SQL_query_perm_mode, *imploded_groups;
	zval *SQL_query_perm_list, *SQL_query_board, *SQL_query, *request;
	zval *num_rows, *row, *retval, *_boards, *max_allowable_mode;
	zval **addDeny = NULL;
	long result;

	// allowedTo($permission, $boards = null).
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &permission, &boards) == FAILURE)
		RETURN_NULL();

	// We didn't expect anything else than strings or arrays.
	if (Z_TYPE_P(permission) != IS_STRING && Z_TYPE_P(permission) != IS_ARRAY)
		RETURN_FALSE;

	// PHP: if (empty($permission))
	if (SMFLIB_EMPTY_P(permission))
	{
		//PHP: return true;
		RETURN_TRUE;
	}

	// PHP: if (empty($user_info))
	if (!SMFLIB_GET_GLOBAL_Z(user_info) || SMFLIB_EMPTY_PP(user_info))
	{
		// PHP: return false;
		RETURN_FALSE;
	}

	// PHP: if ($user_info['is_admin'])
	if (SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_admin) && Z_TYPE_PP(is_admin) == IS_BOOL && Z_LVAL_PP(is_admin) != 0)
	{
		// PHP: return true;
		RETURN_TRUE;
	}

	// Return error on no permissions.
	if (!SMFLIB_GET_KEY_VAL_ZZ(*user_info, permissions) || Z_TYPE_PP(permissions) != IS_ARRAY)
		php_error(E_ERROR, "allowedTo(): $user_info['permissions'] not set");

	// PHP: if ($boards === null)
	if (!boards || Z_TYPE_P(boards) == IS_NULL)
	{
		// PHP: if (!is_array($permission) && in_array($permission, $user_info['permissions']))
		if (Z_TYPE_P(permission) != IS_ARRAY)
		{
			zval *in_array;

			SMFLIB_CALL_FUNCTION_ZZ("in_array", permission, *permissions, in_array);
			if (Z_LVAL_P(in_array))
			{
				// PHP: return true;
				zval_ptr_dtor(&in_array);
				RETURN_TRUE;
			}
			zval_ptr_dtor(&in_array);
		}
		// PHP: elseif (is_array($permission) && count(array_intersect($permission, $user_info['permissions'])) != 0)
		else
		{
			zval *intersect;

			SMFLIB_CALL_FUNCTION_ZZ("array_intersect", permission, *permissions, intersect);
			if (zend_hash_num_elements(Z_ARRVAL_P(intersect)) != 0)
			{
				// PHP: return true;
				zval_ptr_dtor(&intersect);
				RETURN_TRUE;
			}
			zval_ptr_dtor(&intersect);
		}

		// PHP: else
		// PHP: return false;
		RETURN_FALSE;
	}
	else
	{
		// Create a copy of $boards for editing purposes.
		ALLOC_INIT_ZVAL(_boards);
		ZVAL_ZVAL(_boards, boards, 1, 0);

		// PHP: elseif (!is_array($boards))
		if (Z_TYPE_P(_boards) != IS_ARRAY)
		{
			// PHP: $boards = array($boards);
			convert_to_array(_boards);
		}
	}

	// Some globals have to be set, otherwise get out of here.
	if (!SMFLIB_GET_GLOBAL_Z(modSettings) || !SMFLIB_GET_GLOBAL_Z(db_prefix) || !SMFLIB_GET_GLOBAL_Z(ID_MEMBER) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, groups))
		RETURN_FALSE;

	ALLOC_INIT_ZVAL(max_allowable_mode);

	// PHP: if (empty($modSettings['permission_enable_by_board']) && !in_array('moderate_board', $user_info['permissions']))
	if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, permission_enable_by_board) || SMFLIB_EMPTY_PP(permission_enable_by_board))
	{
		zval *can_moderate_board;

		SMFLIB_CALL_FUNCTION_SZ("in_array", "moderate_board", sizeof("moderate_board") - 1, *permissions, can_moderate_board);
		if (!Z_LVAL_P(can_moderate_board))
		{
			zval *post_reply;
			zval *temp;

			// PHP: $temp = is_array($permission) ? $permission : array($permission);
			ALLOC_INIT_ZVAL(temp);
			ZVAL_ZVAL(temp, permission, 1, 0);
			if (Z_TYPE_P(permission) != IS_ARRAY)
				convert_to_array(temp);

			// PHP: if (in_array('post_reply_own', $temp) || in_array('post_reply_any', $temp))
			SMFLIB_CALL_FUNCTION_SZ("in_array", "post_reply_own", sizeof("post_reply_own") - 1, temp, post_reply);
			if (!Z_LVAL_P(post_reply))
			{
				zval_ptr_dtor(&post_reply);
				SMFLIB_CALL_FUNCTION_SZ("in_array", "post_reply_any", sizeof("post_reply_any") - 1, temp, post_reply);
			}
			if (Z_LVAL_P(post_reply))
			{
				//PHP: $max_allowable_mode = 3;
				ZVAL_LONG(max_allowable_mode, 3);
			}
			// PHP: elseif (in_array('post_new', $temp))
			else
			{
				zval *post_new;

				SMFLIB_CALL_FUNCTION_SZ("in_array", "post_new", sizeof("post_new") - 1, temp, post_new);
				if (Z_LVAL_P(post_new))
				{
					// PHP: $max_allowable_mode = 2;
					ZVAL_LONG(max_allowable_mode, 2);
				}
				// PHP: elseif (in_array('poll_post', $temp))
				else
				{
					zval *poll_post;

					SMFLIB_CALL_FUNCTION_SZ("in_array", "poll_post", sizeof("poll_post") - 1, temp, poll_post);
					if (Z_LVAL_P(poll_post))
					{
						// PHP: $max_allowable_mode = 0;
						ZVAL_LONG(max_allowable_mode, 0);
					}
					zval_ptr_dtor(&poll_post);
				}
				zval_ptr_dtor(&post_new);
			}
			zval_ptr_dtor(&post_reply);
		}
		zval_ptr_dtor(&can_moderate_board);
	}

	/* PHP: $request = db_query("
		SELECT MIN(bp.addDeny) AS addDeny
		FROM ({$db_prefix}boards AS b, {$db_prefix}board_permissions AS bp)
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = $ID_MEMBER)
		WHERE b.ID_BOARD IN (" . implode(', ', $boards) . ")" . (isset($max_allowable_mode) ? "
			AND b.permission_mode <= $max_allowable_mode" : '') . "
			AND bp.ID_BOARD = " . (empty($modSettings['permission_enable_by_board']) ? '0' : 'IF(b.permission_mode = 1, b.ID_BOARD, 0)') . "
			AND bp.ID_GROUP IN (" . implode(', ', $user_info['groups']) . ", 3)
			AND bp.permission " . (is_array($permission) ? "IN ('" . implode("', '", $permission) . "')" : " = '$permission'") . "
			AND (mods.ID_MEMBER IS NOT NULL OR bp.ID_GROUP != 3)
		GROUP BY b.ID_BOARD", __FILE__, __LINE__);*/

	SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, _boards, imploded_boards);

	if (Z_TYPE_P(max_allowable_mode) == IS_NULL)
	{
		ALLOC_INIT_ZVAL(SQL_query_perm_mode);
		ZVAL_EMPTY_STRING(SQL_query_perm_mode);
	}
	else
		SMFLIB_CALL_FUNCTION_SL("sprintf", SMFLIB_QUERY_PERM_MODE, sizeof(SMFLIB_QUERY_PERM_MODE) - 1, Z_LVAL_P(max_allowable_mode), SQL_query_perm_mode);
	zval_ptr_dtor(&max_allowable_mode);

	ALLOC_INIT_ZVAL(SQL_query_board);
	if (!SMFLIB_GET_KEY_VAL_ZZ(*modSettings, permission_enable_by_board) || SMFLIB_EMPTY_PP(permission_enable_by_board))
	{
		ZVAL_STRINGL(SQL_query_board, "0", sizeof("0") - 1, 1);
	}
	else
	{
		ZVAL_STRINGL(SQL_query_board, "IF(b.permission_mode = 1, b.ID_BOARD, 0)", sizeof("IF(b.permission_mode = 1, b.ID_BOARD, 0)") - 1, 1);
	}

	SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, *groups, imploded_groups);

	// Compile the SQL query part: "= 'permission'"
	if (Z_TYPE_P(permission) == IS_STRING)
	{
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_PERM_LIST_SINGLE, sizeof(SMFLIB_QUERY_PERM_LIST_SINGLE) - 1, permission, SQL_query_perm_list);
	}
	// Compile the SQL query part: "IN ('permission_1', 'permission_2')"
	else if (Z_TYPE_P(permission) == IS_ARRAY)
	{
		zval *imploded_perms;

		SMFLIB_CALL_FUNCTION_SZ("implode", "', '", sizeof("', '") - 1, permission, imploded_perms);
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_PERM_LIST_MULTIPLE, sizeof(SMFLIB_QUERY_PERM_LIST_MULTIPLE) - 1, imploded_perms, SQL_query_perm_list);
		zval_ptr_dtor(&imploded_perms);
	}
	else
		php_error(E_ERROR, "phplib_allowedTo(): Unexpected datatype or permission parameter");

	// Bring all the components together in one big query.
	SMFLIB_CALL_FUNCTION_SZZZZZZZ("sprintf", SMFLIB_QUERY_PERM, sizeof(SMFLIB_QUERY_PERM) - 1, *db_prefix, *ID_MEMBER, imploded_boards, SQL_query_perm_mode, SQL_query_board, imploded_groups, SQL_query_perm_list, SQL_query);
	zval_ptr_dtor(&imploded_boards);
	zval_ptr_dtor(&imploded_groups);
	zval_ptr_dtor(&SQL_query_perm_mode);
	zval_ptr_dtor(&SQL_query_perm_list);
	zval_ptr_dtor(&SQL_query_board);

	SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
	zval_ptr_dtor(&SQL_query);

	// PHP: if (mysql_num_rows($request) != count($boards))
	SMFLIB_CALL_FUNCTION_Z("mysql_num_rows", request, num_rows);
	if (Z_TYPE_P(num_rows) == IS_LONG && Z_LVAL_P(num_rows) != zend_hash_num_elements(Z_ARRVAL_P(_boards)))
	{
		// PHP: return false;
		SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&request);
		zval_ptr_dtor(&num_rows);
		zval_ptr_dtor(&_boards);
		RETURN_FALSE;
	}
	zval_ptr_dtor(&num_rows);
	zval_ptr_dtor(&_boards);

	// PHP: $result = true;
	result = 1;

	// PHP:while ($row = mysql_fetch_assoc($request))
	SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)
		if (SMFLIB_GET_KEY_VAL_ZZ(row, addDeny))
		{
			convert_to_long(*addDeny);
			result &= Z_LVAL_PP(addDeny) != 0;
		}
		else
			php_error(E_ERROR, "phplib_allowedTo(): Column 'addDeny' not found");
	SMFLIB_MYSQL_FETCH_ASSOC_END(request, row)

	// PHP: mysql_free_result($request);
	SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&request);

	// PHP: return $result;
	if (result == 1)
		RETURN_TRUE;

	RETURN_FALSE;
}

PHP_FUNCTION(smflib_isAllowedTo)
{
	// Input parameters.
	zval *permission, *_permission, *boards = NULL, *_boards;

	// Global variables.
	zval **user_info, **txt, **_GET;

	// Global hash variables.
	zval **error_permission, **is_guest, **txt_cannot_do_this = NULL, **value = NULL;

	// Local variables.
	zval *allowed_to, *retval, *cannot_do_this, *light_permissions;
	int i;

	// isAllowedTo($permission, $boards = null).
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &permission, &boards) == FAILURE)
		RETURN_NULL();

	// Create a copy of $boards to play with.
	ALLOC_INIT_ZVAL(_boards);
	if (boards)
	{
		ZVAL_ZVAL(_boards, boards, 1, 0);
	}
	else
	{
		ZVAL_NULL(_boards);
	}

	// Create a copy of $permission to play with.
	ALLOC_INIT_ZVAL(_permission);
	ZVAL_ZVAL(_permission, permission, 1, 0);


	// This function shouldn't be called with no permissions.
	if (SMFLIB_EMPTY_P(permission))
		php_error(E_ERROR, "smflib_isAllowedTo(): no permissions were passed");

	// Initialize heavy permissions (if they weren't already).
	if (!heavy_permissions)
	{
		ALLOC_INIT_ZVAL(heavy_permissions);
		array_init(heavy_permissions);
		for (i = 0; _heavy_permissions[i]; i++)
			add_next_index_string(heavy_permissions, _heavy_permissions[i], 1);
	}

	// PHP: $permission = is_array($permission) ? $permission : array($permission);
	if (Z_TYPE_P(_permission) != IS_ARRAY)
		convert_to_array(_permission);

	// PHP: if (!allowedTo($permission, $boards))
	SMFLIB_CALL_FUNCTION_ZZ("allowedTo", _permission, _boards, allowed_to);
	if (Z_LVAL_P(allowed_to) == 0)
	{
		// PHP: $error_permission = array_shift($permission);
		zend_hash_internal_pointer_end(Z_ARRVAL_P(_permission));
		zend_hash_get_current_data(Z_ARRVAL_P(_permission), (void **) &error_permission);

		// Merge 'cannot_' and the permission that will be shown in the error.
		SMFLIB_CALL_FUNCTION_SZ("sprintf", "cannot_%s", sizeof("cannot_%s") - 1, *error_permission, cannot_do_this);

		// PHP: if ($user_info['is_guest'])
		if (!SMFLIB_GET_GLOBAL_Z(user_info) || !SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_guest))
			php_error(E_ERROR, "smflib_isAllowedTo(): $user_info['is_guest'] is not set");
		if (Z_LVAL_PP(is_guest) != 0)
		{
			// PHP: loadLanguage('Errors');
			SMFLIB_CALL_FUNCTION_S("loadLanguage", "Errors", sizeof("Errors") - 1, retval);
			zval_ptr_dtor(&retval);

			// PHP: is_not_guest($txt['cannot_' . $error_permission]);
			if (!SMFLIB_GET_GLOBAL_Z(txt) || zend_hash_find(Z_ARRVAL_PP(txt), Z_STRVAL_P(cannot_do_this), Z_STRLEN_P(cannot_do_this) + 1, (void**) &txt_cannot_do_this) == FAILURE)
			{
				// No txt? Still gotta stop this guest!
				SMFLIB_CALL_FUNCTION("is_not_guest", retval);
				zval_ptr_dtor(&retval);
			}
			else
			{
				SMFLIB_CALL_FUNCTION_Z("is_not_guest", *txt_cannot_do_this, retval);
				zval_ptr_dtor(&retval);
			}

			// They couldn't have come to this point.
			php_error(E_ERROR, "smflib_isAllowedTo(): call to is_not_guest() was unsuccessful");
		}

		SMFLIB_GET_GLOBAL_Z(_GET);

		// PHP: $_GET['action'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "action", "");

		// PHP: $_GET['board'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "board", "");

		// PHP: $_GET['topic'] = '';
		SMFLIB_SET_KEY_VAL_ZCC(*_GET, "topic", "");

		// PHP: writeLog(true);
		SMFLIB_CALL_FUNCTION_B("writeLog", 1, retval);
		zval_ptr_dtor(&retval);

		// PHP: fatal_lang_error('cannot_' . $error_permission, true);
		SMFLIB_CALL_FUNCTION_Z("fatal_lang_error", cannot_do_this, retval);
		zval_ptr_dtor(&retval);
		zval_ptr_dtor(&cannot_do_this);

		// PHP: trigger_error('Hacking attempt...', E_USER_ERROR);
		php_error(E_USER_ERROR, "Hacking attempt...");
	}

	// PHP: if (!allowedTo(array_diff($permission, $heavy_permissions), $boards))
	SMFLIB_CALL_FUNCTION_ZZ("array_diff", _permission, heavy_permissions, light_permissions);
	SMFLIB_CALL_FUNCTION_ZZ("allowedTo", light_permissions, _boards, allowed_to);
	if (Z_LVAL_P(allowed_to) == 0)
	{
		// PHP: validateSession();
		SMFLIB_CALL_FUNCTION("validateSession", retval);
		zval_ptr_dtor(&retval);
	}

	zval_ptr_dtor(&_boards);
	zval_ptr_dtor(&_permission);
	zval_ptr_dtor(&allowed_to);
	RETURN_NULL();
}

PHP_FUNCTION(smflib_boardsAllowedTo)
{
	// Input parameters.
	zval *permission;

	// Global variables.
	zval **db_prefix, **ID_MEMBER, **user_info, **modSettings;

	// Global hash variables.
	zval **is_admin, **user_info_groups, **permission_enable_by_board;
	zval **permissions;

	// Local variables.
	zval *groups, *moderator_array, *max_allowable_mode = NULL, *SQL_query;
	zval *request, *query_part_ID_BOARD, *query_part_allowable_mode, *row;
	zval *imploded_groups, *retval, *boards, *deny_boards, *input_arr;

	// boardsAllowedTo($permission).
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &permission) == FAILURE)
		RETURN_NULL();

	if (!SMFLIB_GET_GLOBAL_Z(user_info))
		php_error(E_ERROR, "smflib_boardsAllowedTo(): $user_info not set");

	// PHP: if ($user_info['is_admin'])
	if (SMFLIB_GET_KEY_VAL_ZZ(*user_info, is_admin))
	{
		// PHP: return array(0);
		array_init(return_value);
		SMFLIB_ADD_INDEX_ZL(return_value, 0);
		return;
	}

	if (!SMFLIB_GET_GLOBAL_Z(db_prefix))
		php_error(E_ERROR, "smflib_boardsAllowedTo(): $db_prefix not set");
	if (!SMFLIB_GET_GLOBAL_Z(ID_MEMBER))
		php_error(E_ERROR, "smflib_boardsAllowedTo(): $ID_MEMBER not set");

	// PHP: $groups = array_diff($user_info['groups'], array(3));
	if (!SMFLIB_GET_KEY_VAL_ZCZ(*user_info, "groups", user_info_groups))
		php_error(E_ERROR, "smflib_boardsAllowedTo(): $user_info['groups'] not set");
	ALLOC_INIT_ZVAL(moderator_array);
	array_init(moderator_array);
	SMFLIB_ADD_INDEX_ZL(moderator_array, 3);
	SMFLIB_CALL_FUNCTION_ZZ("array_diff", *user_info_groups, moderator_array, groups);
	zval_ptr_dtor(&moderator_array);

	// Initialize some query parts.
	ALLOC_INIT_ZVAL(query_part_ID_BOARD);

	// PHP: if (empty($modSettings['permission_enable_by_board']) && !in_array('moderate_board', $user_info['permissions']))
	if (!SMFLIB_GET_GLOBAL_Z(modSettings) || !SMFLIB_GET_KEY_VAL_ZZ(*modSettings, permission_enable_by_board) || SMFLIB_EMPTY_PP(permission_enable_by_board))
	{
		zval *in_array_result;

		if (!SMFLIB_GET_KEY_VAL_ZZ(*user_info, permissions))
			php_error(E_ERROR, "smflib_boardsAllowedTo(): $user_info['permissions'] not set");
		SMFLIB_CALL_FUNCTION_SZ("in_array", "moderate_board", sizeof("moderate_board") - 1, *permissions, in_array_result);
		if (Z_LVAL_P(in_array_result))
		{
			zval *needed_level;
			zval **needed_level_permission;

			/* PHP: $needed_level = array(
				'post_reply_own' => 3,
				'post_reply_any' => 3,
				'post_new' => 2,
				'poll_post' => 0,
			);*/
			ALLOC_INIT_ZVAL(needed_level);
			array_init(needed_level);
			SMFLIB_SET_KEY_VAL_ZCL(needed_level, "post_reply_own", 3);
			SMFLIB_SET_KEY_VAL_ZCL(needed_level, "post_reply_any", 3);
			SMFLIB_SET_KEY_VAL_ZCL(needed_level, "post_new", 2);
			SMFLIB_SET_KEY_VAL_ZCL(needed_level, "poll_post", 0);

			// PHP: if (isset($needed_level[$permission]))
			if (SMFLIB_GET_KEY_VAL_ZZZ(needed_level, permission, needed_level_permission))
			{
				// PHP: $max_allowable_mode = $needed_level[$permission];
				ALLOC_INIT_ZVAL(max_allowable_mode);
				ZVAL_LONG(max_allowable_mode, Z_LVAL_PP(needed_level_permission));
			}
			zval_ptr_dtor(&needed_level);
		}
		zval_ptr_dtor(&in_array_result);

		// Some query preparation work.
		ZVAL_STRINGL(query_part_ID_BOARD, SMFLIB_QUERY_BOARDS_ALLOWED_TO_GLOBAL, sizeof (SMFLIB_QUERY_BOARDS_ALLOWED_TO_GLOBAL) - 1, 1);
	}
	else
	{
		// Some query preparation work.
		ZVAL_STRINGL(query_part_ID_BOARD, SMFLIB_QUERY_BOARDS_ALLOWED_TO_BY_BOARD, sizeof (SMFLIB_QUERY_BOARDS_ALLOWED_TO_BY_BOARD) - 1, 1);
	}


	/* PHP: $request = db_query("
		SELECT b.ID_BOARD, b.permission_mode, bp.addDeny
		FROM ({$db_prefix}boards AS b, {$db_prefix}board_permissions AS bp)
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = $ID_MEMBER)
		WHERE bp.ID_BOARD = " . (empty($modSettings['permission_enable_by_board']) ? '0' : 'IF(b.permission_mode = 1, b.ID_BOARD, 0)') . "
			AND bp.ID_GROUP IN (" . implode(', ', $groups) . ", 3)
			AND bp.permission = '$permission'" . (isset($max_allowable_mode) ? "
			AND (mods.ID_MEMBER IS NOT NULL OR b.permission_mode <= $max_allowable_mode)" : '') . "
			AND (mods.ID_MEMBER IS NOT NULL OR bp.ID_GROUP != 3)", __FILE__, __LINE__);*/
	if (max_allowable_mode)
	{
		SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_QUERY_BOARDS_ALLOWED_TO_ALLOWABLE_MODE, sizeof(SMFLIB_QUERY_BOARDS_ALLOWED_TO_ALLOWABLE_MODE) - 1, max_allowable_mode, query_part_allowable_mode);
		zval_ptr_dtor(&max_allowable_mode);
	}
	else
	{
		ALLOC_INIT_ZVAL(query_part_allowable_mode);
		ZVAL_EMPTY_STRING(query_part_allowable_mode);
	}
	SMFLIB_CALL_FUNCTION_SZ("implode", ", ", sizeof(", ") - 1, groups, imploded_groups);
	zval_ptr_dtor(&groups);
	SMFLIB_CALL_FUNCTION_SZZZZZ("sprintf", SMFLIB_QUERY_BOARDS_ALLOWED_TO, sizeof(SMFLIB_QUERY_BOARDS_ALLOWED_TO) - 1, *db_prefix, *ID_MEMBER, query_part_ID_BOARD, imploded_groups, query_part_allowable_mode, SQL_query);
	zval_ptr_dtor(&query_part_ID_BOARD);
	zval_ptr_dtor(&query_part_allowable_mode);
	zval_ptr_dtor(&imploded_groups);
	SMFLIB_CALL_FUNCTION_ZSL("db_query", SQL_query, __FILE__, sizeof(__FILE__) - 1, __LINE__, request);
	zval_ptr_dtor(&SQL_query);

	// PHP: $boards = array();
	ALLOC_INIT_ZVAL(boards);
	array_init(boards);

	// PHP: $deny_boards = array();
	ALLOC_INIT_ZVAL(deny_boards);
	array_init(deny_boards);

	// PHP: while ($row = mysql_fetch_assoc($request))
	SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)
	{
		zval **addDeny, **ID_BOARD;

		// PHP: if (empty($row['addDeny']))
		if (SMFLIB_GET_KEY_VAL_ZZ(row, addDeny) && SMFLIB_EMPTY_PP(addDeny) && SMFLIB_GET_KEY_VAL_ZZ(row, ID_BOARD))
		{
			// PHP: $deny_boards[] = $row['ID_BOARD'];
			SMFLIB_ADD_INDEX_ZZ_CPY(deny_boards, *ID_BOARD);
		}
		// PHP: else
		else
		{
			// PHP: $boards[] = $row['ID_BOARD'];
			SMFLIB_ADD_INDEX_ZZ_CPY(boards, *ID_BOARD);
		}
	}
	SMFLIB_MYSQL_FETCH_ASSOC_END(request, row);

	// PHP: mysql_free_result($request);
	SMFLIB_CALL_FUNCTION_Z("mysql_free_result", request, retval);
	zval_ptr_dtor(&retval);
	zval_ptr_dtor(&request);

	// PHP: $boards = array_values(array_diff($boards, $deny_boards));
	input_arr = boards;
	SMFLIB_CALL_FUNCTION_ZZ("array_diff", input_arr, deny_boards, boards);
	zval_ptr_dtor(&input_arr);
	zval_ptr_dtor(&deny_boards);
	input_arr = boards;
	SMFLIB_CALL_FUNCTION_Z("array_values", input_arr, boards);
	zval_ptr_dtor(&input_arr);

	// PHP: return $boards;
	RETURN_ZVAL(boards, 1, 1);
}

