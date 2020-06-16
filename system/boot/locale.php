<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB_ROOT') or exit;

// force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

/**
 * Fonctions de Traduction
 */
function __($str) { global $MIB_LANG; return !empty($MIB_LANG[$str]) ? $MIB_LANG[$str] : $str; }
function _e($str) { echo __($str); } // Raccourcis
function __bo($str) { return __($str); } // Version BO
function _ebo($str) { echo __($str); } // Version BO

/**
 * Langues connues du système (mais pas forcément installées)
 */
function mib_language($iso = false, $return = false) {
	$languages_know = array(
		'aa' => 'Afaraf',
		'ab' => 'Аҧсуа',
		'ae' => 'avesta',
		'af' => 'Afrikaans',
		'ak' => 'Akan',
		'am' => 'አማርኛ',
		'an' => 'Aragonés',
		'ar' => '‫العربية',
		'as' => 'অসমীয়া',
		'av' => 'авар мацӀ',
		'ay' => 'aymar aru',
		'az' => 'azərbaycan dili',
		'ba' => 'башҡорт теле',
		'be' => 'Беларуская',
		'bg' => 'български език',
		'bh' => 'भोजपुरी',
		'bi' => 'Bislama',
		'bm' => 'bamanankan',
		'bn' => 'বাংলা',
		'bo' => 'བོད་ཡིག',
		'br' => 'brezhoneg',
		'bs' => 'bosanski jezik',
		'ca' => 'Català',
		'ce' => 'нохчийн мотт',
		'ch' => 'Chamoru',
		'co' => 'corsu',
		'cr' => 'ᓀᐦᐃᔭᐍᐏᐣ',
		'cs' => 'česky',
		'cu' => 'ѩзыкъ словѣньскъ',
		'cv' => 'чӑваш чӗлхи',
		'cy' => 'Cymraeg',
		'da' => 'dansk',
		'de' => 'Deutsch',
		'dv' => '‫ދިވެހި',
		'dz' => 'རྫོང་ཁ',
		'ee' => 'Ɛʋɛgbɛ',
		'el' => 'Ελληνικά',
		'en' => 'English',
		'eo' => 'Esperanto',
		'es' => 'Español',
		'et' => 'Eesti keel',
		'eu' => 'euskara',
		'fa' => '‫فارسی',
		'ff' => 'Fulfulde',
		'fi' => 'suomen kieli',
		'fj' => 'vosa Vakaviti',
		'fo' => 'Føroyskt',
		'fr' => 'Français',
		'fy' => 'Frysk',
		'ga' => 'Gaeilge',
		'gd' => 'Gàidhlig',
		'gl' => 'Galego',
		'gn' => "Avañe'ẽ",
		'gu' => 'ગુજરાતી',
		'gv' => 'Ghaelg',
		'ha' => '‫هَوُسَ',
		'he' => '‫עברית',
		'hi' => 'हिन्दी',
		'ho' => 'Hiri Motu',
		'hr' => 'Hrvatski',
		'ht' => 'Kreyòl ayisyen',
		'hu' => 'Magyar',
		'hy' => 'Հայերեն',
		'hz' => 'Otjiherero',
		'ia' => 'Interlingua',
		'id' => 'Bahasa Indonesia',
		'ie' => 'Interlingue',
		'ig' => 'Igbo',
		'ii' => 'ꆇꉙ',
		'ik' => 'Iñupiaq',
		'io' => 'Ido',
		'is' => 'Íslenska',
		'it' => 'Italiano',
		'iu' => 'ᐃᓄᒃᑎᑐᑦ',
		'ja' => '日本語',
		'jv' => 'basa Jawa',
		'ka' => 'ქართული',
		'kg' => 'KiKongo',
		'ki' => 'Gĩkũyũ',
		'kj' => 'Kuanyama',
		'kk' => 'Қазақ тілі',
		'kl' => 'kalaallisut',
		'km' => 'ភាសាខ្មែរ',
		'kn' => 'ಕನ್ನಡ',
		'ko' => '한국어',
		'kr' => 'Kanuri',
		'ks' => 'कश्मीरी',
		'ku' => 'Kurdî',
		'kv' => 'коми кыв',
		'kw' => 'Kernewek',
		'ky' => 'кыргыз тили',
		'la' => 'latine',
		'lb' => 'Lëtzebuergesch',
		'lg' => 'Luganda',
		'li' => 'Limburgs',
		'ln' => 'Lingála',
		'lo' => 'ພາສາລາວ',
		'lt' => 'lietuvių kalba',
		'lu' => 'Luba-Katanga',
		'lv' => 'latviešu valoda',
		'mg' => 'Malagasy fiteny',
		'mh' => 'Kajin M̧ajeļ',
		'mi' => 'te reo Māori',
		'mk' => 'македонски јазик',
		'ml' => 'മലയാളം',
		'mn' => 'Монгол',
		'mo' => 'Limba moldovenească',
		'mr' => 'मराठी',
		'ms' => 'bahasa Melayu',
		'mt' => 'Malti',
		'my' => 'ဗမာစာ',
		'na' => 'Ekakairũ Naoero',
		'nb' => 'Norsk bokmål',
		'nd' => 'isiNdebele',
		'ne' => 'नेपाली',
		'ng' => 'Owambo',
		'nl' => 'Nederlands',
		//'nl-be' => 'Nederlands (Belgium)',
		'nn' => 'Norsk nynorsk',
		'no' => 'Norsk',
		'nr' => 'Ndébélé',
		'nv' => 'Diné bizaad',
		'ny' => 'chiCheŵa',
		'oc' => 'Occitan',
		'oj' => 'ᐊᓂᔑᓈᐯᒧᐎᓐ',
		'om' => 'Afaan Oromoo',
		'or' => 'ଓଡ଼ିଆ',
		'os' => 'Ирон æвзаг',
		'pa' => 'ਪੰਜਾਬੀ',
		'pi' => 'पाऴि',
		'pl' => 'polski',
		'ps' => '‫پښتو',
		'pt' => 'Português',
		//'pt-br' => 'Português (Brasil)',
		'qu' => 'Runa Simi',
		'rm' => 'rumantsch grischun',
		'rn' => 'kiRundi',
		'ro' => 'română',
		'ru' => 'Русский',
		'rw' => 'Ikinyarwanda',
		'sa' => 'संस्कृतम्',
		'sc' => 'sardu',
		'sd' => 'सिन्धी',
		'se' => 'Davvisámegiella',
		'sg' => 'yângâ tî sängö',
		'sh' => 'Srpskohrvatski',
		'si' => 'සිංහල',
		'sk' => 'slovenčina',
		'sl' => 'slovenščina',
		'sm' => "gagana fa'a Samoa",
		'sn' => 'chiShona',
		'so' => 'Soomaaliga',
		'sq' => 'Shqip',
		'sr' => 'српски језик',
		'ss' => 'SiSwati',
		'st' => 'seSotho',
		'su' => 'Basa Sunda',
		'sv' => 'Svenska',
		'sw' => 'Kiswahili',
		'ta' => 'தமிழ்',
		'te' => 'తెలుగు',
		'tg' => 'тоҷикӣ',
		'th' => 'ไทย',
		'ti' => 'ትግርኛ',
		'tk' => 'Türkmen',
		'tl' => 'Tagalog',
		'tn' => 'seTswana',
		'to' => 'faka Tonga',
		'tr' => 'Türkçe',
		'ts' => 'xiTsonga',
		'tt' => 'татарча',
		'tw' => 'Twi',
		'ty' => 'Reo Mā`ohi',
		'ug' => 'Uyƣurqə',
		'uk' => 'Українська',
		'ur' => '‫اردو',
		'uz' => "O'zbek",
		've' => 'tshiVenḓa',
		'vi' => 'Tiếng Việt',
		'vo' => 'Volapük',
		'wa' => 'Walon',
		'wo' => 'Wollof',
		'xh' => 'isiXhosa',
		'yi' => '‫ייִדיש',
		'yo' => 'Yorùbá',
		'za' => 'Saɯ cueŋƅ',
		'zh' => '中文',
		//'zh-hk' => '中文 (香港)',
		//'zh-tw' => '中文 (臺灣)',
		'zu' => 'isiZulu'
	);

	asort($languages_know);

	if ( $iso ) {
		$iso = utf8_strtolower($iso);

		// si la langue existe
		if( isset($languages_know[$iso]) ) {
			if ( $return ) {
				$return = utf8_strtolower($return);

				if ( $return == 'iso' )
					return $iso;
				else if( $return == 'name' )
					return $languages_know[$iso];
				else if( $return == 'dir' ) {
					return preg_match('/^(ar|dv|fa|ha|he|ps|ur|yi)$/i',$iso) ? 'rtl' : 'ltr';
				}
				else
					return false;
			}
			// On renvoit toutes les infos sur la langue
			else {
				return array(
					'iso' => $iso,
					'name' => $languages_know[$iso],
					'dir' => preg_match('/^(ar|dv|fa|ha|he|ps|ur|yi)$/i',$iso) ? 'rtl' : 'ltr'
				);
			}
		}
		else
			return false;
	}
	else if ( $return )
		return false;

	return $languages_know;
}

/**
 * Charge un fichier de langue
 * 
 * @param string $location Location du fichier de langue
 * @param string $lang Langue à charger
 *
 * @info
 *	{system}
 *		{system}/locales/fr_{system}.po (cache_lang_{lang}_{system}.php)
 *		{system}/locales/fr_{system}_admin.po (cache_lang_{lang}_{system}_admin.php)
 *	{website}
 *		{website}/locales/fr_{website}.po (cache_lang_{lang}_{website}.php)
 *		{website}/locales/fr_{website}_admin.po (cache_lang_{lang}_{website}_admin.php)
 *	{website|system}plugins/{NomDuPlugin}
 *		website/plugins/{NomDuPlugin}/locales/fr_{NomDuPlugin}.po (cache_lang_{lang}_plugin_{NomDuPlugin}.php)
 *		website/plugins/{NomDuPlugin}/locales/fr_{NomDuPlugin}_admin.po (cache_lang_{lang}_plugin_{NomDuPlugin}_admin.php)
 *		ou si le plugin n'existe pas dans website, on test dans system
 *		system/plugins/{NomDuPlugin}/locales/fr_{NomDuPlugin}.po (cache_lang_{lang}_plugin_{NomDuPlugin}.php)
 *		system/plugins/{NomDuPlugin}/locales/fr_{NomDuPlugin}_admin.po (cache_lang_{lang}_plugin_{NomDuPlugin}_admin.php)
 * 
 * @example
 * 	mib_load_locale('system')
 * 		website/locales/fr_{system}.po (cache_lang_{lang}_{system}.php)
 * 		system/locales/fr_{system}.po (cache_lang_{lang}_{system}.php)
 * 	mib_load_locale('website')
 * 		website/locales/fr_{website}.po (cache_lang_{lang}_{website}.php)
 * 		system/locales/fr_{website}.po (cache_lang_{lang}_{website}.php)
 * 	mib_load_locale('plugins/NomDuPlugin')
 * 		website/plugins/{NomDuPlugin}/locales/{lang}_{NomDuPlugin}.po (cache_lang_{lang}_plugin_{NomDuPlugin}.php)
 * 		system/plugins/{NomDuPlugin}/locales/{lang}_{NomDuPlugin}.po (cache_lang_{lang}_plugin_{NomDuPlugin}.php)
 */
$MIB_LANG = array(); // On prépare un tableau dans lequel seront stocké toute les variables de langue
function mib_load_locale($location, $lang = false) {
	global $MIB_PAGE, $MIB_LANG;

	if(!$lang)
		$lang = ($MIB_PAGE['lang']) ? $MIB_PAGE['lang'] : MIB_LANG;
	$lang = strtolower($lang);

	if($location) {
		// Langue de plugin
		if(strpos($location, 'plugins/') !== false) {
			$type = '_plugin_';
			$filename = @next(explode('plugins/', $location));
			if(substr($filename, -6) == '_manage') // Fichier de langue admin d'un plugin
				$location = '/'.mib_trim(substr($location, 0, -6),'/').'/';
			else
				$location = '/'.mib_trim($location,'/').'/';
		}
		// Langue générale
		else {
			$type = '_';
			$filename = $location;
			$location = '/';
		}

		/*
			Charge la langue par defaut
		*/
		if(file_exists(MIB_ROOT.'website'.$location.'locales/'.MIB_LANG.'_'.$filename.'.po'))
			$loc[MIB_LANG]['po'] = MIB_ROOT.'website'.$location.'locales/'.MIB_LANG.'_'.$filename.'.po';
		else if(file_exists(MIB_ROOT.'system'.$location.'locales/'.MIB_LANG.'_'.$filename.'.po'))
			$loc[MIB_LANG]['po'] = MIB_ROOT.'system'.$location.'locales/'.MIB_LANG.'_'.$filename.'.po';

		if(isset($loc[MIB_LANG]['po'])) { // un fichier de langue existe
			$loc[MIB_LANG]['php'] = MIB_CACHE_DIR.'cache_lang_'.MIB_LANG.$type.$filename.'.php';
			$loc[MIB_LANG]['def'] = 'MIB_LOADED_LANG_'.MIB_LANG.$type.$filename;
		}

		/*
			Charge la langue demandée
		*/
		if(MIB_LANG != $lang) { // la langue demandé est différente de la langue par defaut
			if(file_exists(MIB_ROOT.'website'.$location.'locales/'.$lang.'_'.$filename.'.po'))
				$loc[$lang]['po'] = MIB_ROOT.'website'.$location.'locales/'.$lang.'_'.$filename.'.po';
			else if(file_exists(MIB_ROOT.'system'.$location.'locales/'.$lang.'_'.$filename.'.po'))
				$loc[$lang]['po'] = MIB_ROOT.'system'.$location.'locales/'.$lang.'_'.$filename.'.po';

			if(isset($loc[$lang]['po'])) { // un fichier de langue existe
				$loc[$lang]['php'] = MIB_CACHE_DIR.'cache_lang_'.$lang.$type.$filename.'.php';
				$loc[$lang]['def'] = 'MIB_LOADED_LANG_'.$lang.$type.$filename;
			}
		}

		/*
			Charge les langues trouvées
		*/
		if(isset($loc) && is_array($loc)) {
			foreach($loc as $cur_lang) {
				// Charge le cache de la langue
				if (!defined($cur_lang['def']) && file_exists($cur_lang['php']) && !defined('MIB_DEBUG'))
					@include $cur_lang['php'];

				// La langue n'a pas été chargée ou on est en debug, on génère le fichier cache
				if (!defined($cur_lang['def']) || defined('MIB_DEBUG')) {
					if (file_exists($cur_lang['po'])) { // Un fichier .po existe, on génère le nouveau cache
						if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS.'cache.php'; // Si les fonctions de cache n'ont pas été chargées
						mib_generate_lang_cache($cur_lang['po'], $cur_lang['php'], $cur_lang['def']);
						require $cur_lang['php'];
					}
				}
			}
		}
	}
	else
		return false;
}

/**
 * Renvoi la liste des pack de langue disponible
 * 
 * @return array
 */
function mib_locale_languages_list() {
	$languages_list = array();
	$languages_know = mib_language();
	$languages_po = mib_dir_get_contents(MIB_PATH_VAR.'/locales', 'file', 'po');

	foreach ( $languages_po as $file_name => $file_path ) {
		$locale = explode('_', $file_name, 2);
      
		// la langue est connue
		if ( array_key_exists($locale[0], $languages_know) )
			$languages_list[$locale[0]] = $languages_know[$locale[0]];
	}

	asort($languages_list);


	// positionne la langue par défaut en 1er
	unset($languages_list[MIB_LANG]);
	return array_merge(array(MIB_LANG=>$languages_know[MIB_LANG]), $languages_list);
}

/**
 * Est-ce que le site est multi-langue ?
 * 
 * @return {bool}
 */
function mib_locale_is_multilingual() {
	if ( count(mib_locale_languages_list()) > 1 )
		return true;
	else
		return false;
}