<?php


// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$accountManager = new AccountPlugin();
$accountManager->redirectIfNotLogged();


$blog = BlogPlugin::getInstance();


if($MIB_PAGE['url']===''){ // home
    $list = $blog->getList();
    $state=$list->getState();
    require_once MIB_BLOG_HTMLPARTS.'blog-home.php';
    return;
}



$takeForList = 10 ;
if($MIB_PAGE['url']==='site-articles') {

    $filters = [];



    $site = !empty($_GET['site']) ? intval($_GET['site']) : null ;
    if(!empty($site)){
        $filters[]=  ['field'=>'site', 'value'=>$site ];
    }
    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1 ;

    $list = $blog->getList();
    $state = $list->getState();
    $state['sort_by']= 'date';
    $state['sort_dir']= 'desc';
    $blog->modifyFilters($filters);
    $listDatas = $list->getListData($page,$takeForList,$state,$filters);
    $blog->typePage = 'site';
    $blog->site = $blog->getCodification('site',$site);
    require_once MIB_BLOG_HTMLPARTS.'blog-list.php';
    return;
}

// ici on gère les liste de recherche
if($MIB_PAGE['url']==='recherche'){

    $search = !empty($_GET['recherche']) ?$_GET['recherche'] : "Préciser une recherche" ;
    $list = $blog->getList();
    $state=$list->getState();
    $state['sort_by']= 'date';
    $state['sort_dir']= 'desc';
    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1 ;
    //$searchFields = ['title','slug','summary','titleParagraph1','contentParagraph1','titleParagraph2','contentParagraph2','calloutTitle','calloutText'];
    $filters = $list->getFilterForKeyWord($search, $blog->searchFields,['site']);

    $blog->modifyFilters($filters);
    $listDatas = $list->getListData($page,$takeForList,$state,$filters);
    $blog->typePage = 'search';
    $blog->search = $search ;
    require_once MIB_BLOG_HTMLPARTS.'blog-list.php';
    return;
}


// ici on gère les liste de pages
$idCateg = $blog->getCategoryIdForUrl($MIB_PAGE['url']);
if(!empty($idCateg)){


    $filters = [];
    $filters[]=  ['field'=>'category', 'value'=>$idCateg ];


    $site = !empty($_GET['site']) ? intval($_GET['site']) : null ;
    if(!empty($site)){
        $filters[]=  ['field'=>'site', 'value'=>$site ];
    }
    $year = !empty($_GET['year']) ? intval($_GET['year']) : null ;
    if(!empty($year)){
        $filters[]=  ['field'=>'year', 'value'=>$year ];
    }

    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1 ;

    $list = $blog->getList();
    $state = $list->getState();
    $state['sort_by']= 'date';
    $state['sort_dir']= 'desc';
    $blog->modifyFilters($filters);
    $listDatas = $list->getListData($page,$takeForList,$state,$filters);
    $blog->typePage = 'list';
    $blog->category = $blog->getCategory($idCateg);
    require_once MIB_BLOG_HTMLPARTS.'blog-list.php';
    return;
}

// on gère els détail
// on essaie de trouver l'enregistrement à partir de l'URL
$data = $blog->GetFormByUrl($MIB_PAGE['url']);

if(empty($data['_id']))
    mib_error_404();

// si on le trouve on affiche la page
$form = $blog->getForm();
$form->loadData($data['_id']);
$blog->category = $blog->getCategory($form->datas['category']);
require_once MIB_BLOG_HTMLPARTS.'blog-detail.php';





