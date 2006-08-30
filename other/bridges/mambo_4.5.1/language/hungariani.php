<?php
/**
* @version $Id: hungariani.php,v 1.2 2006-08-26 15:39:43 orstio Exp $
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
	var $SMFB_A_CONF_HEADER = 'SMF konfigur�ci�';
	var $SMFB_A_CONF_CONFIG_IS = 'A konfigur�ci� :';
	var $SMFB_A_CONF_WRITEABLE = '�rhat�';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Nem �rhat�';
	var $SMBF_A_CONF_TAB1 = 'Konfigur�l�s';
	var $SMBF_A_CONF_PATH = 'Az SMF �tvonala (abszol�t)';
	var $SMBF_A_CONF_PATH_BUTTON = 'Az �tvonal automatikus l�trehoz�sa';
	var $SMBF_A_CONF_DB_SMF_NAME = 'Az SMF adatb�zis neve';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'Az SMF adatb�zis el�tagja';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'A Mambo/Joomla adatb�zis el�tagja';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'Az SMF f�rum integr�l�sa';
	var $SMBF_A_CONF_WRAPPED = 'Be�gyazott';
	var $SMBF_A_CONF_UNWRAPPED = 'Nem be�gyazott';
	var $SMBF_A_CONF_SETT_SAVED = 'A be�ll�t�sok ment�se k�sz';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'A h�d friss�t�se siker�lt';

	// tooltips
	var $SMBF_A_CONF_PATH_TT_HEADER = 'Az SMF telep�t�si �tvonala';
	var $SMBF_A_CONF_PATH_TT = 'Itt adhatod meg az SMF telep�t�s�nek ABSZOL�T �TVONAL�T. TAN�CS: ha bizonytalan vagy, akkor nyomd meg * Az �tvonal automatikus l�trehoz�sa * gombot';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'Annak az adatb�zisnak a neve, melybe telep�tetted az SMF f�rumot.  Hagyd �resen, ha a Mambo/Joomla! �s az SMF telep�t�se ugyanabba az adatb�zisba t�rt�nt';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'Az SMF adatb�zis el�tagja, norm�l esetben * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Az SMF adatb�zis el�tagja, norm�l esetben * mos_ *, FIGYELEM! Az el�tagnak ugyanannak kell lennie, mint amit a Mambo/Joomla! telep�t�sben megadt�l! TAN�CS: Ha bizonytalan vagy, akkor kattints * A Mambo DB el�tag hozz�ad�sa * gombra';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'A Mambo DB el�tag hozz�ad�sa';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Ez - lehet - itt az egyik legfontosabb be�ll�t�s! Itt kell meghat�roznod, hogy az SMF be�p�lj�n-e (be�gyaz�sra ker�lj�n) a Mamboba, vagy ne (be�gyazatlan legyen). Ett�l a be�ll�t�st�l f�gg�en n�h�ny olyan tov�bbi be�ll�t�st kell megadnod, melyekr�l a * readme* f�jlban olvashatsz';

	// errors/messages
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Nem �rhat� a konfigur�ci�s f�jl!';

	// frontend (user)
	//header

	// general
}

?>