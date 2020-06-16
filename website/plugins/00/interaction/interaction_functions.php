<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 15:44
 */
/**
 * Vérification des valeurs
 *
 * @param {array} $values
 * @param {string} $dbtable
 * @param {int} $id
 */
function interaction_verif ($values, $dbtable, $id = false) {
    global $MIB_DB, $MIB_CONFIG;

    $verif = array(
        'date'				    => !empty($values['date']) ? utf8_strtoupper(mib_clean($values['date'])) : '',
        'type'				    => !empty($values['type']) ? utf8_strtoupper(mib_clean($values['type'])) : '',
        'description'			=> !empty($values['description']) ? mib_trim($values['description']) : '',
        'contact_alpha'			=> !empty($values['contact_alpha']) ? mib_trim($values['contact_alpha']) : '',
        'notes_interactions'	=> !empty($values['note_interactions']) ? mib_trim($values['note_interactions']) : '',
        'contact_id'	    => !empty($values['contact_id']) ? mib_clean($values['contact_id']) : '',
    );

    if ( empty($verif['type']) )
        mib_error_set(__bo('please give your Type.'), 'type');
    if ( empty($verif['contact_id']) )
        mib_error_set(__bo('please select your Investor Contact'), 'contact_id');
//    if ( empty($verif['contact_alpha']) )
//        mib_error_set(__bo('please give your Contact alpha.'), 'contact_alpha');
//    if ( empty($verif['contact_investor']) )
//        mib_error_set(__bo('please give your Contact Investor.'), 'contact_investor');
    return $verif;
}

/**
 * Ajoute
 *
 * @param {array} $values
 * @param {string} $dbtable
 */
function interaction_add($values, $dbtable) {
    global $MIB_DB;

    // vérification des données
    $verifed = interaction_verif($values, $dbtable);

    if ( !mib_error_exists() ) { // aucune erreur

        // ajoute les données en base de données
        $insert = array();
        foreach( $verifed as $k => $v ) {
            if ( !empty($v) || $v == '0' ) {
                $insert[$k] = '\''.$MIB_DB->escape($v).'\'';
            }
        }
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
function interaction_update($id, $values, $dbtable) {
    global $MIB_DB;

    if ( $cur_result = mib_db_get_row_from_table($dbtable, $id) ) {
        // vérification des données
        $verifed = interaction_verif($values, $dbtable, $cur_result['id']);

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
function interaction_delete($id,$dbtable){
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
 * types de interaction :
 *
 * @return {array}
 */
function interaction_type($type = false)
{
    $types = array(
        'Meeting' => 'Meeting',
    );

    if ($type !== false) {
        if (array_key_exists($type, $types))
            return $types[$type];
        else
            return $type;
    }
    return $types;
}


function interaction_select_contact()
{
    global $MIB_DB;

    $dbtable = 'contact';

    // compte le nombre de résultats
    $search['query']['count'] = array(
        'SELECT'	=> 'COUNT(id)',
        'FROM'		=> $dbtable,
    );
    $search = mib_search_count($search);

    // requette des résutats
    $search['query']['result'] = array(
        'SELECT'	=> '*',
        'FROM'		=> $dbtable,
    );
    $search['start_from'] = 0;
    $search['num_results_by_page'] = $search["num_results"];
    $search['sort_by'] = 'id';
    $search = mib_search_result($search);

    $liste = array();
    while ( $cur_result = $MIB_DB->fetch_assoc($search['result']) )
    {
        $liste[ $cur_result["id"] ] = ucfirst($cur_result["surname"])." ".ucfirst($cur_result["firstname"]);
    }
    return $liste;
}


