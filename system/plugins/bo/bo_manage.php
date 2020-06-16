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

$MIB_PLUGIN['core'] = mib_trim(current(explode('/', $MIB_PAGE['info'])));

if(file_exists($MIB_PLUGIN['path'].'core/'.$MIB_PLUGIN['core'].'.php'))
	require $MIB_PLUGIN['path'].'core/'.$MIB_PLUGIN['core'].'.php';