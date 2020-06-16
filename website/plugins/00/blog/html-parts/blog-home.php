<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


/* ---------------------------------- Categories -------------------------------------*/

$categories = ['2', '6', '5', '3'];
$state['take'] = 2;
$state['sort_by'] = 'date';
$state['sort_dir'] = 'desc';

?>

    <div class="Container">
        <?php
        foreach ($categories as $categIndex => $categ) :
            $filters = [['field' => 'category', 'value' => $categ],['field' => 'visibleHomePage', 'value' =>'true']];
            $blog->modifyFilters($filters);
            $datas = $list->loadDataByFilter($state, $filters);
           // var_dump($datas);
            $category = $blog->getCategory($categ);
            $list->datas = $datas;
            ?>
            <div class="HomeRow flex justify-space-between">
                <?php
                if ($categIndex % 2 !== 0) mib_blog_home_section_img($category);
                if (empty($datas)):
                    require MIB_BLOG_HTMLPARTS ."blog-home-nodata.php";
                else:
                    foreach ($datas as $index => $data):
                        require MIB_BLOG_HTMLPARTS ."blog-home-item.php";
                    endforeach;
                endif;
                if ($categIndex % 2 === 0) mib_blog_home_section_img($category);
                ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="PreFooter text-center">
        <h2 class="text-uppercase">Rejoignez St Michel sur ses réseaux</h2>
        <?php include  'blog-feeds.php' ?>


        <div class="text-center">
            <a href="https://www.facebook.com/galette.stmichel/" target="_blank">
                <img src="{{tpl:MIB_THEME}}/img/icons/social/light/facebook.png" alt="" aria-hidden="true">
                <span class="visually-hidden">Facebook</span>
            </a>
            <a href="https://twitter.com/StMichel" target="_blank">
                <img src="{{tpl:MIB_THEME}}/img/icons/social/light/twitter.png" alt="" aria-hidden="true">
                <span class="visually-hidden">Twitter</span>
            </a>
            <a href="https://www.instagram.com/stmichel_fr/" target="_blank">
                <img src="{{tpl:MIB_THEME}}/img/icons/social/light/instagram.png" alt="" aria-hidden="true">
                <span class="visually-hidden">Instagram</span>
            </a>
            <a href="http://snapchat.stmichel.fr/" target="_blank" class="mobile-only">
                <img src="{{tpl:MIB_THEME}}/img/icons/social/light/snapchat.png" alt="" aria-hidden="true">
                <span class="visually-hidden">Snapchat</span>
            </a>
            <a href="https://www.linkedin.com/company/st-michel-biscuits/" target="_blank">
                <img src="{{tpl:MIB_THEME}}/img/icons/social/light/linkedin.png" alt="" aria-hidden="true">
                <span class="visually-hidden">LinkedIn</span>
            </a>
        </div>
    </div>
<?php
function mib_blog_home_section_img($category)
{
    require MIB_BLOG_HTMLPARTS ."blog-home-category.php";
}