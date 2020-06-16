<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 15:32
 */
defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

if ( defined('MIB_AJAX') ) define('MIB_AJAXED', 1); // authorise les requettes ajax

$prospectives = array();

$query = array(
    'SELECT'	=> '*',
    'FROM'		=> 'prospective',
    'WHERE'		=> 'actived > 0',
    'ORDER BY'	=> 'position ASC',
);
$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
if ( $MIB_DB->num_rows($result) ) {
    while($cur_result = $MIB_DB->fetch_assoc($result)){
        $prospectives[$cur_result['id']] = $cur_result;
    }
}