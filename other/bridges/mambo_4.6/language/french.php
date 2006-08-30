<?php
/**
* @version $Id: french.php,v 1.2 2006-08-22 00:33:07 orstio Exp $
* @package smf-bridge
* @copyright (C) 2004-2006 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** French language file for SMF Bridge
* Corrected by NiLuJe (ninuje@gmail.com / www.ak-team.com)
*/

class smfbLanguage
{
	//common (to be used by all)
	var $SMFB_ISO = 'iso-8859-1';
	var $SMFB_DATE_FORMAT_LC = '%A, %d. %B %Y'; // use PHP strftime Format, more info at http://php.net
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // short date
	var $SMFB_DATE_FORMAT_LONG = '%d.%m.%Y %H:%M'; // use PHP strftime Format, more info at http://php.net

	//admin
	var $SMFB_A_CONF_HEADER = 'Configuration du Bridge SMF';
	var $SMFB_A_CONF_CONFIG_IS = 'Le fichier de configuration est :';
	var $SMFB_A_CONF_WRITEABLE = 'Modifiable';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Non modifiable';
	var $SMBF_A_CONF_TAB1 = 'Configuration';
	var $SMBF_A_CONF_PATH = 'Chemin vers SMF (absolu)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Cr�er le chemin automatiquement';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Nom de la base de donn�es de SMF';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'Pr�fixe des tables SMF';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Pr�fixe des tables Mambo/Joomla';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'Int�gration du forum SMF';
	var $SMBF_A_CONF_WRAPPED = 'Int�gr�';
	var $SMBF_A_CONF_UNWRAPPED = 'Pleine page';
	var $SMBF_A_CONF_SETT_SAVED = 'Les r�glages ont �t� enregistr�s';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Bridge mis � jour avec succ�s';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'Chemin vers l\'installation du forum SMF';
	var $SMBF_A_CONF_PATH_TT = 'D�finissez ici le CHEMIN ABSOLU vers votre installation de SMF. CONSEIL: Si vous n\'�tes pas s�r, cliquez sur le bouton * Cr�er le chemin automatiquement *';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'Le nom de la base de donn�es utilis�e par SMF. Laissez le vide si vous avez install� Mambo/Joomla et SMF dans la m�me base de donn�es';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Pr�fixe des tables SMF, normalement * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Pr�fixe des tables Mambo/Joomla, normalement * mos_ * pour Mambo, et * jos_ * pour Joomla, ATTENTION: Le pr�fixe doit �tre identique � celui d�fini pour votre installation de Mambo/Joomla! CONSEIL: Si vous n\'�tes pas s�r, cliquez sur le bouton * Ajouter le pr�fixe des tables Mambo/Joomla *';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Ajouter le pr�fixe des tables Mambo/Joomla';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'C\'est - peut-�tre - l\'un des r�glages les plus importants! Vous devez sp�cifier si SMF sera int�gr� (comme un composant classique) dans mambo/Joomla, ou s\'il sera affich� comme un SMF classique (Pleine page). Selon votre choix, il est possible que vous ayez � d�finir d\'autres r�glages, comme sp�cifi� dans le * readme *';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Le fichier de configuration n\'est pas modifiable!';

	// frontend (user)
	//header

	// general
}

?>