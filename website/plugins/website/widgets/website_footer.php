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
$links = siteGetFooterMenu($MIB_PAGE['lang']);

?>

 <section id="footer" class="footer">
    <div class="container">
        <div class="row">
                        <div class="main_footer_area white-text p-b-3">
                            <div class="col-md-3">
                                <div class="single_f_widget p-t-3 wow fadeInUp">
                                    <img src="img/logo.png" alt="" />
                                    <div class="single_f_widget_text">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="single_f_widget m-t-3 wow fadeInUp">
                                    <h4 class="text-lowercase">Some features</h4>
                                    <div class="single_f_widget_text f_reatures">
                                        <ul>
                                            <li><i class="fa fa-check"></i>Lorem </li>
                                            <li><i class="fa fa-check"></i>Aliquam </li>
                                            <li><i class="fa fa-check"></i>Vestibu</li>
                                            <li><i class="fa fa-check"></i>Lorem ipsu</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="single_f_widget m-t-3 wow fadeInUp">
                                    <h4 class="text-lowercase">Tags</h4>
                                    <!--<div class="single_f_widget_text f_tags">
                                        <a href="#!">corporate</a>
                                        <a href="#!">agency</a>
                                        <a href="#!">portfolio</a>
                                        <a href="#!">blog</a>
                                        <a href="#!">elegant</a>
                                        <a href="#!">professional</a>
                                        <a href="#!">business</a>
                                    </div>-->
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="single_f_widget m-t-3 wow fadeInUp">
                                    <h4 class="text-lowercase">Flicker Posts</h4>
                                    <!--<div class="single_f_widget_text f_flicker">
                                        <img src="img/flipcker1.jpg" alt="" /> 
                                        <img src="img/flipcker2.jpg" alt="" /> 
                                        <img src="img/flipcker3.jpg" alt="" /> 
                                        <img src="img/flipcker4.jpg" alt="" /> 
                                        <img src="img/flipcker3.jpg" alt="" /> 
                                        <img src="img/flipcker2.jpg" alt="" /> 
                                        <img src="img/flipcker4.jpg" alt="" /> 
                                        <img src="img/flipcker1.jpg" alt="" /> 
                                    </div>-->
                                </div>
                            </div>
                        </div>
        </div>
    </div>
    <footer <?php if ('contact' === $rub) : ?> class="Section"<?php endif ?> role="contentinfo">
        <div class="">
            <nav role="navigation" class="">
                <ul class="">
                    <?php foreach ($links as $link) : ?>
                        <li>
                            <a href="<?= $link['href'] ?>"><?= $link['label'] ?></a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </nav>
          
        </div>
    </footer>

    <div class="main_coppyright">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <div class="copyright_text m-t-2 text-xs-center">
                        <p class="wow zoomIn" data-wow-duration="1s">Bénévolat Voisins</a>© Copyright <?= date('Y') ?> All Rights Reserved</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <!--<div class="socail_coppyright text-sm-right m-t-2 text-xs-center wow zoomIn">
                        <a href="#!"><i class="fa fa-facebook"></i></a>
                        <a href="#!"><i class="fa fa-twitter"></i></a>
                        <a href="#!"><i class="fa fa-google-plus"></i></a>
                        <a href="#!"><i class="fa fa-rss"></i></a>
                        <a href="#!"><i class="fa fa-vimeo"></i></a
                        <a href="#!"><i class="fa fa-pinterest"></i></a>
                        <a href="#!"><i class="fa fa-linkedin"></i></a> 
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</section>




<script type="text/javascript">

</script>

