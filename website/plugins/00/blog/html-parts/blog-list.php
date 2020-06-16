<?php


// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

$type = $blog->typePage;
switch ($type) {

    case 'list': // liste de catégorie d'articles
        require_once MIB_BLOG_HTMLPARTS . 'blog-list-category-header.php';
        break;
    case 'search': // recherche
        require_once MIB_BLOG_HTMLPARTS . 'blog-list-search-header.php';
        break;
    case 'site': // recherche
        require_once MIB_BLOG_HTMLPARTS . 'blog-list-site-header.php';
        break;

}
?>
    <div class="Container">
        <div class="Box PostList Hen">
            <?php if (empty($list->datas)):

                require MIB_BLOG_HTMLPARTS . "blog-list-nodata.php";

            else :
                foreach ($list->datas as $index => $data):
                    require MIB_BLOG_HTMLPARTS . "blog-list-item.php";
                endforeach;
            endif;
            ?>
        </div>
    </div>

<?php require_once 'blog-pagination.php' ?>


<?php if (!empty($blog->category) && $blog->category['id'] === '2'): ?>
    <div class="PreFooter text-center">
        {{widget:website map}}
    </div>
<?php endif ?>