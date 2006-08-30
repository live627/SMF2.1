#ifndef PHP_SMFLIB_H
#define PHP_SMFLIB_H 1

#define PHP_SMFLIB_VERSION "SMF 2.0 Alpha(a) r1"
#define PHP_SMFLIB_SMF_VERSION "SMF 2.0 Alpha"

#define PHP_SMFLIB_EXTNAME "smflib"

extern zend_module_entry smflib_module_entry;
#define phpext_smflib_ptr &smflib_module_entry

#ifdef PHP_WIN32
#define PHP_SMFLIB_API __declspec(dllexport)
#else
#define PHP_SMFLIB_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

// PHP_MINFO_FUNCTION(smflib);

PHP_FUNCTION(smflib_init);
// !!! Remove me
PHP_FUNCTION(smflib_debug);
PHP_MINIT_FUNCTION(smflib);
PHP_MSHUTDOWN_FUNCTION(smflib);
PHP_RSHUTDOWN_FUNCTION(smflib);
PHP_MINFO_FUNCTION(smflib);


// Security.php
PHP_FUNCTION(smflib_is_admin);
PHP_FUNCTION(smflib_validateSession);
PHP_FUNCTION(smflib_is_not_guest);
PHP_FUNCTION(smflib_is_not_banned);
PHP_FUNCTION(smflib_banPermissions);
PHP_FUNCTION(smflib_log_ban);
PHP_FUNCTION(smflib_isBannedEmail);
PHP_FUNCTION(smflib_checkSession);
PHP_FUNCTION(smflib_checkSubmitOnce);
PHP_FUNCTION(smflib_setBit);
PHP_FUNCTION(smflib_getBit);
PHP_FUNCTION(smflib_allowedTo);
PHP_FUNCTION(smflib_isAllowedTo);
PHP_FUNCTION(smflib_boardsAllowedTo);

// QueryString.php
PHP_FUNCTION(smflib_cleanRequest);
PHP_FUNCTION(smflib_addslashes__recursive);
PHP_FUNCTION(smflib_htmlspecialchars__recursive);
PHP_FUNCTION(smflib_urldecode__recursive);
PHP_FUNCTION(smflib_stripslashes__recursive);
PHP_FUNCTION(smflib_htmltrim__recursive);
PHP_FUNCTION(smflib_ob_sessrewrite);

// Errors.php
PHP_FUNCTION(smflib_log_error);
PHP_FUNCTION(smflib_db_error);
PHP_FUNCTION(smflib_fatal_error);
PHP_FUNCTION(smflib_fatal_lang_error);
PHP_FUNCTION(smflib_error_handler);
PHP_FUNCTION(smflib_db_fatal_error);

// Load.php
PHP_FUNCTION(smflib_reloadSettings);

inline zval *db_query(char *string, int string_len, char *file, int file_len, int line TSRMLS_DC);
int smflib_in_array(zval*, HashTable* TSRMLS_DC);
int smflib_require_once(char *file, int len TSRMLS_DC);

// ** get global variables.

// $z_ret = &GLOBALS['z_ret'];
#define SMFLIB_GET_GLOBAL_Z(z_ret) (zend_hash_find(&EG(symbol_table), #z_ret, sizeof(#z_ret), (void**) &z_ret) == SUCCESS)

// $z_ret = &GLOBALS['c_key'];
#define SMFLIB_GET_GLOBAL_CZ(c_key, z_ret) (zend_hash_find(&EG(symbol_table), c_key, sizeof(c_key), (void**) &z_ret) == SUCCESS)


// ** get hash value.

// $z_ret = &$z_var['z_ret'];
#define SMFLIB_GET_KEY_VAL_ZZ(z_var, z_ret) (zend_hash_find(Z_ARRVAL_P(z_var), #z_ret, sizeof(#z_ret), (void**) &z_ret) == SUCCESS)

// $z_ret = &$z_var['c_key'];
#define SMFLIB_GET_KEY_VAL_ZCZ(z_var, c_key, z_ret) (zend_hash_find(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void**) &z_ret) == SUCCESS)

// $z_ret = &$zvar[l_index]
#define SMFLIB_GET_KEY_VAL_ZLZ(z_var, l_index, z_ret) (zend_hash_index_find(Z_ARRVAL_P(z_var), l_index, (void **) &z_ret) == SUCCESS)

// $z_ret = &$z_var[$z_key];
#define SMFLIB_GET_KEY_VAL_ZZZ(z_var, z_key, z_ret) (zend_hash_find(Z_ARRVAL_P(z_var), Z_STRVAL_P(z_key), Z_STRLEN_P(z_key) + 1, (void**) &z_ret) == SUCCESS)


// ** set global hash variables.

// $GLOBALS['c_key'] = 'c_val';
#define SMFLIB_SET_GLOBAL_CC(c_key, c_val)									\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, c_val, sizeof(c_val) - 1, 1);							\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $GLOBALS['c_key'] = l_val;
#define SMFLIB_SET_GLOBAL_CL(c_key, l_val)									\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_LONG(tmp, l_val);													\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $GLOBALS['c_key'] = $s_val;
#define SMFLIB_SET_GLOBAL_CS(c_key, s_val, len_val)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, s_val, len_val, 1);										\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $GLOBALS['c_key'] = &z_val;
#define SMFLIB_SET_GLOBAL_CZ(c_key, z_val) zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &z_val, sizeof(zval *), NULL)

// $GLOBALS['c_key'] = z_val;
#define SMFLIB_SET_GLOBAL_CZ_CPY(c_key, z_val)								\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $GLOBALS['c_key'] = b_val;
// $z_ret = &$GLOBALS['c_key'];
#define SMFLIB_SET_GLOBAL_CBZ(c_key, b_val, z_ret)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_BOOL(tmp, b_val);													\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $GLOBALS['c_key'] = l_val;
// $z_ret = &$GLOBALS['c_key'];
#define SMFLIB_SET_GLOBAL_CLZ(c_key, l_val, z_ret)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_LONG(tmp, l_val);													\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $GLOBALS['c_key'] = &$zval;
// z_ret = &$GLOBALS['c_key'];
#define SMFLIB_SET_GLOBAL_CZZ(c_key, z_val, z_ret) zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &z_val, sizeof(zval *), (void **) &z_ret)

// $GLOBALS['c_key'] = $zval;
// z_ret = &$GLOBALS['c_key'];
#define SMFLIB_SET_GLOBAL_CZZ_CPY(c_key, z_val, z_ret)						\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_update(&EG(symbol_table), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// *** Set hash values.

// $z_var['c_key'] = 'c_val';
#define SMFLIB_SET_KEY_VAL_ZCC(z_var, c_key, c_val)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, c_val, sizeof(c_val) - 1, 1);							\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var['c_key'] = b_val;
#define SMFLIB_SET_KEY_VAL_ZCB(z_var, c_key, b_val)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_BOOL(tmp, b_val);													\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var['c_key'] = l_val;
#define SMFLIB_SET_KEY_VAL_ZCL(z_var, c_key, l_val)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_LONG(tmp, l_val);													\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var['c_key'] = $s_val;
#define SMFLIB_SET_KEY_VAL_ZCS(z_var, c_key, s_val, len_val)				\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, s_val, len_val, 1);									\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var['c_key'] = &$z_val;
#define SMFLIB_SET_KEY_VAL_ZCZ(z_var, c_key, z_val) zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &z_val, sizeof(zval *), NULL)


// $z_var['c_key'] = $z_val;
#define SMFLIB_SET_KEY_VAL_ZCZ_CPY(z_var, c_key, z_val)						\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var[l_index] = $z_val;
#define SMFLIB_SET_KEY_VAL_ZLZ(z_var, l_index, z_val) zend_hash_index_update(Z_ARRVAL_P(z_var), l_index, (void *) &z_val, sizeof(zval *), NULL)

// $z_var[$s_key] = $z_val;
#define SMFLIB_SET_KEY_VAL_ZSZ(z_var, s_key, len_key, z_val) zend_hash_update(Z_ARRVAL_P(z_var), s_key, len_key + 1, (void *) &z_val, sizeof(zval *), NULL)

// $z_var['c_key'] = $z_val;
#define SMFLIB_SET_KEY_VAL_ZZZ_CPY(z_var, z_key, z_val)						\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_update(Z_ARRVAL_P(z_var), Z_STRVAL_P(z_key), Z_STRLEN_P(z_key) + 1, (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var['c_key'] = 'c_val';
// $z_ret = &$z_var['c_key'];
#define SMFLIB_SET_KEY_VAL_ZCCZ(z_var, c_key, c_val, z_ret)		\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, c_val, sizeof(c_val) - 1, 1);							\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $z_var['c_key'] = l_val;
// $z_ret = &$z_var['c_key'];
#define SMFLIB_SET_KEY_VAL_ZCLZ(z_var, c_key, l_val, z_ret)					\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_LONG(tmp, l_val);													\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $z_var['c_key'] = $s_val;
// $z_ret = &$z_var['c_key'];
#define SMFLIB_SET_KEY_VAL_ZCSZ(z_var, c_key, s_val, len_val, z_ret)		\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, s_val, len_val, 1);									\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $z_var['c_key'] = &$z_val;
// $z_ret = &$z_var['c_key'];
#define SMFLIB_SET_KEY_VAL_ZCZZ(z_var, c_key, z_val, z_ret) zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &z_val, sizeof(zval *), (void **) &z_ret)

// $z_var['c_key'] = $z_val;
// $z_ret = &$z_var['c_key'];
#define SMFLIB_SET_KEY_VAL_ZCZZ_CPY(z_var, c_key, z_val, z_ret)				\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_update(Z_ARRVAL_P(z_var), c_key, sizeof(c_key), (void *) &tmp, sizeof(zval *), (void **) &z_ret);	\
}

// $zvar[l_index] = 'c_val';
// $z_ret = &$zvar[l_index];
#define SMFLIB_SET_KEY_VAL_ZLCZ(z_var, l_index, c_val, z_ret)				\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, c_val, sizeof(c_val) - 1, 1);							\
	zend_hash_index_update(Z_ARRVAL_P(z_var), l_index, (void *) &tmp, sizeof(zval *),  (void **) &z_ret);	\
}

// $z_var[$z_key] = &$z_val;
// $z_ret = &$z_var[$z_key];
#define SMFLIB_SET_KEY_VAL_ZZZZ(z_var, z_key, z_val, z_ret) zend_hash_update(Z_ARRVAL_P(z_var), Z_STRVAL_P(z_key), Z_STRLEN_P(z_key) + 1, (void *) &z_val, sizeof(zval *), (void **) &z_ret)


// ** Add un-numbered indexes to a hash-array.

// $z_var[] = &$z_val;
#define SMFLIB_ADD_INDEX_ZZ(z_var, z_val) zend_hash_next_index_insert(Z_ARRVAL_P(z_var), (void *) &z_val, sizeof(zval *), NULL)

// $z_var[] = $z_val;
#define SMFLIB_ADD_INDEX_ZZ_CPY(z_var, z_val)								\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_ZVAL(tmp, z_val, 1, 0);											\
	zend_hash_next_index_insert(Z_ARRVAL_P(z_var), (void *) &tmp, sizeof(zval *), NULL);	\
}

// $z_var[] = 'c_val';
#define SMFLIB_ADD_INDEX_ZC(z_var, c_val)									\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, c_val, sizeof(c_val) - 1, 1);							\
	zend_hash_next_index_insert(Z_ARRVAL_P(z_var), &tmp, sizeof(zval *), NULL);	\
}

// $z_var[] = l_val;
#define SMFLIB_ADD_INDEX_ZL(z_var, l_val)									\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_LONG(tmp, l_val);													\
	zend_hash_next_index_insert(Z_ARRVAL_P(z_var), &tmp, sizeof(zval *), NULL);	\
}

// $z_var[] = $s_val;
#define SMFLIB_ADD_INDEX_ZS(z_var, s_val, len_val)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	ZVAL_STRINGL(tmp, s_val, len_val, 1);									\
	zend_hash_next_index_insert(Z_ARRVAL_P(z_var), &tmp, sizeof(zval *), NULL);	\
}


// unset($GLOBALS['c_key']);
#define SMFLIB_UNSET_GLOBAL_C(c_key) zend_hash_del(&EG(symbol_table), c_key, sizeof(c_key));

#define SMFLIB_UNSET_KEY_VAL_ZC(z_var, c_key) zend_hash_del(Z_ARRVAL_P(z_var), c_key, sizeof(c_key));

// $z_var = array();
// Only use if $z_var already is an initialized array.
#define SMFLIB_EMPTY_ARR_Z(z_var) zend_hash_clean(Z_ARRVAL_P(z_var))

// $z_var['c_val'] = array();
#define SMFLIB_ARR_INIT_ZC(z_var, c_key)									\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	array_init(tmp);														\
	SMFLIB_SET_KEY_VAL_ZCZ(z_var, c_key, tmp);								\
}

// $z_var['c_val'] = array();
// $z_ret = &$z_var['c_val'];
#define SMFLIB_ARR_INIT_ZCZ(z_var, c_key, z_ret)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	array_init(tmp);														\
	SMFLIB_SET_KEY_VAL_ZCZZ(z_var, c_key, tmp, z_ret);						\
}

// $z_var[$z_key] = array();
// $z_ret = &$z_var[$z_key];
#define SMFLIB_ARR_INIT_ZZZ(z_var, z_key, z_ret)							\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	array_init(tmp);														\
	SMFLIB_SET_KEY_VAL_ZZZZ(z_var, z_key, tmp, z_ret);						\
}

// $GLOBALS['z_ret'] = array();
// $z_ret = &$GLOBALS['z_ret'];
#define SMFLIB_ARR_INIT_GLOBAL_Z(z_ret)										\
{																			\
	zval *tmp;																\
	MAKE_STD_ZVAL(tmp);														\
	array_init(tmp);														\
	SMFLIB_SET_GLOBAL_CZZ(#z_ret, tmp, z_ret);								\
}


// count($z_var)
#define SMFLIB_COUNT_Z(z_var) (*z_var).value.ht->nNumOfElements


// *** String comparision.

// Based on DVAL_TO_LVAL from Zend/zend_operators.c
#define SMFLIB_DVAL_TO_LVAL(z_val) (Z_DVAL_P(z_val) > LONG_MAX ? (long) LONG_MAX : (long) Z_DVAL_P(z_val))
//#define SMFLIB_DVAL_TO_LVAL(z_val) (long) Z_DVAL_P(z_val)

// Looks like a pretty crappy conversion, but I guess this is how PHP likes it.
#define SMFLIB_ARR_TO_LVAL(z_val) (zend_hash_num_elements(Z_ARRVAL_P(z_val)) ? 1 : 0)

#define SMFLIB_STR_TO_LVAL(z_val) strtol(Z_STRVAL_P(z_val), NULL, 10)

#define SMFLIB_Z_TO_L(z_val) (Z_TYPE_P(z_val) == IS_NULL ? 0 : (Z_TYPE_P(z_val) == IS_BOOL || Z_TYPE_P(z_val) == IS_LONG || Z_TYPE_P(z_val) == IS_RESOURCE ? Z_LVAL_P(z_val) : (Z_TYPE_P(z_val) == IS_DOUBLE ? SMFLIB_DVAL_TO_LVAL(z_val) :(Z_TYPE_P(z_val) == IS_STRING ? SMFLIB_STR_TO_LVAL(z_val) : (Z_TYPE_P(z_val) == IS_ARRAY ? SMFLIB_ARR_TO_LVAL(z_val) : 0)))))

// $s_val == 'c_val';
#define SMFLIB_CMP_EQ_SC(s_val, len_val, c_val) !zend_binary_strcmp(s_val, len_val, c_val, sizeof(c_val) - 1)

// $s_val != 'c_val';
#define SMFLIB_CMP_NOT_EQ_SC(s_val, len_val, c_val) zend_binary_strcmp(s_val, len_val, c_val, sizeof(c_val) - 1)

// $s1 == $s2;
#define SMFLIB_CMP_EQ_SS(s1, len1, s2, len2) !zend_binary_strcmp(s1, len1, s2, len2)

// $s1 != $s2;
#define SMFLIB_CMP_NOT_EQ_SS(s1, len1, s2, len2) zend_binary_strcmp(s1, len1, s2, len2)

// $s_zval == $z_val;
#define SMFLIB_CMP_EQ_SZ(s_val, len_val, z_val) !zend_binary_strcmp(s_val, len_val, Z_STRVAL_P(z_val), Z_STRLEN_P(z_val))

// $s_zval != $z_val;
#define SMFLIB_CMP_NOT_EQ_SZ(s_val, len_val, z_val) zend_binary_strcmp(s_val, len_val, Z_STRVAL_P(z_val), Z_STRLEN_P(z_val))

// $z_val == 'c_val';
#define SMFLIB_CMP_EQ_ZC(z_val, c_val) !zend_binary_strcmp(Z_STRVAL_P(z_val), Z_STRLEN_P(z_val), c_val, sizeof(c_val) - 1)

// $z_val != 'c_val';
#define SMFLIB_CMP_NOT_EQ_ZC(z_val, c_val) zend_binary_strcmp(Z_STRVAL_P(z_val), Z_STRLEN_P(z_val), c_val, sizeof(c_val) - 1)

// $z1 == $z2
#define SMFLIB_CMP_EQ_ZZ(z1, z2) !zend_binary_strcmp(Z_STRVAL_P(z1), Z_STRLEN_P(z1), Z_STRVAL_P(z2), Z_STRLEN_P(z2))

// $z1 != $z2
#define SMFLIB_CMP_NOT_EQ_ZZ(z1, z2) zend_binary_strcmp(Z_STRVAL_P(z1), Z_STRLEN_P(z1), Z_STRVAL_P(z2), Z_STRLEN_P(z2))

// empty($zp);
#define SMFLIB_EMPTY_P(z_val) (z_val == NULL || SMFLIB_Z_TO_L(z_val) == 0)

// empty($zpp);
#define SMFLIB_EMPTY_PP(z_val) (z_val == NULL || *z_val == NULL || SMFLIB_Z_TO_L(*z_val) == 0)

// is_numeric($z_val)
#define SMFLIB_IS_NUMERIC(z_val) (Z_TYPE_P(z_val) == IS_LONG || Z_TYPE_P(z_val) == IS_DOUBLE || (Z_TYPE_P(z_val) == IS_STRING && is_numeric_string(Z_STRVAL_P(z_val), Z_STRLEN_P(z_val), NULL, NULL, 0)))


// *** strpos().

// Returns the _absolute_ offset or NULL if not found.
// Z: zval; S: string with length; C: constant string.
#define SMFLIB_STRPOS_SC(s_haystack, len_haystack, c_needle) php_memnstr(s_haystack, c_needle, sizeof(c_needle) - 1, s_haystack + len_haystack)
#define SMFLIB_STRPOS_ZC(z_haystack, c_needle) php_memnstr(Z_STRVAL_P(z_haystack), c_needle, sizeof(c_needle) - 1, Z_STRVAL_P(z_haystack) + Z_STRLEN_P(z_haystack))
#define SMFLIB_STRPOS_ZZ(z_haystack, z_needle) php_memnstr(Z_STRVAL_P(z_haystack), Z_STRVAL_P(z_needle), Z_STRLEN_P(z_needle), Z_STRVAL_P(z_haystack) + Z_STRLEN_P(z_haystack))

// Returns the relative offset like PHP's strpos would (only use if you know the needle is present!)
#define SMFLIB_REL_STRPOS_SC(s_haystack, len_haystack, c_needle) (php_memnstr(s_haystack, c_needle, sizeof(c_needle) - 1, s_haystack + len_haystack) - s_haystack)
#define SMFLIB_REL_STRPOS_ZC(z_haystack, c_needle) (php_memnstr(Z_STRVAL_P(z_haystack), c_needle, sizeof(c_needle) - 1, Z_STRVAL_P(z_haystack) + Z_STRLEN_P(z_haystack)) - Z_STRVAL_P(z_haystack))
#define SMFLIB_REL_STRPOS_ZZ(z_haystack, z_needle) (php_memnstr(Z_STRVAL_P(z_haystack), Z_STRVAL_P(z_needle), Z_STRLEN_P(z_needle), Z_STRVAL_P(z_haystack) + Z_STRLEN_P(z_haystack)) - Z_STRVAL_P(z_haystack))


//	if (function_exists($z_func))
//	{
//		$z_ret = &'z_func';
//		return true;
//	}
//	return false;
#define SMFLIB_FUNCTION_EXISTS_ZZ(z_func, z_ret) zend_hash_find(CG(function_table), Z_STRVAL_P(z_func), Z_STRLEN_P(z_func) + 1, (void**) &z_ret) == SUCCESS


// while ($row = mysql_fetch_assoc($request))
#define SMFLIB_MYSQL_FETCH_ASSOC_BEGIN(request, row)						\
while (1)																	\
{																			\
	SMFLIB_CALL_FUNCTION_Z("mysql_fetch_assoc", request, row);			\
	if (Z_TYPE_P(row) == IS_NULL || (Z_TYPE_P(row) == IS_BOOL && Z_LVAL_P(row) == 0))	\
	{																		\
		zval_ptr_dtor(&row);												\
		break;																\
	}

#define SMFLIB_MYSQL_FETCH_ASSOC_END(request, row)							\
	zval_ptr_dtor(&row);													\
}

// while ($row = mysql_fetch_row($request))
#define SMFLIB_MYSQL_FETCH_ROW_BEGIN(request, row)							\
while (1)																	\
{																			\
	SMFLIB_CALL_FUNCTION_Z("mysql_fetch_row", request, row);				\
	if (Z_TYPE_P(row) == IS_NULL || (Z_TYPE_P(row) == IS_BOOL && Z_LVAL_P(row) == 0))	\
	{																		\
		zval_ptr_dtor(&row);												\
		break;																\
	}

#define SMFLIB_MYSQL_FETCH_ROW_END(request, row)							\
	zval_ptr_dtor(&row);													\
}


// foreach ($z_arr as $k => $v)
// {
//   $z_val = &$z_arr[$k];
#define SMFLIB_FOREACH_BEGIN_ZZ(z_arr, z_val)								\
{																			\
	HashPosition pos;														\
	zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(z_arr), &pos);			\
	while (zend_hash_get_current_data_ex(Z_ARRVAL_P(z_arr), (void**) &z_val, &pos) == SUCCESS)	\
	{

// }
#define SMFLIB_FOREACH_END_ZZ(z_arr, z_val)									\
		zend_hash_move_forward_ex(Z_ARRVAL_P(z_arr), &pos);					\
	}																		\
}



// !!! Debugging
#define PR(z)																\
				SMFLIB_CALL_FUNCTION_Z("print_r", z, retval);		\
				zval_ptr_dtor(&retval)

/* Contrary to PHP, quotes in here don't escape dollar-signs($), but do need
 to escape all slashes!*/
#define SMFLIB_PREG_BAD_IP "~^((0|10|172\\.16|192\\.168|255|127\\.0)\\.|unknown)~"
#define SMFLIB_PREG_REGULAR_IP "/^(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})$/"
#define SMFLIB_PREG_URL_ROOT "~^([^/]+//[^/]+)~"
#define SMFLIB_PREG_SEARCH_SESSIONLESS_URL "/\"%1$s(?!\\?%2$s)(\\?)?/"
#define SMFLIB_PREG_REPLACE_SESSIONLESS_URL "\"%1$s?%2$s;"
#define SMFLIB_PREG_SEARCH_DEBUG_URL "/\"%1$s(\\?)?/"
#define SMFLIB_PREG_REPLACE_DEBUG_URL "\"%1$s?debug;"
#define SMFLIB_PREG_SEARCH_QUERYLESS_URL_SESS "/\"%1$s\\?(?:%2$s;)((?:board|topic)=[^#\"]+?)(#[^\"]*?)?\"/e"
#define SMFLIB_PREG_REPLACE_QUERYLESS_URL_SESS "'\"' . $scripturl . '/' . strtr('$1', '&;=', '//,') . '.html?' . SID . '$2\"'"
#define SMFLIB_PREG_SEARCH_QUERYLESS_URL "/\"%1$s\\?((?:board|topic)=[^#\"]+?)(#[^\"]*?)?\"/e"
#define SMFLIB_PREG_REPLACE_QUERYLESS_URL "'\"' . $scripturl . '/' . strtr('$1', '&;=', '//,') . '.html$2\"'"
#define SMFLIB_PREG_GET_TABLE_LIST "~(?:[\\n\\r]|^)[^\\']+?(?:FROM|JOIN|UPDATE|TABLE) ((?:[^\\n\\r(]+?(?:, )?)*)~s"
#define SMFLIB_PREG_CANT_OPEN_FILE "/^Can't open file:\\s*[']?([^\\.]+?)\\./"
#define SMFLIB_PREG_CANT_OPEN_KEY_FILE "/^Incorrect key file for table:\\s*[']?([^']+?)'\\./"
#define SMFLIB_PREG_CHECK_SESSION_VALID_IP "~(?:[^\\.]+\\.)?([^\\.]{3,}\\..+)\\z~i"
#define SMFLIB_PREG_CHECK_SESSION_FROM_ACTION "~[?;&]action=%1$s([;&]|$)~"
#define SMFLIB_PREG_LOAD_AVG_COLUMNS "~^([^ ]+?) ([^ ]+?) ([^ ]+)~"
#define SMFLIB_PREG_LOAD_AVG_UPTIME "~load average[s]?: (\\d+\\.\\d+), (\\d+\\.\\d+), (\\d+\\.\\d+)~i"
#define SMFLIB_PREG_GET_BACKTICKED_DB "~^`(.+?)`\\.(.+?)$~"

#define SMFLIB_FORMAT_VERSION "%1$s(a)"
#define SMFLIB_FORMAT_ERROR_FILE "%1$s<br />%2$s"
#define SMFLIB_FORMAT_ERROR_FILE_TXT "%1$s<br />%2$s: %3$s"
#define SMFLIB_FORMAT_ERROR_LINE "%1$s<br />%2$s"
#define SMFLIB_FORMAT_ERROR_LINE_TXT "%1$s<br />%2$s: %3$d"
#define SMFLIB_FORMAT_CHECK_SPACE "%1$s - check database storage space."
#define SMFLIB_FORMAT_ERROR_CONTEXT "%1$s<br />%2$s: %3$s<br />%4$s: %5$d"
#define SMFLIB_FORMAT_ERROR_OR_WARNING "<br />\n\
		<b>%1$s</b>: %2$s in <b>%3$s</b> on line <b>%4$d</b><br />"
#define SMFLIB_FORMAT_IP_QUERY_PART "((%1$s BETWEEN bi.ip_low1 AND bi.ip_high1)\n\
	AND (%2$s BETWEEN bi.ip_low2 AND bi.ip_high2)\n\
	AND (%3$s BETWEEN bi.ip_low3 AND bi.ip_high3)\n\
	AND (%4$s BETWEEN bi.ip_low4 AND bi.ip_high4))"
#define SMFLIB_FORMAT_UNKNOWN_IP "(bi.ip_low1 = 255 AND bi.ip_high1 = 255\n\
	AND bi.ip_low2 = 255 AND bi.ip_high2 = 255\n\
	AND bi.ip_low3 = 255 AND bi.ip_high3 = 255\n\
	AND bi.ip_low4 = 255 AND bi.ip_high4 = 255)"
#define SMFLIB_FORMAT_HOSTNAME_QUERY_PART "('%1$s' LIKE bi.hostname)"
#define SMFLIB_FORMAT_EMAIL_QUERY_PART "('%1$s' LIKE bi.email_address)"
#define SMFLIB_FORMAT_MEMBER_QUERY_PART "bi.ID_MEMBER = %1$d"

#define SMFLIB_HTML_SPACES " \t\n\r\x0B\0\xA0"


#define SMFLIB_QUERY_PERM "\n\
	SELECT MIN(bp.addDeny) AS addDeny\n\
	FROM (%1$sboards AS b, %1$sboard_permissions AS bp)\n\
		LEFT JOIN %1$smoderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = %2$d)\n\
	WHERE b.ID_BOARD IN (%3$s)%4$s\n\
		AND bp.ID_BOARD = %5$s\n\
		AND bp.ID_GROUP IN (%6$s, 3)\n\
		AND bp.permission %7$s\n\
		AND (mods.ID_MEMBER IS NOT NULL OR bp.ID_GROUP != 3)\n\
	GROUP BY b.ID_BOARD"
#define SMFLIB_QUERY_PERM_MODE "\n\
		AND b.permission_mode <= %1$d"
#define SMFLIB_QUERY_PERM_LIST_SINGLE "= '%1$s'"
#define SMFLIB_QUERY_PERM_LIST_MULTIPLE "IN ('%1$s')"

#define SMFLIB_QUERY_LOG_ERROR "\n\
	INSERT INTO %1$slog_errors\n\
		(ID_MEMBER, logTime, ip, url, message, session)\n\
	VALUES (%2$d, %3$d, '%4$s', '%5$s', '%6$s', '%7$s')"

#define SMFLIB_QUERY_REPAIR_TABLE "\n\
	REPAIR TABLE %1$s"

#define SMFLIB_QUERY_BAN_MAIN "\n\
	SELECT bi.ID_BAN, bg.cannot_access, bg.cannot_register, bg.cannot_post, bg.cannot_login, bg.reason\n\
	FROM (%1$sban_groups AS bg, %1$sban_items AS bi)\n\
	WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP\n\
		AND (bg.expire_time IS NULL OR bg.expire_time > %2$d)\n\
		AND (%3$s)"

#define SMFLIB_QUERY_BAN_COOKIE "\n\
	SELECT bi.ID_BAN, bg.reason\n\
	FROM (%1$sban_items AS bi, %1$sban_groups AS bg)\n\
	WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP\n\
		AND (bg.expire_time IS NULL OR bg.expire_time > %2$d)\n\
		AND bg.cannot_access = 1\n\
		AND bi.ID_BAN IN (%3$s)\n\
	LIMIT %4$s"

#define SMFLIB_QUERY_BAN_CLEAR_ONLINE "\n\
	DELETE FROM %1$slog_online\n\
	WHERE ID_MEMBER = %2$d\n\
	LIMIT 1"

#define SMFLIB_QUERY_BAN_UPDATE_HITS "\n\
	UPDATE %1$sban_items\n\
	SET hits = hits + 1\n\
	WHERE ID_BAN IN (%2$s)"

#define SMFLIB_QUERY_BAN_INSERT_LOG "\n\
	INSERT INTO %1$slog_banned\n\
		(ID_MEMBER, ip, email, logTime)\n\
	VALUES (%2$d, '%3$s', '%4$s', %5$d)"

#define SMFLIB_QUERY_LOG_BAN_MAIN "\n\
	INSERT INTO %1$slog_banned\n\
		(ID_MEMBER, ip, email, logTime)\n\
	VALUES (%2$d, '%3$s', %4$s, %5$d)"

#define SMFLIB_QUERY_LOG_BAN_HITS "\n\
	UPDATE %1$sban_items\n\
	SET hits = hits + 1\n\
	WHERE ID_BAN IN (%2$s)"

#define SMFLIB_QUERY_BANNED_EMAIL "\n\
	SELECT bi.ID_BAN, bg.%1$s, bg.cannot_access, bg.reason\n\
	FROM (%2$sban_items AS bi, %2$sban_groups AS bg)\n\
	WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP\n\
		AND '%3$s' LIKE bi.email_address\n\
		AND (bg.%1$s = 1 OR bg.cannot_access = 1)"

#define SMFLIB_QUERY_BOARDS_ALLOWED_TO "\n\
	SELECT b.ID_BOARD, b.permission_mode, bp.addDeny\n\
	FROM (%1$sboards AS b, %1$sboard_permissions AS bp)\n\
		LEFT JOIN %1$smoderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = %2$d)\n\
	WHERE bp.ID_BOARD = %3$s\n\
		AND bp.ID_GROUP IN (%4$s, 3)\n\
		AND bp.permission = '$permission'%5$s\n\
		AND (mods.ID_MEMBER IS NOT NULL OR bp.ID_GROUP != 3)"
#define SMFLIB_QUERY_BOARDS_ALLOWED_TO_BY_BOARD "IF(b.permission_mode = 1, b.ID_BOARD, 0)"
#define SMFLIB_QUERY_BOARDS_ALLOWED_TO_GLOBAL "0"
#define SMFLIB_QUERY_BOARDS_ALLOWED_TO_ALLOWABLE_MODE "\n\
		AND (mods.ID_MEMBER IS NOT NULL OR b.permission_mode <= %1$d)"

#define SMFLIB_QUERY_SET_MODE "SET sql_mode='', AUTOCOMMIT=1"

#define SMFLIB_QUERY_LOAD_SETTINGS "\n\
	SELECT variable, value\n\
	FROM %1$ssettings"

#define SMFLIB_QUERY_NUM_ONLINE "\n\
	SELECT COUNT(session)\n\
	FROM %1$slog_online"

#define SMFLIB_QUERY_SHOW_TABLES "\n\
	SHOW TABLES\n\
	LIKE '%1$s%%'"

#define SMFLIB_QUERY_SHOW_TABLES_BACKTICK "\n\
	SHOW TABLES\n\
	FROM `%1$s`\n\
	LIKE '%2$s%%'"

#define SMFLIB_QUERY_OPTIMIZE_TABLE "\n\
	OPTIMIZE TABLE `%1$s`"

static zval *heavy_permissions = NULL;
static char *_heavy_permissions[] = {
	"admin_forum",
	"manage_attachments",
	"manage_smileys",
	"manage_boards",
	"edit_news",
	"moderate_forum",
	"manage_bans",
	"manage_membergroups",
	"manage_permissions",
	NULL
};
static zval *denied_permissions = NULL;
static char *_denied_permissions[] = {
	"pm_send",
	"calendar_post", "calendar_edit_own", "calendar_edit_any",
	"poll_post",
	"poll_add_own", "poll_add_any",
	"poll_edit_own", "poll_edit_any",
	"poll_lock_own", "poll_lock_any",
	"poll_remove_own", "poll_remove_any",
	"manage_attachments", "manage_smileys", "manage_boards", "admin_forum", "manage_permissions",
	"moderate_forum", "manage_membergroups", "manage_bans", "send_mail", "edit_news",
	"profile_identity_any", "profile_extra_any", "profile_title_any",
	"post_new", "post_reply_own", "post_reply_any",
	"delete_own", "delete_any", "delete_replies",
	"make_sticky",
	"merge_any", "split_any",
	"modify_own", "modify_any", "modify_replies",
	"move_any",
	"send_topic",
	"lock_own", "lock_any",
	"remove_own", "remove_any",
	NULL
};


/**/

/*
  	Declare any global variables you may need between the BEGIN
	and END macros here:

ZEND_BEGIN_MODULE_GLOBALS(smflib)
	long  global_value;
	char *global_string;
ZEND_END_MODULE_GLOBALS(smflib)
*/

/* In every utility function you add that needs to use variables
   in php_smflib_globals, call TSRMLS_FETCH(); after declaring other
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as SMFG(variable).  You are
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define SMFG(v) TSRMG(smflib_globals_id, zend_smflib_globals *, v)
#else
#define SMFG(v) (smflib_globals.v)
#endif

#endif
