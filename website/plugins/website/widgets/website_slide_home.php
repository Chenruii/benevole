<?php

/*---

	Copyright (c) 2010-2014 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit; // Assurons nous que le script n'est pas executé "directement"

$rub = $MIB_PAGE['rub'];
$links = siteGetTopMenu($MIB_PAGE['lang']);
?>

<div id="home" class="slider">
            <ul class="slides">
                <li>
                    <img src="../theme/website/img/homebenner1.png"> <!-- random image -->
                    <div class="caption center-align">
                        <div class="single_home">
                            <h1>Solidarité</h1>
                            <p>To fish peach or not to peach fish</p>
                            <button type="button" class="btn btn-danger m-t-3 waves-effect waves-red"><a href="annonce"></a>Lire plus</button>
                        </div>
                    </div>
                </li>
                <li>
                    <img src="../theme/website/img/homebenner.jpg"> <!-- random image -->
                    <div class="caption center-align">
                        <div class="single_home">
                           <h1>Solidarité</h1>
                            <p>To fish peach or not to peach fish</p>
                            <button type="button" class="btn btn-danger m-t-3 waves-effect waves-red">Lire plus</button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
