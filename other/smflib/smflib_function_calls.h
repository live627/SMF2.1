#ifndef SMFLIB_FUNCTION_CALLS_H
#define SMFLIB_FUNCTION_CALLS_H 1

// Call a function like my_function().
#define SMFLIB_CALL_FUNCTION(fname, retval)									\
{																			\
	zval *function_name;													\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 0, NULL, 0, NULL TSRMLS_CC) != SUCCESS)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
}

// Call a user function like my_function().
#define SMFLIB_CALL_USER_FUNCTION(fname, fname_len, retval)					\
{																			\
	zval *function_name;													\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, fname_len, 1);						\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 0, NULL, 0, NULL TSRMLS_CC) != SUCCESS)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
}

// Call a function like my_function(bool).
#define SMFLIB_CALL_FUNCTION_B(fname, b1, retval)							\
{																			\
	zval **paramlist[1];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_BOOL(p1, b1);														\
	paramlist[0] = &p1;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 1, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(long).
#define SMFLIB_CALL_FUNCTION_L(fname, l1, retval)							\
{																			\
	zval **paramlist[1];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_LONG(p1, l1);														\
	paramlist[0] = &p1;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 1, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(string).
#define SMFLIB_CALL_FUNCTION_S(fname, s1, s1_len, retval)					\
{																			\
	zval **paramlist[1];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 1, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(zval).
#define SMFLIB_CALL_FUNCTION_Z(fname, zp1, retval)							\
{																			\
	zval **paramlist[1];													\
	zval *function_name;													\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 1, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
}

// Call a function like my_function(bool, bool).
#define SMFLIB_CALL_FUNCTION_BB(fname, b1, b2, retval)						\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_BOOL(p1, b1);														\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(NULL, bool).
#define SMFLIB_CALL_FUNCTION_NB(fname, b2, retval)							\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(string, bool).
#define SMFLIB_CALL_FUNCTION_SB(fname, s1, s1_len, b2, retval)				\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(string, long).
#define SMFLIB_CALL_FUNCTION_SL(fname, s1, s1_len, l2, retval)		\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_LONG(p2, l2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(string, string).
#define SMFLIB_CALL_FUNCTION_SS(fname, s1, s1_len, s2, s2_len, retval)		\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(string, zval).
#define SMFLIB_CALL_FUNCTION_SZ(fname, s1, s1_len, zp2, retval)				\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(zval, bool).
#define SMFLIB_CALL_FUNCTION_ZB(fname, zp1, b2, retval)						\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p2;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(zval, long).
#define SMFLIB_CALL_FUNCTION_ZL(fname, zp1, l2, retval)						\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p2;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_LONG(p2, l2);														\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(zval, string).
#define SMFLIB_CALL_FUNCTION_ZS(fname, zp1, s2, s2_len, retval)				\
{																			\
	zval **paramlist[2];													\
	zval *function_name, *p2;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(zval, zval).
#define SMFLIB_CALL_FUNCTION_ZZ(fname, zp1, zp2, retval)					\
{																			\
	zval **paramlist[2];													\
	zval *function_name;													\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 2, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
}

// Call a function like my_function(string, bool, bool).
#define SMFLIB_CALL_FUNCTION_SBB(fname, s1, s1_len, b2, b3, retval)			\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p1, *p2, *p3;										\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_BOOL(p3, b3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(string, string, zval).
#define SMFLIB_CALL_FUNCTION_SSZ(fname, s1, s1_len, s2, s2_len, zp3, retval)\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p1, *p2;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	paramlist[2] = &zp3;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(string, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZL(fname, s1, s1_len, zp2, l3, retval)\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p1, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(string, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZZ(fname, s1, s1_len, zp2, zp3, retval)\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(zval, bool, bool).
#define SMFLIB_CALL_FUNCTION_ZBB(fname, zp1, b2, b3, retval)				\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_BOOL(p2, b2);														\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_BOOL(p3, b3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, long, long).
#define SMFLIB_CALL_FUNCTION_ZLL(fname, zp1, l2, l3, retval)				\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_LONG(p2, l2);														\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, long, zval).
#define SMFLIB_CALL_FUNCTION_ZLZ(fname, zp1, l2, zp3, retval)				\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_LONG(p2, l2);														\
	paramlist[1] = &p2;														\
	paramlist[2] = &zp3;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
}

// Call a function like my_function(zval, string, long).
#define SMFLIB_CALL_FUNCTION_ZSL(fname, zp1, s2, s2_len, l3, retval)		\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, string, string).
#define SMFLIB_CALL_FUNCTION_ZSS(fname, zp1, s2, s2_len, s3, s3_len, retval)\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_STRINGL(p3, s3, s3_len, 1);										\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, string, zval).
#define SMFLIB_CALL_FUNCTION_ZSZ(fname, zp1, s2, s2_len, zp3, retval)		\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p2;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	paramlist[2] = &zp3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
}

// Call a user function like my_function(zval, zval, bool).
#define SMFLIB_CALL_USER_FUNCTION_ZZB(fname, fname_len, zp1, zp2, b3, retval)	\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p3;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, fname_len, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_BOOL(p3, b3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, zval, long).
#define SMFLIB_CALL_FUNCTION_ZZL(fname, zp1, zp2, l3, retval)				\
{																			\
	zval **paramlist[3];													\
	zval *function_name, *p3;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(zval, zval, zval).
#define SMFLIB_CALL_FUNCTION_ZZZ(fname, zp1, zp2, zp3, retval)				\
{																			\
	zval **paramlist[3];													\
	zval *function_name;													\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 3, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
}

// Call a function like my_function(string, zval, string, zval).
#define SMFLIB_CALL_FUNCTION_SZSZ(fname, s1, s1_len, zp2, s3, s3_len, zp4, retval)	\
{																			\
	zval **paramlist[4];													\
	zval *function_name, *p1, *p3;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_STRINGL(p3, s3, s3_len, 1);										\
	paramlist[2] = &p3;													\
	paramlist[3] = &zp4;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 4, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p3);														\
}

// Call a function like my_function(string, zval, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZZZ(fname, s1, s1_len, zp2, zp3, zp4, retval)	\
{																			\
	zval **paramlist[4];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 4, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(zval, zval, zval, bool).
#define SMFLIB_CALL_FUNCTION_ZZZB(fname, zp1, zp2, zp3, b4, retval)			\
{																			\
	zval **paramlist[4];													\
	zval *function_name, *p4;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	ALLOC_INIT_ZVAL(p4);													\
	ZVAL_BOOL(p4, b4);										\
	paramlist[3] = &p4;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 4, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p4);														\
}

// Call a function like my_function(string, zval, zval, zval, long).
#define SMFLIB_CALL_FUNCTION_SZZZL(fname, s1, s1_len, zp2, zp3, zp4, l5, retval)	\
{																			\
	zval **paramlist[5];													\
	zval *function_name, *p1, *p5;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	ALLOC_INIT_ZVAL(p5);													\
	ZVAL_LONG(p5, l5);														\
	paramlist[4] = &p5;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 5, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p5);														\
}

// Call a function like my_function(string, zval, zval, zval, zval, long).
#define SMFLIB_CALL_FUNCTION_SZZZZL(fname, s1, s1_len, zp2, zp3, zp4, l5, retval)	\
{																			\
	zval **paramlist[5];													\
	zval *function_name, *p1, *p5;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	ALLOC_INIT_ZVAL(p5);													\
	ZVAL_LONG(p5, l5);														\
	paramlist[4] = &p5;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 5, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
	zval_ptr_dtor(&p5);														\
}

// Call a function like my_function(string, zval, zval, zval, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZZZZ(fname, s1, s1_len, zp2, zp3, zp4, zp5, retval)	\
{																			\
	zval **paramlist[5];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	paramlist[4] = &zp5;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 5, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(string, zval, zval, zval, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZZZZZ(fname, s1, s1_len, zp2, zp3, zp4, zp5, zp6, retval)	\
{																			\
	zval **paramlist[6];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	paramlist[4] = &zp5;													\
	paramlist[5] = &zp6;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 6, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

// Call a function like my_function(zval, string, long, zval, zval, long).
#define SMFLIB_CALL_FUNCTION_ZSLZZL(fname, zp1, s2, s2_len, l3, zp4, zp5, l6, retval)	\
{																			\
	zval **paramlist[6];													\
	zval *function_name, *p2, *p3, *p6;										\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	ALLOC_INIT_ZVAL(p2);													\
	ZVAL_STRINGL(p2, s2, s2_len, 1);										\
	paramlist[1] = &p2;														\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	paramlist[3] = &zp4;													\
	paramlist[4] = &zp5;													\
	ALLOC_INIT_ZVAL(p6);													\
	ZVAL_LONG(p6, l6);														\
	paramlist[5] = &p6;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 6, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p2);														\
	zval_ptr_dtor(&p3);														\
	zval_ptr_dtor(&p6);														\
}

// Call a function like my_function(zval, zval, long, zval, zval, long).
#define SMFLIB_CALL_FUNCTION_ZZLZZL(fname, zp1, zp2, l3, zp4, zp5, l6, retval)	\
{																			\
	zval **paramlist[6];													\
	zval *function_name, *p3, *p6;											\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	paramlist[0] = &zp1;													\
	paramlist[1] = &zp2;													\
	ALLOC_INIT_ZVAL(p3);													\
	ZVAL_LONG(p3, l3);														\
	paramlist[2] = &p3;														\
	paramlist[3] = &zp4;													\
	paramlist[4] = &zp5;													\
	ALLOC_INIT_ZVAL(p6);													\
	ZVAL_LONG(p6, l6);														\
	paramlist[5] = &p6;														\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 6, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p3);														\
	zval_ptr_dtor(&p6);														\
}

// Call a function like my_function(string, zval, zval, zval, zval, zval, zval, zval).
#define SMFLIB_CALL_FUNCTION_SZZZZZZZ(fname, s1, s1_len, zp2, zp3, zp4, zp5, zp6, zp7, zp8, retval)	\
{																			\
	zval **paramlist[8];													\
	zval *function_name, *p1;												\
	ALLOC_INIT_ZVAL(function_name);											\
	ZVAL_STRINGL(function_name, fname, sizeof(fname) - 1, 1);				\
	ALLOC_INIT_ZVAL(p1);													\
	ZVAL_STRINGL(p1, s1, s1_len, 1);										\
	paramlist[0] = &p1;														\
	paramlist[1] = &zp2;													\
	paramlist[2] = &zp3;													\
	paramlist[3] = &zp4;													\
	paramlist[4] = &zp5;													\
	paramlist[5] = &zp6;													\
	paramlist[6] = &zp7;													\
	paramlist[7] = &zp8;													\
	if (call_user_function_ex(CG(function_table), NULL, function_name, &retval, 8, paramlist, 0, NULL TSRMLS_CC) == FAILURE)	\
		zend_error(E_ERROR, "Cannot find function %s", fname);				\
	zval_ptr_dtor(&function_name);											\
	zval_ptr_dtor(&p1);														\
}

#endif
