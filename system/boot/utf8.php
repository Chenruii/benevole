<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB_ROOT') or exit;

// vérifie si PCRE à été complilé avec le support UTF-8
$UTF8_ar = array();
if ( preg_match('/^.{1}$/u', "ñ", $UTF8_ar) != 1 )
	trigger_error('PCRE is not compiled with UTF-8 support', E_USER_ERROR);
unset($UTF8_ar);

// répertoire UTF8
if ( !defined('UTF8') )
	define('UTF8', MIB_PATH_SYS.'utf8');

if ( extension_loaded('mbstring') && !defined('UTF8_USE_MBSTRING') && !defined('UTF8_USE_NATIVE') )
	define('UTF8_USE_MBSTRING', true);
else
	define('UTF8_USE_NATIVE', true);

// utf8_strpos() et utf8_strrpos() on besoin de utf8_bad_strip() pour échapper les caractères invalides.
// Mbstring ne le fait pas alors que l'implémentation Native si.
require UTF8.'/utils/bad.php';

if ( defined('UTF8_USE_MBSTRING') ) {

	if ( ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING )
		trigger_error('String functions are overloaded by mbstring', E_USER_ERROR);

	mb_language('uni');
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8');

	if ( !defined('UTF8_CORE') )
		require UTF8.'/mbstring/core.php';
}
elseif ( defined('UTF8_USE_NATIVE') ) {
	if ( !defined('UTF8_CORE') ) {
		require UTF8.'/utils/unicode.php';
		require UTF8.'/native/core.php';
	}
}

// charge certaines implémentations Native utf8 necessaire au système
require UTF8.'/trim.php';
require UTF8.'/substr_replace.php';
require UTF8.'/ucwords.php';

/**
 * Supprime tous les "bad" caractères (les caractères qui ne s'affiche pas correctement sur une page, ou sont
 * invisibles, etc)
 * Voir: http://kb.mozillazine.org/Network.IDN.blacklist_chars
 */
function utf8_remove_bad_characters($array) {
	static $bad_utf8_chars;

	if ( !isset($bad_utf8_chars) ) {
		$bad_utf8_chars = array(
			"\0"		=> '',		// NULL						0000	*
			"\xcc\xb7"	=> '',		// COMBINING SHORT SOLIDUS OVERLAY		0337	*
			"\xcc\xb8"	=> '',		// COMBINING LONG SOLIDUS OVERLAY		0338	*
			"\xe1\x85\x9F"	=> '',		// HANGUL CHOSEONG FILLER			115F	*
			"\xe1\x85\xA0"	=> '',		// HANGUL JUNGSEONG FILLER			1160	*
			"\xe2\x80\x8b"	=> '',		// ZERO WIDTH SPACE				200B	*
			"\xe2\x80\x8c"	=> '',		// ZERO WIDTH NON-JOINER			200C
			"\xe2\x80\x8d"	=> '',		// ZERO WIDTH JOINER				200D
			"\xe2\x80\x8e"	=> '',		// LEFT-TO-RIGHT MARK				200E
			"\xe2\x80\x8f"	=> '',		// RIGHT-TO-LEFT MARK				200F
			"\xe2\x80\xaa"	=> '',		// LEFT-TO-RIGHT EMBEDDING			202A
			"\xe2\x80\xab"	=> '',		// RIGHT-TO-LEFT EMBEDDING			202B
			"\xe2\x80\xac"	=> '', 		// POP DIRECTIONAL FORMATTING			202C
			"\xe2\x80\xad"	=> '',		// LEFT-TO-RIGHT OVERRIDE			202D
			"\xe2\x80\xae"	=> '',		// RIGHT-TO-LEFT OVERRIDE			202E
			"\xe2\x80\xaf"	=> '',		// NARROW NO-BREAK SPACE			202F	*
			"\xe2\x81\x9f"	=> '',		// MEDIUM MATHEMATICAL SPACE			205F	*
			"\xe2\x81\xa0"	=> '',		// WORD JOINER					2060
			"\xe3\x85\xa4"	=> '',		// HANGUL FILLER				3164	*
			"\xef\xbb\xbf"	=> '',		// ZERO WIDTH NO-BREAK SPACE			FEFF
			"\xef\xbe\xa0"	=> '',		// HALFWIDTH HANGUL FILLER			FFA0	*
			"\xef\xbf\xb9"	=> '',		// INTERLINEAR ANNOTATION ANCHOR		FFF9	*
			"\xef\xbf\xba"	=> '',		// INTERLINEAR ANNOTATION SEPARATOR		FFFA	*
			"\xef\xbf\xbb"	=> '',		// INTERLINEAR ANNOTATION TERMINATOR		FFFB	*
			"\xef\xbf\xbc"	=> '',		// OBJECT REPLACEMENT CHARACTER			FFFC	*
			"\xef\xbf\xbd"	=> '',		// REPLACEMENT CHARACTER			FFFD	*
			"\xc2\xad"	=> '-',		// SOFT HYPHEN					00AD
			"\xE2\x80\x9C"	=> '"',		// LEFT DOUBLE QUOTATION MARK			201C
			"\xE2\x80\x9D"	=> '"',		// RIGHT DOUBLE QUOTATION MARK			201D
			"\xE2\x80\x98"	=> '\'',	// LEFT SINGLE QUOTATION MARK			2018
			"\xE2\x80\x99"	=> '\'',	// RIGHT SINGLE QUOTATION MARK			2019
			"\xe2\x80\x80"	=> ' ',		// EN QUAD					2000	*
			"\xe2\x80\x81"	=> ' ',		// EM QUAD					2001	*
			"\xe2\x80\x82"	=> ' ',		// EN SPACE					2002	*
			"\xe2\x80\x83"	=> ' ',		// EM SPACE					2003	*
			"\xe2\x80\x84"	=> ' ',		// THREE-PER-EM SPACE				2004	*
			"\xe2\x80\x85"	=> ' ',		// FOUR-PER-EM SPACE				2005	*
			"\xe2\x80\x86"	=> ' ',		// SIX-PER-EM SPACE				2006	*
			"\xe2\x80\x87"	=> ' ',		// FIGURE SPACE					2007	*
			"\xe2\x80\x88"	=> ' ',		// PUNCTUATION SPACE				2008	*
			"\xe2\x80\x89"	=> ' ',		// THIN SPACE					2009	*
			"\xe2\x80\x8a"	=> ' ',		// HAIR SPACE					200A	*
			"\xE3\x80\x80"	=> ' ',		// IDEOGRAPHIC SPACE				3000	*
		);
	}

	if ( is_array($array) )
		return array_map('utf8_remove_bad_characters', $array);

	$array = utf8_bad_strip($array);

	$array = str_replace(array_keys($bad_utf8_chars), array_values($bad_utf8_chars), $array);

	return $array;
}

/**
 * Supprime tous les "bad" caractères contenu dans les variables globales d'envois
 */
function mib_remove_bad_characters() {
	$_GET = utf8_remove_bad_characters($_GET);
	$_POST = utf8_remove_bad_characters($_POST);
	$_COOKIE = utf8_remove_bad_characters($_COOKIE);
	$_REQUEST = utf8_remove_bad_characters($_REQUEST);
}
mib_remove_bad_characters();
