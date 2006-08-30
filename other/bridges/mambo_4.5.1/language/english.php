<?php
/**
* @version $Id: english.php,v 1.1 2005-11-01 16:27:16 compuart Exp $
* @package smf-bridge
* @copyright (C) 2004 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** English language file for SMF Bridge */

class smfbLanguage
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; //Verwendet das PHP strftime Format
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'SMF-Configuration';
	var $SMFB_A_CONF_CONFIG_IS = 'Configuration is :';
	var $SMFB_A_CONF_WRITEABLE = 'Writeable';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Not writeable';
	var $SMBF_A_CONF_TAB1 = 'Configuration';
	var $SMBF_A_CONF_PATH = 'Path to SMF (absolut)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Create path automatically';
	var $SMBF_A_CONF_DB_SMF_NAME = 'SMF database name';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'SMF database prefix';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Mambo database prefix';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'SMF Forum integration';
	var $SMBF_A_CONF_WRAPPED = 'Wrapped';
	var $SMBF_A_CONF_UNWRAPPED = 'Unwrapped';
	var $SMBF_A_CONF_SETT_SAVED = 'Settings saved';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Bridge upgraded Successfully';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF installation path';
	var $SMBF_A_CONF_PATH_TT = 'Define here the ABSOLUTE PATH to your SMF installation. HINT: if you unsure click the button * Create path automatically *';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'The name of the database where you installed SMF.  Leave blank if you have installed Mambo and SMF in the same database';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Prefix for the SMF database, normally * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Prefix for the SMF database, normally * mos_ *, ATTENTION: the prefix must be the same as you have defined in your Mambo installation! HINT: If you are unsure click the button * Add Mambo DB prefix *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Add Mambo DB prefix';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'This is - maybe - one of the most important settings here! Here you have to define if SMF will be integrated (wrapped) within Mambo or not (unwrapped). Depending on this setting you have to define some additional setting which can be read in the * readme*';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Configuration file not writeable!';

	// frontend (user)
	//header

	// general
}

?>