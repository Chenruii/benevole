<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
defined('MIB_MANAGE') or exit;

// Requette JSON
defined('MIB_JSON') or exit;
define('MIB_JSONED', 1);

// Is get_host an IP address ?
if (@preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $_GET['ip'])) {
	$ip = $_GET['ip'];
	$host = @gethostbyaddr($ip);

	$MIB_PLUGIN['json'] = array(
		'title'		=> $ip,
		'value'		=> __('Le nom de l\'hôte est : '.$host),
		'options'		=> array('duration' => -1)
	);
}
else
	error(__('Le lien que vous avez suivi est incorrect ou périmé.'));