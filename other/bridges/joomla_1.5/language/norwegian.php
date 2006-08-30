<?php
/**
* @version $Id: norwegian.php,v 1.2 2006-05-07 16:34:41 orstio Exp $
* @package smf-bridge
* @copyright (C) 2005 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** Norwegian language file for SMF Bridge, translated by Jørgen Bjørnes, http://www.inventorforum.net */

class smfbLanguage
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF-Konfigurasjon';
	var $SMFB_A_CONF_CONFIG_IS = 'Konfigurasjonen er :';
	var $SMFB_A_CONF_WRITEABLE = 'Skrivbar';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Ikke Skrivbar';
	var $SMBF_A_CONF_TAB1 = 'Konfigurasjon';
	var $SMBF_A_CONF_PATH = 'Sti til SMF (absolutt)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Finn Sti Automatisk';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'SMF database prefix';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Mambo database prefix';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'SMF Forum integrasjon';
	var $SMBF_A_CONF_WRAPPED = 'Wrapped';
	var $SMBF_A_CONF_UNWRAPPED = 'Unwrapped';
	var $SMBF_A_CONF_SETT_SAVED = 'Innstillinger lagret';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Bridge oppgradert uten problemer';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF installasjons-sti';
	var $SMBF_A_CONF_PATH_TT = 'Definer den ABSOLUTTE STIEN til SMF-installasjonen. HINT: hvis du er usikker, klikk på knappen * Finn Sti Automatisk *';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Prefix til SMF databasen, normalt * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Prefix til MAMBO databasen, normallt * mos_ *, MERK: prefiken må være den samme som du har definert i Mambo-installasjonen! HINT: hvis du er usikker, klikk på knappen * Legg til Mambo prefiks *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Legg til Mambo prefiks';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Dette er - kanskje - en av de viktigste innstillingene her! Her definerer du om SMF skal være wrapped inne i Mambo eller ikke (unwrapped). Avhengig av denne innstillingen må du definere noen ekstra innstillinger som du finner i * readme *';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Konfigurasjonsfil ikke skrivbar!';

	// frontend (user)
	//header

	// general
}

?>