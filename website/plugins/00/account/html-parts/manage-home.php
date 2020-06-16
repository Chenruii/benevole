<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

?>

    <h1 class="admin-page-title"> Gestions des comptes </h1>
    <section class="admin-section">
        <h1>

            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>
                    task-list-sync</title>
                <g>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M15 18.761h-3.75v3.75"></path>
                    <path class="a" d="M22.667 19.494a5.572 5.572 0 0 1-10.74-.733" fill="none" stroke="currentColor"
                          stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 15.761h3.75v-3.75"></path>
                    <path class="a" d="M11.833 15.028a5.572 5.572 0 0 1 10.74.733" fill="none" stroke="currentColor"
                          stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 10.511h4.5"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 14.261h3"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 18.011h3"></path>
                    <path class="a"
                          d="M7.5 23.261H2.25a1.5 1.5 0 0 1-1.5-1.5V6.011a1.5 1.5 0 0 1 1.5-1.5H6a3.75 3.75 0 0 1 7.5 0h3.75a1.5 1.5 0 0 1 1.5 1.5v2.25"
                          fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" d="M9.75 3.761a.375.375 0 1 1-.375.375.375.375 0 0 1 .375-.375" fill="none"
                          stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
            </svg>
            Import/export
        </h1>

        <a class=" btn-action-square co-excel tips"
           href="<?php echo $MIB_PLUGIN['name'] . '/importAccount/' ?>" title="Envoyer un fichier"
           upload="Importer le fichier des salariés :: Envoyer un fichier (<?php echo str_replace('M', 'Mo', ini_get('upload_max_filesize')); ?> max)"
           rel="Importer le fichier des salariés">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <g>
                    <circle class="a" cx="17.25" cy="17.261" r="6" fill="none" stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                    </circle>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 20.261v-6"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 14.261l2.25 2.25"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 14.261L15 16.511"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 10.511h5.25"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 14.261h3"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 18.011h3"></path>
                    <path class="a"
                          d="M9.75 23.261h-7.5a1.5 1.5 0 0 1-1.5-1.5V6.011a1.5 1.5 0 0 1 1.5-1.5H6a3.75 3.75 0 0 1 7.5 0h3.75a1.5 1.5 0 0 1 1.5 1.5v2.25"
                          fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" d="M9.75 3.761a.375.375 0 1 1-.375.375.375.375 0 0 1 .375-.375" fill="none"
                          stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
            </svg>
            <br>
            <span class="upload">Importer le <br> fichier des salariés</span>
        </a>


        <a class="btn-action-square co-excellight  tips" href="<?php echo $MIB_PLUGIN['name'] . '/getCompteFile/' ?>"
           title="Récupérer le fichier des comptes newsletter" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>
                    task-list-download-1</title>
                <g>
                    <circle class="a" cx="17.25" cy="17.261" r="6" fill="none" stroke="currentColor"
                            stroke-linecap="round" stroke-linejoin="round"></circle>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 14.261v6"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 20.261L15 18.011"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M17.25 20.261l2.25-2.25"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 10.511h5.25"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 14.261h3"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 18.011h3"></path>
                    <path class="a"
                          d="M9.75 23.261h-7.5a1.5 1.5 0 0 1-1.5-1.5V6.011a1.5 1.5 0 0 1 1.5-1.5H6a3.75 3.75 0 0 1 7.5 0h3.75a1.5 1.5 0 0 1 1.5 1.5v2.25"
                          fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" d="M9.75 3.761a.375.375 0 1 1-.375.375.375.375 0 0 1 .375-.375" fill="none"
                          stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
            </svg>
            <br>
            <span class="upload">Récupérer le fichier <br> de tous les  comptes </span></a>

    </section>
    <section class="admin-section">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>send-email</title>
                <g>
                    <path class="a"
                          d="M2.759 15.629a1.664 1.664 0 0 1-.882-3.075L20.36 1a1.663 1.663 0 0 1 2.516 1.72l-3.6 19.173a1.664 1.664 0 0 1-2.966.691l-5.21-6.955z"
                          fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" d="M11.1 15.629H8.6V20.8a1.663 1.663 0 0 0 2.6 1.374l3.178-2.166z" fill="none"
                          stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M11.099 15.629l11.08-14.59"></path>
                </g>
            </svg>
            Envoi des notifications de publications
        </h1>


        <form action="account/createNewsLetter" method="post" target="_json">
            <div class="Form-field">
                <select name="nbarticles" required>
                    <option></option>
                    <option value="1">le dernier article</option>
                    <option value="2">les 2 derniers articles</option>
                    <option value="3">les 3 derniers articles</option>
                    <option value="4">les 4 derniers articles</option>
                    <option value="5">les 5 derniers articles</option>
                </select>
                <button type="submit" class="btn-admin-validate">Création</button>
                <br>
                <a href="account/previewNewsLetter?nbarticles=5"  target="account/previewNewsLetter" class="btn-admin js-newsletter-preview" >Prévisualiser ( 5 articles ) </a>
            </div>
        </form>
        <br>
        <a href="https://us20.admin.mailchimp.com/" target="_blank">aller sur Mailchimp </a>

    </section>
    <script type="text/javascript">

        // $$('.js-newsletter-preview').addEvent('click',function(){
        //   //  console.log('titit')
        //     var count = $$('[name="nbarticles"]').get('value');
        //     var myHTMLRequest = new Request.HTML({url:'account/previewNewsLetter',data : 'nbarticles='+count});
        //     myHTMLRequest.success= function(arg1,arg2,arg3,arg4){
        //       //  console.log(arg1,arg2,arg3,arg4);
        //         MIB_Bo.acpbox.alert(arg1,'Prévisualisation');
        //         return false ;
        //     };
        //     myHTMLRequest.send();
        // })

    </script>

<?php
$liste = MibboFormManager::getList('recurentemployee', MIB_ACCOUNT_PATH);
$liste->loadData();
$state = $liste->getState();
$liste->editUrl = 'account/recurrent_edit';
?>

    <section class="admin-section">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>multiple-users-wifi</title><g><path class="cls-1" d="M18 2.873a9.539 9.539 0 0 0-12 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path class="cls-1" d="M8.5 6.488a5.566 5.566 0 0 1 7 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><circle class="cls-1" cx="3.375" cy="13.875" r="2.625" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></circle><path class="cls-1" d="M7.514 19.983A4.5 4.5 0 0 0 .75 18.1" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><circle class="cls-1" cx="20.625" cy="13.875" r="2.625" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></circle><path class="cls-1" d="M16.486 19.983A4.5 4.5 0 0 1 23.25 18.1" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><circle class="cls-1" cx="12" cy="13.125" r="3.375" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></circle><path class="cls-1" d="M18 23.25a6.054 6.054 0 0 0-12 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
            Gestion des salariés récurrents
            <br><small style="font-weight: normal"> seront ajoutés à la tables des employées pouvant s'inscrire  à chaque import de fichier </small>
        </h1>
        <a href="<?php echo $MIB_PLUGIN['name'] ?>/recurrent_create" class="Link">
            <svg viewBox="0 0 24 24" width="20" height="20">
                <g>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M12 7.5v9">

                    </path>
                    <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          d="M7.5 12h9"></path>
                    <circle class="a" cx="12" cy="12" r="11.25" fill="none" stroke="currentColor" stroke-linecap="round"
                            stroke-linejoin="round"></circle>
                </g>
            </svg>
            Ajouter
        </a>

        <?= $liste->renderFullTable('/account', $liste->datas, $state); ?>

    </section>



<?php
//require_once __DIR__ . '/../MibboMailChimp.php';
//$mailChimp = new MibboMailChimp();
//$mailChimp->test();




