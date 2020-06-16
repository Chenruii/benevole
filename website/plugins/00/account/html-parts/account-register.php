<form action="" class="Form Form--login text-center" method="POST">
    <fieldset>
        <legend>Première connexion</legend>

        <?php if (!empty($registerError)): ?>
            <div class="has-error">
                <?= $registerError ?>
            </div>
        <?php endif ?>


<!--        <div class="Form-element" style="min-height: 16rem;font-size: 1.5rem;" >-->
<!--            <br><br>Bientôt disponible-->
<!--        </div>-->

        <div class="Form-element">
            Merci d'indiquer votre prénom et nom tout en <b>MAJUSCULES</b>,
            <br><b>sans accents</b>. Les espaces et trait d'union - sont autorisés
        </div>
        <div class="Form-element">
                Le mot de passe doit contenir : <br> 8 caractères au moins ,1 Majuscule,  1 Minuscule ,1 Chiffre
        </div>

        <div class="Form-element">
            <label class="visually-hidden" for="lastname">Nom</label>
            <input type="text" id="lastname" name="lastname" placeholder="Nom" required>
        </div>
        <div class="Form-element">
            <label class="visually-hidden" for="firstname">Prénom</label>
            <input type="text" id="firstname" name="firstname" placeholder="Prénom" required>
        </div>
        <div class="Form-element">
            <label class="" for="birthdate">Date de naissance</label> <br/>
            <input type="date" id="birthdate" name="birthdate" placeholder="Date de naissance" required>
        </div>
        <div class="Form-element">
            <label class="visually-hidden" for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Email" autocomplete="off" required>
        </div>
        <div class="Form-element">
            <label class="visually-hidden" for="email">Mot de passe</label>
            <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$"
                   title="au mois huit  caractères dont une majuscule, une minuscule, un chiffre" minlength="8"
                   id="password" name="password" placeholder="Mot de passe" required autocomplete="off">
        </div>
        <!--        <div class="Form-element">-->
        <!--            <label class="visually-hidden" for="newpasswordconf">Confirmation</label>-->
        <!--            <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$"  minlength="8" id="newpasswordconf" name="newpasswordconf" placeholder="Confirmation"-->
        <!--                   required>-->
        <!--        </div>-->
        <div class="Form-element">
            <label for="news"> <input type="checkbox" id="news" name="news" value="1">Recevoir les notifications</label>
        </div>

        <div class="Form-element">
            <button class="button" type="submit">> S'enregistrer</button>
        </div>
    </fieldset>
</form>


<script type="text/javascript">
    $(function() {
        var checkNotifcation = $('#news');
        $('#email').on('change',function(){
            if($(this).val().indexOf('@stmichel.fr')!==-1){
                checkNotifcation.prop('checked',true);
                checkNotifcation.prop('disabled',true);
            }else {
                checkNotifcation.prop('disabled',false);
            }
        })
    });
</script>

