<?php
/**
* @version $Id: italian.php,v 1.1.2.1 01/01/2006 14:18
* @package smf-bridge
* @copyright (C) 2004 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author clarensio
* Mambo and Joomla is Free Software
*/

/** linguaggio Italiano per SMF Bridge */

class smfbLanguage
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF-Configurazione';
	var $SMFB_A_CONF_CONFIG_IS = 'La configurazione &egrave;:';
	var $SMFB_A_CONF_WRITEABLE = 'Scrivibile';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Non scrivibile';
	var $SMBF_A_CONF_TAB1 = 'Configurazione';
	var $SMBF_A_CONF_PATH = 'Path per SMF (assoluta)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Creare path automaticamente';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Nome database SMF';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'prefix database SMF';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'prefix database Mambo/Joomla';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'Integrazione Forum SMF';
	var $SMBF_A_CONF_WRAPPED = 'Integrato';
	var $SMBF_A_CONF_UNWRAPPED = 'Non integrato';
	var $SMBF_A_CONF_SETT_SAVED = 'Impostazioni salvate';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Upgrade Bridge avvenuto con successo';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'Path di installazione di SMF';
	var $SMBF_A_CONF_PATH_TT = 'Definite qui ABSOLUTE PATH della vostra installazione SMF. CONSIGLIO: Se non siete certi, cliccare sul pulsante *Creare path automaticamente*';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'Il nome del database dove &egrave; installato SMF. Lasciare vuoto (blank) se avete installato SMF e Mambo/Joomla nello stesso database';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Prefix per il database SMF, normalmente * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Prefix per il database di Mambo/Joomla, normalmente * mos_/jos_ *, ATTENZIONE: il prefix deve essere lo stesso che &egrave; stato definito al momento della installazione di Mambo/Joomla! CONSIGLIO: Se non siete certi, cliccare sul pulsante *Aggiungi prefix DB Mambo/Joomla*';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Aggiungi prefix DB Mambo/Joomla';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Questo pu&ograve; rivelarsi - secondo me - uno dei parametri pi&ugrave importanti! Qui si potr&agrave; definire se SMF sar&agrave integrato (wrapped) dentro Mambo/Joomla o no (unwrapped). Dipende da questo settaggio ovvero da ulteriori settaggi da leggere nel file *readme*';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'File di configurazione non scrivibile!';

	// frontend (user)
	//header

	// general
}

?>