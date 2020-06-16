<?php


class BlogPlugin
{


    /**  @var MIB_DbLayerX $MIB_DB */
    private $MIB_DB;

    /**  @var [] $options Options globales du module */
    private $options = [];

    /**  @var [] $definition Défintion générale et commune d'une article de blog */
    private $definition;

    /**  @var [] $partsDefintion Liste des définitions pour les parties ajoutables */
    private $partsDefintion;

    /**  @var [] string */
    public $sources;

    // liste des champs concerné par la recherche par mot clé
    public $seachFields = [];

    /** @var   MibboList */
    public $list = null;

    public static function getInstance($options = null): BlogPlugin
    {
        $blog = new BlogPlugin();
        if (!empty($options) && is_array($options)) {
            foreach ($options as $k => $v) {
                $blog->setOption($k, $v);
            }
        }
        $blog->init();
        if (function_exists('customizeMibBlog')) {
            customizeMibBlog($blog);
        }

        return $blog;
    }

    public function __construct()
    {
        global $MIB_DB;
        $this->MIB_DB = $MIB_DB;
    }

    /** Permet d'initialiser les différentes varialble en s'appuyant sur les options */
    public function init()
    {

        $defaultOptions = $this->getDefaultOptions();
        $this->options = array_merge($this->options, $defaultOptions);
        $this->definition = MibboFormManager::getForm('blog', __DIR__ . DIRECTORY_SEPARATOR . 'blog.json');
        $this->getContentPartList(); // on initialise les partie de blog
    }

    /** Renvoi les options par défault */
    private function getDefaultOptions()
    {
        $options = [];
        $options['partDirectory'] = __DIR__ . DIRECTORY_SEPARATOR . 'blog-parts' . DIRECTORY_SEPARATOR;
        $options['formKey'] = 'blog';
        $options['dbTableName'] = 'blog';
        return $options;
    }

    public function setOption($option, $value)
    {
        $this->options = $this->options ?? [];
        $this->options[$option] = $value;
    }

    public function getOption($option)
    {
        $this->options = $this->options ?? [];
        return empty($this->options[$option]) ? null : $this->options[$option];
    }

    public function getContentPartList()
    {
        if (!empty($this->partsDefintion)) {
            return $this->partsDefintion;
        }
        $directoty = $this->getOption('partDirectory');
        $parts = [];
        $list = mib_readdir($directoty);
        if ($list === false) {
            $list = [];
        }
        foreach ($list as $file) {
            if ($file === 'blog.json' || substr($file, 0, 5) !== 'blog-' || substr($file, -5) !== '.json') {
                continue;
            }
            $type = substr($file, 5, -5);
            try {
                $strContent = file_get_contents($directoty . $file);
                $def = json_decode($strContent, true);
                $label = !empty($def['label']) ? $def['label'] : $type;
                $parts[] = ['label' => $label, 'type' => $type, 'definition' => $def];

            } catch (Exception $ex) {

            }
        }
        $this->partsDefintion = $parts;
        return $this->partsDefintion;
    }

    public function getForm(): MibboForm
    {
        $form = MibboFormManager::getForm($this->getOption('formKey'), __DIR__);
        $form->sources = $this->sources;
        return $form;
    }

    public function getList(): MibboList
    {
        if (empty($this->list)) {
            $this->list = MibboFormManager::getList($this->getOption('formKey'), __DIR__);
            $this->list->sources = $this->sources;
        }
        return $this->list;
    }

    public function renderJsBo()
    {
        return '
            <script src="/blog/blog_manage.js"></script>
            <script type="text/javascript">
                var blogEngine = new BlogManageEngine();
                blogEngine.init();   
            </script>
        ';
    }

    public function GetListByFilter($filters,$state=null)
    {
        $this->getList();
        $datas = $this->list->loadDataByFilter($state, $filters);
        $this->list->datas = $datas;
        return $datas;
    }

    public function modifyFilters(&$filters){
        $filters[] =  ['field'=>'publishState', 'value'=>"3" ];
    }

    public function GetFormByUrl($url)
    {
        $state= [];
        $state['sort_by'] = 'date';
        $state['sort_dir'] = 'desc';
        $url = str_replace('fr_', '', $url);
        $this->getList();
        $data = $this->list->loadDataByFilter($state, [['field' => 'slug', 'value' => $url]]);
        return empty($data[0]['_id']) ? null : $data[0];
    }

    public function GetListByKeyWord($state, $search, $fields)
    {
        $this->getList();
        $filter = $this->list->getFilterForKeyWord($search,$fields);
        $data = $this->list->loadDataByFilter($state, $filter);
        return empty($data[0]['_id']) ? null : $data[0];
    }

    public function getCategoryIdForUrl($url)
    {
        $categories = !empty($this->sources['category']) ? $this->sources['category'] : [];
        foreach ($categories as $category) {
            if ($category['href'] === $url)
                return $category['id'];
        }
        return false;
    }

    public function getCategory($id)
    {
        $categories = !empty($this->sources['category']) ? $this->sources['category'] : [];
        foreach ($categories as $category) {
            if ($category['id'] === $id)
                return $category;
        }
        return false;
    }

    public function getCodifications($type)
    {
        $items = !empty($this->sources[$type]) ? $this->sources[$type] : [];
        return $items;
    }

    public function getCodification($type,$id)
    {
        $items = !empty($this->sources[$type]) ? $this->sources[$type] : [];
        if(!empty($items)){
            foreach ($items as $item){
                if($item['id']==$id){
                    return $item ;
                }
            }
        }
        return [];
    }

    public function getCategories()
    {
        $categories = !empty($this->sources['category']) ? $this->sources['category'] : [];
        return $categories;
    }

    public function initHandlerPageCateg(){
        $categories = $this->getCategories();
        foreach ($categories as $categ){
            $pageKey = MibboFormManager::getPageKey(['url'=>$categ['href'], 'lang'=>'fr']);
            MibboFormManager::changePageTemplate($pageKey, 'blog','blog');
        }
    }

    public function getFeeds($type, $count){

        $path = MIB_ROOT . DIRECTORY_SEPARATOR . "feeds" . DIRECTORY_SEPARATOR . $type.'.json';

        if (file_exists($path)) {
            $f = file_get_contents($path);
            $datas = json_decode($f,true);
            if(empty($datas))
                return [];

            switch ($type){
                case 'instagram':
                    return $this->getFeedsInstagram($datas, $count);
                    break;

            }
        }
    }

    private function getFeedsInstagram($datas , $count){
        $feeds= [];
        if (!empty($datas['feed']['medias'])) {

            $liste = $datas['feed']['medias'];
            $i = 0 ;

            foreach ($liste as  $it) {
                if ($i >= $count)
                    break;
                $item = ['img' => $it['thumbnailSrc'], 'link' => $it['link'], 'date' => $it['date']['date'],'title'=>$it['caption'],'type'=>'instagram'];
                $feeds[] = $item;
                $i ++;
            }
        }
        return $feeds;
    }

}


