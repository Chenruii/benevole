<?php

$baseUrl = mib_get_base_url().'/';
//$baseUrl = 'http://stmichel.2boandco.com/'

?>




<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="2BO&CO Studio - 2boandco.com">
    <title> Gazette St Michel</title>
    <style>
        a{
            text-decoration: none;
            color: #572a31;
        }
        .PostList > .Post{
            display: block;
        }
        .PostList > .Post:not(:first-child) {
            margin-top: 25px;
        }
        .Post-title {
            color: #e5530e;
        }
        body{
            color: #572a31;
        }
        .button {
            background-color: #e5530e;
            border: 1px solid #f29f24;
            color: #fff;
            padding: .5rem;
            text-decoration: none;
            text-transform: uppercase;
        }
        .visually-hidden {
            border: 0 !important;
            clip: rect(1px, 1px, 1px, 1px) !important;
            -webkit-clip-path: inset(50%) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important;
        }
        .text-bold{
            font-weight: 800;
        }
        .PostList .Post-content {
            max-width: calc(100% - 250px);
            box-sizing: border-box;
            padding-left: 1rem;
            display: inline-block;
        }
        .PostList .Post-img {
            width: 250px;
            box-sizing: border-box;
            overflow: hidden;
        }
        .PostList .Post-content {
            max-width: calc(100% - 270px);
            box-sizing: border-box;
            padding-left: 1rem;
            vertical-align: top;
        }
        h1{
            color:#fff;
            padding-bottom: 1rem;
        }
        .Footer {
            background-color: #fff;
            padding-left: 2rem;
            padding-right: 2rem;
        }
        .flex {
            display: flex;
        }
        .Footer-left, .Footer-right {
            padding-top: 2rem;
        }
        .Footer > * {
            flex-basis: calc(100% / 3);
        }
        .justify-space-between {
            justify-content: space-between;
        }
        .mb2 {
            margin-bottom: 1rem;
        }
        .Footer  ul {
            list-style-type: none;
            padding-left: 0;
            margin-top: 0;
        }
        .flex-column {
            display: flex;
            flex-direction: column;
        }

        @media screen and (max-width: 899px) {
            .flex:not(.force-flex-row) {
                flex-direction: column;
            }

            .Footer-left, .Footer-right {
                text-align: center;
            }

            .Post {
                flex-direction: column !important;
            }

            .Post-content{
                max-width: 100% !important;
                width: auto !important;
            }

            .Post-content {
                padding-left: 0 !important;
            }
        }
    </style>
</head>

<body>
<div>


    <header style="text-align: center;background: url('<?= $baseUrl ?>/theme/website/img/page-background-stripped.jpg') #ef7b00;padding:2rem">
        <img src="<?=$baseUrl ?>theme/website/img/header-hen.png">
        <h1> Des nouvelles de St-Michel </h1>
    </header>

    <?php $articlesCount = count($list->datas) ?>
    <div class="PostList mb2">
    <?php for ($index = 0; $index < $articlesCount; $index++): ?>

            <?php
            $image = $list->getFieldDisplay($index, 'thumb');
            if (empty($image)) {
                $image = "theme/website/img/home-post.jpg";
            }
            $tags = $list->getFieldDisplay($index, 'tag');
            if (!empty($tags)) {
                $tags = explode(';', $tags);
                $tags = array_filter($tags, function ($el) {
                    if (!empty($el)) {
                        return $el;
                    }
                });
            }
            $tags = !empty($tags) ? ('#' . join(' | #', $tags)) : '';
            $site = $list->getFieldDisplay($index, 'site');
            $site = !empty($site) ? ($site . ' | ') : '';
            ?>
            <article class="Post" style="width: 100%">
                <img class="Post-img" src="<?= $baseUrl. $image ?>"
                     alt="<?= $list->getFieldDisplay($index, 'title') ?>">
                <div class="Post-content">
                    <h2 class="Post-title"><?= $list->getFieldDisplay($index, 'title') ?> </h2>
                    <p ><?= $list->getFieldDisplay($index, 'summary') ?></p>
                    <a href="<?=$baseUrl . $list->getFieldDisplay($index, 'slug') ?>" class="button">
                        <span aria-hidden="true">></span>
                        En savoir
                        <span aria-hidden="true">+</span>
                    </a>
                </div>
            </article>


    <?php endfor; ?>
    </div>


    <footer class="Footer flex" role="contentinfo">
        <div role="navigation" class="Footer-left">
            <div class="flex">
                <ul class="flex-column">
                    <li>
                        <a href="<?= $baseUrl ?>entreprise">L'Entreprise</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>la-vie-des-sites">La vie des sites</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>nous-st-michelois">Nous, St Michelois</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>nos-produits">Nos produits</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>cote-client">Côté clients</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>evenements">Événements</a>
                    </li>
                    <li>
                        <a href="<?= $baseUrl ?>le-saviez-vous">Le saviez-vous ? </a>
                    </li>
                </ul>
            </div></div>
        <div class="Footer-center text-center">
            <img src="<?= $baseUrl ?>theme/website/img/footer-logo-area.jpg" alt="" aria-hidden="true">
        </div>
        <div class="Footer-right text-right flex-column justify-space-between">
            <div class="Footer-rightLinks">
                <a href="https://www.stmichel.fr/" class="text-bold">www.stmichel.fr</a>
                <a href="https://www.facebook.com/galette.stmichel/" target="_blank">
                    <img src="<?= $baseUrl ?>theme/website/img/icons/social/dark/facebook.png" alt="" aria-hidden="true">
                    <span class="visually-hidden">Facebook</span>
                </a>
                <a href="https://twitter.com/StMichel" target="_blank">
                    <img src="../theme/website/img/icons/social/dark/twitter.png" alt="" aria-hidden="true">
                    <span class="visually-hidden">Twitter</span>
                </a>
                <a href="https://www.instagram.com/stmichel_fr/" target="_blank">
                    <img src="<?= $baseUrl ?>theme/website/img/icons/social/dark/instagram.png" alt="" aria-hidden="true">
                    <span class="visually-hidden">Instagram</span>
                </a>
                <a href="http://snapchat.stmichel.fr/" target="_blank" class="mobile-only">
                    <img src="../theme/website/img/icons/social/dark/snapchat.png" alt="" aria-hidden="true">
                    <span class="visually-hidden">Snapchat</span>
                </a>
                <a href="https://www.linkedin.com/company/st-michel-biscuits/" target="_blank">
                    <img src="<?= $baseUrl ?>theme/website/img/icons/social/dark/linkedin.png" alt="" aria-hidden="true">
                    <span class="visually-hidden">LinkedIn</span>
                </a>
            </div>
            <div class="mb2">Tous droits réservés - La Gazette St Michel</div>
        </div>
    </footer>
</body>
</html>



