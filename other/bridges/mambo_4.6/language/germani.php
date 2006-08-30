<?php
/**
* @version $Id: germani.php,v 1.2 2006-08-22 00:33:07 orstio Exp $
* @package smf-bridge
* @copyright (C) 2004 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** German (informal) translation for SMF Bridge */

class smfbLanguage 
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF-Konfiguration';
	var $SMFB_A_CONF_CONFIG_IS = 'Konfiguration ist :';
	var $SMFB_A_CONF_WRITEABLE = 'Beschreibbar';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Nicht Beschreibbar';
	var $SMBF_A_CONF_TAB1 = 'Konfiguration';
	var $SMBF_A_CONF_PATH = 'Pfad zu SMF (absolut)'; // Path to SMF (NOT A URL!!)
	var $SMBF_A_CONF_PATH_BUTTON = 'Automatisch Pfad erstellen';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'SMF Datenbank Prefix'; // SMF database prefix
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Mambo Datenbank Prefix'; // Mambo database prefix
	var $SMBF_A_CONF_WRAPPED_TITLE = 'SMF Forum eingebunden'; // Forum wrapped or unwrapped?
	var $SMBF_A_CONF_WRAPPED = 'Integriert'; // Wrapped
	var $SMBF_A_CONF_UNWRAPPED = 'Nicht integriert'; // Unwrapped
	var $SMBF_A_CONF_SETT_SAVED = 'Einstellungen wurden gespeichert'; // Settings saved

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF Installationspfad';
	var $SMBF_A_CONF_PATH_TT = 'Hier den ABSOLUTEN PFAD zur SMF-Installation angeben. HINWEIS: wenn nicht klar ist welcher Pfad anzugeben ist, dann Button * Automatisch Pfad erstellen * dr&uumlcken';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Hier die Vorzeichen f&uuml;r die SMF-Datenbank angeben, normalerweise * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Hier die Vorzeichen f&uuml;r die Mambo-Datenbank angeben, normalerweise * mos_ *, ACHTUNG: die Vorzeichen m&uuml;ssen mit der bestehenden Mamboinstallation &uuml;bereinstimmen! HINWEIS: Durch Dr&uuml;cken des Buttons kann die bereits vorgegebene Mambodatenbankprefix &uuml;bernommen werden';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Mambo DB Prefix einf&uuml;gen';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Eine der wichtigsten Einstellungen &uuml;berhaupt! Hier wird bestimmt, ob SMF innerhalb (also wrapped) von Mambo eingebunden werden soll, oder nicht (unwrapped). Abh&auml;ngig davon sind etliche weitere Einstellungen welche in der * readme * nachgelesen werden k&ouml;nnen.';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Konfigurationsdatei nicht beschreibbar!'; // Config file not writeable!

	// frontend
	//header

	// general
}

?>