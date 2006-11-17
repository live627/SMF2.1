<?php
// Version: 2.0 Alpha; Install

// These should be the same as those in index.language.php.
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['smf_installer'] = 'Instalador SMF';
$txt['installer_language'] = 'Idioma';
$txt['installer_language_set'] = 'Establecer';
$txt['congratulations'] = '&iexcl;Felicidades!, el proceso de instalaci&oacute;n se ha completado';
$txt['congratulations_help'] = 'Si en alg&uacute;n momento necesitas soporte, o SMF no funciona correctamente, recuerda que <a href="http://www.simplemachines.org/community/index.php" target="_blank">hay ayuda disponible</a> en caso que la necesites.';
$txt['still_writable'] = 'Todav&iacute;a se puede escribir en tu directorio de instalaci&oacute;n. Se recomienda que uses chmod para que no sea escribible por razones de seguridad.';
$txt['delete_installer'] = 'Haz <i>click</i> aqu&iacute; para borrar este archivo install.php ahora.';
$txt['delete_installer_maybe'] = '<i>(no funciona en todos los servidores.)</i>';
$txt['go_to_your_forum'] = 'Ahora puedes ver <a href="%s">tu foro reci&eacute;n instalado</a> y comenzar a usarlo. Primero debes asegurarte de ingresar con tu usuario, para posteriormente accesar al &aacute;rea de administraci&oacute;n.';
$txt['good_luck'] = '&iexcl;Buena Suerte!<br />Simple Machines';

$txt['user_refresh_install'] = 'Foro Actualizado';
$txt['user_refresh_install_desc'] = 'Mientras se instalaba, el instalador encontr&oacute; que (con los detalles que proporcionaste) una o m&aacute;s de las tablas que deb&iacute;an crearse ya exist&iacute;an.<br />Cualquier tabla faltante en tu instalaci&oacute;n ha sido recreada con los datos de default, pero no se borr&oacute; ninguna informaci&oacute;n de las tablas existentes.';

$txt['default_topic_subject'] = '&iexcl;Bienvenido a SMF!';
$txt['default_topic_message'] = '&iexcl;Bienvenido al foro Simple Machines!<br /><br />Esperamos que disfrutes usar tu foro.&nbsp; Si tienes alg&uacute;n problema, si&eacute;ntete libre de [url=http://www.simplemachines.org/community/index.php]solicitarnos ayuda[/url].<br /><br />&iexcl;Gracias!<br />Simple Machines';
$txt['default_board_name'] = 'Discusi&oacute;n General';
$txt['default_board_description'] = 'Si&eacute;ntete libre de hablar de cualquier cosa en este foro.';
$txt['default_category_name'] = 'Categor&iacute;a General';
$txt['default_time_format'] = '%d de %B de %Y, %I:%M:%S %p';
$txt['default_news'] = 'SMF - &iexcl;Reci&eacute;n Instalado!';
$txt['default_karmaLabel'] = 'Karma:';
$txt['default_karmaSmiteLabel'] = '[smite]';
$txt['default_karmaApplaudLabel'] = '[applaud]';
$txt['default_reserved_names'] = 'Admin\nWebmaster\nGuest\nroot';
$txt['default_smileyset_name'] = 'Default';
$txt['default_classic_smileyset_name'] = 'Classic';
$txt['default_theme_name'] = 'SMF Default Theme - Core';
$txt['default_classic_theme_name'] = 'Classic YaBB SE Theme';
// Untranslated!
$txt['default_babylon_theme_name'] = 'Babylon Theme';

$txt['default_administrator_group'] = 'Administrador';
$txt['default_global_moderator_group'] = 'Moderador Global';
$txt['default_moderator_group'] = 'Moderador';
$txt['default_newbie_group'] = 'Novato';
$txt['default_junior_group'] = 'Usuario Jr';
$txt['default_full_group'] = 'Usuario Completo';
$txt['default_senior_group'] = 'Usuario Sr.';
$txt['default_hero_group'] = 'Usuario H&eacute;roe';

$txt['default_smiley_smiley'] = 'Sonrisa';
$txt['default_wink_smiley'] = 'Gi&ntilde;ar';
$txt['default_cheesy_smiley'] = 'Cheesy';
$txt['default_grin_smiley'] = 'Sonreir';
$txt['default_angry_smiley'] = 'Enojado';
$txt['default_sad_smiley'] = 'Triste';
$txt['default_shocked_smiley'] = 'Impresionado';
$txt['default_cool_smiley'] = 'Cool';
$txt['default_huh_smiley'] = 'Huh?';
$txt['default_roll_eyes_smiley'] = 'Girar ojos';
$txt['default_tongue_smiley'] = 'Lengua';
$txt['default_embarrassed_smiley'] = 'Avergonzado';
$txt['default_lips_sealed_smiley'] = 'Labios sellados';
$txt['default_undecided_smiley'] = 'Indeciso';
$txt['default_kiss_smiley'] = 'Beso';
$txt['default_cry_smiley'] = 'Llorar';
$txt['default_evil_smiley'] = 'Malvado';
$txt['default_azn_smiley'] = 'Azn';
$txt['default_afro_smiley'] = 'Afro';

$txt['error_message_click'] = 'Haz <i>click</i> aqu&iacute;';
$txt['error_message_try_again'] = 'para intentar este paso nuevamente.';
$txt['error_message_bad_try_again'] = 'para intentar instalar de todos modos, pero es <i>altamente</i> recomendado que NO lo hagas.';

$txt['install_settings'] = 'Configuraci&oacute;n b&aacute;sica';
$txt['install_settings_info'] = 'Solamente algunas cosas que necesitamos que configures ;).';
$txt['install_settings_name'] = 'Nombre del foro';
$txt['install_settings_name_info'] = 'Este es el nombre de tu foro, ej. &quot;El Foro de Pruebas&quot;.';
$txt['install_settings_name_default'] = 'Mi Comunidad';
$txt['install_settings_url'] = 'URL del Foro';
$txt['install_settings_url_info'] = 'Este es el URL de tu foro <b>sin la \'/\' del final</b>.<br />En la mayor&iacute;a de las ocasiones, puedes dejar el valor de default de este cuadro de texto - por lo general tiene el valor correcto.';
$txt['install_settings_compress'] = 'Salida Gzip';
$txt['install_settings_compress_title'] = 'Comprimir salida para ahorrar ancho de banda.';
// In this string, you can translate the word "PASS" to change what it says when the test passes.
$txt['install_settings_compress_info'] = 'Esta funci&oacute;n no funciona correctamente en todos los servidores, pero puede ahorrar mucho ancho de banda.<br />Haz <i>click</i> <a href="install.php?obgz=1&amp;pass_string=APROBADO" onclick="return reqWin(this.href, 200, 60);">aqu&iacute;</a> para probarlo. (debe decir simplemente "APROBADO".)';
$txt['install_settings_dbsession'] = 'Sesiones de la Base de Datos';
$txt['install_settings_dbsession_title'] = 'Usar la base de datos para las sesiones en lugar de usar archivos.';
$txt['install_settings_dbsession_info1'] = 'Esta opci&oacute;n casi siempre es recomendable seleccionarla, ya que hace las sesiones m&aacute;s fiables.';
$txt['install_settings_dbsession_info2'] = 'No parece que esta opci&oacute;n vaya a funcionar en tu servidor, pero puedes intentarlo.';
// Untranslated!
$txt['install_settings_utf8'] = 'UTF-8 Character Set';
$txt['install_settings_utf8_title'] = 'Use UTF-8 as default character set';
$txt['install_settings_utf8_info'] = 'This feature lets both the database and the forum use an international character set, UTF-8. This can be useful when working with multiple languages that use different character sets.';
$txt['install_settings_stats'] = 'Allow Stat Collection';
$txt['install_settings_stats_title'] = 'Allow Simple Machines to Collect Basic Stats Monthly';
$txt['install_settings_stats_info'] = 'If enabled, this will allow Simple Machines to visit your site once a month to collect basic statistics. This will help us make decisions as to which configurations to optimize the software for. For more information please visit our <a href="http://www.simplemachines.org/about/stats.php" target="_blank">info page</a>.';
$txt['install_settings_proceed'] = 'Proceder';

// Untranslated!
$txt['db_settings'] = 'Database Server Settings';
$txt['db_settings_info'] = 'These are the settings to use for your database server.  If you don\'t know the values, you should ask your host what they are.';
$txt['db_settings_type'] = 'Database Type';
$txt['db_settings_type_info'] = 'Multiple supported database types were detected - which do you wish to use.';
$txt['db_settings_server'] = 'Nombre del servidor';
$txt['db_settings_server_info'] = 'Casi siempre es localhost - si no lo sabes, puedes intentar localhost.';
$txt['db_settings_username'] = 'Nombre de usuario';
$txt['db_settings_username_info'] = 'Especifica aqu&iacute; el nombre de usuario que necesitas para conectarte a tu base de datos.<br />Si no lo sabes, intenta con el nombre de usuario de tu cuenta ftp, la mayor&iacute;a de las veces es el mismo.';
$txt['db_settings_password'] = 'Contrase&ntilde;a de';
$txt['db_settings_password_info'] = 'Aqu&iacute;, introduce la contrase&ntilde;a para conectarte a tu base de datos.<br />Si no la sabes, intenta con la contrase&ntilde;a de tu cuenta ftp.';
$txt['db_settings_database'] = 'Nombre de la base de datos';
$txt['db_settings_database_info'] = 'Especifica el nombre de la base de datos en la que deseas que SMF almacene sus datos.<br />Si esta base de datos no existe, el instalador intentar&aacute; crearla.';
$txt['db_settings_prefix'] = 'Prefijo para las tablas';
$txt['db_settings_prefix_info'] = 'El prefijo para cada tabla de la base de datos.  <b>&iexcl;No instales dos foros con el mismo prefijo!</b><br />Este valor permite varias instalaciones en una base de datos.';

$txt['user_settings'] = 'Crea tu cuenta';
$txt['user_settings_info'] = 'Ahora el instalador crear&aacute; una nueva cuenta de administrador para t&iacute;.';
$txt['user_settings_username'] = 'Tu nombre de usuario';
$txt['user_settings_username_info'] = 'Especifica el nombre con el que ingresar&aacute;s.<br />Este nombre NO podr&aacute; ser cambiado despu&eacute;s, pero s&iacute; el nombre que se mostrar&aacute;.';
$txt['user_settings_password'] = 'Contrase&ntilde;a';
$txt['user_settings_password_info'] = 'Especifica tu contrase&ntilde;a aqu&iacute;, &iexcl;y recu&eacute;rdala bien!';
$txt['user_settings_again'] = 'Contrase&ntilde;a';
$txt['user_settings_again_info'] = '(Para verificarla.)';
$txt['user_settings_email'] = 'Direcci&oacute;n email';
$txt['user_settings_email_info'] = 'Introduce tu direcci&oacute;n de email.  <b>Debe ser una direcci&oacute;n v&aacute;lida.</b>';
$txt['user_settings_database'] = 'Contrase&ntilde;a de la base de datos';
$txt['user_settings_database_info'] = 'El instalador necesita que le proporciones la contrase&ntilde;a de la base de datos para crear una cuenta de administrador, por razones de seguridad.';
$txt['user_settings_proceed'] = 'Finalizar';

$txt['ftp_setup'] = 'Informaci&oacute;n de la conexi&oacute;n FTP';
$txt['ftp_setup_info'] = 'Este instalador puede conectarse v&iacute;a FTP para arreglar los archivos que necesitan tener permisos de escritura y no los poseen. Si esto no funciona para ti deber&aacute;s acceder manualmente y modificar los permisos de escritura. Toma nota que que esto no soporta SSL por el momento.';
$txt['ftp_server'] = 'Servidor';
$txt['ftp_server_info'] = 'Debes especificar el servidor y el puerto de tu servidor de FTP.';
$txt['ftp_port'] = 'Puerto';
$txt['ftp_username'] = 'Nombre de usuario';
$txt['ftp_username_info'] = 'El nombre de usuario con el que ingresar&aacute;s. <i>Esta informaci&oacute;n no ser&aacute; guardada en ning&uacute;n lado.</i>';
$txt['ftp_password'] = 'Contrase&ntilde;a';
$txt['ftp_password_info'] = 'La contrase&ntilde;a para poder ingresar. <i>Esta informaci&oacute;n no ser&aacute; guardada en ning&uacute;n lado.</i>';
$txt['ftp_path'] = 'Ruta de la instalaci&oacute;n';
$txt['ftp_path_info'] = 'Esta es la ruta <i>relativa</i> que se usar&aacute; en tu servidor FTP.';
$txt['ftp_path_found_info'] = 'La ruta en el cuadro superior fue detectado autom&aacute;ticamente.';
$txt['ftp_connect'] = 'Conectarse';
$txt['ftp_setup_why'] = '&iquest;Para que sirve este paso?';
$txt['ftp_setup_why_info'] = 'Algunos archivos necesitan tener permisos de escritura para que SMF funcione correctamente.  Este paso permite que el instalador los haga escribibles por t&iacute;.  Sin embargo, en algunos casos esto no funciona - si es tu caso, modifica los siguientes archivos a 777 (escribibles):';
$txt['ftp_setup_again'] = 'para comprobar si estos archivos son escribibles nuevamente.';

$txt['error_php_too_low'] = '&iexcl;Advertencia!  Parece que no tienes instalada una versi&oacute;n de PHP en tu servidor que cumpla con los <b>requisitos m&iacute;nimos para instalaci&oacute;n</b> que necesita SMF.<br />Si no eres el due&ntilde;o del servidor, deber&aacute;s solicitarle a tu proveedor de alojamiento que actualice PHP, o usar un servidor diferente - de otra manera, actualiza PHP a una versi&oacute;n m&aacute;s reciente.<br /><br />Si estas seguro que tu versi&oacute;n de PHP es lo suficientemente reciente puedes continuar, , pero es <i>altamente</i> recomendado que NO lo hagas.';
$txt['error_missing_files'] = '&iexcl;Imposible encontrar archivos imprescindibles para la instalaci&oacute;n en el directorio de este script!<br /><br />Comprueba que hayas subido el paquete completo de instalaci&oacute;n, inclu&iacute;do el archivo sql, e intenta nuevamente.';
$txt['error_session_save_path'] = '&iexcl;Por favor informa a tu proveedor de alojamiento que el valor de <b>session.save_path especificado en php.ini</b> es inv&aacute;lido! Necesita ser cambiado  a un directorio que <b>exista</b>, y que sea <b>escribible</b> por el usuario bajo el cual se est&aacute; ejecutando PHP.<br />';
$txt['error_windows_chmod'] = 'Est&aacute;s en un servidor Windows, y algunos de los archivos cruciales no son escribibles.  Solicita a tu proveedor de alojamiento que le otorgue <b>permisos de escritura</b> al usuario bajo el cual se est&aacute; ejecutando PHP.  Los siguientes archivos o directorios deben ser escribibles:';
$txt['error_ftp_no_connect'] = 'Imposible conectarse al servidor FTP con esta combinaci&oacute;n de valores.';
// Untranslated!
$txt['error_db_file'] = 'Cannot find database source script! Please check file %s is within your forum source directory.';
$txt['error_db_connect'] = 'No se puede conectar al servidor de la base de datos con los valores proporcionados.<br /><br />Si no est&aacute; seguro de qu&eacute; valores proporcionar, por favor contacta a tu proveedor de alojamiento.';
$txt['error_db_too_low'] = 'The version of your database server is very old, and does not meet SMF\'s minimum requirements.<br /><br />Please ask your host to either upgrade it or supply a new one, and if they won\'t, please try a different host.';
$txt['error_db_database'] = 'El instalador no pudo accesar a la base de datos &quot;<i>%s</i>&quot;.  En algunos servidores, tienes que crear la base de datos en tu panel de control antes que SMF pueda usarla.  Algunos tambi&eacute;n a&ntilde;aden prefijos - como tu nombre de usuario- a los nombres de la bases de datos.';
// Untranslated!
$txt['error_db_queries'] = 'Some of the queries were not executed properly.  This could be caused by an unsupported (development or old) version of your database software.<br /><br />Technical information about the queries:';
$txt['error_db_queries_line'] = 'L&iacute;nea #';
// Untranslated!
$txt['error_db_missing'] = 'The installer was unable to detect any database support in PHP.  Please ask your host to ensure that PHP was compiled with the desired database, or that the proper extension is being loaded.';
$txt['error_session_missing'] = 'El instalador no detecto soporte para sesiones en la instalaci&oacute;n de PHP en tu servidor.  P&iacute;dele por favor a tu proveedor de hospedaje que se asegure que PHP haya sido compilado con soporte para sesiones (De hecho, lo debieron haber compilado expl&iacute;citamente in dicho soporte.)';
$txt['error_user_settings_again_match'] = '&iexcl;Has escrito dos contrase&ntilde;as completamente diferentes.!';
$txt['error_user_settings_taken'] = 'Lo sentimos, ya existe un usuario registrado con ese usuario o contrase&ntilde;a.<br /><br />No se cre&oacute; una nueva cuenta.';
$txt['error_user_settings_query'] = 'Ha ocurrido un error en la base de datos cuando se trataba de crear un administrador.  El error ha sido:';
$txt['error_subs_missing'] = 'No es posible encontrar el archivo Sources/Subs.php. Comprueba que lo has subido correctamente, e int&eacute;ntalo nuevamente.';
$txt['error_db_alter_priv'] = 'La cuenta de datos que especificaste no tiene permiso para las funciones ALTER, CREATE, o DROP en las tablas de la base de datos; &eacute;stos comandos son necesarios para el funcionamiento correcto de SMF.';
// Untranslated!
$txt['error_versions_do_not_match'] = 'The installer has detected another version of SMF already installed with the specified information.  If you are trying to upgrade, you should use the upgrader, not the installer.<br /><br />Otherwise, you may wish to use different information, or create a backup and then delete the data currently in the database.';
$txt['error_mod_security'] = 'The installer has detected the mod_security module is installed on your web server. Mod_security will block submitted forms even before SMF gets a say in anything. SMF has a built-in security scanner that will work more effectively than mod_security and that won\'t block submitted forms.<br /><br /><a href="http://www.simplemachines.org/redirect/mod_security">More information about disabling mod_security</a>';
$txt['error_utf8_version'] = 'The current version of your database doesn\'t support the use of the UTF-8 character set. You can still install SMF without any problems, but only with UTF-8 support unchecked. If you would like to switch over to UTF-8 in the future (e.g. after the database server of your forum has been upgraded to version >= %s), you can convert your forum to UTF-8 through the admin panel.';

?>