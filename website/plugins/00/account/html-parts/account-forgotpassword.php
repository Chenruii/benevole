


<?php if (!empty($forgotPasswordError)): ?>
    <form action="" method="post" class="Form Form--account text-center">
        <legend>Modifier le mot de passe</legend>
        <br><br>
        <div class="has-error">
            <b><?= $forgotPasswordError ?></b>
        </div>
    </form>
<?php else : ?>
    <form action="" method="post" class="Form Form--account text-center">
        <input type="hidden" name="_token" value="<?= $formCode ?>">
        <input type="hidden" name="action" value="forgot-password">
        <legend>Modifier le mot de passe</legend>
    <?php if (!empty($forgotPasswordInfo)): ?>
        <div class="has-error">
            <b><?= $forgotPasswordInfo?></b>
            <br><br>
        </div>
    <?php endif; ?>
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
                    <label class="visually-hidden" for="newpassword">Nouveau mot de passe</label>
                    <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" minlength="8"
                           id="newpassword" name="newpassword" placeholder="Nouveau" required>
                </div>
                <div class="Form-element">
                    <label class="visually-hidden" for="newpasswordconf">Confirmation</label>
                    <input type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" minlength="8"
                           id="newpasswordconf" name="newpasswordconf" placeholder="Confirmation"
                           required>
                </div>
                <div class="Form-element">
                    <button class="button" type="submit">> Enregistrer</button>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>