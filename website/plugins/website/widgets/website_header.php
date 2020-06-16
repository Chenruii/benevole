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

<header role="banner" class="">
    <div class='preloader'><div class='loaded'>&nbsp;</div></div>

        <nav class="navbar navbar-fixed-top navbar-light bg-faded">
            <!--Collapse button-->
            <div class="container">
                <a href="#" data-activates="mobile-menu" class="button-collapse right"><i class="fa fa-bars black-text"></i></a>

                <!--Content for large and medium screens-->
                <div class="navbar-desktop">
                    <!--Navbar Brand-->
                    <a class="navbar-brand" href="#home"><img src="img/logo.png" alt="" /></a>
                    <!--Links-->
                    <ul class="nav navbar-nav pull-right hidden-md-down text-uppercase">
                <?php foreach ($links as $link) : ?>
                     <li class="nav-item">
                        <a class="nav-link" href="<?= $link['href'] ?>"><?= $link['label'] ?></a>
                    </li>
                <?php endforeach ?>
                    </ul>
                </div>

                <!-- Content for mobile devices-->
                <div class="navbar-mobile">

                    <ul class="side-nav" id="mobile-menu">
                    <?php foreach ($links as $link) : ?>
                     <li class="nav-item">
                        <a class="nav-link" href="<?= $link['href'] ?>"><?= $link['label'] ?></a>
                    </li>
                    <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </nav>

  <div class="">
        <nav role="navigation">
            
        </nav>
  </div>
</header>