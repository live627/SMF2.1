<?php
/**
* @version $Id: dutch.php,v 1.1 2005-11-01 16:27:16 compuart Exp $
* @package smf-bridge
* @copyright (C) 2005 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** Dutch language file for SMF Bridge by Puc_conDoin */

class smfbLanguage 
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF-Configuratie';
	var $SMFB_A_CONF_CONFIG_IS = 'Configuratie is :';
	var $SMFB_A_CONF_WRITEABLE = 'Schrijfbaar';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Niet schrijfbaar';
	var $SMBF_A_CONF_TAB1 = 'Configuratie';
	var $SMBF_A_CONF_PATH = 'Pad naar SMF (absoluut)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Creeër pad automatisch';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'SMF database prefix';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Mambo database prefix';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'SMF Forum integratie';
	var $SMBF_A_CONF_WRAPPED = 'Wrapped';
	var $SMBF_A_CONF_UNWRAPPED = 'Unwrapped';
	var $SMBF_A_CONF_SETT_SAVED = 'Intellingen opgeslagen';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Brug succesvol verbeterd';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF installatie pad';
	var $SMBF_A_CONF_PATH_TT = 'Definieer hier het ABSOLUTE PAD van je SMF installatie. HINT: Als je het niet zeker weet klik je op de knop * Creeër pad automatisch *';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Prefix voor de SMF database, meestal * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Prefix voor de MAMBO database, meestal * mos_ *, ATTENTIE: de prefix moet precies dezelfde zijn als die je gebruikt hebt bij je Mambo installatie! HINT: Als je het niet zeker weet klik je op de knop * Voeg Mambo DB prefix toe *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Voeg Mambo DB prefix toe';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Dit is - waarschijnlijk - een van de belangrijkste instellingen hier! Je moet hier aangeven of je SMF geïntegreerd met Mambo wilt hebben (wrapped) of niet (unwrapped). Afhankelijk van deze instelling zul je wat extra instellingen moeten definiëren, waarover je meer info kunt vinden in de * readme *';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Configuratie bestand niet schrijfbaar!';

	// frontend (user)
	//header

	// general
}

?>