<?php

/*---
z
	Copyright (c) 2010-2014 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit; // Assurons nous que le script n'est pas executé "directement"

/*
	FICHIER DE FONCTIONS COMMUNES CHARGÉES PAR MIBBO
*/

function siteGetTopMenu($lang){
    $links = array(
        array(
            'href' => 'theme',
            'label' => __('Thèmes'),
        ),
        array(
            'href' => 'annonce',
            'label' => __('Annonces'),
        ),
        array(
            'href' => 'about',
            'label' =>__('About'),
        ),
        array(
            'href' => 'association',
            'label' =>__('Offre'),
        ),
        array(
            'href' => 'contact',
            'label' => __('Contactez-nous'),
        ),
       
    );
    return $links;
}

function siteGetFooterMenu($lang){
    $links = array(
        array(
            'href' => 'contact',
            'label' => __('Contactez-nous'),
        ),
        array(
            'href' => 'legal',
            'label' => __('Mentions légales'),
        )
    );
    return $links;
}
