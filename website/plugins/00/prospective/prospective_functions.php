<?php

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"


/**
 * Vérification des valeurs
 *
 * @param {array} $values
 * @param {string} $dbtable
 * @param {int} $id
 */
function bo_prospective_verif($values, $dbtable, $id = false) {
    global $MIB_DB, $MIB_CONFIG;

    $verif = array(
        'investor_name'				=> !empty($values['investor_name']) ? utf8_strtoupper(mib_clean($values['investor_name'])) : '',
        'investor_type'				=> !empty($values['investor_type']) ? utf8_strtoupper(mib_clean($values['investor_type'])) : '',
        'street_name'				=> !empty($values['street_name']) ? mib_trim($values['street_name']) : '',
        'zip_code'				=> !empty($values['zip_code']) ? mib_trim(mib_clean($values['zip_code'])) : '',
        'city'				=> !empty($values['city']) ? utf8_strtoupper(mib_clean($values['city'])) : '',
        'country'			=> !empty($values['country']) ? mib_trim(mib_clean($values['country'])) : '',
        'website'			=> !empty($values['website']) ? utf8_strtolower(mib_clean($values['website'])) : '',
        'alpha_relationship'			=> !empty($values['alpha_relationship']) ? mib_trim($values['alpha_relationship']) : '',
        'alpha_investor'			=> !empty($values['alpha_investor']) ? mib_trim($values['alpha_investor']) : '',
        'fund_management'			=> !empty($values['fund_management']) ? mib_trim($values['fund_management']) : '',
        'private_equity_allocation'			=> !empty($values['private_equity_allocation']) ? mib_trim($values['private_equity_allocation']) : '',
        'typical_bite_size'			=> !empty($values['typical_bite_size']) ? mib_trim($values['typical_bite_size']) : '',
        'co_investment_appetite'			=> !empty($values['co_investment_appetite']) ? mib_trim($values['co_investment_appetite']) : '',
        'co_investment_bite_size'			=> !empty($values['co_investment_bite_size']) ? mib_trim($values['co_investment_bite_size']) : '',
        'invested_in'			=> !empty($values['invested_in']) ? mib_trim($values['invested_in']) : '',
        'overview_investor'			=> !empty($values['overview_investor']) ? mib_trim($values['overview_investor']) : '',
        'alpha_history'			=> !empty($values['alpha_history']) ? mib_trim($values['alpha_history']) : '',
    );

    if ( utf8_strlen($verif['investor_name']) > 50 )
        mib_error_set(__bo('Le titre est trop long.'), 'investor_name');
    else if ( empty($verif['investor_name']) )
        mib_error_set(__bo('Veuillez indiquer un nom.'), 'investor_name');

    if ( empty($verif['investor_type']) )
        mib_error_set(__bo('please give your Investor Type.'), 'investor_type');
    return $verif;
}



/**
 * Ajoute
 *
 * @param {array} $values
 * @param {string} $dbtable
 */
function bo_prospective_add($values, $dbtable)
{
    global $MIB_DB;

    // vérification des données
    $verifed = bo_prospective_verif($values, $dbtable);
    if ( !mib_error_exists() ) { // aucune erreur

        // ajoute les données en base de données
        $insert = array();
        foreach( $verifed as $k => $v ) if ( !empty($v) ) $insert[$k] = '\''.$MIB_DB->escape($v).'\'';
        $query = array(
            'INSERT'	=> implode(',', array_keys($insert)),
            'INTO'		=> $dbtable,
            'VALUES'	=> implode(',', $insert)
        );
        $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
        $verifed['id'] = $MIB_DB->insert_id();

        return $verifed;
    }
    else
        return false;
}


/**
 * Update
 *
 * @param {array} $id
 * @param {array} $values
 * @param {string} $dbtable
 */
function bo_prospective_update($id, $values, $dbtable) {
    global $MIB_DB;

    if ( $cur_result = mib_db_get_row_from_table($dbtable, $id) ) {
        // vérification des données
        $verifed = bo_prospective_verif($values, $dbtable, $cur_result['id']);

        if ( !mib_error_exists() ) { // aucune erreur

            // modification des données
            $set = array();
            foreach( $cur_result as $k => $v ) {
                if ( isset($verifed[$k]) ) {
                    // met à jour uniquement les données qui ont changées
                    if ( $v != $verifed[$k] )
                        $set[$k] = $k.'='.($verifed[$k] !== '' ? '\''.$MIB_DB->escape($verifed[$k]).'\'' : 'NULL');
                }
                else
                    $verifed[$k] = $v; // complète pour renvoyer toutes les données
            }

            // il y a des modifs
            if ( !empty($set) ) {
                $query = array(
                    'UPDATE'	=> $dbtable,
                    'SET'		=> implode(',', $set),
                    'WHERE'		=> 'id='.$cur_result['id']
                );
                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
                $verifed['id'] = $MIB_DB->insert_id();
            }

            return $verifed;
        }
    }

    return false;
}



/**
 * Supprime
 *
 * @param {int} $id
 * @param {string} $dbtable
 */
function prospective_delete($id,$dbtable){
    global $MIB_DB;

    if ( $cur_result = mib_db_get_row_from_table($dbtable, $id) ) {
        // supprime de la DB
        $query = array(
            'DELETE'	=> $dbtable,
            'WHERE'		=> 'id='.$cur_result['id']
        );
        $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    }

    return true;
}

/**
 * investor type of prospective
 *
 * @return {array}
 */
function prospective_investor_type($type = false){
    $types = array(
        'Family Office '    => 'Family Office',
        'Insurance'         => 'Insurance',
        'Fund of Funds'     => 'Fund of Funds',
        'Pension Fund'      => 'Pension Fund',
        'Private Investor'  => 'Private Investor',
        'Bank'              => 'Bank',
        'Endowment'         => 'Endowment' ,
    );

    if ($type !== false) {
        if (array_key_exists($type, $types))
            return $types[$type];
        else
            return $type;
    }
    return $types;
}

/**
 * country of prospective
 *
 * @return {array}
 */
function prospective_country($country = false){
    $countries = array(
        "AF" => "Afghanistan",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "BQ" => "British Antarctic Territory",
        "IO" => "British Indian Ocean Territory",
        "VG" => "British Virgin Islands",
        "BN" => "Brunei",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CT" => "Canton and Enderbury Islands",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos [Keeling] Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo - Brazzaville",
        "CD" => "Congo - Kinshasa",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "CI" => "Côte d’Ivoire",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "NQ" => "Dronning Maud Land",
        "DD" => "East Germany",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "FQ" => "French Southern and Antarctic Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and McDonald Islands",
        "HN" => "Honduras",
        "HK" => "Hong Kong SAR China",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JT" => "Johnston Island",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Laos",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macau SAR China",
        "MK" => "Macedonia",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "FX" => "Metropolitan France",
        "MX" => "Mexico",
        "FM" => "Micronesia",
        "MI" => "Midway Islands",
        "MD" => "Moldova",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar [Burma]",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NT" => "Neutral Zone",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "KP" => "North Korea",
        "VD" => "North Vietnam",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PC" => "Pacific Islands Trust Territory",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territories",
        "PA" => "Panama",
        "PZ" => "Panama Canal Zone",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "YD" => "People's Democratic Republic of Yemen",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn Islands",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RO" => "Romania",
        "RU" => "Russia",
        "RW" => "Rwanda",
        "RE" => "Réunion",
        "BL" => "Saint Barthélemy",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "MF" => "Saint Martin",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "CS" => "Serbia and Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and the South Sandwich Islands",
        "KR" => "South Korea",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syria",
        "ST" => "São Tomé and Príncipe",
        "TW" => "Taiwan",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UM" => "U.S. Minor Outlying Islands",
        "PU" => "U.S. Miscellaneous Pacific Islands",
        "VI" => "U.S. Virgin Islands",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "SU" => "Union of Soviet Socialist Republics",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "ZZ" => "Unknown or Invalid Region",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VA" => "Vatican City",
        "VE" => "Venezuela",
        "VN" => "Vietnam",
        "WK" => "Wake Island",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe",
        "AX" => "Åland Islands",
    );

    if ($country !== false) {
        if (array_key_exists($country, $countries))
            return $countries[$country];
        else
            return $country;
    }
    return $countries;
}


/**
 * alpha relationship de prospective
 *
 * @return {array}
 */
function prospective_relationship ($relationship = false) {
    $relationships = array(

        'Patrick Herman'		    => 'Patrick Herman - Paris',
        'Olaf Kordes'		        => 'Olaf Kordes - Paris',
        'Marlene Bazouin'		    => 'Marlene Bazouin - Paris',
        'Amelie Finaz de Villaine'	=> 'Amelie Finaz de Villaine - Paris',
        'Matthieu Leroy'		    => 'Matthieu Leroy - Paris',
        'Laurence Chaumeil'		    => 'Laurence Chaumeil - Paris',
        'David Kusters'		        => 'David Kusters - Paris',
        'Felix Jones'		        => 'Felix Jones - Paris',
        'Antoine Klein'		        => 'Antoine Klein - Paris',
        'Nathalie Couté'		    => 'Nathalie Couté - Paris',
        'Laurence Truong'		    => 'Laurence Truong - Paris',

        'Edoardo Lanzavecchia'		=> 'Edoardo Lanzavecchia - Milan',
        'Valentina Pippolo'		    => 'Valentina Pippolo - Milan',
        'Marco Bernardi'		    => 'Marco Bernardi - Milan',
        'Luca Zachetti'		        => 'Luca Zachetti - Milan',
        'Michele Bertola'		    => 'Michele Bertola - Milan',
        'Pasquale Cavaliere'		=> 'Pasquale Cavaliere - Milan',
        'Daniele Ferrigni'		    => 'Daniele Ferrigni - Milan',
        'Paolo Magni'		        => 'Paolo Magni - Milan',
        'Silvia Scietto'		    => 'Silvia Scietto - Milan',
        'Francesca Gerli'		    => 'Francesca Gerli - Milan',
        'Arabella Caporello'		=> 'Arabella Caporello - Milan',
        'Matteo Cavagnis'		    => 'Matteo Cavagnis - Milan',

        'Bianca Do'		            => 'Bianca Do - Germany',
        'Antonietta Koschwitz'		=> 'Antonietta Koschwitz - Germany',

        'Arnaud Million'		    => 'Arnaud Million - Luxembourg',
        'Sebastien Wiander'		    => 'Sebastien Wiander - Luxembourg',
        'Yann Dremiere'		        => 'Yann Dremiere - Luxembourg',
        'Herve Hautin'		        => 'Herve Hautin - Luxembourg',
        'Nicolas Dumont'		    => 'Nicolas Dumont - Luxembourg',
        'Sandrine Nez'		        => 'Sandrine Nez - Luxembourg',
        'Helena Quinn'		        => 'Helena Quinn - Luxembourg',

        'Capstone'		        => 'Capstone',
    );

    if( $relationship !== false ) {
        if ( array_key_exists($relationship, $relationships) )
            return $relationships[$relationship];
        else
            return $relationship;
    }

    return $relationships;
}

function prospective_co_investment_appetite( $value = false)
{
    $table = array(
        0 => "yes",
        1 => "no"
    );
    if( $value === FALSE){
        return $table;
    }
    return $table[$value];
}

function prospective_status( $value = false)
{
    $table = array(
        0 => "Prospective",
        1 => "Existent");
    if( $value === FALSE){
        return $table;
    }
    return $table[$value];
}



function prospective_alpha_investor($value= false){
    $investor = array(
        0 => "yes",
        1 => "no"
    );
    if( $value === FALSE){
        return $investor;
    }
    return $investor[$value];
}