<?php
/**
* @version $Id: hungarian.php,v 1.2 2006-09-16 16:11:35 orstio Exp $
* @package smf-bridge
* @copyright (C) 2004 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** Hungarian language file for SMF Bridge */

class smfbLanguage
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-2';
	var $SMFB_DATE_FORMAT_LC = '%Y. %B %d., %A'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %Y.%m.%d.'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%Y.%m.%d., %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF konfiguráció';
	var $SMFB_A_CONF_CONFIG_IS = 'A konfiguráció :';
	var $SMFB_A_CONF_WRITEABLE = 'Írható';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Nem írható';
	var $SMBF_A_CONF_TAB1 = 'Konfigurálás';
	var $SMBF_A_CONF_PATH = 'Az SMF útvonala (abszolút)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Az útvonal automatikus létrehozása';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Az SMF adatbázis neve';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'Az SMF adatbázis elõtagja';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'A Mambo/Joomla adatbázis elõtagja';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'Az SMF fórum integrálása';
	var $SMBF_A_CONF_WRAPPED = 'Beágyazott';
	var $SMBF_A_CONF_UNWRAPPED = 'Nem beágyazott';
	var $SMBF_A_CONF_SETT_SAVED = 'A beállítások mentése kész';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'A híd frissítése sikerült';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'Az SMF telepítési útvonala';
	var $SMBF_A_CONF_PATH_TT = 'Itt adhatja meg az SMF telepítésének ABSZOLÚT ÚTVONALÁT. TANÁCS: ha bizonytalan, akkor nyomja meg * Az útvonal automatikus létrehozása * gombot';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'Annak az adatbázisnak a neve, melybe telepítette az SMF fórumot.  Hagyja üresen, ha a Mambo/Joomla! és az SMF telepítése ugyanabba az adatbázisba történt';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Az SMF adatbázis elõtagja, normál esetben * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Az SMF adatbázis elõtagja, normál esetben * mos_ *, FIGYELEM! Az elõtagnak ugyanannak kell lennie, mint amit a Mambo/Joomla! telepítésben megadott! TANÁCS: Ha bizonytalan, akkor kattintson * A Mambo DB elõtag hozzáadása * gombra';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'A Mambo DB elõtag hozzáadása';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Ez - lehet - itt az egyik legfontosabb beállítás! Itt kell meghatároznia, hogy az SMF beépüljön-e (beágyazásra kerüljön) a Mamboba, vagy ne (beágyazatlan legyen). Ettõl a beállítástól függõen néhány olyan további beállítást kell megadnia, melyekrõl a * readme* fájlban olvashat';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Nem írható a konfigurációs fájl!';

	// frontend (user)
	//header

	// general
}

?>