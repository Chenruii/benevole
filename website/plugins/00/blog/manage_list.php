<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;



$blog = BlogPlugin::getInstance();
$partList = $blog->getContentPartList();



/*--------------------------------------------*/
/*  chargement du formulaire                  */
/*--------------------------------------------*/
$blog->getList();
if (empty($blog->list)) {
    error("Erreur dans le chargement de la liste ");
    exit();
}
// on ajoute les sources de données dynamiques définies dans  team_manage.php
$blog->list->editUrl = $MIB_PLUGIN['name'].'/edit';
$blog->list->deleteUrl = $MIB_PLUGIN['name'].'/delete';
$blog->list->listUrl = $MIB_PLUGIN['name'];
//

$state = $blog->list->getState();
$state['columns']= ['title','slug','publishState','category','site','date','visibleSlider','visibleHomePage','visibleHighLight'];
if(empty($state['sort_by'])){
    $state['sort_by']="date";
    $state['sort_dir']="desc";
}

$list = $blog->getList();


$take= 25;
$filters = null;
$page = empty($_POST['page']) ? 1 : intval($_POST['page']);
$search = empty($_POST['search']) ? '' : $_POST['search'];
$searchValue = '';
if(!empty($search)){
    $search = explode(':',$search);
    $searchField = empty($search[0]) ? null : $search[0];
    $searchValue= empty($search[1]) ? null : $search[1];
    if(!empty($searchValue) && $searchField==='keyword'){
        $filters =  $list->getFilterForKeyWord($searchValue, ['title']);
        $take = 200 ;
    }
}

//




$listDatas = $list->getListData($page, $take , $state,$filters);

//function initPublihedDatas($datas){
//    $form = MibboFormManager::getForm('blog',__DIR__);
//    foreach ($datas as $data){
//
//        $form->loadData($data['_id']);
//        $data['publishState'] = '3';
//        //   var_dump($form);
//        MibboFormManager::saveFormData($form,$data);
//    }
//}
//resetDatas($listDatas['datas']);
//var_dump($listDatas['datas']);

$blog->list->customRenderers['slug']= function($display){
    return '<a href="/'.$display.'" target="_blank">'.$display.'</a> ';
}


/*--------------------------------------------*/
/*  Rendu de la liste                       */
/*--------------------------------------------*/
?>

<h1 xmlns="http://www.w3.org/1999/html">Articles</h1>
    <br>
    <hr>
    <a href="<?php echo $MIB_PLUGIN['name']?>/create" class="Link">
        <svg viewBox="0 0 24 24" width="20" height="20"><g><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M12 7.5v9"></path><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h9"></path><circle class="a" cx="12" cy="12" r="11.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></circle></g></svg>
        Ajouter
    </a>
    <br>
    <br>
    <div>
       <span style="float: right; height: 2rem "> Nombre d'éléments : <b> <?= empty($listDatas['pagination']['totalCount']) ? 0 :$listDatas['pagination']['totalCount']  ?></b>
    </span>
       <input type="text" class="input" id="blog-bo-search" value="<?= $searchValue ?>">
<!--  <button type="button" id="blog-bo-search-btn">Recherche </button>-->
        <a href="<?=$MIB_PLUGIN['name']?>"  data-search="" id="blog-bo-search-link"  method="POST" > Rechercher </a>
    </div>

    <br>
<?php  echo $blog->list->renderFullTable($MIB_PLUGIN['name'], $listDatas,$state) ; ?>
<script type="text/javascript">
    var searchInput  = $$('#blog-bo-search');
    var searchInputLink  = $$('#blog-bo-search-link');
    var searchInputBtn  = $$('#blog-bo-search-btn');

    searchInputLink.addEvents({
        'click' : function() {
            var data =  searchInputLink.retrieve('data');
            searchInputLink.store('data', 'search=keyword:'+searchInput.get('value'));
          //  consosole.log(this);

        }
    })


    // var data =  a.retrieve('data');
    // if(data){
    //     data= data+'&';
    // }
    // data = "search="+a.get('data-search');
    // a.store('data', data);

</script>






