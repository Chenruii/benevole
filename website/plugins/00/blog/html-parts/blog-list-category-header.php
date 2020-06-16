<div class="CategoryBanner  CategoryBanner-<?= $blog->category['id'] ?>  text-center" style="background-image: url('{{tpl:MIB_THEME}}/img/categories/category-<?= $blog->category['id'] ?>-header.jpg');">
    <h1 class="CategoryBanner-title"> <?=  $blog->category['label'] ?></h1>
    <div class="CategoryBanner-logo">
        <img src="{{tpl:MIB_THEME}}/img/categories/category-<?= $blog->category['id'] ?>-white.png" alt="<?=  $blog->category['label'] ?>">
    </div>
</div>

<?php
 $currentYear = date('Y');
 $yearList = range(2019,$currentYear);

 $siteListe = $blog->getCodifications('site');

?>


<div class="DataBar text-uppercase">
    <div class="Container">
        <form action="">
            <fieldset>
                <legend class="visually-hidden">Filtrez les articles</legend>
                <div class="flex justify-center align-items-center">
                    <h2 class="mb0" aria-hidden="true">Filtrer les articles</h2>
                    <div class="Form-element">
                        <label class="visually-hidden" for="site">Site</label>
                        <select name="site" id="site" class="js-filter">
                            <option value="<?= mib_get_current_url_with_params(['site'=>''])?>">Tous les sites</option>
                            <?php
                                foreach ($siteListe as $site):
                                $selected = mib_get_current_params('site')==$site['id'] ? 'selected':null;
                                ?>
                                <option <?= $selected ?> value="<?= mib_get_current_url_with_params(['site'=>$site['id']])?>"><?=$site['label']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="Form-element">
                        <label class="visually-hidden" for="year">Année</label>
                        <select name="year" id="year" class="js-filter">
                            <option value="<?= mib_get_current_url_with_params(['year'=>''])?>">Toutes les  années</option>
                            <?php foreach ($yearList as $year):
                                $selected = mib_get_current_params('year')==$year ? 'selected':null;
                                ?>
                                <option <?= $selected ?> value="<?= mib_get_current_url_with_params(['year'=>$year])?>"><?=$year?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<script type="text/javascript">

    var filters = document.querySelectorAll('.js-filter');
    if(filters && filters.length){
        filters.forEach(function(el){
            el.addEventListener('change',function(){
                var url = this.value;
                if(url){
                   window.location.href=  url;
                }
            });
        });
    }


</script>