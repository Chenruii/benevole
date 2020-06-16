<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 26/03/2019
 * Time: 12:15
 */


define("MIB_BLOG_PATH", MIB_PATH_VAR."plugins".DIRECTORY_SEPARATOR.'blog'.DIRECTORY_SEPARATOR);
define("MIB_BLOG_HTMLPARTS", MIB_BLOG_PATH."html-parts".DIRECTORY_SEPARATOR);
if(!class_exists('BlogPlugin'))
    require_once MIB_BLOG_PATH.'BlogPluginClass.php';


function customizeMibBlog(BlogPlugin $blog ){

    $sources = [];
    $sources['category'] = [
        ['id'=>'1','label'=>'L\'Entreprise','href'=>'entreprise'],
        ['id'=>'2','label'=>'La vie des sites','href'=>'la-vie-des-sites'],
        ['id'=>'6','label'=>'Nous, St Michelois','href'=>'nous-st-michelois'],
        ['id'=>'5','label'=>'Nos produits','href'=>'nos-produits'],
        ['id'=>'3','label'=>'Côté clients','href'=>'cote-client'],
        ['id'=>'4','label'=>'Événements','href'=>'evenements'],
        ['id'=>'7','label'=>'Le saviez-vous ? ','href'=>'le-saviez-vous']
    ];
    $sources['site'] = [
        ['id'=>'3','label'=>'St Michel Chef Chef','href'=>'site-st-michel'],
        ['id'=>'1','label'=>'Guingamp','href'=>'site-guingamp'],
        ['id'=>'2','label'=>'Avranches','href'=>'site-avranches'],
        ['id'=>'4','label'=>'Commercy','href'=>'site-commercy'],
        ['id'=>'5','label'=>'Grobost','href'=>'site-grobost'],
//        ['id'=>'6','label'=>'Bovetti','href'=>'site-bovetti'],
        ['id'=>'7','label'=>'Champagnac','href'=>'site-champagnac'],
        ['id'=>'11','label'=>'Donsuemor','href'=>'site-donsuemor'],
        ['id'=>'8','label'=>'Contres','href'=>'site-contres'],
        ['id'=>'9','label'=>'Entrepôt Blois','href'=>'site-blois'],
        ['id'=>'10','label'=>'St Michel Développement','href'=>'site-st-michel-devel'],
    ];



    $blog->sources = $sources;
    $blog->searchFields = ['title','slug','summary','titleParagraph1','contentParagraph1','titleParagraph2','contentParagraph2','calloutTitle','calloutText','tag'];
}