<?php


//if($accountManager->isAdminBo()) : ?>
<!--    <<form action="" class="Form Form--login text-center" style="min-height: 25rem">-->
<!--        <fieldset class="color-white">-->
<!--            <br>-->
<!--            <br> <br>-->
<!--            page non disponible pour les admins BO-->
<!--        </fieldset>-->
<!---->
<!--    </form>-->
<!--<br><br>-->
<!---->
<?php // return ;   endif ;

$currentUser = $accountManager->getCurrentUser();
if (empty($currentUser))
    exit;
$isStMichel = strpos($currentUser['email'],'stmichel.fr')!==false  ;

$mailChecked = (!empty($currentUser['news']) && $currentUser['news'] !== '0') || $isStMichel ? 'checked' : '';
$mailDisabled = $isStMichel ? 'disabled' : '';
$mailTitle = $isStMichel? 'title="les adresses stmichel.fr reçoivent les newsletters"' : '';


?>


<div class="Form Form--account text-center">
    <fieldset class="color-white">
        <h1><?= $currentUser['firstname'] ?> <?= $currentUser['lastname'] ?></h1>
        <?= $currentUser['email'] ?>
    </fieldset>
</div>

<form action="" method="post" class="Form Form--account text-center">
    <input type="hidden" name="action" value="changePassword">
    <fieldset>
        <legend>Modifier le mot de passe</legend>
        <div class="flex">
            <div class="text-center">
                <ul class="no-style text-left">
                    <li> 8 caractères au moins</li>
                    <li> 1 Majuscule</li>
                    <li> 1 Minuscule</li>
                    <li> 1 Chiffre</li>
                </ul>
            </div>
            <div class="ml2">
                <div class="Form-element">
                    <label class="visually-hidden" for="oldpassword">Mot de passe actuel</label>
                    <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$"  minlength="8" id="oldpassword" name="oldpassword" placeholder="Mot de passe actuel"
                           required>
                </div>
                <div class="Form-element">
                    <label class="visually-hidden" for="newpassword">Nouveau mot de passe</label>
                    <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$"  minlength="8" id="newpassword" name="newpassword" placeholder="Nouveau" required>
                </div>
                <div class="Form-element">

                    <label class="visually-hidden" for="newpasswordconf">Confirmation</label>
                    <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$"  minlength="8" id="newpasswordconf" name="newpasswordconf" placeholder="Confirmation"
                           required>
                </div>

                <div class="Form-element">
                    <button class="button" type="submit">> Enregistrer</button>
                </div>
            </div>

        </div>
    </fieldset>
</form>


<form action="" method="post" class="Form Form--account text-center">
    <input type="hidden" name="action" value="changeEmails">
    <legend>Gérer mes abonnements</legend>

    <div class="Form-element">
        <label <?=$mailTitle?>>
            <input type="checkbox" name="news" value="1" <?= $mailChecked ?> <?=$mailDisabled?> >
            Recevoir les notifications
        </label>
    </div>

    <div class="Form-element">
        <button class="button" type="submit">> Enregistrer</button>
    </div>

</form>
<form action="" method="post" class="Form Form--account  Form--accountDelete text-center">
    <input type="hidden" name="action" value="deleteAccount">

    <legend>Supprimer mon compte</legend>

    <p> Cliquer sur ce bouton pour supprimer votre compte <br>
        <strong>Cette action est définitive</strong></p>
    <div class="Form-element">
        <br>
        <button class="button" type="submit">> Supprimer mon compte</button>
    </div>
</form>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>


<style>

    .jconfirm-holder{
        max-width: 350px;
        margin: 0 auto;
    }
    .jconfirm .jconfirm-box{
        border-radius: 1px;
    }
    .jconfirm.jconfirm-light .jconfirm-box .jconfirm-buttons button{
        font-weight: 300;
    }

</style>

<script>
    var deleteChecked = false ;
    $('.Form--accountDelete').on('submit',function(ev){

        if(deleteChecked)
            return  ;

        var form = $(this);
        ev.preventDefault();
        $.confirm({
            title: 'Confirmation',
            content: 'Voulez vous supprimer votre compte? <br> Cette action est définitive !',
            buttons: {
                confirmer: function () {
                    deleteChecked = true ;
                    form.submit();
                },
                annuler: function () {

                }
            }
        });


    });





</script>

