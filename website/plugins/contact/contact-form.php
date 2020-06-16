<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
?>


<section class="">

    <?php if (!empty($errors)) : ?>
        <ul class="">
            <?php foreach ($errors as $error) : ?>
               <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php if(!empty($_GET['success'])): ?>
    <div class="">
        <?= __('Votre message a bien été envoyé'); ?>
        <br><br>
    </div>
<?php endif; ?>


    <div class="">
        <div class="">
            <form method="post" class="" >
                <div class="">
                    <div class="">
                        <label for="lastname"><?= __('Nom') ?>*</label>
                        <input id="lastname" name="lastname" type="text" required>
                    </div>
                    <div class="">
                        <label for="firstname"><?= __('Prénom') ?>*</label>
                        <input id="firstname" name="firstname" type="text" required>
                    </div>
                </div>
                <div class="">
                    <div class="">
                        <label for="email"><?= __('Email') ?>*</label>
                        <input id="email" name="email" type="email" required>
                    </div>
                    <div class="">
                        <label for="tel"><?= __('Téléphone') ?></label>
                        <input id="tel" name="tel" type="tel">
                    </div>
                </div>
                <div class="">
                    <label class="" for="subject"><?= __('Sujet') ?>*</label>
                    <input name="subject" id="subject" type="text" placeholder="<?= __('Sujet') ?>" required />
                </div>
                <div class="">
                    <label class="" for="message"><?= __('Message') ?>*</label>
                    <textarea name="message" id="message" cols="30" rows="10"
                              placeholder="<?= __('Message *') ?>" required></textarea>
                </div>

                <div class="">
                    <br>
                    <button class="" type="submit"><?= __('Envoyer') ?></button>
                </div>
            </form>

        </div>
    </div>
</section>
