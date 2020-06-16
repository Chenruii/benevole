<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 15:34
 */
defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

// charge les fonctions dédiés
if ( file_exists($MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php') ) require_once $MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php';


// table utilisée dans la base de données
$MIB_PLUGIN['dbtable'] = 'prospective';

// champs de formulaire du plugin
$MIB_PLUGIN['inputs'] = array(
    'status'      =>array(
        'label'				=> __bo('Statut'),
        'type'              => 'radio',
        'options'           => prospective_status()
    ),
    'investor_name'		=> array(
        'label'				=> __bo('Investor Name'),
        'type'				=> 'text',
        'req'				=> true,
    ),
    'investor_type'		=> array(
        'label'				=> __bo('Investor type'),
        'type'				=> 'select',
        'req'				=> true,
        'default'			=> 'Bank',
        'options'			=> array(
            __bo('types') => prospective_investor_type(),
        ),
    ),
    'street_name'		=> array(
        'label'				=> __bo('Adress'),
        'type'				=> 'text',
    ),
    'zip_code'		        => array(
        'label'				=> __bo('Zip Code'),
        'type'				=> 'text',
    ),
    'city'		        => array(
        'label'				=> __bo('City'),
        'type'				=> 'text',
    ),
    'country'		    => array(
        'label'				=> __bo('Country'),
        'type'				=> 'select',
        'req'				=> true,
        'default'			=> 'FR',
        'options'			=> array(
            __bo('countries') => prospective_country(),
        ),
    ),
    'website'		        => array(
        'label'				=> __bo('Website'),
        'type'				=> 'text',
    ),
    'alpha_relationship'=> array(
        'label'				=> __bo('Alpha Relationship Owner'),
        'type'				=> 'select',
        'req'				=> true,
        'default'			=> 'relationship1',
        'options'			=> array(
            __bo('relationships') => prospective_relationship(),
        ),
    ),
    'alpha_investor'	=> array(
        'label'				=> __bo('Existing Investor in Alpha?'),
        'type'              => 'radio',
        'options'           => prospective_alpha_investor()
    ),
    'apef4'	=> array(
        'label'				=> __bo('APEF 4 (amount)'),
        'type'				=> 'text',
    ),'apef5'	=> array(
        'label'				=> __bo('APEF 5 (amount)'),
        'type'				=> 'text',
    ),'apef6'	=> array(
        'label'				=> __bo('APEF 6 (amount)'),
        'type'				=> 'text',
    ),'apef7'	=> array(
        'label'				=> __bo('APEF 7 (amount)'),
        'type'				=> 'text',
    ),

    'previous'	=> array(
        'label'				=> __bo('Previous Co-Investment with Alpha? '),
        'type'				=> 'text',
    ),
    'fund_management'	=> array(
        'label'				=> __bo('Total Funds under Management'),
        'type'				=> 'text',
    ),
    'private_equity_allocation'		=> array(
        'label'				=> __bo('Private Equity Allocation'),
        'type'				=> 'text',
    ),
    'typical_bite_size'	=> array(
        'label'				=> __bo('Typical bite size'),
        'type'				=> 'text',
    ),
    'co_investment_appetite'		=> array(
        'label'				=> __bo('Co-Investment appetite'),
        'type'              => 'radio',
        'options'           => prospective_co_investment_appetite()
    ),
    'co_investment_bite_size'		=> array(
        'label'				=> __bo('Co-investment bite size'),
        'type'				=> 'text',
    ),
    'invested_in'		=> array(
        'label'				=> __bo('Also invested in'),
        'type'				=> 'text',
    ),
    'overview_investor'		=> array(
        'label'				=> __bo('Overview of Investor'),
        'type'          => 'textarea',
    ),
    'alpha_history'		    => array(
        'label'				=> __bo('History with Alpha'),
        'type'          => 'textarea',
    ),
);


// séparation des actions par fichier pour une meilleur lisibilité
$MIB_PLUGIN['action'] = mib_get_request_infos();
if ( $MIB_PLUGIN['action'] && file_exists($MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php') ) {
    require_once $MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php';

    return;
}
// erreur, l'action n'a pas de fichier correspondant
else if ( $MIB_PLUGIN['action'] ) {
    if ( defined('MIB_JSON') )
        define('MIB_JSONED', 1);
    else
        define('MIB_AJAXED', 1);

    error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));
}
else if ( !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax pour la page d'accueil du plugin
/*
	Accueil
*/
if ( $MIB_USER['can_write_plugin'] && file_exists($MIB_PLUGIN['path'].'manage/add.php') ) // l'utilisateur à les droits en écriture
    require_once $MIB_PLUGIN['path'].'manage/add.php';
if ( file_exists($MIB_PLUGIN['path'].'manage/search.php') )
    require_once $MIB_PLUGIN['path'].'manage/search.php';