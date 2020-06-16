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
if ( !defined('MIB_MANAGE')) return;

if(current(explode('/', $MIB_PAGE['info'])) == 'idle')
	require $MIB_PLUGIN['path'].'core/idle.php'; // Gestionnaire d'auto-connexion au BO
else if(!$MIB_USER['is_guest'] && current(explode('/', $MIB_PAGE['info'])) == 'logout')
	require $MIB_PLUGIN['path'].'core/logout.php'; // Déconnexion du BO
else if(!$MIB_USER['is_guest'])
	require $MIB_PLUGIN['path'].'core/admin.php'; // Chargement de l'interface admin du BO
else
	require $MIB_PLUGIN['path'].'core/login.php'; // Page de Connexion du BO