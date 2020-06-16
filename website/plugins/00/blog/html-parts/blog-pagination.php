<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;





?>





<?php if (!empty($listDatas['pagination']['totalPage']) && $listDatas['pagination']['totalPage'] >= 1) :

    $min = $listDatas['pagination']['page']>4 ?  $listDatas['pagination']['page']-4 : 1 ;
    $max = min($min + 7, $listDatas['pagination']['totalPage']) ;
    //var_dump($listDatas['pagination']);
    ?>
    <div class="Container">
        <div class="text-center">
            <?php if($listDatas['pagination']['page']!==1) : ?>
                <a class="Pagination-prev"   href="<?= mib_get_current_url_with_params(['page' =>$listDatas['pagination']['page']-1 ]) ?>">&lang;</a>
            <?php endif;?>
            <span>
            <?php for ($i = $min; $i <= $max ; $i++) :
                $selected = $i === $listDatas['pagination']['page'] ? 'is-active' : '';
                ?>
                <a class="Pagination-link <?= $selected ?>"   href="<?= mib_get_current_url_with_params(['page' => $i]) ?>"><?= $i ?></a>
            <?php endfor; ?>
            </span>
            <?php if($listDatas['pagination']['page'] < $listDatas['pagination']['totalPage']) : ?>
                <a class="Pagination-next"   href="<?= mib_get_current_url_with_params(['page' =>$listDatas['pagination']['page']+1 ]) ?>">&rang;</a>
            <?php endif;?>
        </div>
    </div>

<?php endif; ?>
<br>
