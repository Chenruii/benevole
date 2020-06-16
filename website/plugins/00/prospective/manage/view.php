<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 16:12
 */

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

if ( !$cur_result = mib_db_get_row_from_table($MIB_PLUGIN['dbtable'], mib_get_request_infos(2)) ) error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));

if ( defined('MIB_AJAX') && !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax

$current_prospective = $cur_result;

?>
<style>
    fieldset.investor-details{
        width: auto;
        text-align: center;
        margin: 6px 0;

    }
    .investor {
        float: right;
        width: 33%;
        margin-left: 10%;
        text-align: left;
        margin-bottom: 2%;
    }
    .details{
        float: right;
        margin-right: 5%;
        width: 33%;
        text-align: left;
        margin-bottom: 6%;
    }
    fieldset.overview{
        float: right;
        width: 45%;
        text-align: left;
        margin-right: 2%;
        margin-top: 11px;

    }
    fieldset.history{
        float: left;
        width: 45%;
        text-align: left;
        margin-left: 2%;
    }
    textarea{
        width: 100%;
        margin-left: -25%;
        height: 100%;
    }
    .description{
        padding-top: 7%;
        margin-bottom: 2%;
    }
    legende {
        font-weight: bold;
        font-size: 12px;
    }
    .button {
        -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
        -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
        box-shadow:inset 0px 1px 0px 0px #ffffff;
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f9f9f9), color-stop(1, #e9e9e9));
        background:-moz-linear-gradient(top, #f9f9f9 5%, #e9e9e9 100%);
        background:-webkit-linear-gradient(top, #f9f9f9 5%, #e9e9e9 100%);
        background:-o-linear-gradient(top, #f9f9f9 5%, #e9e9e9 100%);
        background:-ms-linear-gradient(top, #f9f9f9 5%, #e9e9e9 100%);
        background:linear-gradient(to bottom, #f9f9f9 5%, #e9e9e9 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f9f9f9', endColorstr='#e9e9e9',GradientType=0);
        background-color:#f9f9f9;
        border:1px solid #dcdcdc;
        display:inline-block;
        cursor:pointer;
        color:#666666;
        font-family:Arial;
        font-size:10px;
        font-weight:bold;
        padding:6px 24px;
        text-decoration:none;
        text-shadow:0px 1px 0px #ffffff;
    }
    .button:hover {
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #e9e9e9), color-stop(1, #f9f9f9));
        background:-moz-linear-gradient(top, #e9e9e9 5%, #f9f9f9 100%);
        background:-webkit-linear-gradient(top, #e9e9e9 5%, #f9f9f9 100%);
        background:-o-linear-gradient(top, #e9e9e9 5%, #f9f9f9 100%);
        background:-ms-linear-gradient(top, #e9e9e9 5%, #f9f9f9 100%);
        background:linear-gradient(to bottom, #e9e9e9 5%, #f9f9f9 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#e9e9e9', endColorstr='#f9f9f9',GradientType=0);
        background-color:#e9e9e9;
    }
    .button:active {
        position:relative;
        top:1px;
    }

</style>
    <fieldset class="investor-details"><legend><?php __('Investor Details'); ?> : <?php echo mib_html($cur_result['name']); ?></legend>
        <div class="investors_details">
            <div class="investor">
                <?php if ( !empty($cur_result['status']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['status']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['status']); ?></p>
                        </div>
                    </div>
                <?php endif; ?> <?php if ( !empty($cur_result['investor_name']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['investor_name']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['investor_name']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['investor_type']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['investor_type']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['investor_type']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['street_name']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['street_name']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['street_name']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['zip_code']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['zip_code']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['zip_code']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['city']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['city']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['city']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['country']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['country']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['country']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['website']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['website']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['website']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['alpha_relationship']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['alpha_relationship']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['alpha_relationship']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['alpha_investor']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['alpha_investor']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['alpha_investor']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="details">
                <?php if ( !empty($cur_result['fund_management']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['fund_management']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['fund_management']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['private_equity_allocation']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['private_equity_allocation']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['private_equity_allocation']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['typical_bite_size']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['typical_bite_size']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['typical_bite_size']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="option-row">
                    <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['co_investment_appetite']['label']; ?></div>
                    <div class="option-item">
                        <p><?php echo mib_html(prospective_co_investment_appetite($cur_result['co_investment_appetite'])); ?></p>
                    </div>
                </div>
                <?php if ( !empty($cur_result['co_investment_bite_size']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['co_investment_bite_size']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['co_investment_bite_size']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( !empty($cur_result['invested_in']) ): ?>
                    <div class="option-row">
                        <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['invested_in']['label']; ?></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['invested_in']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <div class="description">
            <fieldset class="overview"><legende>History with Alpha</legende>
                <?php if ( !empty($cur_result['alpha_history']) ): ?>
                    <div class="option-row">
                        <div class="option-title"></div>
                        <div class="option-item">
                            <p><?php echo mib_html($cur_result['alpha_history']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </fieldset>
            <fieldset class="history"><legende><?php _ebo('Overview of Investor'); ?></legende>

                <?php if ( !empty($cur_result['overview_investor']) ): ?>
                    <div class="option-row">
                        <div class="option-title"></div>
                        <div class="option-item">
                            <p class="textarea"><?php echo mib_html($cur_result['overview_investor']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </fieldset>
        </div>

        <?php if ( $MIB_USER['can_write_plugin'] ): ?>
            <div class="option-row">
                <a href="<?php echo $MIB_PLUGIN['name'].'/edit/'.$cur_result['id']; ?>" target="_self" class="button"><span class="edit"><?php _ebo('Edit'); ?></span></a>
            </div>
        <?php endif; ?>
    </fieldset>





<fieldset>
    <legend><?php _ebo('List Contacts / Interactions'); ?></legend>
<?php

$search = mib_search_build(array(
    'uid'					    => mib_uid($MIB_PLUGIN['name'].basename(__FILE__, '.php')),
    'target'				    => '_self',
    'sort_by_autorized'			=> array('contact_id'), // Champs de la table sur lequels le tri est autorizé
    'sort_by_default'			=> 'contact_id', // Champ de la table utilisé pour le tri par défaut
    'sort_dir_default'			=> 'DESC', // Ordre de tri par défaut
    'start_from'                => 0 , // debut pagination
    'num_results_by_page'		=> 20, // Nombre de résultat à afficher par page
    'cols'					    => array( // Colonnes à affiché pour le tableau des résultats
        'surname'		            => 'Surname',
        'firstname'                 => 'Firstname',
        'date'			            => 'Date',
        'type'			            => 'Type of Interaction',
        'actions'                    =>'Add a new an Interaction',
    ),
    'filters'		=> array(
        'surname'			    => array(
            'label'				=> __bo('surname'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'surname',
        ),
        'firstname'			    => array(
            'label'				=> __bo('firstname'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'firstname',
        ),
        'type'			   => array(
            'label'				=> __bo('type'),
            'type'				=> 'text',
            'where'				=> $MIB_CONFIG['like_command'],
            'in'				=> 'type',
        ),
    ),
));



// compte le nombre de résultats
$search['query']['count'] = array(
    'SELECT'	=> 'count(*)',
    'FROM'		=> 'contact c',
    'JOINS'		=> array(
        array(
            'LEFT JOIN'		=> 'interaction AS i',
            'ON'			=> 'i.contact_id = c.id'
        ),
    ),
    'WHERE' => "c.prospective_id = ".$cur_result['id']
);

$search = mib_search_count($search); // comptage des résultats
$search = mib_search_table_start($search); // initialisation du tableau de résultats


$search['query']['result'] = array(
    'SELECT'	=> '*, i.id as interaction_id',
    'FROM'		=> 'contact c',
    'JOINS'		=> array(
        array(
            'LEFT JOIN'		=> 'interaction AS i',
            'ON'			=> 'i.contact_id = c.id'
        ),
    ),
    'WHERE' => "c.prospective_id = ".$cur_result['id']
);

$search = mib_search_result($search); // lance la recherche
if ( $search['num_rows'] )
{

    while ( $cur_result = $MIB_DB->fetch_assoc($search['result']) )
    {
        echo '<tr data-id="'.$cur_result['id'].'">'; // START LIGNE

        foreach( $search['cols'] as $k => $v )
        {

            echo '<td class="tc tc-'.$k.((isset($cur_result['deleted']) && $cur_result['deleted']) > 0 ? ' tc-deleted' : '').'">'; // START COLONNE

            if ( $k == 'surname' ) {
                echo '<a href="contact/view/'.$cur_result['contact_id'].'" title="'.mib_html($cur_result['surname']).'">';
                echo mib_html(utf8_strtoupper($cur_result['surname']));
                echo '</a>';
            }
            else if ( $k == 'type' ) {
                echo '<a href="interaction/view/'.$cur_result['interaction_id'].'" title="'.mib_html($cur_result['type']).'">';
                echo mib_html(utf8_strtoupper($cur_result['type']));
                echo '</a>';
            }
            else if ( $k == 'actions' ) {
                echo '<a class="tips iconimg add" href="interaction?contact_id='.$cur_result['contact_id'].'" rel="Add a new interaction" style=""></a>';
            }
            else if ( isset($cur_result[$k]) )
                echo mib_html($cur_result[$k]);
            echo '</td>';
        } // END COLONNE

        echo '</tr>';
    }
} // END LIGNE


$search = mib_search_table_end($search); // fin du tableau de résultats

?>
    <a class="button" href="contact?prospective_id=<?php echo $current_prospective['id'];?>" >Add a new Contact</a>

</fieldset>

<fieldset><legend><?php _ebo('Delete'); ?> :</legend>
    <?php if ( $MIB_USER['can_write_plugin'] ): ?>
        <div class="option-actions">
            <?php mib_form_delete_button('button-secure', $MIB_PLUGIN['name'].'/delete/'.$cur_result['id'], __bo('Delete a Prospective').' : '.mib_html($cur_result['name'])); ?>
        </div>
    <?php endif; ?>
</fieldset>

