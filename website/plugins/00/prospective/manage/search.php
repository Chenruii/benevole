<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 16:12
 */

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"
if ( !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax

$search = mib_search_build(array(
    'uid'					    => mib_uid($MIB_PLUGIN['name'].basename(__FILE__, '.php')),
    'target'				    => '_self',
    'sort_by_autorized'			=> array('id'), // Champs de la table sur lequels le tri est autorizé
    'sort_by_default'			=> 'id', // Champ de la table utilisé pour le tri par défaut
    'sort_dir_default'			=> 'DESC', // Ordre de tri par défaut
    'start_from'                => 0 , // debut pagination
    'num_results_by_page'		=> 20, // Nombre de résultat à afficher par page
    'cols'					    => array( // Colonnes à affiché pour le tableau des résultats
//        'status'				=> __bo('Statut'),
        'id'				    => 'Réf',
        'investor_name'			=> 'Investor Name',
        'investor_type'			=> 'Investor type',
        'street_name'			=> 'Street Name',
        'zip_code'			=> 'Zip Code',
        'city'			=> 'City',
        'country'			=> 'Country',
//        'website'			=> 'Website',
        'previous'			=> 'Previous Co-Investment W? ',
        'fund_management'			=> 'Total Funds under Management',
        'private_equity_allocation'			=> 'Private Equity Allocation',
        'typical_bite_size'			=> 'Typical bite size',
        'co_investment_appetite'			=> 'Co-Investment appetite',
        'co_investment_bite_size'			=> 'Co-investment bite size',
        'invested_in'			=> 'Overview of Investor',
        'actions'               => '',
    ),
    'filters'		=> array(
        'investor_name'			    => array(
            'label'				=> __bo('Investor Name'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'investor_name',
        ),
        'investor_type'			   => array(
            'label'				=> $MIB_PLUGIN['inputs']['investor_type']['label'],
            'type'				=> 'select',
            'options'			=> $MIB_PLUGIN['inputs']['investor_type']['options'],
            'where'				=>  'investor_type=',
            'in'				=> 'investor_type'
        ),
        'country'			   => array(
            'label'				=> $MIB_PLUGIN['inputs']['country']['label'],
            'type'				=> 'select',
            'options'			=> $MIB_PLUGIN['inputs']['country']['options'],
            'where'				=>  'country=',
            'in'				=> 'country'
        ),


    ),
));

// affiche le formulaire pour filtrer la recherche
?>

    <fieldset class="toggle"><legend><?php echo __('Search a Prospective Investor'); ?></legend>
        <form method="get" action="<?php echo $search['target']; ?>" target="<?php echo $search['target']; ?>">
            <?php $search = array_merge($search, mib_form_build_search_filters($search['uid'], $search['filters'])); ?>
            <div class="option-actions">
                <button type="submit" class="button"><span class="filter"><?php echo __('Search'); ?></span></button>
            </div>
        </form>
    </fieldset>
    <style>
        #<?php echo $search['uid']; ?>-results .tc-status {
            width: 70px;
            text-align: center;
        }
        #<?php echo $search['uid']; ?>-results .tc-lang > img {
            border-top: 8px solid transparent;
        }
        #<?php echo $search['uid']; ?>-results .tc-img {
            width: 100px;
            text-align: center;
        }
        #<?php echo $search['uid']; ?>-results .tc-investor_name {
            width: 80px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-investor_type {
            width: 80px;
            text-align: center;
            font-weight: bold;
        }
        #<?php echo $search['uid']; ?>-results .tc-actions {
            width: 30px;
            text-align: center;
        }
        #<?php echo $search['uid']; ?>-results .tc-website{
            width: 50px;
            text-align: center;
        }
        #<?php echo $search['uid']; ?>-results .tc-previous{
            width: 50px;
            text-align: center;
        }
    </style>

<?php
// compte le nombre de résultats
$search['query']['count'] = array(
    'SELECT'	=> 'COUNT(id)',
    'FROM'		=> $MIB_PLUGIN['dbtable'],
);

$search = mib_search_count($search); // comptage des résultats
$search = mib_search_navigation($search); // navigation des résultats de la recherche
$search = mib_search_table_start($search); // initialisation du tableau de résultats



// requette des résutats
$search['query']['result'] = array(
    'SELECT'	=> '*',
    'FROM'		=> $MIB_PLUGIN['dbtable'],
);

$search = mib_search_result($search); // lance la recherche

if ( $search['num_rows'] ) { while ( $cur_result = $MIB_DB->fetch_assoc($search['result']) ) {
    echo '<tr data-id="' . $cur_result['id'] . '">'; // START LIGNE

    foreach( $search['cols'] as $k => $v ) { echo '<td class="tc tc-'.$k.((isset($cur_result['deleted']) && $cur_result['deleted']) > 0 ? ' tc-deleted' : '').'">'; // START COLONNE
        if ( $k == 'id' ) {

            echo mib_html($cur_result['id']);

        }
        if ( $k == 'investor_name' ) {
            echo '<a href="'.$MIB_PLUGIN['name'].'/view/'.$cur_result['id'].'" title="'.mib_html($cur_result['investor_name']).'">';
            echo mib_html(utf8_strtoupper($cur_result['investor_name']));
            echo '</a>';
        }
        else if ( $k == 'status' ) {
            echo prospective_status($cur_result['status']);
        }
        else if ( $k == 'investor_type' ) {
            // format type
            if ( array_key_exists($cur_result['investor_type'], prospective_investor_type()) ) {
                $investor_type = explode(' ', prospective_investor_type($cur_result['investor_type']), 2);
                if ( count($investor_type) > 1 )
                    echo '<span class="tips" title="'.mib_html(prospective_investor_type($cur_result['investor_type'])).'">'.mib_html(current($investor_type)).'</span>';
                else
                    echo mib_html(prospective_investor_type($cur_result['investor_type']));
            }
            else
                echo mib_html(prospective_investor_type($cur_result['investor_type']));
        }
        else if ( $k == 'actions' ) {
            mib_form_delete_button('minbutton-secure', $MIB_PLUGIN['name'].'/delete/'.$cur_result['id'], __bo('Delete').' : '.mib_html($cur_result['name']));
        }
        else if ( isset($cur_result[$k]) )
            echo mib_html($cur_result[$k]);

        echo '</td>'; } // END COLONNE

    echo '</tr>'; } } // END LIGNE

$search = mib_search_table_end($search); // fin du tableau de résultats
