<?php
// Version: 2.0 Alpha; Help

// Important! Before editing these language files please read the text at the topic of index.spanish.php.

global $helptxt;
$helptxt = array();

$txt[1006] = 'Cerrar ventana';

$helptxt['manage_boards'] = '
	<b>Editar foros</b><br />
	En este men&uacute; puedes crear/reordenar/eliminar foros, y las categor&iacute;as
	arriba de ellos. Por ejemplo, si tienes un amplio sitio web
	que ofrece informaci&oacute;n acerca de &quot;Anime&quot;, &quot;Carros&quot; y &quot;M&uacute;sica&quot;,
	&eacute;stos deben ser las categor&iacute;as a mayor nivel que debes crear. Debajo de esas
	categor&iacute;as probablemente desees crear &quot;sub-categorias&quot; jer&aacute;rquicas,
	o &quot;Foros&quot; para contener temas dentro de cada uno. Es una jerarqu&iacute;a simple, con esta estructura: <br />
	<ul>
		<li>
			<b>Anime</b>
			&nbsp;- Una &quot;categor&iacute;a&quot;
		</li>
		<ul>
			<li>
				<b>Dragon Ball</b>
				&nbsp;- Un foro en la categor&iacute;a de &quot;Anime&quot;
			</li>
			<ul>
				<li>
					<b>Dragon Ball Z</b>
					&nbsp;- Un subforo dentro del foro de &quot;Dragon Ball&quot;
				</li>
			</ul>
			<li><b>Aa! Megami-sama</b>
			&nbsp;- Un foro dentro de la categor&iacute;a de &quot;Anime&quot;</li>
		</ul>
	</ul>
	Las Categor&iacute;as te permiten organizar el foro de mensajes en temas (&quot;Anime, Carros&quot;),
	y los &quot;Foros&quot; dentro de ellas son los temas en los cuales los usuarios pueden
	publicar mensajes. En el ejemplo anterior, un usuario interesado en un Audi,
	publicar&iacute;a un mensaje en el foro &quot;Carros->Audi&quot;. Las Categor&iacute;as permiten
	encontrar r&aacute;pidamente cuales son sus intereses: En vez de &quot;Tienda&quot; se tienen
	tiendas de &quot;Hardware&quot; y &quot;Electrodomesticos&quot; a las que puedes ir.
	Esto simplifica tu b&uacute;squeda por &quot;Pantalla&quot;, ya que puedes ir a la &quot;categor&iacute;a&quot; de
	tienda de Hardware en vez de la tienda de Electrodom&eacute;sticos (donde encontrar&iacute;as televisiones de pantalla
	plana en vez de, probablemente, protectores de pantalla para la computadora).<br />

	Como se puede percibir arriba, un foro es una pieza importante dentro de la categor&iacute;a.
	Si quieres discutir acerca de &quot;Audi&quot;, debes ir a la categor&iacute;a &quot;Autos&quot; e ir al foro
	&quot;Audi&quot; para publicar tus mensajes acerca de lo que opinas en ese foro.<br />
	Las funciones administrativas en este men&uacute; son para crear nuevos foros en cada
	categor&iacute;a, reordenarlas (poner &quot;Audi&quot; abajo de &quot;Ferrari&quot;), o borrar un
	foro completamente.';

$helptxt['edit_news'] = '<b>Editar Noticias del foro</b><br />
	Esto te permite especificar el texto para los elementos de las noticias mostradas en la p&aacute;gina &iacute;ndice del foro.
	Agrega cualquier elemento que desees (ej., &quot;Nueva versi&oacute;n del portal http://HablaJapones.org&quot;). Cada elemento de las noticias se separa
	por un <enter>';

$helptxt['view_members'] = '
	<ul>
		<li>
			<b>Ver todos los usuarios</b><br />
			Ver todos los usuarios en el foro de mensajes. Se te presenta una lista con hiperv&iacute;nculo, de todos
			los nombres de los usuarios. Puedes hacer click en cualquiera de los nombres para obtener mayores
			detalles de un usuario en especial (sitio web, edad, sexo, etc), y como Administrador, puedes modificar
			cualquiera de esos datos. Tienes un total control sobre los usuarios, incluyendo la posibilidad de
			borrarlos del foro de mensajes.<br /><br />
		</li>
		<li>
			<b>Esperando aprobaci&oacute;n</b><br />
			Esta secci&oacute;n se muestra solamente si tienes activado que los administradores aprueben todos los nuevos registros de usuarios. Cualquiera que se registre para unirse a tu
			foro se volver&aacute; un usuario completo cuando haya sido aprobado por un administrador. La secci&oacute;n muestra todos aquellos usuarios que
			est&eacute;n esperando aprobaci&oacute;n, junto con su email y direcci&oacute;n IP. Tu puedes escoger ya sea aceptar y rechazar (borrar)
			cualquier usuario en la lista al seleccionar el cuadro al lado del usuario, y seleccionando la acci&oacute;n del cuadro colapsable al final
			de la pantalla. Cuando rechaces un usuario, puedes escoger borrar el usuario con o sin notificarle de tu decisi&oacute;n.<br /><br />
		</li>
		<li>
			<b>Esperando activaci&oacute;n</b><br />
			Esta secci&oacute;n ser&aacute; visible solamente si tienes activado en el foro el que los usuarios activen sus cuentas. Esta secci&oacute;n listar&aacute; todos los
			usuarios que no han activado sus nuevas cuentas. Desde esta pantalla puedes escoger aceptar, rechazar o recordarles a los
			usuarios con registros pendientes. Como en la opci&oacute;n anterior, puedes escoger enviarle email al usuario para informarle de la
			acci&oacute;n que hayas tomado.<br /><br />
		</li>
	</ul>';

$helptxt['ban_members'] = '<b>Usuarios con acceso prohibido</b><br />
	SMF permite &quot;banear&quot; o &quot;restringir el acceso&quot; a usuarios, para prevenir el acceso a personas que han violado
	la confianza del foro de mensajes, al hacer spam, ser groseros, etc. Esto te permite restringirles el
	acceso a los usuarios que no desees mas en tu foro de mensajes. Como administrador, cuando ves los mensajes,
	puedes ver el IP de cada usuario que utiliz&oacute; cuando public&oacute; el mensaje. En la lista de accesos prohibidos,
	simplemente introduce la direcci&oacute;n IP, guarda los cambios, y ellos no podr&aacute;n accesar el foro desde ese IP.<br />
	Tambi&eacute;n puedes restringir el acceso de usuarios usando su direcci&oacute;n de email, o su nombre de usuario.';

$helptxt['modsettings'] = '<b>Config. y Opciones de \'Mods\' instalados</b><br />
	SMF tiene algunos mods preinstalados, puedes activarlos o desactivarlos desde este men&uacute;.';

$helptxt['number_format'] = '<b>Formato de N&uacute;meros</b><br />
	Puedes ajustar c&oacute;mo los n&uacute;meros ser&aacute;n mostrados al usuario.  El formato es:<br />
	<div style="margin-left: 2ex;">1,234.00</div><br />
	Donde \',\' es el caracter utilizado para dividir los grupos de miles, \'.\' es el caracter utilizado como el punto decimal y el n&uacute;mero de ceros indica la exactit&uacute;d de los redondeos.';

$helptxt['time_format'] = '<b>Formato de Hora</b><br />
	Puedes ajustar como visualizar&aacute;s la hora y la fecha. Hay muchas palabras, pero es relativamente f&aacute;cil.
	El formato sigue las especificaciones de la funcion strftime de PHP, y se describen a continuaci&oacute;n (m&aacute;s detalles pueden encontrarse en <a href="http://www.php.net/manual/function.strftime.php" target="_blank">php.net</a>).<br />
	<br />
	Los siguientes caracteres se reconocen en la cadena del formato:<br />
	<span class="smalltext">
	&nbsp;&nbsp;%a - nombre abreviado del d&iacute;a de la semana <br />
	&nbsp;&nbsp;%A - nombre completo del d&iacute;a de la semana <br />
	&nbsp;&nbsp;%b - nombre abreviado del mes <br />
	&nbsp;&nbsp;%B - nombre completo del mes <br />
	&nbsp;&nbsp;%d - d&iacute;a del mes (01 a 31) <br />
	&nbsp;&nbsp;%D<b>*</b> - lo mismo que %m/%d/%y <br />
	&nbsp;&nbsp;%e<b>*</b> - d&iacute;a del mes (1 a 31) <br />
	&nbsp;&nbsp;%H - hora usando formato de 24 horas (rango 00 a 23) <br />
	&nbsp;&nbsp;%I - hora usando formato de 12 horas (rango 01 a 12) <br />
	&nbsp;&nbsp;%m - mes como n&uacute;mero (01 a 12) <br />
	&nbsp;&nbsp;%M - minuto como n&uacute;mero <br />
	&nbsp;&nbsp;%p - &quot;am&quot; o &quot;pm&quot; de acuerdo a la hora actual<br />
	&nbsp;&nbsp;%R<b>*</b> - hora en formato de 24 horas <br />
	&nbsp;&nbsp;%S - segundos como n&uacute;mero decimal <br />
	&nbsp;&nbsp;%T<b>*</b> - hora actual, de la misma manera que %H:%M:%S <br />
	&nbsp;&nbsp;%y - a&ntilde;o en formato de 2 d&iacute;gitos (00 a 99) <br />
	&nbsp;&nbsp;%Y - a&ntilde;o en formato de 4 d&iacute;gitos <br />
	&nbsp;&nbsp;%Z - zona horaria o nombre o abreviaci&oacute;n <br />
	&nbsp;&nbsp;%% - caracter \'%\'  <br />
	<br />
	<i>* No funciona en servidores Windows.</i></span>';

$helptxt['live_news'] = '<b>Anuncios en vivo</b><br />
	Este cuadro muestra los anuncios recientemente actualizados desde <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.
	Debes revisar aqu&iacute; de vez en cuando por actualizaciones, nuevas versiones, e informaci&oacute;n importante del equipo de Simple Machines.';

// Update from English files!
$helptxt['registrations'] = '<b>Manejo del Registro de Usuarios</b><br />
	Esta secci&oacute;n contiene todas las funciones que pueden ser necesarias para manejar nuevos registros de usuarios en el foro. Contiene hasta tres
	secciones que son visibles dependiendo de la configuraci&oacute;n de tu foro. &#201;stas son:<br /><br />
	<ul>
		<li>
			<b>Registrar Nuevo usuario</b><br />
			Desde esta pantalla puedes escoger registrar nuevas cuentas en nombre de los nuevos usuarios. Esto puede ser &uacute;til en foros donde el registro est&aacute; cerrado
			para nuevos usuarios, o en casos donde el administrador desea crear una cuenta de prueba. Si la opci&oacute;n de requerir activaci&oacute;n de la cuenta
			est&aacute; seleccionada, se le enviar&aacute; un email al usuario, con una liga a la que se le deber&aacute; hacer click antes de que puedan usar la cuenta. Asimismo, puedes
			seleccionar el enviar por email al usuario una nueva contrase&ntilde;a a su direcci&oacute;n de email.<br /><br />
		</li>
		<li>
			<b>Editar Carta de Aceptaci&oacute;n al registrarse</b><br />
			Esto te permite establecer el texto para la carta de aceptaci&oacute;n mostrada a los usuarios cuando est&aacute;n en
			el proceso de registro para obtener una cuenta en tu foro de mensajes.
			Puedes cambiar cualquier texto de la carta de aceptaci&oacute;n original que se unclute en SMF.
		</li>
		<li>
			<b>Configuraci&oacute;n</b><br />
			Esta secci&oacute;n ser&aacute; visible solamente si tienes permisos para administrar el foro. Desde esta pantalla puedes decidir el m&eacute;todo de registro
			que ser&aacute; usado en tu foro, asi como algunas otras configuraciones.
		</li>
	</ul>';

$helptxt['modlog'] = '<b>Log de Moderaci&oacute;n</b><br />
	Esta secci&oacute;n le permite a los administradores mantenerse al tanto de todas las acciones de moderaci&oacute;n que los moderadores del foro han realizado. Para asegurarse que
	los moderadores no puedan eliminar las referencias a las acciones que ellos han realizado, las entradas no pueden eliminarse hasta 24 horas despu&eacute;s de que la acci&oacute;n se haya realizado.
	La columna \'objetos\' lista cualquier variable asociada con la acci&oacute;n.';
$helptxt['error_log'] = '<b>Log de Errores</b><br />
	El log de errores rastrea cualquier error grave encontrado por usuarios al usar tu foro. Lista todos esos errores por fecha, que puede ser usada para ordenar
	al hacer click en la flecha negra al lado de cada fecha. Asimismo, puedes filtrar los errores al hacer click en la imagen al lado de cada estad&iacute;stica de error. Esto
	te permite filtrar, por ejemplo, por usuario. Cuando un filtro est&aacute; activo, solamente los resultados que concuerden con el filtro, ser&aacute;n mostrados.';
$helptxt['theme_settings'] = '<b>Configuraci&oacute;n del Tema</b><br />
	La pantalla de configuraci&oacute;n te permite cambiar las configuraci&oacute;n espec&iacute;fica de un tema. Esta configuraci&oacute;n incluye opciones tales como el directorio de los temas e informaci&#243;n de URLs pero tambi&eacute;n
	opciones que afectan el dise&ntilde;o de un tema en tu foro. La mayor&iacutea de los temas tendr&aacute;n una variedad de opciones configurables por el usuario, permiti&eacute;ndote adaptar un tema
	para satisfacer las necesidades individuales de tu foro.';
// Update from English files!
$helptxt['smileys'] = '<b>Centro de Smileys</b><br />
	Aqu&iacute; puedes agregar y eliminar smileys as&iacute; como conjuntos de smileys. Es importante mencionar que si un smiley est&aacute; en un conjunto, debe estar en todos los conjuntos - de otra manera, podr&iacute;a
	ser confuso para tus usuarios cuando utilicen diferentes conjuntos.';
$helptxt['calendar'] = '<b>Administrar Calendario</b><br />
	Aqu&iacute; puedes modificar la configuraci&oacute;n del calendario, asi como agregar y eliminar los d&iacute;as festivos que aparecen en el calendario.';

// Untranslated!
$helptxt['serversettings'] = '<b>Server Settings</b><br />
	Here you can perform the core configuration for your forum. This section includes the database and url settings, as well as other
	important configuration items such as mail settings and caching. Think carefully whenever editing these settings as an error may
	render the forum inaccessible';

$helptxt['topicSummaryPosts'] = 'Esto te permite especificar el n&uacute;mero de mensajes anteriores mostrados en el sumario de temas, en la pantalla de responder.';
$helptxt['enableAllMessages'] = 'Establece esto al n&uacute;mero <em>m&aacute;ximo</em> de mensajes que un tema puede tener para mostrar el enlace <i>todos</i>.  Si estableces este valor menor al de &quot;M&aacute;ximo n&uacute;mero de mensajes a mostrar en una p&aacute;gina de Tema&quot; lo unico que conseguir&aacute;s es que nunca se muestre, y si lo estableces muy alto, puede alentar tu foro.';
$helptxt['enableStickyTopics'] = 'Mensajes fijados son temas que permanecen en la parte superior de la lista de mensajes.
	Son usados generalmente para mensajes importantes. Solamente moderadores y administradores pueden fijar un tema.';
$helptxt['allow_guestAccess'] = 'El desseleccionar esta opci&oacute;n limitar&aacute; a los visitantes a hacer solamente las funciones mas b&aacute;sicas - ingresar, registrarse, recordar contrase&ntilde;a, etc - en tu foro.  Esto NO es lo mismo que deshabilitar el acceso de los visitantes a los foros.';
$helptxt['userLanguage'] = 'Al activar esta opci&oacute;n, los usuarios pueden seleccionar el archivo de idioma que usar&aacute;n.
	Esto no afectar&aacute; la selecci&oacute;n predeterminada.';
// Untranslated! - change hits into page views
$helptxt['trackStats'] = 'Estad&iacute;sticas:<br />Esto permite a los usuarios ver los &uacute;ltimos mensajes, y los temas mas populares de tu foro de mensajes.
	Tambi&eacute;n muestra varias estad&iacute;sticas, como el m&aacute;ximo de usuarios conectados al mismo tiempo, nuevos usuarios, y nuevos temas.<hr />
	Hits:<br />Agrega otra columna a la p&aacute;gina de estad&iacute;sticas con el numero de hits en tu foro.';
$helptxt['titlesEnable'] = 'Activando los T&iacute;tulos Personalizados le permitir&aacute; a los usuarios que cuenten con el permiso respectivo, el especificar un t&iacute;tulo especial por ellos mismos.
	&Eacute;ste se mostrar&aacute; debajo del nombre.<br /><i>ej.:</i><br />Omar<br />Saiya-jin';
$helptxt['topbottomEnable'] = 'Esto agregar&aacute; los botones ir arriba y abajo, para que los usuarios puedan ir a la parte superior e inferior de la p&aacute;gina sin
	hacer scroll.';
$helptxt['onlineEnable'] = 'Est mostr&aacute; una imagen indicando si el usuario est&aacute; conectado o no.';
$helptxt['todayMod'] = 'Esto mostrar&aacute; \'Hoy\' o \'Ayer\' en vez de la fecha.';
$helptxt['enablePreviousNext'] = 'Esto mostrar&aacute; una liga al tema anterior y al siguiente.';
$helptxt['pollMode'] = 'Esto especifica si las encuestas est&aacute;n activadas o no: Si las encuestas est&aacute;n desactivadas, cualquier encuesta ya existente ser&aacute; oculta
		del listado de temas. Puedes escoger el continuar mostrando los temas sin su encuesta asociada a ellos seleccionando
		&quot;Mostrar Encuestas existentes como Temas&quot;.Para seleccionar qui&eacute;n puede publicar encuestas, ver encuestas, y otras cosas, puedes
		permitir o restringir sus permisos. Recuerda esto si las encuestas no est&aacute;n funcionando.';
$helptxt['enableVBStyleLogin'] = 'Esto mostrar&aacute; un cuadro para ingresar tu usuario/contrase&ntilde;a en la parte inferior del foro de mensajes.';
$helptxt['enableCompressedOutput'] = 'Esta opci&oacute;n compactar&aacute; la salida para reducir el consumo de ancho de banda,
	pero necesita que zlib est&eacute; instalado en el servidor.';
$helptxt['databaseSession_enable'] = 'Esta opci&oacute;n hace uso de la base de datos para guardar informaci&oacute;n de sesiones - es mejor para servidores con la carga balanceada, pero ayuda con todos los problemas de timeout y puede hacer m&aacute;s r&aacute;pido al foro.  No funciona si session.auto_start est&aacute; activado.';
$helptxt['databaseSession_loose'] = 'Activando esta opci&oacute; decrementar&aacute; el ancho de banda que consume tu foro, y hace que al hacer <i>click</i> en atr&aacute;s no recargue la p&aacute;gina - lo malo de esto es que los (nuevos) iconos no se actualizar&aacute;n, entre otras cosas. (a menos que hagas <i>click</i> en esa p&aacute;gina eb vez de regresar a ella.)';
$helptxt['databaseSession_lifetime'] = 'Este es el n&uacute;mero de segundos que durar&aacute;n las sesiones despu&eacute;s que no hayan sido accesadas.  Si una sesi&oacute;n no es accesada por mucho tiempo, se dice que ha &quot;expirado&quot;.  Se recomienda cualquier valor arriba de 2400.';
$helptxt['enableErrorLogging'] = 'Esto registrar&aacute; (log) cualquier error, como un ingreso de usuario inv&aacute;lido, para que puedas ver que sali&oacute; mal.';
// Untranslated!
$helptxt['allow_disableAnnounce'] = 'This will allow users to opt out of notification of topics you announce by checking the &quot;announce topic&quot; checkbox when posting.';
$helptxt['disallow_sendBody'] = 'Esta opci&oacute;n elimina la posibilidad de recibir el texto de las respuestas y los mensajes en los emails de notificaci&oacute;n.<br /><br />Es com&uacute;n que los usuarios, por error, respondan a los emails de notificaci&oacute;n, lo que significa en la mayor&iacute;a de las veces que el webmaster recibe la respuesta.';
$helptxt['compactTopicPagesEnable'] = 'Esto mostrar&aacute; como se mostrar&aacute; la selecci&oacute;n de las p&aacute;ginas.<br /><i>Ej.:</i>
		&quot;3&quot; para mostrar: 1 ... 4 [5] 6 ... 9 <br />
		&quot;5&quot; para mostrar: 1 ... 3 4 [5] 6 7 ... 9';
$helptxt['timeLoadPageEnable'] = 'Esto mostrar&aacute; en la parte inferior del foro, el tiempo en segundos que SMF necesit&oacute; para crear la p&aacute;gina.';
$helptxt['removeNestedQuotes'] = 'Esto mostrar&aacute; solamente una cita del mensaje en cuesti&oacute;n, pero no cualquier otro que haya estado presente de otros mensajes.';
$helptxt['simpleSearch'] = 'Esto mostrar&aacute; una forma de b&uacute;squeda simple, con una liga a una forma para b&uacute;squedas avanzadas.';
$helptxt['max_image_width'] = 'Esto te permitir&aacute; establecer el m&aacute;ximo de una imagen publicada. Im&aacute;genes mas peque&ntilde;as que el m&aacute;ximo no son afectadas.';
$helptxt['mail_type'] = 'Esta opci&oacute;n te permite escoger entre usar las opciones por defecto de PHP, o sobreescribirlas con SMTP.  PHP no soporta el usar autentificaci&oacute;n con SMTP (que en la actualidad, muchos servidores lo requieren) asi que, de necesitarlo, selecciona SMTP.  Recuerda que SMTP puede ser m&aacute;s lento, y algunos servidores no toman nombres de usuarios y contrase&ntilde;as.<br /><br />No necesitas llenar los valores de SMTP, si esta opci&oacute;n est&aacute; utilizando los valores por defecto de PHP.';
$helptxt['attachment_manager_settings'] = 'Los archivos adjuntos son archivos que los usuarios pueden subir, y adjuntar a un mensaje.<br /><br />
		<b>Revisar la extensi&oacute;n</b>:<br /> &iquest;Deseas revisar la extensi&oacute;n de los archivos?<br />
		<b>Extensiones permitidas</b>:<br /> Puedes especificar las extensiones permitidas para los archivos adjuntos.<br />
		<b>Directorio</b>:<br /> La ruta a tu directorio de archivos adjuntos<br />(ejemplo: /home/sitios/tusitio/www/foro/attachments)<br />
		<b>Tama&ntilde;o m&aacute;ximo del directorio de archivos adjuntos</b> (en KB):<br /> Selecciona el tama&ntilde;o m&aacute;ximo que puede tener el directorio de archivos adjuntosque tan grande ser&aacute, incluyendo todos los archivos dentro de est&aacute;.<br />
		<b>Tama&ntilde;o m&aacute;ximo de todos los archivos adjuntos en el mensaje</b> (en KB):<br /> Selecciona el tama&ntilde;o m&aacute;ximo de todos los archivos adjuntos del mensaje.  Si es menor que el limite de cada archivo adjunto, &eacute;ste ser&aacute; el l&iacute;mite.<br />
		<b>Tama&ntilde;o m&aacute;ximo de cada archivo adjunto</b> (en KB):<br /> Selecciona el tama&ntilde;o m&aacute;ximo de cada archivo adjunto.<br />
		<b>N&uacute;mero m&aacute;ximo de archivos adjuntos por mensaje</b>:<br /> Selecciona el n&uacute;mero de archivos adjuntos que un usuario puede subir, por mensaje.<br />
		<b>Mostrar los archivos adjuntos como im&aacute;genes en los mensajes</b>:<br /> Si el archivo subido es una imagen, &eacute;sta se mostrar&aacute; debajo del mensaje.<br />
		<b>Ajustar el tama&ntilde;o de las im&aacute;genes cuando se muestren debajo de los mensajes</b>:<br /> Si la opci&oacute;n anterior fue seleccionada, esto guardar&aacute; un attachment separado (mas peque&ntilde;o) para el thumbnail para ahorrar ancho de banda.<br />
		<b>Ancho y Alto m&aacute;ximos de los thumbnails</b>:<br /> Usado solamente con la opci&oacute;n &quot;Ajustar el tama&ntilde;o de las im&aacute;genes cuando se muestren debajo de los mensajes&quot;, el ancho y alto m&aacute;ximos al que se le reducir&aacute;n el tama&ntilde;o a las im&aacute;gen. Se les cambiar&acute; el tama&ntilde;o proporcionalmente.';
$helptxt['karmaMode'] = 'Karma es una funci&oacute;n que muestra la popularidad de un usuario Los usuarios, si tienen el permiso correspondiente, pueden
		\'aplaudir\' or \'castigar\' a otros usuarios, que es como su popularidad es calculada. Puedes cambiar el
		n&uacute;mero de mensajes necesarios para tener &quot;karma&quot;, el tiempo entre castigos o aplausos, y si los administradores
		tienen que esperar este tiempo tambi&eacute;n.<br /><br />El que grupos de usuarios puedan castigar a otros se contola a trav&eacute;s
		de un permiso. Si tienes problemas al tratar de hacer funcionar esta opci&oacute;n para todo mundo, deber&iacute;as revisar nuevamente tus permisos.';
$helptxt['cal_enabled'] = 'El calendario puede ser usado para mostrar cumplea&ntilde;os, o momentos importantes en tu foro.<br /><br />
		<b>Mostrar d&iacute;as como enlaces a \'Publicar evento\'</b>:<br />Esto le permite a los usuarios publicar eventos para ese d&iacute;a, cuando ellos hagan <i>click en esa fecha</i><br />
		<b>Mostrar n&uacute;meros de semana</b>:<br />Mostrar cual semana del a&ntilde;o es.<br />
		<b>M&aacute;ximo de d&iacute;as adelantados en el &iacute;ndice del foro</b>:<br />Si le pones 7, todos los eventos de la pr&oacute;xima semana se mostrar&aacute;n.<br />
		<b>Mostrar d&iacute;as festivos en el &iacute;ndice del foro</b>:<br />Muestra los dias festivos del d&iacute;a de hoy en una barra del calendario en el &iacute;ndice del foro.<br />
		<b>Mostrar cumplea&ntilde;os en el &iacute;ndice del foro</b>:<br />Muestra los cumplea&ntilde;os del d&iacute;a de hoy en una barra del calendario en el &iacute;ndice del foro.<br />
		<b>Mostrar eventos en el &iacute;ndice del foro</b>:<br />Muestra los eventos del d&iacute;a de hoy en una barra del calendario en el &iacute;ndice del foro.<br />
		<b>Foro default donde se publicar&aacute;n</b>:<br />&iquest;Cu&aacute;l es el foro de default en el que se publicar&aacute;n los eventos?<br />
		<b>A&ntilde;o m&iacute;nimo</b>:<br />Selecciona el &quot;primer&quot; a&ntilde;o en la lista del calendario.<br />
		<b>A&ntilde;o m&aacute;ximo</b>:<br />Selecciona el &quot;&uacute;ltimo&quot; a&ntilde;o en la lista del calendario<br />
		<b>Color para los cumplea&ntilde;os</b>:<br />Selecciona el color del texto cuando se muestren cumplea&ntilde;os<br />
		<b>Color para los eventos</b>:<br />Selecciona el color del texto cuando se muestren eventos<br />
		<b>Color para d&iacute;as festivos</b>:<br />Selecciona el color del texto cuando se muestren d&iacute;as festivos<br />
		<b>Permitir que los eventos se extiendan varios d&iacute;as</b>:<br />Seleccionalo para permitir que los eventos se expandan m&uacute;ltiples d&iacute;as.<br />
		<b>N&uacute;mero m&aacute;ximo de d&iacute;as que un evento puede expandirse</b>:<br />Selecciona el m&aacute;ximo n&uacute;mero de d&iacute;as que un evento puede expandirse.<br /><br />
		Recuerda que el uso del calendario (publicar eventos, ver eventos, etc.) est&aacute; controlado por los permisos especificados en la pantalla de permisos.';
$helptxt['localCookies'] = 'SMF usa cookies para guardar informaci&oacute;n al ingresar, en la computadora del usuario.
	Las cookies pueden guardarse globalmente (<i>tusitio.com</i>) o localmente (<i>tusitio.com/ruta/al/foro</i>).<br />
	Selecciona esta opci&oacute;n si estas teniendo problemas con usarios que est&aacute;n siento sacados de tu foro de mensajes automaticamente.<hr />
	Cookoes almacenadas globalmente son menos seguras cuando se usan en un servidor web compartido (como Tripod).<hr />
	Cookies locales no funcionan afuera del directorio del foro, asi que si tu foro est&aacute; almacenado en <i>www.tusitio.com/foro</i>, p&aacute;ginas como <i>www.tusitio.com/index.php</i> no pueden accesar la informaci&oacute;n de la cuenta.
	Especialmente cuando se usa SSI.php, se recomienda el uso de cookies globales.';
$helptxt['enableBBC'] = 'El seleccionar esta opci&oacute;n le permitir&aacute; a tus usuarios el poder utilizar Bulletin Board Code (BBC) en el foro, permitiendoles darle formato a sus mensajes con im&aacute;genes, estilos de texto, y m&aacute;s.';
$helptxt['time_offset'] = 'No todos los administradores de los foros necesitan que el foro use la misma zona horaria que el servidor en el que est&aacute; hospedado. Usa esta opci&oacute;n para especificar una diferencia horaria (en horas) en la que el foro debe operar, comparada con la hora del servidor. Son permitidos valores negativos y decimales.';
$helptxt['spamWaitTime'] = 'Aqu&iacute; puedes seleccionar el tiempo de debe transcurrir entre publicaci&oacute;n de mensajes. Esto puede utilizarse para evitar que las personas hagan spam en tu foro, al limitarles qu&eacute; tan seguido pueden publicar mensajes.';

$helptxt['enablePostHTML'] = 'Esto permitir&aacute; el publicar mensajes tags b&aacute;sicos de HTML:
	&lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;pre&gt;, &lt;blockquote&gt;, &lt;img src=&quot;&quot; /&gt;, &lt;a href=&quot;&quot;&gt;, y &lt;br /&gt;.';

$helptxt['themes'] = 'Aqu&iacute; puedes escoger si el usuario puede seleccionar temas, qu&eacute; tema ser&aacute; usado por los invitados,
	entre varias opciones. Haz <i>click</i> en cualquiera de los temas de la derecha para cambiar su configuraci&oacute;n.';
$helptxt['theme_install'] = 'Esto te permite instalar nuevos temas.  Puedes hacerlo desde un directorio previamente creado, subiendo el archivo para el tema, o copiando el tema de default.<br /><br />Toma en cuenta que el archivo o directorio debe tener el archivo de definici&oacute;n <tt>theme_info.xml</tt>.';
$helptxt['enableEmbeddedFlash'] = 'Esta opci&oacute;n le permitir&aacute; a tus usuarios usar Flash dentro de sus mensajes,
	como si fueran im&aacute;genes.  Esto es un posible riesgo de seguridad, aunque pocos han podido explorarlo.
	&iexcl;USALO BAJO TU PROPIO RIESGO!';
// !!! Add more information about how to use them here.
$helptxt['xmlnews_enable'] = 'Permite hacer una liga a las <a href="%s?action=.xml;sa=news">Noticas Recientes</a>
	y datos similares.  Se recomienda que limites el tama&ntilde;o de los mensajes/noticias porque cuando los datos rss se muestran
	en algunos clientes como Trillian, se trunca la informaci&oacute;n.';
$helptxt['hotTopicPosts'] = 'Cambia el n&uacute;mero de mensajes en un tema necesarios para alcanzar el estado de &quot;caliente&quot; o
	&quot;muy caliente&quot;.';
$helptxt['globalCookies'] = 'Permite el uso de cookies independientes de subdominio.  Por ejemplo, si...<br />
	Tu sitio est&aacute; en http://www.simplemachines.org/,<br />
	Y tu foro est&aacute; en http://foro.simplemachines.org/,<br />
	Usando esta modificaci&oacute;n, te permitir&aacute; accesar las cookies del foro en tu sitio.';
$helptxt['securityDisable'] = 'Esto <i>desactiva</i> la revisi&oacute;n adicional de contrase&ntilde;a para acceder la secci&oacute;n de administraci&oacute;n. &iexcl;NO es recomendable!';
$helptxt['securityDisable_why'] = 'Esta es tu contrase&ntilde;a actual. (la misma que usas para ingresar.)<br /><br />El que tengas que escribirla ayuda a asegurarnos que realmente desees realizar la tarea administrativa que est&eacute;s realizando,y que eres <b>t&uacute;</b> realmente.';
$helptxt['emailmembers'] = 'En este mensaje puedes usar algunas &quot;variables&quot;.  &Eacute;stas son:<br />
	{\$board_url} - El URL de tu foro.<br />
	{\$current_time} - La hora actual.<br />
	{\$member.email} - El correo electronico del usuario destino.<br />
	{\$member.link} - La liga del usuario destino.<br />
	{\$member.id} - El ID del usuario destino.<br />
	{\$member.name} - El nombre del usuario destino.  (mayor personalizaci&oacute;n)<br />
	{\$latest_member.link} - Liga al &uacute;ltimo usuario registrado.<br />
	{\$latest_member.id} - El ID del &uacute;ltimo usuario registrado.<br />
	{\$latest_member.name} - El nombre del &uacute;ltimo usuario registrado.';
$helptxt['attachmentEncryptFilenames'] = 'Encriptar los nombres de los attachments te permite tener m&aacute;s de un archivo subido como attachment
	con el mismo nombre. Para mayor seguridad usa archivos .php para bajar los archivos adjuntos.  Sin embargo, hace m&aacute;s dif&iacute;cil reconstruir
	la base de datos si algo dr&aacute;stico sucede.';

$helptxt['failed_login_threshold'] = 'Especifica el n&uacute;mero de intentos fallidos de ingreso, antes de redireccionarlos a la pantalla de recordatorio de contrase&ntilde;as.';
$helptxt['oldTopicDays'] = 'Si esta opci&oacute;n est&aacute; activada se le mostrar&aacute; al usuario una advertencia cuando intente responder a un tema que no ha tenido nuevas respuestas por el tiempo especificado, en d&iacute;as, en esta opci&oacute;n. Pon 0 para desactivar esta funci&oacute;n.';
$helptxt['edit_wait_time'] = 'N&uacute;mero de segundos que deben transcurrir despu&eacute;s de la publicaci&oacute;n de un mensaje, para que se registre la fecha de la &uacute;ltima modificaci&oacute;n.';
$helptxt['edit_disable_time'] = 'N&uacute;mero de minutos transcurridos permitidos antes de que un usuario no pueda continuar editando un mensaje que ellos han publicado. Pon 0 para desactivarlo. <br /><br /><i>Note: Esto no tendr&aacute; efecto en los usuarios que tengan el permiso para editar los mensajes de otros usuarios.</i>';
$helptxt['enableSpellChecking'] = 'Activar la revisi&oacute;n de ortograf&iacute;a. DEBES tener la librer&iacute;a pspell instalada en tu servidor y configurado PHP para la utilice. Tu servidor ' . (function_exists('pspell_new') == 1 ? 'SI' : 'NO') . ' parece que tenga esta opci&oacute;n configurada.';
// Untranslated!
$helptxt['disable_wysiwyg'] = 'This setting disallows all users from using the WYSIWYG (&quot;What You See Is What You Get&quot;) editor on the post page.';
$helptxt['lastActive'] = 'Especifica el n&uacute;mero de minutos en los que, antes de ese tiempo, un usuario se sigue mostrando activo en el &iacute;ndice del foro. El default son 15 minutos.';

// Untranslated!
$helptxt['customoptions'] = 'This section defines the options that a user may choose from a drop down list. There are a few key points to note in this section:
	<ul>
		<li><b>Default Option:</b> Whichever option box has the &quot;radio button&quot; next to it selected will be the default selection for the user when they enter their profile.</li>
		<li><b>Removing Options:</b> To remove an option simply empty the text box for that option - all users with that selected will have their option cleared.</li>
		<li><b>Reordering Options:</b> You can reorder the options by moving text around between the boxes. However - an important note - you must make sure you do <b>not</b> change the text when reordering options as otherwise user data will be lost.</li>
	</ul>';

$helptxt['autoOptDatabase'] = 'Esta opci&oacute;n optimiza automaticamente la base de datos cada X d&iacute;as.  Especifica 1 para realizar una optimizaci&oacute;n diaria.  Asimismo, puedes especificar un m&aacute;ximo n&uacute;mero de usuarios en l&iacute;nea, para que no sobrecargues tu servidor o incomodes a muchos usuarios.';
$helptxt['autoFixDatabase'] = 'Esto arreglar&aacute; autom&aacute;ticamente tablas en la base de datos con problemas, y continuar&aacute; como si nada hubiera sucedido.  Esto puede ser &uacute;til, ya que la &uacute;nica manera de arreglar este tipo de problemas, es REPARANDO la tabla, y tu foro no estar&aacute; ca&iacute;do hasta que te des cuenta.  Se te enviar&aacute; un email cuando esto suceda.';

$helptxt['enableParticipation'] = 'Esto muestra un peque&ntilde;o icono en los temas en que el usuario ha publicado mensajes.';

$helptxt['db_persist'] = 'Mantiene la conexi&oacute;n activa para incrementar el rendimiento.  Si tu NO est&aacute;s en un servidor dedicado, esto puede causarte problemas con tu proveedor de hospedaje.';
// Untranslated!
$helptxt['ssi_db_user'] = 'Optional setting to use a different database user and password when you are using SSI.php.';

$helptxt['queryless_urls'] = 'Esto cambia el formato de los URLs un poco, para que sean m&aacute;s agradables para los servicios de b&uacute;squeda (google, por ejemplo).  Estos URLs se ver&aacute;n como, por ejemplo: index.php/action_profile/u_1.';
// Untranslated!
$helptxt['countChildPosts'] = 'Checking this option will mean that posts and topics in a board\'s child board will count toward its totals on the index page.<br /><br />This will make things notably slower, but means that a parent with no posts in it won\'t show \'0\'.';
$helptxt['fixLongWords'] = 'Esta opci&oacute;n divide las palabras que sean mas largas de cierta longitud, en partes, para que no destruyan la apariencia del foro. (en lo posible...)';
$helptxt['allow_ignore_boards'] = 'Checking this option will allow users to select boards they wish to ignore.';

$helptxt['who_enabled'] = 'Esta opci&oacute;n te permite activar o desactivar la posibilidad de que los usuarios vean qui&eacute;n est&aacute; en linea navegando el foro, as&iacute; como lo que est&aacute;n haciendo.';

$helptxt['recycle_enable'] = '&quot;Recicla&quot; temas y mensajes eliminados al foro especificado.';

// Untranslated!
$helptxt['enableReportPM'] = 'This option allows your users to report personal messages they receive to the administration team. This may be useful in helping to track down any abuse of the personal messaging system.';
$helptxt['max_pm_recipients'] = 'This option allows you to set the maximum amount of recipients allowed in a single personal message sent by a forum member. This may be used to help stop spam abuse of the PM system. Note that users with permission to send newsletters are exempt from this restriction. Set to zero for no limit.';
$helptxt['pm_posts_verification'] = 'This setting will force users to enter a code shown on a verification image each time they are sending a personal message. Only users with a post count below the number set will need to enter the code - this should help combat automated spamming scripts.';
$helptxt['pm_posts_per_hour'] = 'This will limit the number of personal messages which may be sent by a user in a one hour period. This does not affect admins or moderators.';

$helptxt['default_personalText'] = 'Establece el texto por defecto que tendr&aacute;n los usuarios, como su &quot;texto personal.&quot;';

$helptxt['modlog_enabled'] = 'Guardar logs de todas las acciones de los moderadores.';

$helptxt['guest_hideContacts'] = 'Si esta opci&oacute;n est&aacute; seleccionada las direcciones de email y los detalles de los mensajeros (ICQ, Y!, MSN)
	de todos tus usuarios se le ocultar&aacute;n a los visitantes de tu foro';

$helptxt['registration_method'] = 'Esta opci&oacute;n determina que m&eacute;todo de registro es usada para las personas que deseen unirse a tu foro. Puedes seleccionarlo entre:<br /><br />
	<ul>
		<li>
			<b>Registro Desactivado:</b><br />
				Desactiva el proceso de registro, con este m&eacute;todo nadie puede registrarse en tu foro.<br />
		</li><li>
			<b>Registro Inmediato</b><br />
				Los nuevos usuarios pueden ingresar y publicar inmediatamente despues de registrarse en tu foro.<br />
		</li><li>
			<b>Activaci&oacute;n de Usuario</b><br />
				Cuando esta opci&oacute;n esta activada cualquier usuario que se registre en tu foro tendr&aacute; una liga de activaci&oacute; que se le enviar&aacute; por email que tendr&aacute;n que visitar antes que puedan convertirse usuarios v&aacute;lidos<br />
		</li><li>
			<b>Aprobaci&#243;n de Usuarios</b><br />
				Esta opci&oacute;n har&aacute; que todos los nuevos usuarios que se registren en tu foro necesiten ser aprobados por un administrador para que se puedan volver usuarios v&aacute;lidos.
		</li>
	</ul>';
$helptxt['send_validation_onChange'] = 'Cuando esta opci&oacute;n est&aacute; seleccionada todos los usuarios que cambien su direcci&oacute;n de email en su perfil tendr&aacute;n que reactivar sus cuenta desde el email enviado a la nueva direcci&oacute;n';
$helptxt['send_welcomeEmail'] = 'Cuando esta opci&oacute;n est&aacute; seleccionada a todos los nuevos usuarios se les enviar&aacute; un email de bienvenida a tu foro';
// Untranslated!
$helptxt['password_strength'] = 'This setting determines the strength required for passwords selected by your forum users. The more &quot;strong&quot; the password, the harder it should be to compromise uesr accounts.
	The possible settings are:
	<ul>
		<li><b>Low:</b> The password must be at least four characters long.</li>
		<li><b>Medium:</b> The password must be at least eight characters long, and can not be part of a users name or email address.</li>
		<li><b>High:</b> As for medium, except the password must also contain a mixture of upper and lower case letters, and at least one number.</li>
	</ul>';

$helptxt['coppaAge'] = 'El valor especificado en este cuadro determinar&aacute; la edad m&iacute;nima que los nuevos usuarios deben tener para que se les conceda acceso inmediato a los foros.
	Durante el proceso de registro se les pedir&aacute; que confirmen si son mayores a esa edad, y de no serlo, puede o neg&aacute;rsele su solicitud, o suspendarla esperando por la aprobaci&oacute;n de los padres - dependiendo del tipo de restricci&oacute;n escogida.
	Si se pone 0 en este valor, entonces todas las restricciones de edad se ignorar&aacute;n.';
$helptxt['coppaType'] = 'Si las restricciones de edad est&aacute;n activas, entonces este valor determinar&aacute; qu&eacute; pasar&aacute; cuando un usuario m&aacute;s joven de la edad m&iacute;nima intenta registrarse en tu foro. Hay dos posibilidades:
	<ul>
		<li>
			<b>Rechazar su solicitud de registro:</b><br />
				A cualquier nuevo usuario que no cumpla con la edad m&iacute;nima se le rechazar&aacute;a su solicitud de registro inmediatamente.<br />
		</li><li>
			<b>Requerir aprovaci&oacute;n del Padre o Tutor</b><br />
				A cualquier nuevo usuario que no cumpla con la edad m&iacute;nima su cuentra se marcar&aacute; como esperando autorizaci&oacute;n, y se le mostrar&aacute; una forma en la que sus padres o tutores deben dar el permiso para que se convierta en un usuario del foro.
				A ellos tambi&eacute;n se les mostrar&aacute; una forma con los datos de contacto que se especificaron en la pantalla de configuraci&oacute;n, para que puedan enviar la forma al administrador por correo o fax.
		</li>
	</ul>';
$helptxt['coppaPost'] = 'Los cuadros de contacto son requeridos para que las formas que otorgan el permiso a los usuarios por debajo de la edad m&iacute;nima pueda ser enviada al administador del foro. Estos detalles ser&aacute;n mostrados a todos los usuarios debajo de la edad m&iacute;nima, y son necesarios para la aprobaci&oacute;n del padre o tutor. Por lo menos se debe proveer una direcci&oacute;n postal o un n&uacute;mero de fax.';

$helptxt['allow_hideOnline'] = 'Cuando esta opci&oacute;n est&aacute; seleccionada todos los usuarios podr&aacuten ocultarle a los dem&aacute;s usuarios si est&aacute;n conectados (excepto a los administradores). Si est&aacute; desactivado, solamente los usuarios que pueden moderar el foro pueden ocultar su presencia. Es importante mencionar que deshabilitando esta opci&oacute;n no cambia el estado de ning&uacute;n usuario existente - simplemente les impide ocultarse en el futuro.';
$helptxt['allow_hideEmail'] = 'Cuando esta opci&oacute;n est&aacute; seleccionada los usuarios pueden escoger ocultar su direcci&oacute;n email de otros usuarios. Sin embargo, los administradores siempre pueden ver la direcci&oacute;n email de cualquier usuario.';

$helptxt['latest_support'] = 'Este panel te muestra algunos de problemas y preguntas m&aacute;s comunes de la configuraci&oacute;n de tu servidor. No te preocupes, esta informaci&oacute;n no se registra en ning&uacute;n momento.<br /><br />Si permanece como &quot;Obteniendo informaci&oacute;n de soporte...&quot;, tu computadora muy probablemente no se puede conectar a <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_packages'] = 'Aqu&iacute; puedes ver algunos de los m&aacute;s populares mods, as&iacute; como algunos paquetes o mods aleatorios, con instalaciones r&aacute;pidas y sencillas.<br /><br />Si esta secci&oacute;n no se puede mostrar, probablemente tu computadora no se puede conectar a <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_themes'] = 'Esta &aacute;rea muestra algunos de los &uacute;ltimos y m&aacute;s populares temas de <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.  Puede que no se muestre correctamente si tu computadora no puede encontrar <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';

$helptxt['secret_why_blank'] = 'Por tu seguridad, la respuesta a tu pregunta (as&iacute; como tu contrase&ntilde;a) est&aacute; encriptada de una manera en la que SMF puede decirte solamente si est&aacute; correcta, as&iacute;, jam&aacute;s podr&aacute; decirte (&iexcl;o a alguien m&aacute;s, que es lo importante!) cual es tu respuesta o tu contrase&ntilde;a.';
$helptxt['moderator_why_missing'] = 'Debido a que la moderaci&oacute;n se realiza en cada foro, debes hacer a un usuario moderador desde la <a href="javascript:window.open(\'%s?action=admin;area=manageboards\'); self.close();">interface de manejo de foros</a>.';

$helptxt['permissions'] = 'A trav&eacute;s de los permisos les permites o impides a los grupos hacer cosas espec&iacute;ficas.<br /><br />Puedes modificar varios foros al mismo tiempo usando las casillas, o busca en los permisos por un grupo espec&iacute;fico al hacer click en \'Modificar.\'';
$helptxt['permissions_board'] = 'Si un foro se especifica como \'Global,\' significa que el foro no tendr&aacute; permisos especiales.  \'Local\' significa que tendr&aacute; sus propios permisos - separados de los globales.  Esto te permite tener un foro que tiene m&aacute;s (o menos) permisos que otro, sin que sea necesario que los especifiques para cada uno de los foros.';
$helptxt['permissions_quickgroups'] = 'Estos te permiten usar la configuraci&oacute;n &quot;default&quot; de permisos - est&aacute;ndar significa \'nada especial\', restrictivo significa \'como visitante\', moderador significa \'que un moderador tiene\', y por &uacute;ltimo \'mantenimiento\' significa permisos muy cercanos a los de un administrador.';
// Untranslated!
$helptxt['permissions_deny'] = 'Denying permissions can be useful when you want take away permission from certain members. You can add a membergroup with a \'deny\'-permission to the members you wish to deny a permission.<br /><br />Use with care, a denied permission will stay denied no matter what other membergroups the member is in.';
$helptxt['permissions_postgroups'] = 'Enabling permissions for post count based groups will allow you to attribute permissions to members that have posted a certain amount of messages. The permissions of the post count based groups are <em>added</em> to the permissions of the regular membergroups.';
$helptxt['membergroup_guests'] = 'The Guests membergroup are all users that are not logged in.';
$helptxt['membergroup_regular_members'] = 'The Regular Members are all members that are logged in, but that have no primary membergroup assigned.';
$helptxt['membergroup_administrator'] = 'The administrator can, per definition, do anything and see any board. There are no permission settings for the administrator.';
$helptxt['membergroup_moderator'] = 'The Moderator membergroup is a special membergroup. Permissions and settings assigned to this group apply to moderators but only <em>on the boards they moderate</em>. Outside these boards they\'re just like any other member.';
$helptxt['membergroups'] = 'En SMF hay dos tipos de grupos a los que tus usuarios pueden pertenecer. Estos son:
	<ul>
		<li><b>Grupos Regulares:</b> Un grupo regular es un grupo en el que los usuarios no se les ingresa autom&aacute;ticamente. Para ingresar a un usuario al grupo simplemente ve a su perfil y haz <i>click</i> en &quot;Configuraci&oacute;n de la cuenta&quot;. Ah&iacute; puedes asignarle todos los grupos regulares a los que deseas que pertenezca.</li>
		<li><b>Grupos de Mensajes:</b> A diferencia de los grupos regulares, este tipo de grupos no pueden ser asignados. En vez de eso, los usuarios son asignados autom&aacute;ticamente a un grupo, cuando alcanzan el m&iacute;nimo de mensajes publicados necesarios para pertenecer a dicho grupo.</li>
	</ul>';

$helptxt['calendar_how_edit'] = 'Puedes editar esos eventos haciendo click en el asterisco rojo (*) al lado de sus nombres.';

$helptxt['maintenance_general'] = 'Desde aqu&iacute;, puedes optimizar todas tus tablas (&iexcl;hacerlas m&aacute;s peque&ntilde;as y m&aacute;s r&aacute;pidas!), aseg&uacute;rate de tener las versiones m&aacute;s nuevas, encontrar cualquier error que pueda estar afectando tu foro, recontar totales, y vaciar los logs.<br /><br />Los dos &uacute;ltimos puedes ignorarlos a menos que algo est&eacute; fallando, pero no afecta en nada hacerlo.';
$helptxt['maintenance_backup'] = 'Esta &aacute;rea te permite guardar una copia de todos los mensajes, configuraciones, usuarios, y otra informaci&oacute;n de tu foro en un archivo muy grande.<br /><br />Es recomendado hacerlo a menudo, probablemente semanalmete, por seguridad.';
$helptxt['maintenance_rot'] = 'Esto te permite <b>completa</b> e <b>irrevocablemente</b> borrar temas viejos. Es recomendable que intentes hacer un respaldo primero, en caso que accidentalmente borres algo que no deseabas.<br /><br />Usa esta opci&oacute;n con cuidado.';

$helptxt['avatar_server_stored'] = 'Esto le permite a tus usuarios seleccionar avatares almacenados en tu servidor. Est&aacute;n, generalmente, en el mismo lugar que SMF dentro del directorio avatars.<br />Como tip, si creas subdirectorios en ese directorio, puedes hacer &quot;categor&iacute;as&quot; de avatares.';
$helptxt['avatar_external'] = 'Con esta opci&oacute;n activada, tus usuarios pueden especificar un URL para sus propios avatares. La desventaja de esto es, en algunos casos, puedan utilizar im&aacute;genes muy grandes o que no desees que utilicen en tu foro.';
$helptxt['avatar_download_external'] = 'Con esta opci&oacute;n activada, se descargar&aacute; el avatar del URL especificado por el usuario. Si el proceso fue realizado con &eacute;xito, el avatar se tratar&aacute; como un avatar subido por el usuario.';
$helptxt['avatar_upload'] = 'Esta opci&oacute;n es similar a la de &quot;Permitir a los usuarios seleccionar un avatar externo&quot;, Excepto que tienes un mejor control de los avatares, es m&aacute;s f&aacute;cil cambiarles el tama&ntilde;o, y tus usuarios no necesitan tener un lugar donde hospedar sus avatares..<br /><br />Sin embargo, la desventaja es que puede consumir mucho espacio en tu servidor.';
$helptxt['avatar_download_png'] = 'Los PNG son m&aacute;s grandes, pero ofrecen una mejor calidad de compresi&oacute;n. De no estar seleccionado, se usar&aacute;a en su lugar JPEG - que generalmente es de menor tama&ntilde;o, pero con menor calidad.';

$helptxt['disableHostnameLookup'] = 'Esto desactiva la b&uacute;squeda de nombres de servidores, que en algunos servidores es muy lento.  Es importante mencionar que &eacute;sto har&aacute; la restricci&oacute;n de accesos menos eficaz.';

$helptxt['search_weight_frequency'] = 'Los factores de peso se usan para determinar la relevancia de los resultados de la b&uacute;squeda. Cambia estos factores de peso para que coincida con las cosas que son importantes especificamente para tu foro. Por ejemplo, un foro de un sitio de noticias, puede necesitar un valor relativamente alto para \'antig&uuml;edad del &uacuteltimo mensaje que coincidi&oacute;\'. Todos los valores son relativos, relacionados entre s&iacute;, y deben ser enteros positivos.<br /><br />Este factor cuenta la cantidad de mensajes que coincidieron y los divide por el n&uacute;mero total de mensajes dentro del tema.';
$helptxt['search_weight_age'] = 'Los factores de peso se usan para determinar la relevancia de los resultados de la b&uacute;squeda. Cambia estos factores de peso para que coincida con las cosas que son importantes especificamente para tu foro. Por ejemplo, un foro de un sitio de noticias, puede necesitar un valor relativamente alto para \'antig&uuml;edad del &uacuteltimo mensaje que coincidi&oacute;\'. Todos los valores son relativos, relacionados entre s&iacute;, y deben ser enteros positivos.<br /><br />Este factor califica la antig&uuml;edad del &uacute;ltimo mensaje dentro de un tema. Entre m&aacute;s reciente es, mayor su puntuaci&oacute;n.';
$helptxt['search_weight_length'] = 'Los factores de peso se usan para determinar la relevancia de los resultados de la b&uacute;squeda. Cambia estos factores de peso para que coincida con las cosas que son importantes especificamente para tu foro. Por ejemplo, un foro de un sitio de noticias, puede necesitar un valor relativamente alto para \'antig&uuml;edad del &uacuteltimo mensaje que coincidi&oacute;\'. Todos los valores son relativos, relacionados entre s&iacute;, y deben ser enteros positivos.<br /><br />Este factor est&aacute; basado en el tama&ntilde;o del tema. Entre m&aacute;s mensajes tenga un tema, mayor su puntuaci&oacute;n.';
$helptxt['search_weight_subject'] = 'Los factores de peso se usan para determinar la relevancia de los resultados de la b&uacute;squeda. Cambia estos factores de peso para que coincida con las cosas que son importantes especificamente para tu foro. Por ejemplo, un foro de un sitio de noticias, puede necesitar un valor relativamente alto para \'antig&uuml;edad del &uacuteltimo mensaje que coincidi&oacute;\'. Todos los valores son relativos, relacionados entre s&iacute;, y deben ser enteros positivos.<br /><br />Este factor revisa si se encuentran coincidencias en el asunto del tema.';
$helptxt['search_weight_first_message'] = 'Los factores de peso se usan para determinar la relevancia de los resultados de la b&uacute;squeda. Cambia estos factores de peso para que coincida con las cosas que son importantes especificamente para tu foro. Por ejemplo, un foro de un sitio de noticias, puede necesitar un valor relativamente alto para \'antig&uuml;edad del &uacuteltimo mensaje que coincidi&oacute;\'. Todos los valores son relativos, relacionados entre s&iacute;, y deben ser enteros positivos.<br /><br />Este factor revisa si se encuentran coincidencias en el primer mensaje del tema.';
// Untranslated!
$helptxt['search_weight_sticky'] = 'Weight factors are used to determine the relevancy of a search result. Change these weight factors to match the things that are specifically important for your forum. For instance, a forum of a news site, might want a relatively high value for \'age of last matching message\'. All values are relative in relation to each other and should be positive integers.<br /><br />This factor looks whether a topic is sticky and increases the relevancy score if it is.';
$helptxt['search'] = 'Aqu&iacute; puedes ajustar la configuraci&oacute;n de la funci&oacute;n de b&uacute;squeda.';
// Untranslated!
$helptxt['search_why_use_index'] = 'A search index can greatly improve the performance of searches on your forum. Especially when the number of messages on a forum grows bigger, searching without an index can take a long time and increase the pressure on your database. If your forum is bigger than 50.000 messages, you might want to consider creating a search index to assure peak performance of your forum.<br /><br />Note that a search index can take up quite some space. A fulltext index is a built-in index of MySQL. It\'s relatively compact (approximately the same size as the message table), but a lot of words aren\'t indexed and it can, in some search queries, turn out to be very slow. The custom index is often bigger (depending on your configuration it can be up to 3 times the size of the messages table) but it\'s performance is better than fulltext and relatively stable.';

$helptxt['see_admin_ip'] = 'A los administradores y moderadores se les muestran las IPs para facilitar la moderaci&oacute;n y para hacer m&aacute;s f&aacute;cil el rastreo de personas indeseables. Recuerda que las direcciones IP no siempre son identificatorias, y que las IPs cambian peri&oacute;dicamente.<br /><br />Tambi&eacute;n se les permite a los usuarios ver su propia IP.';
$helptxt['see_member_ip'] = 'Tu direcci&oacute;n IP es mostrada solamente a t&iacute; y a los moderadores. Recuerda que esta informaci&oacute;n no es identificatoria y muchas IPs cambian peri&oacute;dicamente.<br /><br />No puedes ver las IPs de otros usuarios y ellos no pueden ver la tuya.';

// Untranslated!
$helptxt['ban_cannot_post'] = 'The \'cannot post\' restriction turns the forum into read-only mode for the banned user. The user cannot create new topics, or reply to existing ones, send personal messages or vote in polls. The banned user can however still read personal messages and topics.<br /><br />A warning message is shown to the users that are banned this way.';

// Untranslated!
$helptxt['posts_and_topics'] = '
	<ul>
		<li>
			<b>Post Settings</b><br />
			Modify the settings related to the posting of messages and the way messages are shown. You can also enable the spell check here.
		</li><li>
			<b>Bulletin Board Code</b><br />
			Enable the code that show forum messages in the right layout. Also adjust which codes are allowed and which aren\'t.
		</li><li>
			<b>Censored Words</b>
			In order to keep the language on your forum under control, you can censor certain words. This function allows you to convert forbidden words into innocent versions.
		</li><li>
			<b>Topic Settings</b>
			Modify the settings related to topics. The number of topics per page, whether stickey topics are enabled or not, the number of messages needed for a topic to be hot, etc.
		</li>
	</ul>';

?>