/****
/**********************************************************************************
* smflib.c                                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3 Public                                    *
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


#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"

#include "smflib_function_calls.h"
#include "php_smflib.h"

// For php_implode.
#include "ext/standard/php_string.h"

// For php_info_print_table_*()
#include "ext/standard/info.h"


/* If you declare any globals in php_smflib.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(smflib)
*/

static function_entry smflib_functions[] = {
	PHP_FE(smflib_init, NULL)
// !!! Remove me
	PHP_FE(smflib_debug, NULL)
	PHP_FE(smflib_is_admin, NULL)
	PHP_FE(smflib_validateSession, NULL)
	PHP_FE(smflib_is_not_guest, NULL)
	PHP_FE(smflib_is_not_banned, NULL)
	PHP_FE(smflib_banPermissions, NULL)
	PHP_FE(smflib_log_ban, NULL)
	PHP_FE(smflib_isBannedEmail, NULL)
	PHP_FE(smflib_checkSession, NULL)
	PHP_FE(smflib_checkSubmitOnce, NULL)
	PHP_FE(smflib_setBit, NULL)
	PHP_FE(smflib_getBit, NULL)
	PHP_FE(smflib_allowedTo, NULL)
	PHP_FE(smflib_isAllowedTo, NULL)
	PHP_FE(smflib_boardsAllowedTo, NULL)
	PHP_FE(smflib_cleanRequest, NULL)
	PHP_FE(smflib_addslashes__recursive, NULL)
	PHP_FE(smflib_htmlspecialchars__recursive, NULL)
	PHP_FE(smflib_urldecode__recursive, NULL)
	PHP_FE(smflib_stripslashes__recursive, NULL)
	PHP_FE(smflib_htmltrim__recursive, NULL)
	PHP_FE(smflib_ob_sessrewrite, NULL)
	PHP_FE(smflib_log_error, NULL)
	PHP_FE(smflib_db_error, NULL)
	PHP_FE(smflib_fatal_error, NULL)
	PHP_FE(smflib_fatal_lang_error, NULL)
	PHP_FE(smflib_error_handler, NULL)
	PHP_FE(smflib_db_fatal_error, NULL)
	PHP_FE(smflib_reloadSettings, NULL)
	{NULL, NULL, NULL}
};

static function_entry smflib_alias_functions[] = {
	PHP_FALIAS(is_admin, smflib_is_admin, NULL)
	PHP_FALIAS(validateSession, smflib_validateSession, NULL)
	PHP_FALIAS(is_not_guest, smflib_is_not_guest, NULL)
	PHP_FALIAS(is_not_banned, smflib_is_not_banned, NULL)
	PHP_FALIAS(banPermissions, smflib_banPermissions, NULL)
	PHP_FALIAS(log_ban, smflib_log_ban, NULL)
	PHP_FALIAS(isBannedEmail, smflib_isBannedEmail, NULL)
	PHP_FALIAS(checkSession, smflib_checkSession, NULL)
	PHP_FALIAS(checkSubmitOnce, smflib_checkSubmitOnce, NULL)
	PHP_FALIAS(setBit, smflib_setBit, NULL)
	PHP_FALIAS(getBit, smflib_getBit, NULL)
	PHP_FALIAS(allowedTo, smflib_allowedTo, NULL)
	PHP_FALIAS(isAllowedTo, smflib_isAllowedTo, NULL)
	PHP_FALIAS(boardsAllowedTo, smflib_boardsAllowedTo, NULL)
	PHP_FALIAS(cleanRequest, smflib_cleanRequest, NULL)
	PHP_FALIAS(addslashes__recursive, smflib_addslashes__recursive, NULL)
	PHP_FALIAS(htmlspecialchars__recursive, smflib_htmlspecialchars__recursive, NULL)
	PHP_FALIAS(urldecode__recursive, smflib_urldecode__recursive, NULL)
	PHP_FALIAS(stripslashes__recursive, smflib_stripslashes__recursive, NULL)
	PHP_FALIAS(htmltrim__recursive, smflib_htmltrim__recursive, NULL)
	PHP_FALIAS(ob_sessrewrite, smflib_ob_sessrewrite, NULL)
	PHP_FALIAS(log_error, smflib_log_error, NULL)
	PHP_FALIAS(db_error, smflib_db_error, NULL)
	PHP_FALIAS(fatal_error, smflib_fatal_error, NULL)
	PHP_FALIAS(fatal_lang_error, smflib_fatal_lang_error, NULL)
	PHP_FALIAS(error_handler, smflib_error_handler, NULL)
	PHP_FALIAS(db_fatal_error, smflib_db_fatal_error, NULL)
	PHP_FALIAS(reloadSettings, smflib_reloadSettings, NULL)
	{NULL, NULL, NULL}
};

zend_module_entry smflib_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	PHP_SMFLIB_EXTNAME,
	smflib_functions,
	PHP_MINIT(smflib),
	PHP_MSHUTDOWN(smflib),
	NULL,
	NULL,
	ZEND_MINFO(smflib),
#if ZEND_MODULE_API_NO >= 20010901
	PHP_SMFLIB_VERSION,
#endif
	STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_SMFLIB
ZEND_GET_MODULE(smflib)
#endif

// !!! This would be very useful to have for support!
/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("smflib.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_smflib_globals, smflib_globals)
    STD_PHP_INI_ENTRY("smflib.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_smflib_globals, smflib_globals)
PHP_INI_END()
*/
/* }}} */

/* {{{ php_smflib_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_smflib_init_globals(zend_smflib_globals *smflib_globals)
{
	smflib_globals->global_value = 0;
	smflib_globals->global_string = NULL;
}
*/
/* }}} */

/*
// Keep on dreaming ;)
class smf_zval
{
	public:
		smf_zval()
		{
			ALLOC_INIT_ZVAL(&content);
		}
		smf_zval(const char* str)
		{
			ALLOC_INIT_ZVAL(&content);
			ZVAL_STRINGL(&content, str, sizeof(str) - 1, 1);
		}
		smf_zval(int i)
		{
			ALLOC_INIT_ZVAL(&content);
			ZVAL_LONG(&content, i);
		}
		smf_zval(double d)
		{
			ALLOC_INIT_ZVAL(&content);
			ZVAL_DOUBLE(&content, i);
		}
		smf_zval(zval *z)
		{
			content = *z;
			copy_ctor(content);
		}
		~smf_zval()
		{
			if (copy)
				zval_ptr_dtor(&copy);
		}
		operator char*
		{
			if (Z_TYPE(content) == IS_STRING)
				return Z_STRVAL(content);
			else
			{
				if (copy)
					zval_ptr_dtor(&copy);
				copy = &content;
				copy_ptr_ctor(copy);
				convert_to_string(copy);
				return Z_STRVAL_P(copy);
			}
		}
		bool operator==(int i)
		{
			switch (Z_TYPE(content))
			{
				case IS_LONG:
					return i == Z_LVAL(content);
				case IS_BOOL:
					return i == 0 ? Z_LVAL(content) == 0 : Z_LVAL(content) != 0;
				case IS_DOUBLE:
					return (double) i - Z_DVAL(content) == 0;
				case IS_STRING:
					if (copy)
						zval_ptr_dtor(&copy);
					copy = &content;
					copy_ptr_ctor(copy);
					convert_to_long(copy);
					return Z_LVAL_P(copy) == i;
				case IS_NULL:
					return i == 0;
				default:
					return 0;
			}
		}
		void operator+=(int i)
		{
			if (Z_TYPE(content) == IS_LONG)
				Z_LVAL(content) += i;
			else if (Z_TYPE(content) == IS_DOUBLE)
				Z_DVAL(content) += (double) i;
			else if (Z_TYPE(content) == IS_STRING)
			{
				convert_to_long(content);
				Z_LVAL(content) += i;
			}
			else if (Z_TYPE(content) == IS_NULL)
				ZVAL_LONG(content, i);
		}
		void sprintf(const char *format, ...)
		{
		}
		zval content;
	private:
		zval *copy = NULL;
};
*/

// Loosely based on zend_include_or_eval_handler()
int smflib_require_once(char *file, int len TSRMLS_DC)
{
	int dummy = 1;
	zend_file_handle file_handle;
	zend_op_array *new_op_array = NULL;
	zval **original_return_value = EG(return_value_ptr_ptr);

	if (SUCCESS == zend_stream_open(file, &file_handle TSRMLS_CC))
	{
		if (!file_handle.opened_path)
			file_handle.opened_path = estrndup(file, len);

		if (zend_hash_add(&EG(included_files), file_handle.opened_path, strlen(file_handle.opened_path) + 1, (void *)&dummy, sizeof(int), NULL) == SUCCESS)
		{
			new_op_array = zend_compile_file(&file_handle, ZEND_REQUIRE TSRMLS_CC);
			zend_destroy_file_handle(&file_handle TSRMLS_CC);
		}
		else
		{
			// Only require the file once.
			zend_file_handle_dtor(&file_handle);
			return 1;
		}
	}
	else
	{
		zend_message_dispatcher(ZMSG_FAILED_REQUIRE_FOPEN, file);
		return 0;
	}

	if (new_op_array)
	{
		zend_op_array *cur_op_array = EG(active_op_array);
		zend_op **cur_op_line = EG(opline_ptr);

		EG(active_op_array) = new_op_array;

		zend_execute(new_op_array TSRMLS_CC);

		if (*EG(return_value_ptr_ptr))
			zval_ptr_dtor(EG(return_value_ptr_ptr));

		EG(opline_ptr) = cur_op_line;
		EG(active_op_array) = cur_op_array;
		destroy_op_array(new_op_array TSRMLS_CC);
		efree(new_op_array);
		if (EG(exception))
			php_error(E_ERROR, "something 'exceptional' happened when including a file :P");
	}
	EG(return_value_ptr_ptr) = original_return_value;

	return 1;
}


// CALLER MUST FREE/DTOR: return value (may be NULL), string, and file.
// !!! Change this to replace/do everything for db_query() later.
inline zval *db_query(char *string, int string_len, char *file, int file_len, int line TSRMLS_DC)
{
	zval **args[1];
	zval *retval = NULL, *query;
	zval *func_name;
	int call_result;

	ALLOC_INIT_ZVAL(func_name);
	ZVAL_STRINGL(func_name, "mysql_query", 11, 1);

	ALLOC_INIT_ZVAL(query);
	ZVAL_STRINGL(query, string, string_len, 0);
	args[0] = &query;

	call_result = call_user_function_ex(CG(function_table), NULL, func_name, &retval, 1, args, 0, NULL TSRMLS_CC);

	zval_ptr_dtor(&func_name);
	zval_ptr_dtor(&query);

	// !!! This should never happen unless PHP doesn't have MySQL support.
	if (call_result != SUCCESS)
		php_error(E_ERROR, "db_query(): Unable to call mysql_query()");

	return retval;
}

PHP_FUNCTION(smflib_init)
{
	// Input parameters.
	zval *disabled_functions = NULL;

	// Local variables.
	zval *input_str;
	zval **forum_version;
	zend_module_entry *module;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|z", &disabled_functions) == FAILURE)
		RETURN_NULL();

	// Apparently the version is not set. Return false.
	if (!SMFLIB_GET_GLOBAL_Z(forum_version) || Z_TYPE_PP(forum_version) != IS_STRING)
		RETURN_FALSE;

	// Versions _have_ to match exactly.
	if (zend_binary_strcmp(Z_STRVAL_PP(forum_version), Z_STRLEN_PP(forum_version), PHP_SMFLIB_SMF_VERSION, sizeof(PHP_SMFLIB_SMF_VERSION) - 1) != 0)
		RETURN_FALSE;

	// PHP: $forum_version = sprintf("%1$s(a)", $forum_version);
	input_str = *forum_version;
	SMFLIB_CALL_FUNCTION_SZ("sprintf", SMFLIB_FORMAT_VERSION, sizeof(SMFLIB_FORMAT_VERSION) - 1, input_str, *forum_version);
	zval_ptr_dtor(&input_str);

	// Create aliases for the functions used (smflib_allowedTo -> allowedTo).
	if (zend_register_functions(NULL, smflib_alias_functions, NULL, 0 TSRMLS_CC) == FAILURE)
		php_error(E_ERROR, "smflib_init(): Unable to register SMF's library functions");

	// Register the function that will clean up the mess afterwards.
	if (zend_hash_find(&module_registry, "smflib", 7, (void **) &module) == FAILURE)
		php_error(E_ERROR, "smflib_init(): Unable to find SMF's library module");

	module->request_shutdown_func = ZEND_RSHUTDOWN(smflib);

	if (disabled_functions && Z_TYPE_P(disabled_functions) == IS_ARRAY)
	{
		zval **function;
		HashTable *target_function_table = CG(function_table);
		HashPosition pos;
		char *lowercase_name = NULL;

		zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(disabled_functions), &pos);
		while (zend_hash_get_current_data_ex(Z_ARRVAL_P(disabled_functions), (void**) &function, &pos) == SUCCESS)
		{
			lowercase_name = estrndup(Z_STRVAL_PP(function), Z_STRLEN_PP(function));
			zend_str_tolower(lowercase_name, Z_STRLEN_PP(function));
			if (zend_hash_del(target_function_table, lowercase_name, Z_STRLEN_PP(function) + 1) == FAILURE)
				php_error(E_ERROR, "smflib_shutdown(): Unable to unregister function '%s'", lowercase_name);
			efree(lowercase_name);
			zend_hash_move_forward_ex(Z_ARRVAL_P(disabled_functions), &pos);
		}
	}

	// Everything went well, smflib has a go!
	RETURN_TRUE;
}

PHP_MINIT_FUNCTION(smflib)
{

	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(smflib)
{

	return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(smflib)
{
	zend_function_entry *alias_functions = smflib_alias_functions;
	HashTable *target_function_table = CG(function_table);
	char *lowercase_name = NULL;
	int namelen;

	// Unregister the aliases created by smf_init().
	while (alias_functions->fname)
	{
		// Create a lower case copy, since all functions are stored that way.
		namelen = strlen(alias_functions->fname);
		lowercase_name = estrndup(alias_functions->fname, namelen);
		zend_str_tolower(lowercase_name, namelen);
		zend_hash_del(target_function_table, lowercase_name, namelen + 1);
		//if (zend_hash_del(target_function_table, lowercase_name, namelen + 1) == FAILURE)
		//	php_error(E_ERROR, "smflib_shutdown(): Unable to unregister function '%s'", alias_functions->fname);
		alias_functions++;
		efree(lowercase_name);
	}

	if (heavy_permissions)
		zval_ptr_dtor(&heavy_permissions);

	// As a proper shutdown function should do...
	return SUCCESS;
}

PHP_MINFO_FUNCTION(smflib)
{
	php_info_print_table_start();
	php_info_print_table_row(2, "SMF library", "enabled");
	php_info_print_table_row(2, "SMF library version", PHP_SMFLIB_VERSION);
	php_info_print_table_row(2, "SMF version supported", PHP_SMFLIB_SMF_VERSION);
	php_info_print_table_end();
	//DISPLAY_INI_ENTRIES();
}

// !!! Remove me.
PHP_FUNCTION(smflib_debug)
{
	zval **modSettings, **test;

	SMFLIB_GET_GLOBAL_Z(modSettings);
	SMFLIB_GET_KEY_VAL_ZZ(*modSettings, test);
	convert_to_long_ex(test);
	//RETURN_ZVAL(*test, 1, 0);
}
