<?php
/**
* @version $Id: french.php,v 1.1 2005-11-01 16:27:16 compuart Exp $
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
	var $SMFB_A_CONF_CONFIG_IS = 'Le Configuration est :';
	var $SMFB_A_CONF_WRITEABLE = 'A affichage';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Non à affichage';
	var $SMBF_A_CONF_TAB1 = 'Configuration';
	var $SMBF_A_CONF_PATH = 'Chemin à SMF (absolu)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Créer le chemin automatiquement';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Nom de DB de SMF';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'Préfixe de DB de SMF';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Préfixe de DB de Mambo';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'L\'integration de SMF';
	var $SMBF_A_CONF_WRAPPED = 'Enveloppé';
	var $SMBF_A_CONF_UNWRAPPED = 'Non emballé';
	var $SMBF_A_CONF_SETT_SAVED = 'Les arrangements ont économisé';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Pont amélioré avec succès';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF chemin d\'installation';
	var $SMBF_A_CONF_PATH_TT = 'Définissez ici le CHEMIN ABSOLU à votre installation de SMF CONSEIL : si vous êtes incertain, cliquez le bouton * créez le chemin automatiquement *';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'La nom pour la base de données de SMF.  Laissez le blanc si vous avez installé Mambo et SMF dans la même base de données';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Préfixe pour la base de données de SMF, normalement * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Préfixe pour la base de données de Mambo, normalement * mos_ *, ATTENTION : le préfixe doit être identique que vous avez défini dans votre installation de Mambo! CONSEIL : Si vous êtes clic incertain le bouton * ajoutez le préfixe de DB de mambo *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Ajoutez le préfixe de DB de Mambo';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'C\'est - peut-être - l\'un des arrangements les plus importants ici! Voici que vous devez définir si SMF sera intégré (enveloppé) dans le mambo ou pas (non emballé). Selon cet arrangement vous devez définir un certain arrangement additionnel qui peut être dedans lu * readme *';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Le dossier de configuration n\'est pas à affichage!';

	// frontend (user)
	//header

	// general
}

?>