PHP_ARG_WITH(smflib, whether to include SMF library support,
[ --with-smflib   Include SMF library support])

if test "$PHP_SMFLIB" = "yes"; then
  AC_DEFINE(HAVE_SMFLIB, 1, [Whether you have SMF library])
  PHP_NEW_EXTENSION(smflib, smflib.c QueryString.c Errors.c Security.c Load.c, $ext_shared)
fi