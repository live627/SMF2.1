<?php
/**
* @version $Id: turkish.php,v 1.2 2006-08-22 00:33:07 orstio Exp $
* @package smf-bridge
* @copyright (C) 2004 mamboworld.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author mic (developer@mamboworld.net) www.mamboworld.net
* Mambo is Free Software
*/

/** SMF Bridge Ýçin Türkçe Dil Dosyasý - Forsakenlad */

class smfbLanguage
{
	//genel ayarlar
	var $SMFB_ISO = 'iso-8859-9';
	var $SMFB_DATE_FORMAT_LC = '%d %B %Y %A'; // varsayýlan zaman formatý
	var $SMFB_DATE_FOMAT_SHORT = ' %d.%m.%Y'; // kýsa zaman formatý
	var $SMFB_DATE_FORMAT_LONG = '%d %B %Y %H:%M'; // saati içeren zaman formatý

	//yönetim bölümü
	var $SMFB_A_CONF_HEADER = 'SMF-Seçenekleri';
	var $SMFB_A_CONF_CONFIG_IS = 'Seçenekler dosyasý:';
	var $SMFB_A_CONF_WRITEABLE = 'Yazýlabilir';
	var $SMBF_A_CONF_NOT_WRITEABLE = 'Yazýlamaz';
	var $SMBF_A_CONF_TAB1 = 'Ayarlar';
	var $SMBF_A_CONF_PATH = 'SMF\'nin bulunduðu klasör';
	var $SMBF_A_CONF_PATH_BUTTON = 'Otomatik olarak yarat';
	var $SMBF_A_CONF_DB_SMF_NAME = 'SMF veritabaný adý';
	var $SMBF_A_CONF_DB_SMF_PREFIX = 'SMF tablo öneki';
	var $SMBF_A_CONF_DB_MOS_PREFIX = 'Mambo tablo öneki';
	var $SMBF_A_CONF_WRAPPED_TITLE = 'SMF Forum entegrasyonu';
	var $SMBF_A_CONF_WRAPPED = 'Tümleþik';
	var $SMBF_A_CONF_UNWRAPPED = 'Ayrý';
	var $SMBF_A_CONF_SETT_SAVED = 'Seçenekler Kaydedildi';
	var $SMBF_A_CONF_UPGRADE_SUCCESS = 'Köprü Baþarýyla Güncellendi';

	// yardým
	var $SMBF_A_CONF_PATH_TT_HEADER = 'SMF\'nin yüklü olduðu klasör';
	var $SMBF_A_CONF_PATH_TT = 'Buraya SMF\'nin bulunduðu TAM YERÝ yazýn. ÝPUCU: eðer yer hakkýnda emin deðilseniz * Otomatik olarak yarat * a týklayýnýz';
	var $SMBF_A_CONF_DB_SMF_NAME_TT = 'SMF\'nin yüklü olduðu veritabanýnýn adý.  Eðer Mambo ve SMF ayný veritabanýnda yüklü ise boþ býrakýn';
	var $SMBF_A_CONF_DB_SMF_PREFIX_TT = 'SMF tablolarýnýn öneki, varsayýlan ek: * smf_ *';
	var $SMBF_A_CONF_DB_MOS_PREFIX_TT = 'Mambo tablolarýnýn öneki, varsayýlan ek: * mos_ *, UYARI: burada gireceðiniz önek, Mambo\'yu yüklerken girdiðiniz önek ile ayný olmalýdýr! ÝPUCU: Eðer emin deðilseniz * Mambo tablolarýnýn önekini ekle * tuþuna týklayýnýz';
	var $SMFB_A_CONF_MOS_PREFIX_BUTTON = 'Mambo tablolarýnýn önekini ekle';
	var $SMBF_A_CONF_WRAPPED_TITLE_TT = 'Buradaki en önemli ayar, o yüzden iþaretlediðiniz seçeneðe dikkat edin! Burada SMF\'nin, Mambo\'nun içinde mi (Tümleþik) yoksa ayrý olarak mý çalýþacaðýný (Ayrý) seçeceksiniz. Burada iþaretleyeceðiniz seçeneðe göre * readme * dosyasýnda bulabileceðiniz bazý deðiþiklikler yapmanýz gerekecek.';

	// hata mesajlarý
	var $SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE = 'Seçenekler dosyasý yazýlabilir deðil!';

	// frontend (user)
	// header

	// general
}

?>