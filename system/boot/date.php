<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB_ROOT') or exit;

/**
 * Définit le timezone
 *
 * @param {string} $timezone
 *	timezone
 */
function mib_date_timezone_set($timezone = 'UTC') {
	if ( function_exists('date_default_timezone_set') )
		date_default_timezone_set($timezone);
	else if ( !ini_get('safe_mode') )
		putenv('TZ='.$timezone);
}
mib_date_timezone_set(); // définit le timezone par défaut pour prévenir des warning

/**
 * TimeZone par zones
 *
 * @return {array}
 */
function mib_date_timezones($return_by_zone = true) {
	$tz_zones = array();

	if ( class_exists('DateTimeZone') )
		$tz_identifiers = DateTimeZone::listIdentifiers();
	else { // PHP < 5.2
		$tz_identifiers = array(
			'Africa/Abidjan',
			'Africa/Accra',
			'Africa/Addis_Ababa',
			'Africa/Algiers',
			'Africa/Asmara',
			'Africa/Bamako',
			'Africa/Bangui',
			'Africa/Banjul',
			'Africa/Bissau',
			'Africa/Blantyre',
			'Africa/Brazzaville',
			'Africa/Bujumbura',
			'Africa/Cairo',
			'Africa/Casablanca',
			'Africa/Ceuta',
			'Africa/Conakry',
			'Africa/Dakar',
			'Africa/Dar_es_Salaam',
			'Africa/Djibouti',
			'Africa/Douala',
			'Africa/El_Aaiun',
			'Africa/Freetown',
			'Africa/Gaborone',
			'Africa/Harare',
			'Africa/Johannesburg',
			'Africa/Juba',
			'Africa/Kampala',
			'Africa/Khartoum',
			'Africa/Kigali',
			'Africa/Kinshasa',
			'Africa/Lagos',
			'Africa/Libreville',
			'Africa/Lome',
			'Africa/Luanda',
			'Africa/Lubumbashi',
			'Africa/Lusaka',
			'Africa/Malabo',
			'Africa/Maputo',
			'Africa/Maseru',
			'Africa/Mbabane',
			'Africa/Mogadishu',
			'Africa/Monrovia',
			'Africa/Nairobi',
			'Africa/Ndjamena',
			'Africa/Niamey',
			'Africa/Nouakchott',
			'Africa/Ouagadougou',
			'Africa/Porto-Novo',
			'Africa/Sao_Tome',
			'Africa/Tripoli',
			'Africa/Tunis',
			'Africa/Windhoek',
			'America/Adak',
			'America/Anchorage',
			'America/Anguilla',
			'America/Antigua',
			'America/Araguaina',
			'America/Argentina/Buenos_Aires',
			'America/Argentina/Catamarca',
			'America/Argentina/Cordoba',
			'America/Argentina/Jujuy',
			'America/Argentina/La_Rioja',
			'America/Argentina/Mendoza',
			'America/Argentina/Rio_Gallegos',
			'America/Argentina/Salta',
			'America/Argentina/San_Juan',
			'America/Argentina/San_Luis',
			'America/Argentina/Tucuman',
			'America/Argentina/Ushuaia',
			'America/Aruba',
			'America/Asuncion',
			'America/Atikokan',
			'America/Bahia',
			'America/Bahia_Banderas',
			'America/Barbados',
			'America/Belem',
			'America/Belize',
			'America/Blanc-Sablon',
			'America/Boa_Vista',
			'America/Bogota',
			'America/Boise',
			'America/Cambridge_Bay',
			'America/Campo_Grande',
			'America/Cancun',
			'America/Caracas',
			'America/Cayenne',
			'America/Cayman',
			'America/Chicago',
			'America/Chihuahua',
			'America/Costa_Rica',
			'America/Creston',
			'America/Cuiaba',
			'America/Curacao',
			'America/Danmarkshavn',
			'America/Dawson',
			'America/Dawson_Creek',
			'America/Denver',
			'America/Detroit',
			'America/Dominica',
			'America/Edmonton',
			'America/Eirunepe',
			'America/El_Salvador',
			'America/Fortaleza',
			'America/Glace_Bay',
			'America/Godthab',
			'America/Goose_Bay',
			'America/Grand_Turk',
			'America/Grenada',
			'America/Guadeloupe',
			'America/Guatemala',
			'America/Guayaquil',
			'America/Guyana',
			'America/Halifax',
			'America/Havana',
			'America/Hermosillo',
			'America/Indiana/Indianapolis',
			'America/Indiana/Knox',
			'America/Indiana/Marengo',
			'America/Indiana/Petersburg',
			'America/Indiana/Tell_City',
			'America/Indiana/Vevay',
			'America/Indiana/Vincennes',
			'America/Indiana/Winamac',
			'America/Inuvik',
			'America/Iqaluit',
			'America/Jamaica',
			'America/Juneau',
			'America/Kentucky/Louisville',
			'America/Kentucky/Monticello',
			'America/Kralendijk',
			'America/La_Paz',
			'America/Lima',
			'America/Los_Angeles',
			'America/Lower_Princes',
			'America/Maceio',
			'America/Managua',
			'America/Manaus',
			'America/Marigot',
			'America/Martinique',
			'America/Matamoros',
			'America/Mazatlan',
			'America/Menominee',
			'America/Merida',
			'America/Metlakatla',
			'America/Mexico_City',
			'America/Miquelon',
			'America/Moncton',
			'America/Monterrey',
			'America/Montevideo',
			'America/Montreal',
			'America/Montserrat',
			'America/Nassau',
			'America/New_York',
			'America/Nipigon',
			'America/Nome',
			'America/Noronha',
			'America/North_Dakota/Beulah',
			'America/North_Dakota/Center',
			'America/North_Dakota/New_Salem',
			'America/Ojinaga',
			'America/Panama',
			'America/Pangnirtung',
			'America/Paramaribo',
			'America/Phoenix',
			'America/Port-au-Prince',
			'America/Port_of_Spain',
			'America/Porto_Velho',
			'America/Puerto_Rico',
			'America/Rainy_River',
			'America/Rankin_Inlet',
			'America/Recife',
			'America/Regina',
			'America/Resolute',
			'America/Rio_Branco',
			'America/Santa_Isabel',
			'America/Santarem',
			'America/Santiago',
			'America/Santo_Domingo',
			'America/Sao_Paulo',
			'America/Scoresbysund',
			'America/Shiprock',
			'America/Sitka',
			'America/St_Barthelemy',
			'America/St_Johns',
			'America/St_Kitts',
			'America/St_Lucia',
			'America/St_Thomas',
			'America/St_Vincent',
			'America/Swift_Current',
			'America/Tegucigalpa',
			'America/Thule',
			'America/Thunder_Bay',
			'America/Tijuana',
			'America/Toronto',
			'America/Tortola',
			'America/Vancouver',
			'America/Whitehorse',
			'America/Winnipeg',
			'America/Yakutat',
			'America/Yellowknife',
			'Antarctica/Casey',
			'Antarctica/Davis',
			'Antarctica/DumontDUrville',
			'Antarctica/Macquarie',
			'Antarctica/Mawson',
			'Antarctica/McMurdo',
			'Antarctica/Palmer',
			'Antarctica/Rothera',
			'Antarctica/South_Pole',
			'Antarctica/Syowa',
			'Antarctica/Vostok',
			'Arctic/Longyearbyen',
			'Asia/Aden',
			'Asia/Almaty',
			'Asia/Amman',
			'Asia/Anadyr',
			'Asia/Aqtau',
			'Asia/Aqtobe',
			'Asia/Ashgabat',
			'Asia/Baghdad',
			'Asia/Bahrain',
			'Asia/Baku',
			'Asia/Bangkok',
			'Asia/Beirut',
			'Asia/Bishkek',
			'Asia/Brunei',
			'Asia/Choibalsan',
			'Asia/Chongqing',
			'Asia/Colombo',
			'Asia/Damascus',
			'Asia/Dhaka',
			'Asia/Dili',
			'Asia/Dubai',
			'Asia/Dushanbe',
			'Asia/Gaza',
			'Asia/Harbin',
			'Asia/Hebron',
			'Asia/Ho_Chi_Minh',
			'Asia/Hong_Kong',
			'Asia/Hovd',
			'Asia/Irkutsk',
			'Asia/Jakarta',
			'Asia/Jayapura',
			'Asia/Jerusalem',
			'Asia/Kabul',
			'Asia/Kamchatka',
			'Asia/Karachi',
			'Asia/Kashgar',
			'Asia/Kathmandu',
			'Asia/Kolkata',
			'Asia/Krasnoyarsk',
			'Asia/Kuala_Lumpur',
			'Asia/Kuching',
			'Asia/Kuwait',
			'Asia/Macau',
			'Asia/Magadan',
			'Asia/Makassar',
			'Asia/Manila',
			'Asia/Muscat',
			'Asia/Nicosia',
			'Asia/Novokuznetsk',
			'Asia/Novosibirsk',
			'Asia/Omsk',
			'Asia/Oral',
			'Asia/Phnom_Penh',
			'Asia/Pontianak',
			'Asia/Pyongyang',
			'Asia/Qatar',
			'Asia/Qyzylorda',
			'Asia/Rangoon',
			'Asia/Riyadh',
			'Asia/Sakhalin',
			'Asia/Samarkand',
			'Asia/Seoul',
			'Asia/Shanghai',
			'Asia/Singapore',
			'Asia/Taipei',
			'Asia/Tashkent',
			'Asia/Tbilisi',
			'Asia/Tehran',
			'Asia/Thimphu',
			'Asia/Tokyo',
			'Asia/Ulaanbaatar',
			'Asia/Urumqi',
			'Asia/Vientiane',
			'Asia/Vladivostok',
			'Asia/Yakutsk',
			'Asia/Yekaterinburg',
			'Asia/Yerevan',
			'Atlantic/Azores',
			'Atlantic/Bermuda',
			'Atlantic/Canary',
			'Atlantic/Cape_Verde',
			'Atlantic/Faroe',
			'Atlantic/Madeira',
			'Atlantic/Reykjavik',
			'Atlantic/South_Georgia',
			'Atlantic/St_Helena',
			'Atlantic/Stanley',
			'Australia/Adelaide',
			'Australia/Brisbane',
			'Australia/Broken_Hill',
			'Australia/Currie',
			'Australia/Darwin',
			'Australia/Eucla',
			'Australia/Hobart',
			'Australia/Lindeman',
			'Australia/Lord_Howe',
			'Australia/Melbourne',
			'Australia/Perth',
			'Australia/Sydney',
			'Europe/Amsterdam',
			'Europe/Andorra',
			'Europe/Athens',
			'Europe/Belgrade',
			'Europe/Berlin',
			'Europe/Bratislava',
			'Europe/Brussels',
			'Europe/Bucharest',
			'Europe/Budapest',
			'Europe/Chisinau',
			'Europe/Copenhagen',
			'Europe/Dublin',
			'Europe/Gibraltar',
			'Europe/Guernsey',
			'Europe/Helsinki',
			'Europe/Isle_of_Man',
			'Europe/Istanbul',
			'Europe/Jersey',
			'Europe/Kaliningrad',
			'Europe/Kiev',
			'Europe/Lisbon',
			'Europe/Ljubljana',
			'Europe/London',
			'Europe/Luxembourg',
			'Europe/Madrid',
			'Europe/Malta',
			'Europe/Mariehamn',
			'Europe/Minsk',
			'Europe/Monaco',
			'Europe/Moscow',
			'Europe/Oslo',
			'Europe/Paris',
			'Europe/Podgorica',
			'Europe/Prague',
			'Europe/Riga',
			'Europe/Rome',
			'Europe/Samara',
			'Europe/San_Marino',
			'Europe/Sarajevo',
			'Europe/Simferopol',
			'Europe/Skopje',
			'Europe/Sofia',
			'Europe/Stockholm',
			'Europe/Tallinn',
			'Europe/Tirane',
			'Europe/Uzhgorod',
			'Europe/Vaduz',
			'Europe/Vatican',
			'Europe/Vienna',
			'Europe/Vilnius',
			'Europe/Volgograd',
			'Europe/Warsaw',
			'Europe/Zagreb',
			'Europe/Zaporozhye',
			'Europe/Zurich',
			'Indian/Antananarivo',
			'Indian/Chagos',
			'Indian/Christmas',
			'Indian/Cocos',
			'Indian/Comoro',
			'Indian/Kerguelen',
			'Indian/Mahe',
			'Indian/Maldives',
			'Indian/Mauritius',
			'Indian/Mayotte',
			'Indian/Reunion',
			'Pacific/Apia',
			'Pacific/Auckland',
			'Pacific/Chatham',
			'Pacific/Chuuk',
			'Pacific/Easter',
			'Pacific/Efate',
			'Pacific/Enderbury',
			'Pacific/Fakaofo',
			'Pacific/Fiji',
			'Pacific/Funafuti',
			'Pacific/Galapagos',
			'Pacific/Gambier',
			'Pacific/Guadalcanal',
			'Pacific/Guam',
			'Pacific/Honolulu',
			'Pacific/Johnston',
			'Pacific/Kiritimati',
			'Pacific/Kosrae',
			'Pacific/Kwajalein',
			'Pacific/Majuro',
			'Pacific/Marquesas',
			'Pacific/Midway',
			'Pacific/Nauru',
			'Pacific/Niue',
			'Pacific/Norfolk',
			'Pacific/Noumea',
			'Pacific/Pago_Pago',
			'Pacific/Palau',
			'Pacific/Pitcairn',
			'Pacific/Pohnpei',
			'Pacific/Port_Moresby',
			'Pacific/Rarotonga',
			'Pacific/Saipan',
			'Pacific/Tahiti',
			'Pacific/Tarawa',
			'Pacific/Tongatapu',
			'Pacific/Wake',
			'Pacific/Wallis',
			'UTC',
		);
	}

	foreach ( $tz_identifiers as $tz ) {
		if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $tz ) ) {
			if ( $return_by_zone ) {
				list($zone, $city) = explode('/', $tz, 2);
				$tz_zones[$zone][$tz] = $city;
			}
			else
				$tz_zones[] = $tz;
		}
	}

	return $tz_zones;
}

/**
 * TimeZone html options select
 *
 * @return {string}
 */
function mib_date_timezones_select_options($selected = null) {
	$tz_options = '';
	$tz_selected_found = false;

	$tz_zones = mib_date_timezones();

	foreach ( $tz_zones as $zones => $timezones ) {
		$tz_options .= '<optgroup label="'.$zones.'">'."\n";

		foreach ( $timezones as $tz => $city ) {
			if ( $tz_selected_found)
				$is_selected = ' ';
			else {
				if ( $tz == $selected ) {
					$is_selected = ' selected="selected" ';
					$tz_selected_found = true;
				}
				else
					$is_selected = ' ';
			}
			$tz_options .= "\t\t".'<option'.$is_selected.'value="'.$tz.'">'.$city.'</option>'."\n";
		}

		$tz_options .= '</optgroup>'."\n";
	}

	return $tz_options;
}

/**
 * Formate la date correctement en fonction des "timezones"
 *
 * @param int $timestamp
 * @param string $date_format
 * @param bool $no_text
 * 
 * @return $formatted_time string Date formatée
 */
function format_time($timestamp, $date_format = null, $no_text = false) {

	if ( $timestamp == '' || $timestamp == 0 )
		return ($no_text ? '' : __('Jamais'));

	if ( $date_format == null )
		$date_format = __('Y-m-d').' '.__('H:i');

	$now = time();

	$formatted_time = date($date_format, $timestamp);

	if ( !$no_text ) {
		$base = date('Y-m-d', $timestamp);
		$today = date('Y-m-d', $now);
		$yesterday = date('Y-m-d', $now - 86400);

		if ( $base == $today )
			$formatted_time = __('Aujourd\'hui').' '.date(__('H:i'), $timestamp);
		else if ( $base == $yesterday )
			$formatted_time = __('Hier').' '.date(__('H:i'), $timestamp);
	}

	return $formatted_time;
}

/**
 * Retourne le timestamp d'une date au format text
 *
 * @param string $date
 * @param string $date_format
 * 
 * @return timestamp
 */
function strdate_to_timestamp($date, $date_format = null) {
	if(!$date_format)
		$date_format = __('Y-m-d');

	// Escape les caractères spéciaux
	$date_format = str_replace('/','\\/', $date_format);

	$days = array(__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday'));
	$months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));

	$daysD = $days; foreach ($daysD as $k => $v) $daysD[$k] = utf8_substr($v, 0, 3);
	$monthsM = $months; foreach ($monthsM as $k => $v) $monthsM[$k] = utf8_substr($v, 0, 3);

	$re = array(
		'd' => '([0-9]{2})',
		'j'	=>	'([0-9]{1,2})',
		'l'	=>	'('.implode('|', $days).')',
		'D'	=>	'('.implode('|', $daysD).')',
		'S'	=>	'(st|nd|rd|th)',
		'F'	=>	'('.implode('|', $months).')',
		'M'	=>	'('.implode('|', $monthsM).')',
		'm'	=>	'([0-9]{2})',
		'n'	=>	'([0-9]{1,2})',
		'Y'	=>	'([0-9]{4})',
		'y'	=>	'([0-9]{2})'
	);

	$arr = array(); // array of indexes

	$g = '';

	// convert our format string to regexp
	for ($i=0;$i<utf8_strlen($date_format);$i++) {
		$c = $date_format[$i];

		if (isset($re[$c])) {
			$arr[] = $c;

			$g .= $re[$c];
		}
		else {
			$g .= $c;
		}
	}

	if(preg_match('/^'.$g.'$/', $date, $matches)) {
		$matches = array_slice($matches, 1);
		$dates = array();

		foreach($arr as $i => $c) {
			$i = $matches[$i];

			switch($c) {
				// year cases
				case 'y':
					$i = '19'.$i; // 2 digit year assumes 19th century (same as PHP)
				case 'Y':
					$dates['year'] = intval($i);
					break;

				// month cases
				case 'F':
					foreach($months as $k => $v) {
						if($v == $i) {
							$i = $k + 1;
							break;
						}
					}
				case 'M':
					foreach($months as $k => $v) {
						if(utf8_substr($v, 0, 3) == $i) {
							$i = $k + 1;
							break;
						}
					}
				case 'm':
				case 'n':
					$dates['month'] = intval($i);
					break;

				// day cases
				case 'd':
				case 'j':
					$dates['day'] = intval($i);
					break;
			}
		}

		if(!empty($dates['year']) && !empty($dates['month']) && !empty($dates['day']))
			return mktime(0, 0, 0, $dates['month'], $dates['day'], $dates['year']);
	}

	return false;
}

/**
 * Formate une heure correctement
 *
 * @param int $time (au format sur 4 nombre ex : 2354 pour 23h54)
 * @param bool $min afficher tous le temp les minutes même pour les heures pile (ex. 2300 donnera 23h00)
 * 
 * @return $formatted_hour string Heure formatée
 */
function format_hour($time, $min = true) {

	if ( strlen($time) < 4 ) $time = sprintf('%04d', $time);

	list($hours, $minutes) = str_split($time, 2);

	if ( empty($hours) )
		$hours = 0;
	else
		$hours = intval($hours);

	if ( empty($minutes) )
		$minutes = 0;
	else
		$minutes = intval($minutes);

	if ( $hours < 0 || $hours > 23 ) $hours = 0;
	if ( $minutes < 0 || $minutes > 59 ) $minutes = 0;

	return $hours.'h'.sprintf('%02d', $minutes);
}

/**
 * Retourne l'heure d'une heure au format text
 *
 * @param string $hour
 * 
 * @return int $hour
 */
function strhour_to_hour($hour) {
	$hour = strtolower($hour);
	if ( strpos($hour,'h') === false )
		$hour = intval(preg_replace("/[^0-9]/", '', $hour));
	else {
		list($h,$m) = explode('h', $hour, 2);
		$hour = intval($h).sprintf('%02d', intval($m));
	}

	if ( $hour < 0 || $hour > 2359 ) $hour = 0;

	return $hour;
}

/**
 * Affiche le nombre de jour depuis une date
 */
function time_ago($timestamp, $use = null, $first_letter = false) {

	if ( !$timestamp || intval($timestamp) <= 0 ) return __('Jamais');

	$seconds = abs(time() - $timestamp);

	if ( $seconds <= 0 ) return __('Jamais');

	// périodes
	$periods = array (
		'Year'		=> array(
			'seconds'	=> 31556926,
			'period'	=> __('Year'),
			'periods'	=> __('Years'),
		),
		'Month'		=> array(
			'seconds'	=> 2629743,
			'period'	=> __('Month'),
			'periods'	=> __('Months'),
		),
		'Week'		=> array(
			'seconds'	=> 604800,
			'period'	=> __('Week'),
			'periods'	=> __('Weeks'),
		),
		'Day'		=> array(
			'seconds'	=> 86400,
			'period'	=> __('Day'),
			'periods'	=> __('Days'),
		),
		'hour'		=> array(
			'seconds'	=> 3600,
			'period'	=> __('Hour'),
			'periods'	=> __('Hours'),
		),
		'minute'		=> array(
			'seconds'	=> 60,
			'period'	=> __('Minute'),
			'periods'	=> __('Minutes'),
		),
		'second'		=> array(
			'seconds'	=> 1,
			'period'	=> __('Second'),
			'periods'	=> __('Seconds'),
		),
	);

	if ( !$use && $seconds <= $periods['Day']['seconds'] ) return __('Aujourd\'hui');

	if ( !$use ) $use = 'YMD';

	// séparation des périodes
	$seconds_left = (float) $seconds;
	$segments = array();
	foreach ( $periods as $p => $v ) {
		if ( $use && strpos($use, $p[0]) === false )
			continue;

		$count = floor($seconds_left / $v['seconds']);
		if ( $count == 0 )
			continue;

		$segments[$p] = $count;
		$seconds_left = $seconds_left % $v['seconds'];
	}

	// affichage au format texte
	$return = array();
	foreach ( $segments as $k => $v ) {
		if ( $first_letter )
			$return[] = $v.strtoupper(substr($periods[$k]['period'], 0, 1));
		else
			$return[] = $v.' '.strtolower($v > 1 ? $periods[$k]['periods'] : $periods[$k]['period']);
	}

	if ( empty($return) && $seconds <= $periods['Day']['seconds'] )
		return __('Aujourd\'hui');
	else
		return implode(' ', $return);
}