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
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Non � affichage';
	var $SMBF_A_CONF_TAB1 = 'Configuration';
	var $SMBF_A_CONF_PATH = 'Chemin � SMF (absolu)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Cr�er le chemin automatiquement';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Nom de DB de SMF';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'Pr�fixe de DB de SMF';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Pr�fixe de DB de Mambo';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'L\'integration de SMF';
	var $SMBF_A_CONF_WRAPPED = 'Envelopp�';
	var $SMBF_A_CONF_UNWRAPPED = 'Non emball�';
	var $SMBF_A_CONF_SETT_SAVED = 'Les arrangements ont �conomis�';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Pont am�lior� avec succ�s';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF chemin d\'installation';
	var $SMBF_A_CONF_PATH_TT = 'D�finissez ici le CHEMIN ABSOLU � votre installation de SMF CONSEIL : si vous �tes incertain, cliquez le bouton * cr�ez le chemin automatiquement *';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'La nom pour la base de donn�es de SMF.  Laissez le blanc si vous avez install� Mambo et SMF dans la m�me base de donn�es';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Pr�fixe pour la base de donn�es de SMF, normalement * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Pr�fixe pour la base de donn�es de Mambo, normalement * mos_ *, ATTENTION : le pr�fixe doit �tre identique que vous avez d�fini dans votre installation de Mambo! CONSEIL : Si vous �tes clic incertain le bouton * ajoutez le pr�fixe de DB de mambo *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Ajoutez le pr�fixe de DB de Mambo';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'C\'est - peut-�tre - l\'un des arrangements les plus importants ici! Voici que vous devez d�finir si SMF sera int�gr� (envelopp�) dans le mambo ou pas (non emball�). Selon cet arrangement vous devez d�finir un certain arrangement additionnel qui peut �tre dedans lu * readme *';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Le dossier de configuration n\'est pas � affichage!';

	// frontend (user)
	//header

	// general
}

?>