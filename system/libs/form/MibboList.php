<?php
/**
 * Created by PhpStorm.
 * User: emeline
 * Date: 02/10/2018
 * Time: 13:16
 */

class MibboList extends MibboAbstractRender
{

    /** @var array liste des champs de la liste */
    public $fields = null;

    /** @var array liste des données */
    public $datas = [];

    /** @var array liste des données de pagination */
    public $paginationInfos = [];
    /** @var string  clé de la liste */
    public $key = null;

    public $editUrl = '';
    public $deleteUrl = '';
    public $listUrl = '';



    public $sortSvg = '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="16" height="16" viewBox="0 0 401.998 401.998" style="enable-background:new 0 0 401.998 401.998;"
	 xml:space="preserve">
	<g>
		<path d="M73.092,164.452h255.813c4.949,0,9.233-1.807,12.848-5.424c3.613-3.616,5.427-7.898,5.427-12.847
			c0-4.949-1.813-9.229-5.427-12.85L213.846,5.424C210.232,1.812,205.951,0,200.999,0s-9.233,1.812-12.85,5.424L60.242,133.331
			c-3.617,3.617-5.424,7.901-5.424,12.85c0,4.948,1.807,9.231,5.424,12.847C63.863,162.645,68.144,164.452,73.092,164.452z" fill="#dadada"/>
		<path d="M328.905,237.549H73.092c-4.952,0-9.233,1.808-12.85,5.421c-3.617,3.617-5.424,7.898-5.424,12.847
			c0,4.949,1.807,9.233,5.424,12.848L188.149,396.57c3.621,3.617,7.902,5.428,12.85,5.428s9.233-1.811,12.847-5.428l127.907-127.906
			c3.613-3.614,5.427-7.898,5.427-12.848c0-4.948-1.813-9.229-5.427-12.847C338.139,239.353,333.854,237.549,328.905,237.549z" fill="#dadada"/>
	</g>
    </svg>';
    public $sortSvgSorted = '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="16" height="16" viewBox="0 0 401.998 401.998" style="enable-background:new 0 0 401.998 401.998;"
	 xml:space="preserve">
	<g>
		<path d="M73.092,164.452h255.813c4.949,0,9.233-1.807,12.848-5.424c3.613-3.616,5.427-7.898,5.427-12.847
			c0-4.949-1.813-9.229-5.427-12.85L213.846,5.424C210.232,1.812,205.951,0,200.999,0s-9.233,1.812-12.85,5.424L60.242,133.331
			c-3.617,3.617-5.424,7.901-5.424,12.85c0,4.948,1.807,9.231,5.424,12.847C63.863,162.645,68.144,164.452,73.092,164.452z" fill="currentColor"/>
		<path d="M328.905,237.549H73.092c-4.952,0-9.233,1.808-12.85,5.421c-3.617,3.617-5.424,7.898-5.424,12.847
			c0,4.949,1.807,9.233,5.424,12.848L188.149,396.57c3.621,3.617,7.902,5.428,12.85,5.428s9.233-1.811,12.847-5.428l127.907-127.906
			c3.613-3.614,5.427-7.898,5.427-12.848c0-4.948-1.813-9.229-5.427-12.847C338.139,239.353,333.854,237.549,328.905,237.549z" fill="#dadada"/>
	</g>
    </svg>';
    public $editSvgIcon = '<svg viewBox="0 0 24 24" width="24" height="24"><g><path class="a" d="M22.63 14.87L15 22.5l-3.75.75.75-3.75 7.63-7.63a2.114 2.114 0 0 1 2.992 0l.008.008a2.114 2.114 0 0 1 0 2.992z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h10.5"></path><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M3.75 11.25h10.5"></path><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M3.75 15.75H9"></path><path class="a" d="M7.5 20.25H2.25a1.5 1.5 0 0 1-1.5-1.5V2.25a1.5 1.5 0 0 1 1.5-1.5h10.629a1.5 1.5 0 0 1 1.06.439l2.872 2.872a1.5 1.5 0 0 1 .439 1.06V8.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>';
    public $deleteSvgIcon = '<svg viewBox="0 0 24 24" width="18" height="18"><g><circle class="a" cx="11.998" cy="12" r="11.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></circle><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M7.498 16.5l8.999-9"></path><path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M16.498 16.5l-9.001-9"></path></g></svg>';
    public $nonListFieldTypes = ['paragraph', 'image', 'gallery', 'file', 'filelist', 'separator'];

    public function renderTable($datas, $state)
    {
        $this->datas = empty($datas) ? [] : $datas;
        $str = $this->renderTableHead($state);
        $str .= $this->renderTableBody($state);
        $str .= '</table>';
        return $str;
    }

    public function renderTableHead($state)
    {
        $str = '<table class="MibboTable"><tr>';
        if (!empty($this->editUrl)) {
            $str .= '<th></th>';
        }
        foreach ($this->fields as $field) {
            if (in_array($field['type'], $this->nonListFieldTypes))
                continue;

            if ($state && !empty($state['columns']) && is_array($state['columns'])) {
                if (!in_array($field['key'], $state['columns'])) {
                    continue;
                }
            }

            $sortCss = '';
            if ($state && $state['sort_by'] === $field['key']) {
                $sortIcon = $this->sortSvgSorted;
                $sortCss = 'sorted-' . ($state['sort_dir'] === 'desc' ? 'desc' : 'asc');

            } else {
                $sortIcon = $this->sortSvg;
            }

            $str .= '<th class="' . $field['key'] . '">' . $field['label'] . ' 
                    <a target="' . $this->listUrl . '" href="' . $this->listUrl . '?sort_by=' . $field['key'] . '" class="' . $sortCss . '">' . $sortIcon . '</a>
                    </th>';
        }
        $str .= '</tr>';

        return $str;
    }

    public function renderTableBody($state)
    {

        $str = '';
        foreach ($this->datas as $index => $data) {
            if(empty($data['_id']))
                continue;
            $str .= '<tr>';

            if (!empty($this->editUrl)) {
                $url = $this->editUrl . '/' . $data['_id'];
                $str .= '
                <td class="MibboTable-actions" >
                    <a  target="' . $url . '" title="éditer/'.$data['_id'].'" href="' . $url . '" data-confirmclose="true">' . $this->editSvgIcon . '</a>
                </th>';
            }


            foreach ($this->fields as $field) {
                if (in_array($field['type'], $this->nonListFieldTypes))
                    continue;


                if ($state && !empty($state['columns']) && is_array($state['columns'])) {
                    if (!in_array($field['key'], $state['columns'])) {
                        continue;
                    }
                }
                $display = $this->getFieldDisplay($index, $field['key']);
                $str .= '<td class="' . $field['key'] . '">' . $display . '</th>';
            }

            $str .= '</tr>';
        }
        return trim($str);
    }

    public function loadData($state = [])
    {
        $datas = MibboFormManager::getListData($this, $state);
        $this->datas = $datas;
        return $datas;
    }

    public function getFilterForKeyWord($search, $fields, $categoriesKey= null)
    {
        $search = trim($search);
        $textSearch = ['search' => $search, 'fields' => $fields, 'field' => 'keyWordSearch'];
        $retour = [$textSearch];
        $matchingCategories  = [];
        if(!empty($categoriesKey)){
            foreach ($categoriesKey as $key ){
                if($this->sources && $this->sources[$key]){
                    foreach($this->sources[$key] as $srcIt){
                        $pos = strpos(strtolower($srcIt['label']) , strtolower($search) );
                        $debug [] = $search .'/'. $srcIt['label'] . "=>" . ($pos === false ? 'false' : $pos ) ;
                        if($pos!==false){
                            $matchingCategories[$key] =  $matchingCategories[$key] ?? [];
                            $matchingCategories[$key][] = $srcIt['id'];
                        }
                    }
                }
            }
        }
        if(!empty($matchingCategories)){
            foreach ($matchingCategories as $k => $ids ){
                $retour[] = ['ids' => $ids, 'key' => $k , 'field' => 'categorySearch'];
            };
        }
        return $retour;
    }

    public function getListData($page, $take, $state = [], $filters = null)
    {


        $count = MibboFormManager::getCountData($this, $state, $filters);
        $page = $page < 1 ? 1 : $page;
        $theoricalStart = ($page - 1) * $take;
        $totalPage = intval($count / $take);
        $totalPage = $count % $take !== 0 ? $totalPage + 1 : $totalPage;
        $start = ($theoricalStart < $count) ? $theoricalStart : 0;
        $page = $start === 0 ? 1 : $page;

        $state['start'] = $start;
        $state['take'] = $take;
        $datas = MibboFormManager::getListData($this, $state, $filters);
        $this->datas = $datas;
        //$this->paginationInfos= ['totalCount'=>$count,'take'=>$take, 'start'=>$start ,'page'=>$page ,'totalPage'=>$totalPage];

        return ['datas' => $datas, 'pagination' => ['totalCount' => $count, 'take' => $take, 'start' => $start, 'page' => $page, 'totalPage' => $totalPage]];
    }

    public function loadDataByFilter($state, $filters)
    {
        $datas = MibboFormManager::getListData($this, $state, $filters);
        $this->datas = $datas;
        return $datas;
    }

    public function getState()
    {

        $state = empty($_SESSION['mibboList' . $this->key]) ? [] : $_SESSION['mibboList' . $this->key];
        if (empty($state)) {
            $state = ['sort_by' => null, 'sort_dir' => '', 'keyword' => ''];
        }
        if (isset($_GET['sort_by'])) {
            $sort = mib_clean($_GET['sort_by']);
            if ($sort === $state['sort_by']) {
                $state['sort_dir'] = $state['sort_dir'] === 'desc' ? 'asc' : 'desc';
            } else {
                $state['sort_dir'] = 'asc';
            }
            $state['sort_by'] = $sort;
        }
        if (isset($_GET['keyword'])) {
            $state['keyword'] = mib_clean($_GET['keyword']);
        }
        $_SESSION['mibboList' . $this->key] = $state;
        return $state;
    }

    /** retour l'affichage pour un champ  */
    public function getFieldDisplay($index, $fieldKey,$options=null)
    {
        $datas = !empty($this->datas[$index]) ? $this->datas[$index] : [];
        $id = !empty($datas['_id']) ? $datas['_id'] : null;
        return $this->getDisplay($id,$datas,$fieldKey,$options);
    }


    public function getFieldRaw($index, $fieldKey)
    {
        $data = !empty($this->datas[$index][$fieldKey]) ? $this->datas[$index][$fieldKey] : '';
        return $data;

    }


    /** retour l'affichage pour un champ  */
    public function getFieldDisplayLang($index, $fieldKey,$options=null)
    {
        global $MIB_PAGE;
        $fieldKey = $fieldKey . strtoupper($MIB_PAGE['lang']);
        return $this->getFieldDisplay($index, $fieldKey,$options);

    }

    public function renderFullTable($url, array $listDatas, array $state)
    {

        if (!empty($listDatas['pagination']['totalPage']) && $listDatas['pagination']['totalPage'] > 1) {
            $this->renderPagination($url, $listDatas);
        }
        $datas = empty($listDatas['datas'])? $listDatas : $listDatas['datas'];
        echo $this->renderTable($datas, $state);
    }

    public function renderPagination($url, $listDatas)
    {
        $min = $listDatas['pagination']['page'] > 4 ? $listDatas['pagination']['page'] - 4 : 1;
        $max = min($min + 25, $listDatas['pagination']['totalPage']);
        //var_dump($listDatas['pagination']);
        ?>
        <div class="MibboPagination ">
            <?php for ($i = $min; $i <= $max; $i++) :
                $selected = $i === $listDatas['pagination']['page'] ? 'is-active' : '';
                ?>
                <a class="MibboPagination-link <?= $selected ?>" data-page="<?= $i ?>"
                   method="POST" href="<?=$url?>"> <?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php
    }


}