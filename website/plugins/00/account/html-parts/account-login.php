<form action="" class="Form Form--login text-center" method="POST">
    <fieldset>
        <legend>Connexion</legend>
        <?php if (!empty($loginError)): ?>
            <div class="has-error">
               <b> <?= $loginError ?></b>
                <br><br>
            </div>
        <?php endif ?>
        <?php if (!empty($loginInfo)): ?>
            <div>
                <?= $loginInfo ?>
                <br><br>
            </div>
        <?php endif ?>
<!--        <div class="Form-element" style="min-height: 16rem;font-size: 1.5rem;" >-->
<!--            <br><br>Bientôt disponible-->
<!--        </div>-->

        <input type="hidden" name="_token" value="<?= $formCodeLogin ?>">
        <div class="Form-element">
            <label class="visually-hidden" for="email">Email</label>
            <input type="email" id="email" name="username" placeholder="Email" required>
        </div>
        <div class="Form-element">
            <label class="visually-hidden" for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Mot de passe" required>
        </div>

        <div class="Form-element">
            <button class="button" type="submit">> Connexion</button>
        </div>
        <div class="Form-element">
            <a href="/account/register" class="text-center">Vous n'avez pas de compte ? Créez votre compte</a><br> 
        </div>

        <div class="Form-element">
            <button type="button"  class="Button-link" data-action="toggle" data-target=".js-forgotPassword" data-focus="#emailforgotpassword">Mot de passe oublié ?</button>
        </div>

    </fieldset>
</form>

<form action="" class="Form Form--account text-center js-forgotPassword" method="POST"  <?= empty($forgotPasswordInfo) ?'hidden':'' ?>>
    <?php if (!empty($forgotPasswordInfo)): ?>
        <div class="has-error">
           <b> <?= $forgotPasswordInfo ?></b>
            <br><br>
        </div>
    <?php endif ?>

    <input type="hidden" name="action" value="sendForgotPassword">
    <input type="hidden" name="_token" value="<?= $formCodeForgot ?>">
    <legend>Mot de passe oublié</legend>
    <div class="Form-element">
        <div class="text-center">

                Saisissez votre email , pour l'envoi de la procédure de modification de mot de passe

        </div>
        <br>
        <label class="visually-hidden" for="emailforgotpassword">Email</label>
        <input type="email" id="emailforgotpassword" name="email" placeholder="Email" required>
    </div>
    <div class="Form-element">
        <button class="button" type="submit">> Renvoyer</button>
    </div>
</form>

<script type="text/javascript">
    $(function() {
        registerToggle();
    });
</script>
