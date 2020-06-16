<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 28/03/19
 * Time: 17:23
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
        'investor_name'         => 'Investor Name',
        'date'			        => 'Date',
        'type'			        => 'Type',
        'contact_alpha'			=> 'Contact Alpha',
        'contact_investor'		=> 'Contact investor',
        'notes_interactions'	=> 'Notes',
        'actions'               => '',
    ),
    'filters'		=> array(
        'type'			=> array(
            'label'				=> $MIB_PLUGIN['inputs']['type']['label'],
            'type'				=> 'select',
            'options'			=> $MIB_PLUGIN['inputs']['type']['options'],
            'where'				=>  'title=',
            'in'				=> 'title'
        ),
        'contact_alpha'			    => array(
            'label'				=> __bo('contact_alpha'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'contact_alpha',
        ),
        'contact_investor'			   => array(
            'label'				=> __bo('contact_investor'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'contact_investor',
        ),
    ),
));

// affiche le formulaire pour filtrer la recherche
?>

    <fieldset class="toggle"><legend><?php _ebo('Search for an Interaction'); ?></legend>
        <form method="get" action="<?php echo $search['target']; ?>" target="<?php echo $search['target']; ?>">
            <?php $search = array_merge($search, mib_form_build_search_filters($search['uid'], $search['filters'])); ?>
            <div class="option-actions">
                <button type="submit" class="button"><span class="filter"><?php _ebo('Search'); ?></span></button>
            </div>
        </form>
        </div></fieldset>
    <div class="alert">
        <?php echo __bo('To be able to change the CONTACT POSITIONING order, please filter your search only by REF and sort the results by position.'); ?>
    </div>
    <style>
        #<?php echo $search['uid']; ?>-results .tc-status {
            width: 35px;
            text-align: center;
        }
        #<?php echo $search['uid']; ?>-results .tc-id {
            width: 35px;
            text-align: center;
        }

        #<?php echo $search['uid']; ?>-results .tc-lang > img {
            border-top: 8px solid transparent;
        }
        #<?php echo $search['uid']; ?>-results .tc-contact_investor {
            width: 80px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-date {
            width: 50px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-investor_name {
            width: 80px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-contact_alpha {
            width: 80px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-type {
            width: 80px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-notes_interactions {
            width: 180px;
            text-align: center;
            font-style: italic;
        }
        #<?php echo $search['uid']; ?>-results .tc-actions {
            width: 30px;
            text-align: center;
        }
    </style>
<?php

$contact_id = NULL;
if( isset($_REQUEST['contact_id']) ){
    $contact_id = $_REQUEST['contact_id'];
}

// active la possibilité de modifier le positionnement uniquement si on filtre par langue
if ( count($search['filters_result']) == 1 && $search['filters_result']['title'] )
    $search['order_positions_forced'] = true;
else
    $search['order_positions_url'] = false;

// compte le nombre de résultats
$search['query']['count'] = array(
    'SELECT'	=> 'COUNT(id)',
    'FROM'		=> $MIB_PLUGIN['dbtable'],
);

if( $contact_id != NULL ){
    $search['query']['count']['WHERE'] = 'contact_id = '.$contact_id;
}

$search = mib_search_count($search); // comptage des résultats
$search = mib_search_navigation($search); // navigation des résultats de la recherche
$search = mib_search_table_start($search); // initialisation du tableau de résultats

// requette des résutats
$search['query']['result'] = array(
    'SELECT'	=> 'i.* , CONCAT(c.firstname, " ", c.surname) as contact_investor, p.investor_name ',
    'FROM'		=> $MIB_PLUGIN['dbtable']." AS i",
    'JOINS'     => array(
        array(  '
        LEFT JOIN' => 'contact as c',
            'ON' => "c.id = i.contact_id"
        ),
        array(  '
        LEFT JOIN' => 'prospective as p',
            'ON' => "c.prospective_id = p.id"
        )
    ),
);

if( $contact_id != NULL ){
    $search['query']['result']['WHERE'] = 'contact_id = '.$contact_id;
}

$search = mib_search_result($search); // lance la recherche
if ( $search['num_rows'] ) { while ( $cur_result = $MIB_DB->fetch_assoc($search['result']) ) { echo '<tr data-id="'.$cur_result['id'].'">'; // START LIGNE

    foreach( $search['cols'] as $k => $v )
    {

        echo '<td class="tc tc-'.$k.((isset($cur_result['deleted']) && $cur_result['deleted']) > 0 ? ' tc-deleted' : '').'">'; // START COLONNE

        if ( $k == 'contact_alpha' ) {
            echo '<a href="'.$MIB_PLUGIN['name'].'/view/'.$cur_result['id'].'" title="'.mib_html($cur_result['contact_alpha']).'">';
            echo mib_html(utf8_strtoupper($cur_result['contact_alpha']));
            echo '</a>';
        }
        else if ( $k == 'investor_name' ) {
            echo mib_html( strtoupper($cur_result['investor_name']));
        }
        else if ( $k == 'contact_investor' ) {
            echo mib_html( ucwords($cur_result['contact_investor']));
        }
        else if ( $k == 'actions' ) {
            mib_form_delete_button('minbutton-secure', $MIB_PLUGIN['name'].'/delete/'.$cur_result['id'], __bo('Delete').' : '.mib_html($cur_result['name']));
        }
        else if ( isset($cur_result[$k]) )
            echo mib_html($cur_result[$k]);

        echo '</td>'; } // END COLONNE

    echo '</tr>'; } } // END LIGNE

$search = mib_search_table_end($search); // fin du tableau de résultats