<?php


// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

$imgInterview =  $form->getFieldRawDatas('interviewImage');
$imageInterview = !empty($imgInterview['path']) ? $imgInterview['path'] : '' ;
$interviewImageTitle = !empty($imgInterview['legend']) ? $imgInterview['legend'] : '' ;
$interviewTitle = $form->getFieldDisplay('interviewTitle');
?>

<div class="Post-interviews">
<?php foreach (range(1,10) as $i) :
     if($i===1 && !empty($interviewTitle)) :?>
    <div class="p2 Post-interview-title"><?=   $interviewTitle ?> </div>
    <?php endif;

    if(!empty($imageInterview) && $i===1) : ?>
    <figure class="p2 Post-interview-image">
        <img src="<?=$imageInterview ?>" />
        <?php if(!empty($interviewImageTitle)) :?>
            <figcaption class="text-center"><?=$interviewImageTitle ?> </figcaption>
        <?php endif; ?>
    </figure>
<?php endif;

    $question = $form->getFieldDisplay('interviewQuestion'.$i);
    $response = $form->getFieldDisplay('interviewResponse'.$i);
    if(empty($question) || empty($response))
        continue;
    ?>
    <div class="Post-interview">
        <div class="Post-interview-item">
            <div class="p1 Post-interview-question"><?= $question ?> </div>
            <div class="p1 Post-interview-response"><?= $response ?></div>
        </div>
    </div>
<?php endforeach;?>
</div>
<div style="clear:both;"></div>