/*
Script:
	Mibbo - JavaScript Core.

Author:
	Jonathan OCHEJ, <jonathan.ochej@2boandco.com>

Copyright (C):
	2010-2015 2BO&CO. This file is part of Mibbo.
*/

/**
 * Implémentation de l'event MIB Click. (simule un wheelclick, ou un Ctrl+click)
 * Ne marche que sur des elements autres que les liens, sinon ca ouvre la page
 * dans un nouvel onglet.
 */
Element.Events.mibclick = {
    base: 'mousedown',
    condition: function (e) {
        return (e.event.button == 1 || e.control == true);
    },
    onAdd: function () {
        this.addEvent('mouseup', function (e) { // supprime data-mibclick
            (function () {
                this.erase('data-mibclick');
            }).delay(500, this);
        });
    }
};

/**
 * Notifier d'inactivitée
 */
var MIB_IdleNotifier = new Class({

    Implements: [Events, Options],

    options: {/*
		onIdle: $empty,
		onLeaveIdle: $empty,
		onActive: $empty,*/
        el: window,
        events: [[window, 'scroll'], [window, 'resize'], [document, 'mousemove'], [document, 'keydown']]
    },

    initialize: function (timeout, options) {
        this.setOptions(options);
        this.timeout = timeout * 1000; // Le timeout est en seconde, on le convertir en microseconde
        this.idle = false; // On considère l'utilisateur actif par défaut

        // Ajoute les events
        this.options.events.each(function (e) {
            e[0].addEvent(e[1], this.newActivity.bind(this))
        }.bind(this))

        this.timer = this.setIdle.delay(this.timeout, this);
    },

    newActivity: function () {
        // Si l'utilisateur est inactif
        if (this.idle) {
            this.idle = false;
            this.fireEvent('leaveIdle');
        } else
            this.fireEvent('active');

        $clear(this.timer);
        this.timer = this.setIdle.delay(this.timeout, this);
    },

    setIdle: function () {
        if (!this.idle) { // Si l'utilisateur est actif, on le met idle
            this.idle = true;
            this.fireEvent('idle');
        }
    }
});

/**
 * Gestionnaire du BO
 */
var MIB_BackOffice = new Class({

        'version': '1.2.1', // Version du BO

        Implements: [Events, Options],

        options: {
            IdDebug: 'MIB_Debug',
            IdMenu: 'MIB_menu',
            IdMenuOptions: 'MIB_menuOptions',
            IdMenuPages: 'MIB_menuPages',
            IdMenuPreviewToggle: 'MIB_menuPreviewToggle',
            IdPage: 'MIB_page',
            IdPannel: 'MIB_pannel',
            IdPannelToggle: 'MIB_pannelToggle',
            IdPannelSeparator: 'MIB_separator',
            IdUserBar: 'MIB_userbar',
            IdHeader: 'MIB_head',
            IdUploadIframe: 'MIB_upload_iframe',

            dashboardId: 'bo/dashboard', // id du DashBoard (page d'accueil par défaut)
            dashboardTitle: 'Tableau de bord',
            dashboardFavicon: '{{tpl:MIB_THEME}}/admin/img/icons/dashboard.png',

            StringSeparator: '___'
        },


        initialize: function (options) {
            this.setOptions(options);

            this.menu = $(this.options.IdMenu);
            this.menuOptions = $(this.options.IdMenuOptions);
            this.menuPreviewToggle = $(this.options.IdMenuPreviewToggle);
            this.menuPages = $(this.options.IdMenuPages);
            this.menuPagesSave = JSON.decode(Cookie.read('menu'));
            if (!this.menuPagesSave) this.menuPagesSave = {};
            this.menuPagesSave = new Hash(this.menuPagesSave);
            this.pages = $(this.options.IdPage);
            this.pannel = $(this.options.IdPannel);
            this.pannelToggle = $(this.options.IdPannelToggle);
            this.pannelSeparator = $(this.options.IdPannelSeparator);
            this.userbar = $(this.options.IdUserBar);
            this.sep = this.options.StringSeparator;
            this.idle = false; // On considère l'utilisateur actif
            this.history = [];
            this.requests = []; // tableau des requette en cours
            this.uploadIframe = $(this.options.IdUploadIframe);

            this.currentpage = this.options.dashboardId;

            // Gestion du redimentionnement du navigateur
            this.resizePages();
            window.addEvent('resize', this.resizePages.bind(this));

            // Ajoute les tips
            this.tips = new Tips('.tips', {className: 'tooltip', offsets: {'x': -16, 'y': 16}});
            this.tips.addEvent('show', function (tip) {
                tip.setStyle('opacity', 0.8);
            });

            // Ajoute le datepicker
            this.datePickers = new MIB_DatePickers();

            // Ajoute le colorpicker
            this.colorPickers = new MIB_ColorPickers();

            // Ajoute ACP box
            this.acpbox = new MIB_ACPbox({className: 'acp'});
            Window.implement({
                $alert: function (message, title, options) {
                    this.acpbox.alert(message, title, options);
                }.bind(this),
                $confirm: function (message, title, options) {
                    this.acpbox.confirm(message, title, options);
                }.bind(this),
                $prompt: function (message, title, options) {
                    this.acpbox.prompt(message, title, options);
                }.bind(this),
                $upload: function (message, title, options) {
                    this.acpbox.prompt(message, title, options);
                }.bind(this)
            });

            // Ajoute la popup
            this.popup = new MIB_Popup({className: 'popup'});

            // Ajoute la fonction Growl et $growl
            this.growl = new MIB_Growl({className: 'growl', top: 68});
            Window.implement({
                $growl: function (message, title, options) {
                    this.growl.alert(message, title, options);
                }.bind(this)
            });

            // Sortable des onglets
            this.menuPagesSortables = new Sortables(
                this.menuPages, {
                    constrain: true,
                    clone: true,
                    revert: true,
                    opacity: 0.3,
                    onSort: function () {
                        var menu = new Hash(this.menuPagesSave);
                        this.menuPagesSave.empty();

                        this.menuPages.getElements('li').each(function (el) {
                            var target = el.retrieve('target');

                            if (target && menu.has(target))
                                this.menuPagesSave[target] = menu[target];
                        }.bind(this));

                        if (menu.has('#currentpage')) this.menuPagesSave['#currentpage'] = menu['#currentpage'];
                        Cookie.write('menu', JSON.encode(this.menuPagesSave), {duration: 15});
                    }.bind(this)
                }
            );

            // Active le Preview menu
            this.menuPreviewToggle.addEvent('mousedown', function (event) {
                event.stopPropagation();
                this.MenuPreviewAction('show');
            }.bind(this));
            $(document.body).addEvent('mousedown', function () {
                this.MenuPreviewAction('hide');
            }.bind(this));

            // Active le Pannel
            this.launchPannel();

            // "AJAXifie" le menu des options et affiche le Tableau de bord en page d'accueil
            this.rendering(this.menuOptions);
            // "AJAXifie" le header
            this.rendering($(this.options.IdHeader));
            // Charge le DashBoard
            this.load(this.options.dashboardId, {
                'title': this.options.dashboardTitle,
                'favicon': this.options.dashboardFavicon
            });

            // Lance la detection du changement de page avec les bouton précédent et suivant
            this.checkHashLocation.periodical(100, this);

            // Charge les onglets enregistrés
            this.menuPagesSave.each(function (v, k) {
                if (k != '#currentpage')
                    this.load(k, {'title': v.title, 'favicon': v.favicon, 'target': v.target, 'showpage': false});
            }.bind(this));
            if (this.menuPagesSave.has('#currentpage')) {
                this.show(this.menuPagesSave['#currentpage']);
            }

            $(document.body).addEvent('mousedown', function () {
                this.tips.hide();
            }.bind(this));
        },


        /**
         * Redimentionne l'element Page du BO en fonction de la taille du navigateur
         */
        resizePages: function () {
            var Pages_height = window.getSize().y;
            if ($(this.options.IdDebug)) // Barre de débug
                Pages_height = Pages_height - $(this.options.IdDebug).getSize().y;
            if ($(this.options.IdHeader)) // Header
                Pages_height = Pages_height - $(this.options.IdHeader).getSize().y;
            if (this.menu) // Menu
                Pages_height = Pages_height - this.menu.getSize().y;

            this.pannelSeparator.setStyle('height', Pages_height);
            this.pannel.setStyle('height', Pages_height);
            this.pages.setStyle('height', Pages_height);
        },


        /**
         * Check l'url de la page pour savoir si on a changé de page ou non
         */
        checkHashLocation: function () {
            if (this.idle) return; // Si utilisateur inactif, on block le checklocation

            this.currenthash = document.location.hash.substr(2); // On ne prend pas '#/' du début

            // Si le hash est différent de la page en cour, et que la page du hash existe "encore"
            if (this.currenthash != this.currentpage && $('page' + this.sep + this.currenthash))
                this.show(this.currenthash);
        },


        /**
         * Changele titre de la page + le favicon si besoin.
         *
         * @param {string} puid - Id de la page
         */
        setDocumentFancy: function (puid) {
            if (!this.documentBaseTitle) // initialise le titre
                this.documentBaseTitle = document.title;

            if (!this.documentBaseFavicon) {// initialise le favicon
                document.getElements('head link').each(function (link) {
                    if (link.get('rel') && (link.get('rel')).test('icon', 'i')) { // Selectionne le/les favicon existant
                        this.documentBaseFavicon = new Element('link', {
                            'rel': link.get('rel'),
                            'type': link.get('type'),
                            'href': link.get('href')
                        });
                        link.destroy();
                    }
                }.bind(this));
            }

            // La page existe
            if (puid && $('page' + this.sep + puid)) {
                // Change le hash
                document.location.hash = '/' + puid;

                // La page à t'elle une page mère ? (ex: plugin1 et plugin1/edit)
                var mainTitle = null;
                if (!$('page' + this.sep + puid).retrieve('page:maintitle')) {
                    var dual = puid.split('/');
                    if (dual.length > 1 && $('page' + this.sep + dual[0])) { // Oui
                        var Mpage = $('page' + this.sep + dual[0]);
                        mainTitle = Mpage.retrieve('page:title');
                        $('page' + this.sep + puid).store('page:maintitle', mainTitle); // stock le titre de la page mère
                        if (!$('page' + this.sep + puid).retrieve('page:favicon') && Mpage.retrieve('page:favicon')) // La page n'a pas de favicon, mais sa page mère si !
                            $('page' + this.sep + puid).store('page:favicon', Mpage.retrieve('page:favicon'));
                    }
                } else
                    mainTitle = $('page' + this.sep + puid).retrieve('page:maintitle')

                if (mainTitle)
                    mainTitle = ' › ' + mainTitle;

                //Change le titre de la page
                if ($('page' + this.sep + puid).retrieve('page:title'))
                    document.title = this.documentBaseTitle + (mainTitle || '') + ' › ' + $('page' + this.sep + puid).retrieve('page:title');
                else
                    document.title = this.documentBaseTitle;

                // Change le favicon
                if (this.documentFavicon)
                    this.documentFavicon.destroy();
                if ($('page' + this.sep + puid).retrieve('page:favicon'))
                    this.documentFavicon = new Element('link', {
                        'rel': 'icon',
                        'type': 'image/x-icon',
                        'href': $('page' + this.sep + puid).retrieve('page:favicon')
                    });
                else
                    this.documentFavicon = this.documentBaseFavicon;
                this.documentFavicon.inject(document.getElement('head'));
            }
        },


        /**
         * Construit le Menu Preview
         *
         * @param action {string} "hide" || "show"
         */
        MenuPreviewAction: function (action) {
            // affiche le menu si il n'existe pas
            if (action == 'show' && this.menuPages.getElements('li').length > 0 && !this.menuPreview) {
                var a;

                // on construit le menu
                this.menuPreview = new Element('ul', {'class': 'menubox'}).set('morph', {duration: 250});

                this.menuPages.getElements('li').each(function (li) {
                    // Si la page existe
                    if ($('page' + this.sep + li.get('id').split(this.sep)[1])) {
                        var puid = li.get('id').split(this.sep)[1];
                        var title = $('page' + this.sep + puid).retrieve('page:title') || null;
                        var mainTitle = $('page' + this.sep + puid).retrieve('page:maintitle') || null;
                        var favicon = $('page' + this.sep + puid).retrieve('page:favicon') || null;

                        if (puid && title) {
                            if (mainTitle) mainTitle = mainTitle + ' › ';

                            a = new Element('a', {
                                'html': '<span class="icontxt"' + (favicon ? 'style="background-image:url(\'' + favicon + '\');"' : '') + '>' + (mainTitle || '') + title + '</span>',
                                'events': {
                                    'mousedown': function () {
                                        this.show(puid);
                                    }.bind(this)
                                }
                            });

                            if (puid == this.currentpage)
                                a.setStyle('font-weight', 'bold');

                            this.menuPreview.adopt(new Element('li').adopt(a));
                        }
                    }
                }.bind(this));

                this.menuPreview.inject(document.body);

                this.menuPreview.setStyles({
                    'top': this.menu.getCoordinates().bottom,
                    'left': this.menuPreviewToggle.getPosition().x
                }).morph({'opacity': [0, 1]});
            }
            // Détruit le menu
            else if (this.menuPreview) {
                this.menuPreview.destroy();
                this.menuPreview = null;
            }
        },


        /**
         * Active le Pannel
         */
        launchPannel: function () {
            // Lance l'accordion
            this.pannelAccordion = new Fx.Accordion($$('.toggler'), $$('.element'), {
                alwaysHide: false,
                display: -1,
                onActive: function (toggler, element) {
                    toggler.addClass('selected');
                },
                onBackground: function (toggler, element) {
                    toggler.removeClass('selected');
                }
            });

            // Ajoute le Toggle du pannel
            this.pannel.set('morph', {duration: 250});
            this.pannelWidth = this.pannel.getStyle('width').toInt();
            $$(this.pannelToggle, this.pannelSeparator).addEvent('mousedown', function () {
                // Hide
                if (this.pannel.getStyle('margin-left').toInt() == 0) {
                    this.pannel.morph({'margin-left': -this.pannelWidth});
                    this.pannelToggle.addClass('hidden');
                }
                // Show
                else {
                    this.pannel.morph({'margin-left': 0});
                    this.pannelToggle.removeClass('hidden');
                }

            }.bind(this));

            // "AJAXifie" le pannel
            this.rendering(this.pannel);
        },


        /**
         * Remplace tous les liens et formulaires par des requêtes AJAX
         *
         * @param el {string} element qu'il faut "ajaxifier"
         */
        rendering: function (el) {

            // Toggle Fieldset
            el.getElements('fieldset.toggle').each(function (fieldset) {
                var fieldset_legend = fieldset.getElement('legend').clone(true, true);
                fieldset.getElement('legend').destroy();

                var fieldset_action = fieldset.getElement('form') ? fieldset.getElement('form').get('action') : fieldset_legend.get('text');

                var fieldset_content = fieldset.get('html');
                fieldset.empty();

                fieldset_legend.inject(fieldset);

                var toggle_fieldset = new Element('div', {'class': 'toggle-content'}).set('html', fieldset_content).inject(fieldset);

                if ($(document.body).retrieve('toggle:' + fieldset_action, false)) {
                    fieldset.addClass('toggle-show');
                }
                fieldset.getElement('legend').addEvent('click', function (e) {
                    e.stop();
                    $(document.body).store('toggle:' + fieldset_action, fieldset.toggleClass('toggle-show').hasClass('toggle-show'));
                });
            });

            el.getElements('a').each(function (a) {
                if (a.get('target') != '_blank') {
                    // On store et supprime le href
                    a.store('href', a.get('href')).erase('href');
                    if (a.get('data-page')) {
                        var data = a.retrieve('data');
                        if (data) {
                            data = data + '&';
                        }
                        data = "page=" + a.get('data-page');
                        a.store('data', data);
                    }
                    if (a.get('data-search')) {
                        var data = a.retrieve('data');
                        if (data) {
                            data = data + '&';
                        }
                        data = "search=" + a.get('data-search');
                        a.store('data', data);
                    }
                    // On store et supprime le favicon
                    a.store('favicon', a.get('favicon')).erase('favicon');
                    // On store et supprime les messages de confirmations
                    a.store('alert', a.get('alert')).erase('alert');
                    a.store('confirm', a.get('confirm')).erase('confirm');
                    a.store('prompt', a.get('prompt')).erase('prompt');
                    a.store('upload', a.get('upload')).erase('upload');

                    // Ajoute les events
                    if (a.get('target') != '_action') {
                        a.addEvents({
                            'click': function (event) {
                                event.stop();
                                if (a.get('data-mibclick')) // mibclick trick ;)
                                    a.erase('data-mibclick');
                                else
                                    this.execute(a);
                            }.bind(this),
                            'mibclick': function (event) {
                                event.stop();
                                this.execute(a, {'showpage': false});
                                a.set('data-mibclick', true);
                            }.bind(this)
                        });
                    }
                }
            }.bind(this));

            el.getElements('form').each(function (form) {
                if (form.get('target') != '_blank') {
                    // On store et supprime le favicon
                    form.store('favicon', form.get('favicon')).erase('favicon');
                    // On store et supprime les messages de confirmations
                    form.store('alert', form.get('alert')).erase('alert');
                    form.store('confirm', form.get('confirm')).erase('confirm');
                    form.store('prompt', form.get('prompt')).erase('prompt');
                    form.store('upload', form.get('upload')).erase('upload');

                    if (form.get('target') != '_action') {
                        form.addEvent('submit', function (event) {
                            event.stop();
                            this.execute(form);
                        }.bind(this));
                    }
                }
            }.bind(this));

            // Attache les tooltips
            this.tips.attach(el.getElements('.tips'));

            // Attache les datepickers
            this.datePickers.attach(el.getElements('.datepickers'));

            // Attache les colorpickers
            this.colorPickers.attach(el.getElements('.colorpickers'));
        },


        /**
         * Execute l'action d'un element
         *
         * @param el {element} element qu'il faut executer
         * @param options
         */
        execute: function (el, options) {
            options = options || {};

            if ($type(el) == 'element') { // uniquement pour les elements
                var message = $pick(el.retrieve('upload'), el.retrieve('prompt'), el.retrieve('confirm'), el.retrieve('alert')); // Message de la box
                if (message) {
                    var dual = message.split('::');
                    if (dual.length > 1) {
                        message = dual[0].trim();
                        var title = dual[1].trim(); // Titre
                        if (dual[2]) {
                            title = '<span class="' + dual[2] + '">' + title + '</span>'; // Class ou Icon rajoutée au titre
                            var secure = (dual[2] == 'secure') ? true : false; // Password ou non ?
                            var input = (!secure && $(dual[2])) ? dual[2] : false; // Spécial input ?
                        }
                    } else
                        var title = null;
                }

                // Popup d'upload
                if (el.retrieve('upload')) {
                    $upload(message, title, {
                        type: 'file', // Sélecteur de fichier
                        onClose: function (value) {
                            if (value) { // Une valeur a été entrée
                                var formUpload = this.acpbox.form;
                                var inputUpload = this.acpbox.input

                                if (el.get('tag') == 'form') { // Formulaire
                                    inputUpload.set('name', 'file');
                                    formUpload.set('action', '{{tpl:MIBpage base_url}}/json/' + el.get('action'));
                                } else { // Lien
                                    inputUpload.set('name', el.get('name') || 'file');
                                    formUpload.set('action', '{{tpl:MIBpage base_url}}/json/' + el.retrieve('href'));
                                }

                                formUpload.set({
                                    'enctype': 'multipart/form-data',
                                    'method': 'post',
                                    'target': 'MIB_upload_iframe'
                                });

                                // Prépare l'iframe
                                $('MIB_upload_iframe').removeEvents();
                                $('MIB_upload_iframe').addEvent('load', function () {
                                    var response;

                                    // On récupère les info envoyés dans l'iframe
                                    if ($('MIB_upload_iframe').contentDocument)
                                        response = $('MIB_upload_iframe').contentDocument;
                                    else if ($('MIB_upload_iframe').contentWindow)
                                        response = $('MIB_upload_iframe').contentWindow.document;
                                    else
                                        response = window.frames['MIB_upload_iframe'].document;

                                    if (response && response.body.innerHTML)
                                        response = JSON.decode(response.body.innerHTML);
                                    else
                                        response.error = true;

                                    response.options = response.options || {};
                                    this.jsontoaction(response);

                                    $('MIB_loader').setStyles.delay(100, $('MIB_loader'), {'visibility': 'hidden'});
                                }.bind(this));
                                formUpload.submit();
                                $('MIB_loader').setStyle('opacity', 0.7);
                            }
                        }.bind(this)
                    });
                }
                // Popup de demande d'information
                else if (el.retrieve('prompt')) {
                    $prompt(message, title, {
                        input: input,
                        type: (secure ? 'password' : 'text'), // Password ou non ?
                        onClose: function (value) {
                            if (value) { // Une valeur a été entrée
                                if (el.get('tag') == 'form') { // Formulaire
                                    new Element('input', { // Ajoute la valeur du prompt au formulaire
                                        'type': 'hidden',
                                        'name': (secure ? 'secure' : 'prompt'),
                                        'value': value
                                    }).inject(el);
                                } else { // Lien
                                    value = value.replace(/\&/g, '%26');
                                    value = value.replace(/\=/g, '%3D');
                                    value = value.replace(/\+/g, '%2B');

                                    options.data = (secure ? 'secure' : 'prompt') + '=' + value; // Ajoute la valeur du prompt à la requette
                                }

                                this.load(el, options);
                            }
                        }.bind(this),
                        value: el.get('prompt-value') || ''
                    });
                }
                // Popup de confirmation
                else if (el.retrieve('confirm')) {
                    $confirm(message, title, {
                        onClose: function (value) {
                            if (value) {
                                this.load(el, options);
                            }
                        }.bind(this)
                    });
                }
                // Popup d'alerte
                else if (el.retrieve('alert')) {
                    $alert(message, title, {
                        onClose: function (value) {
                            if (value) {
                                this.load(el, options);
                            }
                        }.bind(this)
                    });
                } else {
                    this.load(el, options);
                }

            }
        },


        /**
         * Effectue une requette AJAX ou JSON. En fonction de la requette, soit on
         * construit la page, le menu, ou traite la réponse
         *
         * @param el {string|element} element qui envoit l'action ou id de l'element à charger
         * @param options {object} Options
         *
         * @exemple
         *    <a href="Page à charger" title="Titre de l'onglet" target="Page de destination" >Texte du lien</a>
         *    <form method="post|get" action="Page à charger" title="Titre de l'onglet" target="Page de destination">Contenu du formulaire</form>
         */
        load: function (el, options) {
            options = $merge({
                'showpage': true
            }, options);

            // On a un UID
            if ($type(el) == 'string') {
                var uid = el;
            }
            // On un un Element "ajaxifié"
            else if ($type(el) == 'element') {
                options.el = el; // confirme que c'est un element
                if (!options.tag && el.get('tag')) options.tag = el.get('tag');

                var uid = (options.tag == 'form') ? el.get('action') : el.retrieve('href', el.get('href'));
                if (uid.substring(0, 5) == '_self') uid = this.currentpage + uid.substring(5);

                if (!options.method && el.get('method')) options.method = el.get('method');
                if (!options.data && el.retrieve('data')) options.data = el.retrieve('data');
                if (!options.target)
                    options.target = el.get('target') ? el.get('target') : uid;
                if (!options.title)
                    options.title = el.retrieve('tip:title', el.get('title')); // Si l'element à la class "tips", le titre a été storé
                if (!options.favicon)
                    options.favicon = el.retrieve('favicon', el.get('favicon'));

                if (options.tag == 'form' && options.method && options.method == 'get') { // uid autogénéré avec le contenu du formulaire (pour l'historique et l'update)
                    options.autouid = true;
                    uid = uid + (uid.contains('?') ? '&' : '?') + $(el).toQueryString();
                }
//			console.log(options);

            }

            // un uid est déjà présent dans les option (utilisé pour la mise en cache de lastloading par exemple)
            if (options.uid)
                uid = options.uid;

            if (!uid || uid == '') // Pas d'uid !!!
                return;
            else
                options.uid = uid;

            // Valeurs par défaut
            if (!options.method) options.method = 'get';
            if (!options.data) options.data = '';
            if (!options.el) options.el = false;
            if (!options.target || options.target == '_self') options.target = this.currentpage;
            if (!options.title) options.title = uid;

            var loading = options;

            if (!this.requests.contains(uid)) {

                // Requette JSON
                if (loading.target == '_json') {
                    var jsonRequest = new Request.JSON({
                        method: loading.method,
                        url: '{{tpl:MIBpage base_url}}/json/' + uid,
                        onRequest: function () {
                            this.requests.include(uid);
                            $(document.body).addClass('onLoad');
                            // formulaire envoyé
                            if (loading.tag == 'form' && loading.el) {
                                // enlève les messages d'erreur du formulaire
                                loading.el.getElements('.alert-error').destroy();

                                // désactive les boutons du formulaire (évite le double clic intempestif)
                                loading.el.getElements('input[type=submit], button[type=submit]').each(function (b) {
                                    b.addClass('onLoad').set('disabled', true);
                                });
                                loading.el.getElements('input, button').each(function (i) {
                                    i.blur();
                                }); // enlève le focus des element du formulaire
                            }
                        }.bind(this),
                        onComplete: function () {
                            (function () {
                                // ré-active les boutons du formulaire
                                if (loading.tag == 'form' && loading.el)
                                    loading.el.getElements('input[type=submit], button[type=submit]').each(function (b) {
                                        b.erase('disabled').removeClass('onLoad');
                                    });

                                this.requests.erase(uid);
                                if (this.requests.length == 0) $(document.body).removeClass('onLoad'); // seulement si il n'y a pas d'autres requettes en cours

                            }.bind(this)).delay(500); // ajoute un délai pour éviter le multi-requette intempestif
                        }.bind(this),
                        onSuccess: function (response) {
                            response.options = response && response.options || {};
                            this.jsontoaction(response);
                        }.bind(this),
                        onFailure: function () {

                        }.bind(this)
                    });

                    // Si c'est un formulaire
                    if (loading.tag && loading.tag === 'form') {
                        jsonRequest.send(el);
                    } else {
                        jsonRequest.send(loading.data);
                    }

                }
                // Requette AJAX dans une popup (utilisé par exemple pour la configuration des plugins)
                else if (loading.target == '_popup') {
                    this.popup.show();
                    this.popup.setTitle('test de ouf !!!');
                    this.popup.setHtml('<div class="inpage"><div class="message"><p>Bienvenue sur la Popup.</p></div></div>');
                }
                // Requette AJAX qui update un element si il existe (div, p, etc...)
                else if (loading.target && loading.target.charAt(0) == '_') {


                }
                // Requette AJAX standard qui créer un onglet dans le menu, et la page
                else {
                    // création de l'onglet si il n'existe pas
                    if (!$('tab' + this.sep + loading.target)) {
                        var li, a, span, close;

                        li = new Element('li', {
                            'id': 'tab' + this.sep + loading.target
                        }).store('target', loading.target);

                        a = new Element('a', {
                            'events': {
                                'click': function (event) {
                                    event.stop();
                                    if (a.get('data-mibclick')) // mibclick trick ;)
                                        a.erase('data-mibclick');
                                    else
                                        this.show(a.retrieve('href', a.get('href')));
                                }.bind(this),
                                'mibclick': function (event) {
                                    event.stop();
                                    this.remove(a.retrieve('href', a.get('href')));
                                    a.set('data-mibclick', true);
                                }.bind(this)
                            }
                        })
                            .store('href', loading.target);

                        span = new Element('span', {
                            'text': ((loading.title.length > 22) ? loading.title.substr(0, 20) + '..' : loading.title) // tronque le titre trop long
                        });

                        close = new Element('span', {
                            'class': 'close',
                            'events': {
                                'click': function (event) {
                                    this.remove(a.retrieve('href', a.get('href')));
                                }.bind(this),
                                'mouseover': function () {
                                    this.menuPagesSortables.detach();
                                }.bind(this),
                                'mouseout': function () {
                                    this.menuPagesSortables.attach();
                                }.bind(this)
                            }
                        });

                        // Ajoute l'onglet au menu
                        close.inject(span, 'top');
                        this.menuPages.adopt(li.adopt(a.adopt(span)));
                        // AJoute au sortable
                        this.menuPagesSortables.addItems(li);
                        // affiche le menuPreview
                        this.menuPreviewToggle.setStyle('display', 'block');
                        // ajoute à la sauvegarde du menu
                        this.menuPagesSave[loading.uid] = {
                            'target': loading.target,
                            'title': loading.title,
                            'favicon': loading.favicon
                        };
                        Cookie.write('menu', JSON.encode(this.menuPagesSave), {duration: 15});
                    }

                    var page;
                    // création de la page si elle n'existe pas
                    if (!$('page' + this.sep + loading.target)) {
                        page = new Element('div', {
                            'id': 'page' + this.sep + loading.target,
                            'class': 'inpage',
                            'styles': {
                                'display': 'none'
                            }
                        });

                        page.store('page:title', loading.title); // On store le titre le la page
                        if (loading.favicon)
                            page.store('page:favicon', loading.favicon); // On store le favicon le la page

                        // La page à t'elle une page mère ? (ex: plugin1 et plugin1/edit)
                        var dual = loading.target.split('/');
                        if (dual.length > 1 && $('page' + this.sep + dual[0])) { // Oui
                            page.store('page:maintitle', $('page' + this.sep + dual[0]).retrieve('page:title')); // stock le titre de la page mère
                            if (!page.retrieve('page:favicon') && $('page' + this.sep + dual[0]).retrieve('page:favicon')) // La page n'a pas de favicon, mais sa page mère si !
                                page.store('page:favicon', $('page' + this.sep + dual[0]).retrieve('page:favicon'));
                        }

                        this.pages.adopt(page);
                    } else
                        page = $('page' + this.sep + loading.target);

                    var pageRequest = new Request.HTML({
                        method: loading.method,
                        url: '{{tpl:MIBpage base_url}}/ajax/' + uid,
                        update: page,
                        evalScripts: true,
                        onRequest: function () {
                            this.requests.include(uid);
                            $(document.body).addClass('onLoad');
                            // formulaire envoyé
                            if (loading.tag == 'form' && loading.el) {
                                // enlève les messages d'erreur du formulaire
                                loading.el.getElements('.alert-error').destroy();

                                // désactive les boutons du formulaire (évite le double clic intempestif)
                                loading.el.getElements('input[type=submit], button[type=submit]').each(function (b) {
                                    b.addClass('onLoad').set('disabled', true);
                                });
                                loading.el.getElements('input, button').each(function (i) {
                                    i.blur();
                                }); // enlève le focus des element du formulaire
                            }
                        }.bind(this),
                        onComplete: function () {
                            (function () {
                                // ré-active les boutons du formulaire
                                if (loading.tag == 'form' && loading.el)
                                    loading.el.getElements('input[type=submit], button[type=submit]').each(function (b) {
                                        b.erase('disabled').removeClass('onLoad');
                                    });

                                this.requests.erase(uid);
                                if (this.requests.length == 0) $(document.body).removeClass('onLoad'); // seulement si il n'y a pas d'autres requettes en cours

                            }.bind(this)).delay(500); // ajoute un délai pour éviter le multi-requette intempestif
                        }.bind(this),
                        onSuccess: function () {
                            page.store('page:lastloading', loading);
                            this.rendering(page);
                        }.bind(this),
                        onFailure: function () {

                        }.bind(this)
                    });

                    // Si c'est un formulaire
                    if (loading.tag && loading.tag == 'form') {
                        if (!loading.autouid) {

                            pageRequest.send(el);
                        } else {

                            pageRequest.send(loading.data);
                        }

                    } else {

                        pageRequest.send(loading.data);
                    }


                    // On affiche la page
                    if (loading.showpage)
                        this.show(loading.target);
                }
            }
        },


        /**
         * Convertit du JSON en action pour le BO
         *
         * @param response {json}
         */
        jsontoaction:

            function (response) {
                /*
                        Chaque réponse peut être affichée avec growl.
                        $growl(message, title, options);
                            message => [error] ou [value]
                            title => [title]
                            options => {
                                type (error || valid)
                                color
                                duration
                            }

                        [title] => Titre $growl
                        [options] => Options $growl

                        [error] ou [value] =>

                        [location] => Redirection de la page
                        [idle] => {idle/online} Active ou non l'inactivité de l'utilisateur

                        [page] => {
                            [remove] => (pageid1, pageid2, pageid3...)
                            [update] => (pageid1, pageid2, pageid3...) // Update les page de leur dernière action
                            [id] =>
                                [update] =>
                                    tag
                                    method
                                    data
                                    target
                                    title
                                    favicon
                                    showpage
                        },
                        [element] => {
                            [id] =>
                                [set] => // property à définir
                                    html =>
                                    text =>
                                    class =>
                                    href =>
                                    etc...
                                [setStyles]
                                    visibility =>
                                    opacity =>
                                [erase] // property à "erase" séparées par des virgules
                                    (id, class, etc...)
                                [addClass] // class à "add" séparées par des virgules
                                    (class1, class2, etc...)
                                [removeClass] // class à "remove" séparées par des virgules
                                    (class1, class2, etc...)
                                [empty] = true // empty l'element
                                [destroy] = true // destroy l'element
                        }
                    */

                // Il y a une erreur dans la réponse
                if (response.error) {
                    response.title = response.title || 'Error';
                    response.message = response.error || 'Unknow Error';
                    $growl(response.message, response.title, {
                        duration: (response.options.duration || 10000),
                        type: 'error',
                        onShow: function (growl) {
                            this.rendering(growl);
                        }.bind(this)
                    });
                }
                // Aucune erreur et on a un grow à afficher
                else if (response.value) {
                    response.title = response.title || null;
                    response.message = response.value;
                    $growl(response.message, response.title, {
                        duration: (response.options.duration || null),
                        type: (response.options.type || null),
                        color: (response.options.color || null),
                        onShow: function (growl) {
                            this.rendering(growl);
                        }.bind(this)
                    });
                }

                // On redirige après un délay de 1s
                if (response.idle) {
                    this.idle = (response.idle == 'idle') ? true : false;
                }

                // On redirige après un délay de 1s
                if (response.location) {
                    (function () {
                        window.location.href = response.location;
                    }).delay(1000);
                }

                if (response.callBack) {
                    var path = response.callBack.split('.');
                    if (window[path[0]]) {
                        var current = window;
                        for (var i = 0; i < path.length; i++) {

                            if (!current[path[i]])
                                break;

                            current = current[path[i]]
                            // pour le dernier on lance la function
                            if (i === path.length - 1) {
                                if (typeof current === 'function') {
                                    current(response);
                                }
                            }
                        }
                    }
                }

                // On met a jour les elements demandés
                if (response.element) {
                    response.element = new Hash(response.element);
                    response.element.each(function (actions, el) {
                        if (actions && $(el)) { // il y a des actions et l'element existe
                            el = $(el);
                            actions = new Hash(actions);
                            if (actions.has('destroy'))
                                el.destroy();
                            else if (actions.has('empty'))
                                el.empty();
                            else { // actions sur l'element
                                if (actions.has('erase')) {
                                    actions.erase = actions.erase.split(',');
                                    actions.erase.each(function (property) {
                                        el.erase(property.trim());
                                    });
                                }
                                if (actions.has('set')) {
                                    actions.set = new Hash(actions.set);
                                    actions.set.each(function (value, property) {
                                        el.set(property, value);
                                    });
                                }
                                if (actions.has('removeClass')) {
                                    actions.removeClass = actions.removeClass.split(',');
                                    actions.removeClass.each(function (classname) {
                                        el.removeClass(classname.trim());
                                    });
                                }
                                if (actions.has('addClass')) {
                                    actions.addClass = actions.addClass.split(',');
                                    actions.addClass.each(function (classname) {
                                        el.addClass(classname.trim());
                                    });
                                }
                                if (actions.has('setStyles')) {
                                    actions.setStyles = new Hash(actions.setStyles);
                                    el.setStyles(actions.setStyles);
                                }
                                if (actions.has('morph')) {
                                    // Pas de hash pour le morph
                                    if (!el.get('morph')) el.set('morph');
                                    el.morph(actions.morph);
                                }
                            }
                        }
                    });
                }

                // On met a jour les pages demandés
                if (response.page) {
                    response.page = new Hash(response.page);
                    response.page.each(function (pages, type) {
                        if (type == 'remove' || type == 'update') { // Supprime ou Update les pages
                            pages = pages.split(',');
                            pages.each(function (puid) {
                                if (type == 'remove')
                                    this.remove(puid.trim());
                                else if (type == 'update')
                                    this.update(puid.trim());
                            }.bind(this));
                        } else if (type == 'replace') {
                            pages = new Hash(pages);
                            pages.each(function (url, puid) {

                                // Permet d'update la page courante facilement this.update();
                                if (!puid || puid == '_self') puid = this.currentpage;

                                if ($('page' + this.sep + puid)) { // Si la page existe
                                    this.load(url, {
                                        'showpage': true,
                                        'target': puid
                                    });
                                }
                            }.bind(this));
                        } else if (type == 'load') {
                            pages = pages.split(',');
                            pages.each(function (url, puid) {
                                if (!$('page' + this.sep + puid)) { // Si la page n'existe pas
                                    this.load(url, {
                                        'showpage': true,
                                        'target': url
                                    });
                                }
                            }.bind(this));
                        } else if (type == 'position') {
                            pages = pages.split(',');
                            pages.each(function (puid) {
                                if ($('page' + this.sep + puid)) {
                                    $('page' + this.sep + puid).getElements('.table-sortable').each(function (table) {

                                        var small = false;
                                        var tds = table.getElements('td.tc-position');
                                        tds.each(function (td) {
                                            var position = td.get('html').toInt();
                                            if (!small || position < small) {
                                                small = position;
                                            }
                                        }.bind(this));
                                        if (small !== false) {
                                            if (small < 1) small = 1;

                                            if (table.getProperty('data-sort_dir') == 'DESC') tds.reverse();

                                            tds.each(function (td) {
                                                td.set('html', small);
                                                small++;
                                            }.bind(this));
                                        }
                                    }.bind(this));
                                }
                            }.bind(this));
                        }
                    }.bind(this));
                }
            }

        ,


        /**
         * Affiche une page
         *
         * @param el {string|element} element ou id de la page à afficher
         */
        show: function (el) {
            // On a un UID
            if ($type(el) == 'string') var puid = el;
            // On un un Element
            else if ($type(el) == 'element') var puid = el.get('id').split(this.sep)[1];

            // si la page et l'onglet existe
            if ($('page' + this.sep + puid) && $('tab' + this.sep + puid)) {
                // Si la page à affiché est différente de la page en cours d'affichage
                if (this.currentpage != puid) {
                    // Déselectionne l'onglet
                    if ($('tab' + this.sep + this.currentpage)) $('tab' + this.sep + this.currentpage).removeClass('selected');
                    // Cache la page
                    this.hide(this.currentpage);

                    // Affiche la page demandée et sélectionne l'onglet
                    $('page' + this.sep + puid).setStyle('display', 'block');
                    $('tab' + this.sep + puid).set('class', 'selected');

                    // Attribut l'id à la nouvelle page en cours d'affichage
                    this.currentpage = puid;

                    if (this.currentpage != this.options.dashboardId)
                        this.menuPagesSave['#currentpage'] = this.currentpage;
                    else
                        this.menuPagesSave.erase('#currentpage');
                    Cookie.write('menu', JSON.encode(this.menuPagesSave), {duration: 15});
                } else // FOrce l'affichage de la page au cas ou ;)
                    $('page' + this.sep + puid).setStyle('display', 'block');

                // On scroll la page au top
                //this.pages.scrollTo(0,0);
                // Ajoute l'historique de navigation
                this.history.erase(puid);
                this.history.include(puid);

                // Change le favicon, le titre, etc...
                this.setDocumentFancy(puid);
            }
        }
        ,


        /**
         * Cache une page ou d'un element
         *
         * @param el {string|element} element ou id à cacher
         */
        hide: function (el) {
            // On a un UID
            if ($type(el) == 'string') {
                var pid = el;
                var id = el;
            }
            // On un un Element
            else if ($type(el) == 'element') {
                var pid = el.get('id').split(this.sep)[1]; // Id de page
                var id = el.get('id'); // Id d'un element
            }

            // Si c'est une page
            if ($('page' + this.sep + pid))
                $('page' + this.sep + pid).setStyle('display', 'none');
            else if ($(id))
                $(id).setStyle('display', 'none');
        }
        ,


        /**
         * Update une page
         *
         * @param pid {string} id de la page a update
         * @param options {}
         */
        update: function (Pid, options) {
            // Permet d'update la page courante facilement this.update();
            if (!Pid) Pid = this.currentpage;

            if ($('page' + this.sep + Pid)) { // Si la page existe
                if (!options) // Aucune options n'existe
                    options = $('page' + this.sep + Pid).retrieve('page:lastloading');

                options.showpage = false; // On n'affiche jamais une page qu'on update

                this.load(Pid, options);
            }
        }
        ,


        /**
         * Supprime une page ou un element
         *
         * @param el {string|element} element ou id à supprimer
         */
        remove: function (el) {
            var pid, id;
            // On à rien
            if (!el)
                id = pid = this.currentpage;
            // On a un UID
            else if ($type(el) == 'string')
                id = pid = el;
            // On un un Element
            else if ($type(el) == 'element') {
                pid = el.get('id').split(this.sep)[1]; // Id de page
                id = el.get('id'); // Id d'un element
            }

            // Si c'est une page
            if ($('page' + this.sep + pid)) {
                this.hide(pid);

                // Supprime l'onglet et l'enlève de la sortable liste
                if ($('tab' + this.sep + pid)) {
                    this.menuPagesSortables.removeItems($('tab' + this.sep + pid));
                    $('tab' + this.sep + pid).destroy();

                    // ajoute à la sauvegarde du menu
                    this.menuPagesSave.erase(pid);
                    if (this.menuPages.getElements('li').length < 1) {
                        this.menuPreviewToggle.setStyle('display', 'none');
                        this.menuPagesSave.empty();
                    }
                    Cookie.write('menu', JSON.encode(this.menuPagesSave), {duration: 15});
                }
                $('page' + this.sep + pid).destroy();
                // Historique de navigation
                this.history.erase(pid);
                this.show(this.history.getLast());
            } else if ($(id)) {
                this.hide(id);
                $(id).destroy();
            }
        }
    })
;

/**
 * Gestionnaire de notification
 */
var MIB_Growl = new Class({

    Implements: [Options, Events],

    options: {
        /*onShow: function(growl) { },*/

        container: null,
        className: null,
        opacity: 0.9,
        top: 10,
        margin: 10,
        liveDuration: 5000,
        showDuration: 500,
        zindex: 9999
    },

    initialize: function (options) {
        this.setOptions(options);
        this.growls = [];

        this.growl = new Element('div').inject(this.options.container || document.body);
        if (this.options.className)
            this.growl.addClass(this.options.className);
        this.growl.setStyles({
            position: 'absolute',
            'top': this.options.top,
            'visibility': 'hidden',
            'z-index': this.options.zindex
        });
    },

    alert: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};

        // on a un message
        if (params.message) {
            // Rezet l'events show
            this.removeEvents('show');

            var new_growl = {};

            new_growl.item = this.growl.clone().setStyle('opacity', 0).set('morph', {
                unit: 'px',
                duration: this.options.showDuration,
                transition: 'back:out'
            });
            if (params.title) {
                new_growl.title = new Element('h1', {
                    'class': 'growl-title',
                    'html': params.title
                }).inject(new_growl.item);
                new_growl.close = new Element('span', {'class': 'close'}).inject(new_growl.title, 'top');
                new_growl.close.addEvent('click', function (event) {
                    event.stop();
                    this.remove(new_growl);
                }.bind(this));
            }
            new_growl.message = new Element('p', {
                'class': 'growl-text',
                'html': params.message
            }).inject(new_growl.item);

            new_growl.item.addEvents({
                'click': function () {
                    if (new_growl.item.get('data-mibclick')) // mibclick trick ;)
                        new_growl.item.erase('data-mibclick');
                }.bind(this),
                'mibclick': function (event) {
                    event.stop();
                    this.remove(new_growl);
                    new_growl.item.set('data-mibclick', true);
                }.bind(this)
            });

            new_growl.item.inject(this.options.container || document.body);

            new_growl.height = new_growl.item.getSize().y;

            // prépare le morphing
            var to = {'opacity': this.options.opacity, 'z-index': this.options.zindex};

            var last_growl = this.growls.getLast();
            if (last_growl) { // on récup les infos du dernier growl
                to['top'] = new_growl.top = last_growl.top + last_growl.height + this.options.margin;
                new_growl.item.setStyle('top', last_growl.top);
            } else { // Il n'y a plus aucun growl affiché
                to['top'] = new_growl.top = new_growl.item.getPosition().y;
                new_growl.item.setStyle('top', (new_growl.top - new_growl.height));
            }

            this.growls.push(new_growl);

            // Ajoute les events en fonction des options
            if (params.options.color)
                new_growl.item.setStyle('background-color', params.options.color);
            if (params.options.type)
                new_growl.item.addClass('growl-' + params.options.type);

            if (params.options.duration && params.options.duration > -1) // SI duration = -1, pas de durée de vie (attention, il doit y avoir un titre)
                (function () {
                    this.remove(new_growl)
                }.bind(this)).delay(params.options.duration);
            else if (params.options.duration == -1 && params.title) {
                // On ne fait rien, car la duration est illimitée
            } else
                (function () {
                    this.remove(new_growl)
                }.bind(this)).delay(this.options.liveDuration);

            if (params.options.onShow && $type(params.options.onShow) == 'function')
                this.addEvent('show', params.options.onShow);

            new_growl.item.morph(to);

            this.fireEvent('show', new_growl.item);
        }
    },

    remove: function (growl) {
        var index = this.growls.indexOf(growl);
        if (index != -1)
            this.growls.splice(index, 1);

        growl.item.setStyle('z-index', (this.options.zindex - 1)).morph({
            opacity: 0,
            top: (growl.top - growl.height)
        });

        (function () {
            growl.item.destroy()
        }).delay(this.options.showDuration);
    }
});

/**
 * Alert, Confirm, Prompt Box
 */
var MIB_ACPbox = new Class({
    Implements: [Events, Options],

    options: {
        /*onClose: function(returnValue, apcbox) { },*/

        zindex: 999,
        container: null,
        className: null,

        overlayClassName: 'acp-overlay',
        overlayOpacity: 0.5,
        overlayShowDuration: 250,
        fields: null,

        buttonClassName: 'button',
        buttonOkText: 'OK',
        buttonCancelText: 'Annuler',

        inputClassName: 'input'
    },

    initialize: function (options) {
        this.setOptions(options);

        // Si on a un overlay
        if (this.options.overlayOpacity.toFloat() > 0) {
            this.overlay = new Element('div').inject(this.options.container || document.body);
            if (this.options.overlayClassName)
                this.overlay.addClass(this.options.overlayClassName);
            this.overlay.setStyles({
                'position': 'absolute',
                'top': 0,
                'left': 0,
                'opacity': 0,
                'visibility': 'hidden',
                'z-index': (this.options.zindex - 1)
            }).set('morph', {duration: this.options.overlayShowDuration});
            // Resize l'overlay si la fenetre est redimentionnée
            window.addEvent('resize', this.resizeOverlay.bind(this));
        }

        // Création de la box
        this.acpbox = new Element('div').inject(this.options.container || document.body);
        if (this.options.className)
            this.acpbox.addClass(this.options.className);
        this.acpbox.setStyles({
            'position': 'absolute',
            'visibility': 'hidden',
            'z-index': this.options.zindex
        }).set('morph');

        // Préparation des champs de formulaire
        this.form = new Element('form', {
            'class': 'acp-form'
        });
        this.input = new Element('input');

        if (this.options.inputClassName) {
            this.input.addClass(this.options.inputClassName);
        }

        // Préparation des buttons
        this.buttons = new Element('div', {
            'class': 'acp-buttons'
        });
        // Boutton OK
        this.buttonOk = new Element('input', {
            'type': 'submit',
            'value': this.options.buttonOkText
        });
        // Boutton Annuler
        this.buttonCancel = new Element('input', {
            'type': 'submit',
            'value': this.options.buttonCancelText
        });
        if (this.options.buttonClassName) {
            this.buttonOk.addClass(this.options.buttonClassName);
            this.buttonCancel.addClass(this.options.buttonClassName);
        }
    },

    /**
     * Redimentionne l'Overlay à la taille de la fenêtre active
     */
    resizeOverlay: function () {
        if (this.overlay) {
            var size = window.getSize();
            this.overlay.setStyles({
                'height': size.y,
                'width': size.x
            });
        }
    },

    /**
     * Affiche acpbox
     */
    show: function () {
        // Affiche l'overlay
        if (this.overlay) {
            this.resizeOverlay();
            this.overlay.morph({'opacity': this.options.overlayOpacity.toFloat()});
        }

        // Affiche la box
        this.acpbox.setStyles({
            visibility: 'visible',
            opacity: 1
        });

        // Donne le focus au boutton
        if (this.acpbox.retrieve('acp:type') == 'alert')
            this.buttonOk.focus();
        else if (this.acpbox.retrieve('acp:type') == 'confirm')
            this.buttonCancel.focus();
        else if (this.acpbox.retrieve('acp:type') == 'prompt')
            this.input.focus();

        this.fireEvent('show', this);
    },

    /**
     * Ferme acpbox et envois les infos
     */
    close: function (hide) {
        if (this.acpbox.retrieve('acp:hide') || hide) {
            // Enleve les events des actions pour ne pas poster 2 fois l'info (ex. doubleclic)
            this.form.removeEvents().addEvent('submit', function (e) {
                e.stop();
            });
            this.buttonOk.removeEvents();
            this.buttonCancel.removeEvents();

            // Cache la box
            this.acpbox.setStyles({
                visibility: 'hidden',
                opacity: 0
            });

            if (this.overlay)
                this.overlay.morph({'opacity': 0});

            this.fireEvent('hide', this);
        }

        this.fireEvent('close', [this.acpbox.retrieve('acp:return'), this]);
    },

    /**
     * Clean acpbox et l'initialise pour une nouvelle execution
     */
    clean: function () {
        // Enleve les events des actions (buttons + form)
        this.form.removeEvents();
        this.buttonOk.removeEvents();
        this.buttonCancel.removeEvents();

        // Rezet l'events close
        this.removeEvents('close');

        // Rezet le input
        this.input = new Element('input');
        if (this.options.inputClassName) this.input.addClass(this.options.inputClassName);

        // Rezet le type et le return et hide
        this.acpbox.store('acp:return', false).store('acp:type', null).store('acp:hide', true);

        // On vide la box
        this.acpbox.setStyles({'visibility': 'hidden', 'opacity': 0}).empty();
        // SI l'overlay existe
        if (this.overlay)
            this.overlay.setStyles({'visibility': 'hidden', 'opacity': 0});
    },

    alert: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};

        // on a un message
        if (params.message) {
            this.clean();
            this.acpbox.store('acp:type', 'alert');
            if (params.options.hide === false) this.acpbox.store('acp:hide', false);
            if (params.options.onClose && $type(params.options.onClose) == 'function')
                this.addEvent('close', params.options.onClose);
            // Id de la box et de l'overlay
            if (params.options.id) {
                this.acpbox.set('id', params.options.id);
                if (this.overlay)
                    this.overlay.set('id', params.options.id + '-overlay');
            }

            // Titre
            if (params.title) new Element('h1', {'class': 'acp-title', 'html': params.title}).inject(this.acpbox);
            // Message
            new Element('p', {'class': 'acp-text', 'html': params.message}).inject(this.acpbox);
            // Button
            this.buttons.inject(this.acpbox);
            this.buttonOk.addEvent('click', function () {
                this.acpbox.store('acp:return', true);
                this.close();
            }.bind(this)).inject(this.buttons);

            this.show();
        }
    },

    confirm: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};

        // on a un message
        if (params.message) {
            this.clean();
            this.acpbox.store('acp:type', 'confirm');
            if (params.options.hide === false) this.acpbox.store('acp:hide', false);
            if (params.options.onClose && $type(params.options.onClose) == 'function')
                this.addEvent('close', params.options.onClose);
            // Id de la box et de l'overlay
            if (params.options.id) {
                this.acpbox.set('id', params.options.id);
                if (this.overlay)
                    this.overlay.set('id', params.options.id + '-overlay');
            }

            // Titre
            if (params.title) new Element('h1', {'class': 'acp-title', 'html': params.title}).inject(this.acpbox);
            // Message
            new Element('p', {'class': 'acp-text', 'html': params.message}).inject(this.acpbox);
            // Button
            this.buttons.inject(this.acpbox);
            this.buttonOk.addEvent('click', function () {
                this.acpbox.store('acp:return', true);
                this.close();
            }.bind(this)).inject(this.buttons);
            this.buttonCancel.addEvent('click', function () {
                this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);

            this.show();
        }
    },

    prompt: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};

        // on a un message
        if (params.message) {
            this.clean();
            this.acpbox.store('acp:type', 'prompt');
            if (params.options.hide === false) this.acpbox.store('acp:hide', false);
            if (params.options.onClose && $type(params.options.onClose) == 'function')
                this.addEvent('close', params.options.onClose);
            // Id de la box et de l'overlay
            if (params.options.id) {
                this.acpbox.set('id', params.options.id);
                if (this.overlay)
                    this.overlay.set('id', params.options.id + '-overlay');
            }

            // Titre
            if (params.title) new Element('h1', {'class': 'acp-title', 'html': params.title}).inject(this.acpbox);
            // Message
            new Element('p', {'class': 'acp-text', 'html': params.message}).inject(this.acpbox);
            // Input
            this.form.inject(this.acpbox);

            if (params.options.input && $(params.options.input)) {
                this.input = $(params.options.input).clone();
                this.input.inject(this.form);
            } else {
                this.input.inject(this.form);
                this.input.set('type', params.options.type || 'text');
                this.input.set('value', params.options.value || '');
            }

            this.form.addEvent('submit', function (e) {
                e.stop();
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this));

            // Button
            this.buttons.inject(this.acpbox);
            this.buttonOk.addEvent('click', function () {
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);
            this.buttonCancel.addEvent('click', function () {
                this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);

            this.show();
        }
    },

    uploadForm: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};


        // on a un message
        if (params.message) {
            this.clean();
            this.acpbox.store('acp:type', 'prompt');
            if (params.options.hide === false) this.acpbox.store('acp:hide', false);
            if (params.options.onClose && $type(params.options.onClose) == 'function')
                this.addEvent('close', params.options.onClose);
            // Id de la box et de l'overlay
            if (params.options.id) {
                this.acpbox.set('id', params.options.id);
                if (this.overlay)
                    this.overlay.set('id', params.options.id + '-overlay');
            }

            // Titre
            if (params.title) new Element('h1', {'class': 'acp-title', 'html': params.title}).inject(this.acpbox);
            // Message
            new Element('p', {'class': 'acp-text', 'html': params.message}).inject(this.acpbox);
            // Input
            this.form.inject(this.acpbox);

            if (params.options.input && $(params.options.input)) {
                this.input = $(params.options.input).clone();
                this.input.inject(this.form);
            } else {
                this.input.inject(this.form);
                this.input.set('type', params.options.type || 'text');
                this.input.set('value', params.options.value || '');
            }
            if (params.options.fields) {
                this.fields = params.options.fields;
                for (var p in this.fields) {
                    var div = new Element('div', {'class': 'Form-field', 'for': 'uploadFormid_' + p});
                    var label = new Element('label');
                    label.set('html', this.fields[p]);
                    div.append(label);
                    var value = (params.options.fieldValues && params.options.fieldValues[p]) ? params.options.fieldValues[p] : '';
                    var input = new Element('input');
                    input.set('type', 'text');
                    input.set('name', p);
                    input.set('id', 'uploadFormid_' + p);
                    input.set('value', value);
                    div.append(input);
                    div.inject(this.form);
                }
            }

            this.form.addEvent('submit', function (e) {
                e.stop();
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this));

            // Button
            this.buttons.inject(this.acpbox);
            this.buttonOk.addEvent('click', function () {
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);
            this.buttonCancel.addEvent('click', function () {
                this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);

            this.show();
        }
    },
    promptForm: function (message, title, options) {
        var params = Array.link(arguments, {message: String.type, title: String.type, options: Object.type});
        params.options = params.options || {};

        // on a un message
        if (params.message) {
            this.clean();
            this.acpbox.store('acp:type', 'prompt');
            if (params.options.hide === false) this.acpbox.store('acp:hide', false);
            if (params.options.onClose && $type(params.options.onClose) == 'function')
                this.addEvent('close', params.options.onClose);
            // Id de la box et de l'overlay
            if (params.options.id) {
                this.acpbox.set('id', params.options.id);
                if (this.overlay)
                    this.overlay.set('id', params.options.id + '-overlay');
            }

            // Titre
            if (params.title) new Element('h1', {'class': 'acp-title', 'html': params.title}).inject(this.acpbox);
            // Message
            new Element('p', {'class': 'acp-text', 'html': params.message}).inject(this.acpbox);
            // Input
            this.form.inject(this.acpbox);

            if (params.options.fields) {
                this.fields = params.options.fields;
                for (var p in this.fields) {
                    var div = new Element('div', {'class': 'Form-field', 'for': 'uploadFormid_' + p});
                    var label = new Element('label');
                    label.set('html', this.fields[p]);
                    div.append(label);
                    var value = (params.options.fieldValues && params.options.fieldValues[p]) ? params.options.fieldValues[p] : '';
                    var input = new Element('input');
                    input.set('type', 'text');
                    input.set('name', p);
                    input.set('id', 'uploadFormid_' + p);
                    input.set('value', value);
                    div.append(input);
                    div.inject(this.form);
                }
            }


            if (params.options.input && $(params.options.input)) {
                this.input = $(params.options.input).clone();
                this.input.inject(this.form);
            } else {
                this.input.inject(this.form);
                this.input.set('type', params.options.type || 'text');
                this.input.set('value', params.options.value || '');
            }

            this.form.addEvent('submit', function (e) {
                e.stop();
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this));

            // Button
            this.buttons.inject(this.acpbox);
            this.buttonOk.addEvent('click', function () {
                if (this.input.get('value') != '')
                    this.acpbox.store('acp:return', this.input.get('value'));
                else
                    this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);
            this.buttonCancel.addEvent('click', function () {
                this.acpbox.store('acp:return', false);
                this.close();
            }.bind(this)).inject(this.buttons);

            this.show();
        }
    },

});

/**
 * ColorPicker
 */
var MIB_ColorPickers = new Class({
    Implements: [Events, Options],

    options: {
        onShow: function (colorpicker) {
            colorpicker.setStyle('visibility', 'visible');
        },
        onHide: function (colorpicker) {
            colorpicker.setStyle('visibility', 'hidden');
        },
        showDelay: 100,
        hideDelay: 100,
        className: null,
        offsets: {x: -25, y: 25},
        color: '#fff'
    },

    initialize: function () {
        var params = Array.link(arguments, {options: Object.type, elements: $defined});
        this.setOptions(params.options || null);
        this.lock = false;

        this.colorpicker = new Element('div').addEvents({
            'mouseover': function () {
                this.lock = true;
            }.bind(this),
            'mouseout': function () {
                this.lock = false;
            }.bind(this)
        }).inject(document.body);

        if (this.options.className) this.colorpicker.addClass(this.options.className);

        var top = new Element('div', {'class': 'colorpicker-top'}).inject(this.colorpicker);
        this.container = new Element('div', {'class': 'colorpicker'}).inject(this.colorpicker);
        var bottom = new Element('div', {'class': 'colorpicker-bottom'}).inject(this.colorpicker);

        this.radius = 84;
        this.square = 100;
        this.width = 194;
        this.halfWidth = this.width / 2;

        this.preview = new Element('div', {
            'class': 'preview'
        }).addEvents({
            'click': function () {
                this.current = this.color;
                this.elementChange();
            }.bind(this)
        });
        this.colour = new Element('div', {
            'class': 'color'
        });
        this.wheel = new Element('div', {
            'class': 'wheel'
        }).addEvent('mousedown', this.wheelStart.bind(this)).addEvent('mouseup', this.wheelMove.bind(this));
        this.overlay = new Element('div', {
            'class': 'overlay'
        }).addEvent('mousedown', this.overlayStart.bind(this)).addEvent('mouseup', this.overlayMove.bind(this));
        this.colourCursor = new Element('div', {
            'class': 'h-marker marker'
        });
        this.wheelCursor = new Element('div', {
            'class': 'sl-marker marker'
        });
        this.container.adopt(this.preview, this.colour, this.wheel, this.overlay, this.colourCursor, this.wheelCursor);

        this.container.addEvent('mouseup', this.mouseup.bind(this));

        this.colorpicker.setStyles({position: 'absolute', top: 0, left: 0, visibility: 'hidden'});

        if (params.elements) this.attach(params.elements);
    },

    wheelStart: function (e) {
        this.wheelPosition = this.wheel.getPosition();
        this.wheel.addEvent('mousemove', this.wheelMove.bind(this));
        this.wheelMove(e);
    },

    wheelMove: function (e) {
        var x = e.page.x - this.wheelPosition.x - this.halfWidth;
        var y = e.page.y - this.wheelPosition.y - this.halfWidth;
        var hue = Math.atan2(x, -y) / 6.28;
        if (hue < 0) hue += 1;
        this.setHSL([hue, this.hsl[1], this.hsl[2]]);
    },

    overlayStart: function (e) {
        this.overlayPosition = this.overlay.getPosition();
        this.overlay.addEvent('mousemove', this.overlayMove.bind(this));
        this.overlayMove(e);
    },

    overlayMove: function (e) {
        var x = e.page.x - this.overlayPosition.x;
        var y = e.page.y - this.overlayPosition.y;
        var sat = 1 - x / this.square;
        var lum = 1 - y / this.square;
        this.setHSL([this.hsl[0], sat, lum]);
    },

    mouseup: function (e) {
        this.wheel.removeEvents('mousemove', 'mouseup');
        this.overlay.removeEvents('mousemove', 'mouseup');
    },

    keyup: function (e) {
        var color = e.target.value;

        if (color != '' && color.substring(0, 1) != '#') {
            color = '#' + color;
            this.el.set('value', color);
        }

        if (color == '' || color == '#') {
            this.current = color;
            this.el.store('colorpicker:current', this.current);
            this.elementChange(true);
        } else {
            this.setColor(color);

            if (this.unpack(e.target.value)) {
                this.setColor(e.target.value);
                this.current = this.color;
                this.elementChange(true);
            }
        }

    },

    updateDisplay: function () {
        var angle = this.hsl[0] * 6.28;

        var left = Math.round(Math.sin(angle) * this.radius + this.width / 2);
        var top = Math.round(-Math.cos(angle) * this.radius + this.width / 2) + this.preview.getSize().y + 1;

        this.colourCursor.setStyles({
            'left': left,
            'top': top
        });

        var left = Math.round(this.square * (0.5 - this.hsl[1]) + this.width / 2);
        var top = Math.round(this.square * (0.5 - this.hsl[2]) + this.width / 2);
        if (isNaN(left)) left = 0;
        if (isNaN(top)) {
            top = this.overlay.getCoordinates(this.container).top;
            left = this.overlay.getCoordinates(this.container).left;
        } else
            top = top + this.preview.getSize().y + 1;

        this.wheelCursor.setStyles({
            'left': left,
            'top': top
        });

        this.colour.setStyle('background-color', this.pack(this.HSLToRGB([this.hsl[0], 1, 0.5])));

        this.preview.setStyle('background-color', this.color);

        this.el.store('colorpicker:current', this.current);
        this.current = this.color;

        this.el.focus(); // keep focus and do automatique rebuild ;)
    },

    pack: function (rgb) {
        var r = Math.round(rgb[0] * 255);
        var g = Math.round(rgb[1] * 255);
        var b = Math.round(rgb[2] * 255);
        return '#' +
            (r < 16 ? '0' : '') + r.toString(16) +
            (g < 16 ? '0' : '') + g.toString(16) +
            (b < 16 ? '0' : '') + b.toString(16);
    },

    unpack: function (color) {
        if ((color.length == 6 || color.length == 3) && color.substring(0, 1) != '#') color = '#' + color;

        if (color.length == 7) {
            return [parseInt('0x' + color.substring(1, 3), 16) / 255,
                parseInt('0x' + color.substring(3, 5), 16) / 255,
                parseInt('0x' + color.substring(5, 7), 16) / 255];
        } else if (color.length == 4) {
            return [parseInt('0x' + color.substring(1, 2), 16) / 15,
                parseInt('0x' + color.substring(2, 3), 16) / 15,
                parseInt('0x' + color.substring(3, 4), 16) / 15];
        }
    },

    RGBToHSL: function (rgb) {
        var min, max, delta, h, s, l;
        var r = rgb[0], g = rgb[1], b = rgb[2];
        min = Math.min(r, g, b);
        max = Math.max(r, g, b);
        delta = max - min;
        l = (min + max) / 2;
        s = 0;
        if (l > 0 && l < 1) {
            s = delta / (l < 0.5 ? (2 * l) : (2 - 2 * l));
        }
        h = 0;
        if (delta > 0) {
            if (max == r && max != g) h += (g - b) / delta;
            if (max == g && max != b) h += (2 + (b - r) / delta);
            if (max == b && max != r) h += (4 + (r - g) / delta);
            h /= 6;
        }
        return [h, s, l];
    },

    HSLToRGB: function (hsl) {
        var m1, m2, r, g, b;
        var h = hsl[0], s = hsl[1], l = hsl[2];
        m2 = (l <= 0.5) ? l * (s + 1) : l + s - l * s;
        m1 = l * 2 - m2;
        return [this.HueToGRB(m1, m2, h + 0.33333),
            this.HueToGRB(m1, m2, h),
            this.HueToGRB(m1, m2, h - 0.33333)];
    },

    HueToGRB: function (m1, m2, h) {
        h = (h < 0) ? h + 1 : ((h > 1) ? h - 1 : h);
        if (h * 6 < 1) return m1 + (m2 - m1) * h * 6;
        if (h * 2 < 1) return m2;
        if (h * 3 < 2) return m1 + (m2 - m1) * (0.66666 - h) * 6;
        return m1;
    },

    setHSL: function (hsl) {
        this.hsl = hsl;
        this.rgb = this.HSLToRGB(hsl);
        this.color = this.pack(this.rgb);
        this.updateDisplay();
        return this;
    },

    setColor: function (color) {
        if (!color) color = this.el.get('value') || this.options.color;

        var unpack = this.unpack(color);
        if (this.color != color && unpack) {
            this.color = color;
            this.rgb = unpack;
            this.hsl = this.RGBToHSL(this.rgb);
            this.updateDisplay();
        }
        return this;
    },

    attach: function (elements) {
        $$(elements).each(function (element) {
            var colorvalue = element.retrieve('colorpicker:colorvalue', element.get('value'));
            if (!colorvalue) {
                colorvalue = '#ffffff';
                element.store('colorpicker:colorvalue', colorvalue);
            }
            element.store('colorpicker:current', colorvalue);

            var inputFocus = element.retrieve('colorpicker:focus', this.elementFocus.bindWithEvent(this, element));
            var inputBlur = element.retrieve('colorpicker:blur', this.elementBlur.bindWithEvent(this, element));
            element.addEvents({focus: inputFocus, blur: inputBlur});

            element.addEvent('keyup', this.keyup.bind(this));
            element.getPrevious('label').addEvents({
                'click': function (e) {
                    //e.stop();
                    this.current = element.get('value')
                    this.setColor(this.current);
                }.bind(this)
            });
        }, this);
        return this;
    },

    detach: function (elements) {
        $$(elements).each(function (element) {
            element.removeEvent('onfocus', element.retrieve('colorpicker:focus') || $empty);
            element.removeEvent('onblur', element.retrieve('colorpicker:blur') || $empty);
            element.eliminate('colorpicker:focus').eliminate('colorpicker:blur');
            element.getPrevious('label').removeEvents('click');
            element.removeEvents('keyup');
        });
        return this;
    },

    elementFocus: function (event, element) {
        this.el = element;

        this.current = element.retrieve('colorpicker:current');

        this.build();

        this.timer = $clear(this.timer);
        this.timer = this.show.delay(this.options.showDelay, this);

        this.position({page: element.getPosition()});
    },

    elementChange: function (keep) {
        this.el.store('colorpicker:current', this.current);
        this.el.set('value', this.current);
        if (this.current == '' || this.current == '#')
            this.el.getPrevious('label').setStyle('background', null);
        else
            this.el.getPrevious('label').setStyle('background', this.current);

        if (!keep) {
            $clear(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this);
        }
    },

    elementBlur: function (event) {
        if (!this.lock) {
            $clear(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this);
        }
    },

    position: function (event) {
        var size = window.getSize(), scroll = window.getScroll();
        var colorpicker = {x: this.colorpicker.offsetWidth, y: this.colorpicker.offsetHeight};
        var props = {x: 'left', y: 'top'};
        for (var z in props) {
            var pos = event.page[z] + this.options.offsets[z];
            if ((pos + colorpicker[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - colorpicker[z];
            this.colorpicker.setStyle(props[z], pos);
        }
    },

    show: function () {
        this.fireEvent('show', this.colorpicker);
    },

    hide: function () {
        if (this.el.get('value') == '#') this.el.set('value', '');
        this.fireEvent('hide', this.colorpicker);
    },

    build: function () {
        /*
			<div class="color"></div>
			<div class="wheel"></div>
			<div class="overlay"></div>
			<div class="h-marker marker"></div>
			<div class="sl-marker marker"></div>
		*/
        this.setColor(this.current);
        this.updateDisplay();
    }
});

/**
 * DatePicker
 */
var MIB_DatePickers = new Class({

    Implements: [Events, Options],

    options: {
        onShow: function (datepicker) {
            datepicker.setStyle('visibility', 'visible');
        },
        onHide: function (datepicker) {
            datepicker.setStyle('visibility', 'hidden');
        },
        showDelay: 100,
        hideDelay: 100,
        className: null,
        offsets: {x: 0, y: 25},

        dateformat: 'd/m/Y',

        days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'], // days of the week starting at sunday
        months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        weekFirstDay: 1 // first day of the week: 0 = sunday, 1 = monday, etc..
    },

    initialize: function () {
        var params = Array.link(arguments, {options: Object.type, elements: $defined});
        this.setOptions(params.options || null);
        this.lock = false;

        this.datepicker = new Element('div').addEvents({
            'mouseover': function () {
                this.lock = true;
            }.bind(this),
            'mouseout': function () {
                this.lock = false;
            }.bind(this)
        }).inject(document.body);

        if (this.options.className) this.datepicker.addClass(this.options.className);

        var top = new Element('div', {'class': 'datepicker-top'}).inject(this.datepicker);
        this.container = new Element('div', {'class': 'datepicker'}).inject(this.datepicker);
        var bottom = new Element('div', {'class': 'datepicker-bottom'}).inject(this.datepicker);

        this.datepicker.setStyles({position: 'absolute', top: 0, left: 0, visibility: 'hidden'});

        if (params.elements) this.attach(params.elements);
    },

    attach: function (elements) {
        $$(elements).each(function (element) {
            var dateformat = element.retrieve('datepicker:dateformat', element.get('accept'));
            if (!dateformat) {
                dateformat = this.options.dateformat;
                element.store('datepicker:dateformat', dateformat);
            }
            var datevalue = element.retrieve('datepicker:datevalue', element.get('value'));
            if (!datevalue) {
                datevalue = this.format(new Date(), dateformat);
                element.store('datepicker:datevalue', datevalue);
            }
            element.store('datepicker:current', this.unformat(datevalue, dateformat));

            var inputFocus = element.retrieve('datepicker:focus', this.elementFocus.bindWithEvent(this, element));
            var inputBlur = element.retrieve('datepicker:blur', this.elementBlur.bindWithEvent(this, element));
            element.addEvents({focus: inputFocus, blur: inputBlur});

            element.store('datepicker:native', element.get('accept'));
            element.erase('dateformat');
        }, this);
        return this;
    },

    detach: function (elements) {
        $$(elements).each(function (element) {
            element.removeEvent('onfocus', element.retrieve('datepicker:focus') || $empty);
            element.removeEvent('onblur', element.retrieve('datepicker:blur') || $empty);
            element.eliminate('datepicker:focus').eliminate('datepicker:blur');
            var original = element.retrieve('datepicker:native');
            if (original) element.set('dateformat', original);
        });
        return this;
    },

    elementFocus: function (event, element) {
        this.el = element;

        var current = element.retrieve('datepicker:current');
        this.curFullYear = current[0];
        this.curMonth = current[1];
        this.curDate = current[2];

        this.build();

        this.timer = $clear(this.timer);
        this.timer = this.show.delay(this.options.showDelay, this);

        this.position({page: element.getPosition()});
    },

    elementChange: function () {
        this.el.store('datepicker:current', Array(this.curFullYear, this.curMonth, this.curDate));
        this.el.set('value', this.format(new Date(this.curFullYear, this.curMonth, this.curDate), this.el.retrieve('datepicker:dateformat')));

        $clear(this.timer);
        this.timer = this.hide.delay(this.options.hideDelay, this);
    },

    elementBlur: function (event) {
        if (!this.lock) {
            $clear(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this);
        }
    },

    position: function (event) {
        var size = window.getSize(), scroll = window.getScroll();
        var datepicker = {x: this.datepicker.offsetWidth, y: this.datepicker.offsetHeight};
        var props = {x: 'left', y: 'top'};
        for (var z in props) {
            var pos = event.page[z] + this.options.offsets[z];
            if ((pos + datepicker[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - datepicker[z];
            this.datepicker.setStyle(props[z], pos);
        }
    },

    show: function () {
        this.fireEvent('show', this.datepicker);
    },

    hide: function () {
        this.fireEvent('hide', this.datepicker);
    },

    build: function () {
        $A(this.container.childNodes).each(Element.dispose);

        var table = new Element('table').inject(this.container);
        var caption = this.caption().inject(table);
        var thead = this.thead().inject(table);
        var tbody = this.tbody().inject(table);
    },

    // navigate: calendar navigation
    // @param type (str) m or y for month or year
    // @param d (int) + or - for next or prev
    navigate: function (type, d) {
        switch (type) {
            case 'm': // month
                var i = this.curMonth + d;

                if (i < 0 || i == 12) {
                    this.curMonth = (i < 0) ? 11 : 0;
                    this.navigate('y', d);
                } else
                    this.curMonth = i;

                break;
            case 'y': // year
                this.curFullYear += d;

                break;
        }

        this.el.store('datepicker:current', Array(this.curFullYear, this.curMonth, this.curDate));

        this.el.focus(); // keep focus and do automatique rebuild ;)
    },

    // caption: returns the caption element with header and navigation
    // @returns caption (element)
    caption: function () {
        // start by assuming navigation is allowed
        var navigation = {
            prev: {'month': true, 'year': true},
            next: {'month': true, 'year': true}
        };

        var caption = new Element('caption');

        var prev = new Element('a').addClass('prev').appendText('\x3c'); // <
        var next = new Element('a').addClass('next').appendText('\x3e'); // >

        var month = new Element('span').addClass('month').inject(caption);
        if (navigation.prev.month) {
            prev.clone().addEvent('click', function () {
                this.navigate('m', -1);
            }.bind(this)).inject(month);
        }
        new Element('span').set('text', this.options.months[this.curMonth]).addEvent('mousewheel', function (e) {
            e.stop();
            this.navigate('m', (e.wheel < 0 ? -1 : 1));
            this.build();
        }.bind(this)).inject(month);
        if (navigation.next.month) {
            next.clone().addEvent('click', function () {
                this.navigate('m', 1);
            }.bind(this)).inject(month);
        }

        var year = new Element('span').addClass('year').inject(caption);
        if (navigation.prev.year) {
            prev.clone().addEvent('click', function () {
                this.navigate('y', -1);
            }.bind(this)).inject(year);
        }
        new Element('span').set('text', this.curFullYear).addEvent('mousewheel', function (e) {
            e.stop();
            this.navigate('y', (e.wheel < 0 ? -1 : 1));
            this.build();
        }.bind(this)).inject(year);
        if (navigation.next.year) {
            next.clone().addEvent('click', function () {
                this.navigate('y', 1);
            }.bind(this)).inject(year);
        }

        return caption;
    },

    // thead: returns the thead element with day names
    // @returns thead (element)
    thead: function () {
        var thead = new Element('thead');
        var tr = new Element('tr').inject(thead);
        for (i = 0; i < 7; i++) {
            new Element('th').set('text', this.options.days[(this.options.weekFirstDay + i) % 7].substr(0, 2)).inject(tr);
        }

        return thead;
    },

    // tbody: returns the tbody element with day numbers
    // @returns tbody (element)
    tbody: function () {
        var d = new Date(this.curFullYear, this.curMonth, 1);

        var offset = ((d.getDay() - this.options.weekFirstDay) + 7) % 7; // day of the week (offset)
        var last = new Date(this.curFullYear, this.curMonth + 1, 0).getDate(); // last day of this month
        var prev = new Date(this.curFullYear, this.curMonth, 0).getDate(); // last day of previous month

        var v = (this.el.get('value')) ? this.unformat(this.el.get('value'), this.el.retrieve('datepicker:dateformat')) : false;
        var current = new Date(v[0], v[1], v[2]).getTime();

        var d = new Date();
        var today = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); // today obv

        var tbody = new Element('tbody');

        tbody.addEvent('mousewheel', function (e) {
            e.stop(); // prevent the mousewheel from scrolling the page.
            this.navigate('m', (e.wheel < 0 ? -1 : 1));
            this.build();
        }.bind(this));

        for (var i = 1; i < 43; i++) { // 1 to 42 (6 x 7 or 6 weeks)
            if ((i - 1) % 7 == 0) {
                tr = new Element('tr').inject(tbody);
            } // each week is it's own table row

            var td = new Element('td').inject(tr);
            var day = i - offset;
            var date = new Date(this.curFullYear, this.curMonth, day);

            if (day < 1) { // last days of prev month
                day = prev + day;
                td.addClass('inactive');
            } else if (day > last) { // first days of next month
                day = day - last;
                td.addClass('inactive');
            } else {
                if (date.getTime() == current) {
                    td.addClass('hilite');
                } else if (date.getTime() == today) {
                    td.addClass('today');
                } // add class for today

                td.addEvents({
                    'click': function (day) {
                        this.curDate = day;
                        this.elementChange();
                    }.bind(this, day),
                    'mouseover': function (td) {
                        td.addClass('hilite');
                    }.bind(this, td),
                    'mouseout': function (td) {
                        if (date.getTime() != current)
                            td.removeClass('hilite');
                    }.bind(this, td)
                }).addClass('active');
            }

            td.set('text', day);
        }
        return tbody;
    },

    // unformat: takes a value from an input and parses the d, m and y elements
    // @param val (string)
    // @param f (string) any combination of punctuation / separators and d, j, D, l, S, m, n, F, M, y, Y
    // @returns array
    unformat: function (val, f) {
        f = f.escapeRegExp();

        var re = {
            d: '([0-9]{2})',
            j: '([0-9]{1,2})',
            D: '(' + this.options.days.map(function (day) {
                return day.substr(0, 3);
            }).join('|') + ')',
            l: '(' + this.options.days.join('|') + ')',
            S: '(st|nd|rd|th)',
            F: '(' + this.options.months.join('|') + ')',
            m: '([0-9]{2})',
            M: '(' + this.options.months.map(function (month) {
                return month.substr(0, 3);
            }).join('|') + ')',
            n: '([0-9]{1,2})',
            Y: '([0-9]{4})',
            y: '([0-9]{2})'
        }

        var arr = []; // array of indexes

        var g = '';

        // convert our format string to regexp
        for (var i = 0; i < f.length; i++) {
            var c = f.charAt(i);

            if (re[c]) {
                arr.push(c);

                g += re[c];
            } else {
                g += c;
            }
        }

        // match against date
        var matches = val.match('^' + g + '$');

        var dates = new Array(3);

        if (matches) {
            matches = matches.slice(1); // remove first match which is the date

            arr.each(function (c, i) {
                i = matches[i];

                switch (c) {
                    // year cases
                    case 'y':
                        i = '19' + i; // 2 digit year assumes 19th century (same as JS)
                    case 'Y':
                        dates[0] = i.toInt();
                        break;

                    // month cases
                    case 'F':
                        i = i.substr(0, 3);
                    case 'M':
                        i = this.options.months.map(function (month) {
                            return month.substr(0, 3);
                        }).indexOf(i) + 1;
                    case 'm':
                    case 'n':
                        dates[1] = i.toInt() - 1;
                        break;

                    // day cases
                    case 'd':
                    case 'j':
                        dates[2] = i.toInt();
                        break;
                }
            }, this);
        }

        dates[0] = (dates[0]) ? dates[0] : new Date().getFullYear();
        dates[1] = (dates[1] || dates[1] === 0) ? dates[1] : new Date().getMonth();
        dates[2] = (dates[2]) ? dates[2] : new Date().getDate();

        return dates;
    },

    // format: formats a date object according to passed in instructions
    // @param date (obj)
    // @param format (string) any combination of punctuation / separators and d, j, D, l, S, m, n, F, M, y, Y
    // @returns string
    format: function (date, format) {
        var str = '';

        if (date) {
            var j = date.getDate(); // 1 - 31
            var w = date.getDay(); // 0 - 6
            var l = this.options.days[w]; // Sunday - Saturday
            var n = date.getMonth() + 1; // 1 - 12
            var f = this.options.months[n - 1]; // January - December
            var y = date.getFullYear() + ''; // 19xx - 20xx

            for (var i = 0, len = format.length; i < len; i++) {
                var cha = format.charAt(i); // format char

                switch (cha) {
                    // year cases
                    case 'y': // xx - xx
                        y = y.substr(2);
                    case 'Y': // 19xx - 20xx
                        str += y;
                        break;

                    // month cases
                    case 'm': // 01 - 12
                        if (n < 10) {
                            n = '0' + n;
                        }
                    case 'n': // 1 - 12
                        str += n;
                        break;

                    case 'M': // Jan - Dec
                        f = f.substr(0, 3);
                    case 'F': // January - December
                        str += f;
                        break;

                    // day cases
                    case 'd': // 01 - 31
                        if (j < 10) {
                            j = '0' + j;
                        }
                    case 'j': // 1 - 31
                        str += j;
                        break;

                    case 'D': // Sun - Sat
                        l = l.substr(0, 3);
                    case 'l': // Sunday - Saturday
                        str += l;
                        break;

                    case 'N': // 1 - 7
                        w += 1;
                    case 'w': // 0 - 6
                        str += w;
                        break;

                    case 'S': // st, nd, rd or th (works well with j)
                        if (j % 10 == 1 && j != '11') {
                            str += 'st';
                        } else if (j % 10 == 2 && j != '12') {
                            str += 'nd';
                        } else if (j % 10 == 3 && j != '13') {
                            str += 'rd';
                        } else {
                            str += 'th';
                        }
                        break;

                    default:
                        str += cha;
                }
            }
        }

        return str; //  return format with values replaced
    }
});

/**
 * WYSIWYG Editor
 */
$extend(Element.NativeEvents, {'paste': 2, 'input': 2});
Element.Events.paste = {
    base: (Browser.Engine.presto || (Browser.Engine.gecko && Browser.Engine.version < 19)) ? 'input' : 'paste',
    condition: function (e) {
        this.fireEvent('paste', e, 1);
        return false;
    }
};
var MIB_Wysiwyg = new Class({
    Implements: [Events, Options],

    options: {
        buttonsCFG: {
            undo: ['undo', null],
            redo: ['redo', null],
            bold: ['bold', null],
            italic: ['italic', null],
            underline: ['underline', null],
            strikethrough: ['strikethrough', null],
            superscript: ['superscript', null],
            subscript: ['subscript', null],
            justifyleft: ['justifyleft', null],
            justifycenter: ['justifycenter', null],
            justifyright: ['justifyright', null],
            justifyfull: ['justifyfull', null],
            indent: ['indent', null],
            outdent: ['outdent', null],
            formatH1: ['formatblock', '<h1>'],
            formatH2: ['formatblock', '<h2>'],
            formatH3: ['formatblock', '<h3>'],
            formatH4: ['formatblock', '<h4>'],
            formatH5: ['formatblock', '<h5>'],
            formatH6: ['formatblock', '<h6>'],
            formatP: ['formatblock', '<p>'],
            list: ['insertunorderedlist', null],
            listorder: ['insertorderedlist', null],
            inserthorizontalrule: ['inserthorizontalrule', null],
            createlink: ['createlink', 'Veuillez indiquer l\'URL du lien que vous voulez ajouter :', 'Ajouter un lien', 'http://'],
            unlink: ['unlink', null],
            insertimage: ['insertimage', 'Veuillez indiquer l\'URL de l\'image que vous voulez ajouter :', 'Ajouter une image', 'http://'],
            removeformat: ['removeformat', null],
            toggleview: ['toggleview']
        },
        buttons: ['undo', 'redo', null, 'formatH1', 'formatH2', 'formatH3', 'formatH4', 'formatH5', 'formatH6', 'formatP', null, 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', null, 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', null, 'list', 'listorder', 'indent', 'outdent', 'inserthorizontalrule', null, 'insertimage', 'createlink', 'unlink', null, 'removeformat', 'toggleview'],
        iframe: 'about:blank',
        classWYSIWYG: 'wysiwyg',
        classPage: 'page',
        loadJSON: null,
        whitelist: {
            p: [],
            br: [],
            a: ['href', 'target'],
            h1: [],
            h2: [],
            h3: [],
            h4: [],
            h5: [],
            h6: [],
            ol: [],
            ul: [],
            blockquote: [],
            strong: [],
            img: ['src', 'alt'],
            hr: [],
            b: [],
            em: [],
            i: [],
            u: [],
            strike: [],
            sub: [],
            div: ['class']
        }
    },

    initialize: function (textarea, options) {
        this.setOptions(options);

        this.TA = textarea;
        this.size = this.TA.getSize();
        this.CT = new Element('div', {
            'class': this.options.classWYSIWYG,
            'styles': {
                'width': '100%'
            }
        }).injectBefore(this.TA);
        this.TB = new Element('div', {
            'class': 'toolbar',
            'styles': {
                'width': '100%'
            }
        });
        this.IF = new Element('iframe', {
            'frameborder': 0,
            'class': 'hidden',
            'src': this.options.iframe + '?WYSIWYG=true',
            'styles': {
                'height': this.size.y,
                'width': '100%'
            }
        }).addEvent('load', function () {
            this.win = this.IF.contentWindow;
            this.doc = this.win.document;

            this.doc.designMode = 'on';
            if ($try(function () {
                if (this.doc.body) {
                    return true;
                }
            }.bind(this)))
                this.doc.body.className = this.options.classPage;

            this.doc.addEvent('paste', function (e) {
                if (e.event.clipboardData) {
                    var paste = e.event.clipboardData.getData('text/plain');

                    var html = e.event.clipboardData.getData('text/html');
                    if (html !== undefined && html != '') paste = html;

                    this.doc.execCommand('insertHTML', false, this.cleanHTML(paste));
                }
                e.stopPropagation();
                e.preventDefault();
            }.bind(this));

            this.doc.addEvent('click', function (e) {
                if (e.target && e.target.get('tag') == 'img') {

                    if (this.doc.body.createTextRange) {
                        var range = this.doc.body.createTextRange();
                        range.moveToElementText(e.target);
                        range.select();
                    } else if (this.win.getSelection) {
                        var selection = this.win.getSelection();
                        var range = selection.rangeCount > 0 ? selection.getRangeAt(0) : (selection.createRange ? selection.createRange() : null);
                        //var range = this.doc.createRange();
                        if (selection.addRange) {
                            range.selectNode(e.target);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        } else {
                            selection.setBaseAndExtent(e.target, 0, e.target, 1);
                        }
                    }
                }

                e.stopPropagation();
                e.preventDefault();
            }.bind(this));

            this.toggleView();
        }.bind(this));
        this.CT.adopt(this.TB, this.IF, this.TA);
        this.TA.setStyle('width', '100%');

        this.open = false;

        $each(this.options.buttons, function (btn) {
            if (!btn) // Séparateur
                new Element('span', {'class': 'sep'}).inject(this.TB);
            else {
                new Element('span', {'class': 'btn btn-' + btn}).addEvent('click', function () {
                    if (btn == 'toggleview')
                        this.toggleView();
                    else
                        this.exec(btn);
                }.bind(this)).inject(this.TB);
            }
        }, this);

        if (this.options.loadJSON) {
            this.json = new Request.JSON({
                url: this.options.loadJSON, onSuccess: function (responseJSON, responseText) {
                    if (responseJSON.content || responseJSON.content == '')
                        this.TA.value = responseJSON.content;
                    if ($try(function () {
                        if (this.doc.body) {
                            return true;
                        }
                    }.bind(this)))
                        this.doc.body.innerHTML = responseJSON.content;
                }.bind(this)
            }).get();
        }
    },

    cleanHTML: function (html) {
        // supprime les commentaires
        html = html.replace(/<!--[\s\S]*?-->/g, '');

        var html_cleaned = new Element('div', {'html': html});

        html_cleaned.getElements('*').each(function (el) {
            var tag = el.get('tag');

            if ($defined(this.options.whitelist[tag])) {
                // Supprime les attributs non voulu
                Array.prototype.slice.call(el.attributes).each(function (attr) {
                    if (this.options.whitelist[tag].indexOf(attr.name.toLowerCase()) === -1)
                        el.removeAttribute(attr.name);
                }.bind(this));
            } else {
                // conserver les enfants
                el.getChildren().each(function (child) {
                    child.inject(el, 'before');
                });
                // ajoute le texte
                var text = el.get('text').clean();
                if (text != '') el.getParent().appendText(text);
                // détruit le node
                el.destroy();
            }
        }.bind(this));

        html_cleaned = html_cleaned.get('html');

        // supprime les saut de ligne et espace blanc
        html_cleaned = html_cleaned.replace(/(\r\n|\n|\r)/gm, '');
        html_cleaned = html_cleaned.replace(/\s+/g, ' ');
        html_cleaned = html_cleaned.replace(/>\s+</g, '><');

        // saut de ligne
        html_cleaned = html_cleaned.replace(/<\/(div|p|h1|h2|h3|h4|h5|h6|ol|ul|blockquote)>/ig, '</$1>\n')

        return html_cleaned;
    },

    toggleView: function () {
        if ($try(function () {
            if (this.doc.body) {
                return true;
            }
        }.bind(this))) {
            if (this.open) {
                this.toTextarea(true);
            } else {
                this.toEditor(true);
            }
            this.open = !this.open;
        }
    },

    toTextarea: function (view) {
        if (this.open) {
            this.TA.value = this.clean(this.doc.body.innerHTML);
            if (view) {
                this.TA.removeClass('hidden');
                this.IF.addClass('hidden');
                this.TB.addClass('disabled');
                this.TA.focus();
            }
        }
    },

    toEditor: function (view) {
        var val = this.TA.value.trim();
        this.doc.body.innerHTML = val;
        if (view) {
            this.TA.addClass('hidden');
            this.IF.removeClass('hidden');
            this.TB.removeClass('disabled');
        }
    },

    exec: function (b, v) {
        if (this.open) {
            this.IF.contentWindow.focus();
            but = this.options.buttonsCFG[b];
            var val = v || but[1];
            if (!v && but[2]) {
                $prompt(but[1], but[2], {
                    onClose: function (value) {
                        if (value && value != '')
                            this.doc.execCommand(but[0], false, value);
                        else
                            return;
                    }.bind(this),
                    value: but[3] || ''
                });
                //if(!(val=prompt(but[1],but[2]))){return;} // Prompt normal
            } else
                this.doc.execCommand(but[0], false, val);
        }
    },

    clean: function (html) {
        return html
            .replace(/<b>/g, '<strong>').replace(/<\/b>/g, '</strong>')
            .replace(/<i>/g, '<em>').replace(/<\/i>/g, '</em>')
            /*.replace(/(<img [^>]+[^\/])>/g,'$1 />')
		.replace(/(<br [^>]+[^\/])>/g,'$1 />')
		.replace(/(<hr [^>]+[^\/])>/g,'$1 />')
		.replace(/<(img|br|hr)>/g,'<$1 />')*/;
    }
});

/**
 * INDEV : Popup
 */
var MIB_Popup = new Class({
    Implements: [Events, Options],

    options: {
        zindex: 500,
        margin: 0.3, // largeur des marges de la popup en %
        container: null,
        className: null,

        overlayClassName: 'popup-overlay',
        overlayOpacity: 0.5,
        overlayShowDuration: 250
    },

    initialize: function (options) {
        this.setOptions(options);

        // Si on a un overlay
        if (this.options.overlayOpacity.toFloat() > 0) {
            this.overlay = new Element('div').inject(this.options.container || document.body);
            if (this.options.overlayClassName)
                this.overlay.addClass(this.options.overlayClassName);
            this.overlay.setStyles({
                'position': 'absolute',
                'top': 0,
                'left': 0,
                'opacity': 0,
                'visibility': 'hidden',
                'z-index': (this.options.zindex - 1)
            }).set('morph', {duration: this.options.overlayShowDuration});
        }

        // Création de la popup
        this.popup = new Element('div').inject(this.options.container || document.body);
        if (this.options.className)
            this.popup.addClass(this.options.className);
        this.popup.setStyles({
            'position': 'absolute',
            'visibility': 'hidden',
            'z-index': this.options.zindex
        }).set('morph');

        // Titre
        this.buttonClose = new Element('div', {'class': 'popup-close'}).inject(this.popup);

        // Titre
        this.title = new Element('h1', {'class': 'popup-title', 'html': '&nbsp;'}).inject(this.popup);

        // Contenu HTML
        this.html = new Element('div', {
            'class': 'page popup-html',
            'html': '1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>1<br>'
        }).inject(this.popup);

        // Resize l'overlay et la popup si la fenetre est redimentionnée
        window.addEvent('resize', this.resizePopup.bind(this));
    },

    /**
     * Redimentionne l'Overlay et la popup à la taille de la fenêtre active
     */
    resizePopup: function () {
        var size = window.getSize();
        this.popup.setStyles({
            'height': (size.y - size.y * this.options.margin / 2),
            'top': (size.y * this.options.margin / 4),
            'width': (size.x - size.x * this.options.margin),
            'margin-left': -(size.x - size.x * this.options.margin) / 2
        });

        this.html.setStyles({
            'height': this.popup.getSize().y - this.title.getSize().y - 1
        });

        if (this.overlay) {
            this.overlay.setStyles({
                'height': size.y,
                'width': size.x
            });
        }
    },

    /**
     * Affiche popup
     */
    show: function () {
        this.resizePopup();

        // Affiche l'overlay
        if (this.overlay) {
            this.overlay.morph({'opacity': this.options.overlayOpacity.toFloat()});
        }

        // Affiche la popup
        this.popup.setStyles({
            visibility: 'visible',
            opacity: 1
        });

        this.fireEvent('show', this);
    },

    /**
     * Ferme popup
     */
    close: function () {
        // Cache la popup
        this.popup.setStyles({
            visibility: 'hidden',
            opacity: 0
        });

        if (this.overlay)
            this.overlay.morph({'opacity': 0});

        this.fireEvent('hide', this);
    },

    /**
     * Définit le titre de la popup
     */
    setTitle: function (title) {
        if (!title)
            title = '&nbsp;';

        this.title.set('html', title);
    },

    /**
     * Définit le contenu de la popup
     */
    setHtml: function (html) {
        if (!html)
            html = '&nbsp;';

        this.html.set('html', html);
    }
});

/**
 * INDEV : Gestionnaire de DropGrid pour le DashBoard
 */
var MIB_DropGrid = new Class({

    Implements: [Options, Events],

    options: {
        IdMarker: 'MIB_db_marker',
        opacity: 0.9,
        top: 10,
        margin: 10,
        liveDuration: 5000,
        showDuration: 500,
        zindex: 999
    },

    initialize: function (options) {
        this.setOptions(options);
        this.growls = [];

        this.growl = new Element('div').inject(document.body);
        if (this.options.className)
            this.growl.addClass(this.options.className);
        this.growl.setStyles({position: 'absolute', top: this.options.top, visibility: 'hidden'});
    }
});

/*
	Basé sur Sortable.js patché, cette class permet d'ordonner des ligne de tableau
*/

var MIB_Table_Sortables = new Class({

    Implements: [Events, Options],

    options: {/*
		onSort: $empty(element),
		onStart: $empty(element),
		onComplete: $empty(element),*/
        snap: 5,
        handle: '.tc-position',
        constrain: false
    },

    initialize: function (lists, options) {
        this.setOptions(options);
        this.elements = [];
        this.lists = [];
        this.idle = true;
        this.new_order = false;

        this.addLists($$($(lists) || lists));
    },

    attach: function () {
        this.addLists(this.lists);
        return this;
    },

    detach: function () {
        this.lists = this.removeLists(this.lists);
        return this;
    },

    addItems: function () {
        Array.flatten(arguments).each(function (element) {
            this.elements.push(element);
            var start = element.retrieve('sortables:start', this.start.bindWithEvent(this, element));
            (this.options.handle ? element.getElement(this.options.handle) || element : element).addEvent('mousedown', start);
        }, this);
        return this;
    },

    addLists: function () {
        Array.flatten(arguments).each(function (list) {
            this.lists.push(list);
            this.addItems(list.getChildren());
        }, this);
        return this;
    },

    removeItems: function () {
        return $$(Array.flatten(arguments).map(function (element) {
            this.elements.erase(element);
            var start = element.retrieve('sortables:start');
            (this.options.handle ? element.getElement(this.options.handle) || element : element).removeEvent('mousedown', start);

            return element;
        }, this));
    },

    removeLists: function () {
        return $$(Array.flatten(arguments).map(function (list) {
            this.lists.erase(list);
            this.removeItems(list.getChildren());

            return list;
        }, this));
    },

    getDroppables: function () {
        var droppables = this.list.getChildren();
        if (!this.options.constrain) droppables = this.lists.concat(droppables).erase(this.list);
        return droppables.erase(this.element);
    },

    insert: function (dragging, element) {
        var where = 'inside';
        if (this.lists.contains(element)) {
            this.list = element;
            this.drag.droppables = this.getDroppables();
        } else {
            where = this.element.getAllPrevious().contains(element) ? 'before' : 'after';
        }
        this.element.inject(element, where);
        this.new_order = true;
    },

    start: function (event, element) {
        if (!this.idle) return;
        this.idle = false;
        this.element = element;
        this.list = element.getParent();

        this.element.addClass('order');
        this.fireEvent('start', [this.element]);

        this.drag = new Drag.Move((new Element('div').inject(document.body)), {
            snap: this.options.snap,
            container: this.options.constrain && this.element.getParent(),
            droppables: this.getDroppables(),
            onSnap: function () {
                event.stop();
            }.bind(this),
            onEnter: this.insert.bind(this),
            onCancel: this.reset.bind(this),
            onComplete: this.end.bind(this)
        });

        this.drag.start(event);
    },

    end: function () {
        this.drag.detach();
        this.reset();
    },

    reset: function () {
        this.idle = true;
        this.element.removeClass('order');
        if (this.new_order) {
            this.fireEvent('sort', [this.element]);
            this.new_order = false;
        }

        this.fireEvent('complete', this.element);
    },

    serialize: function () {
        var params = Array.link(arguments, {modifier: Function.type, index: $defined});
        var serial = this.lists.map(function (list) {
            return list.getChildren().map(params.modifier || function (element) {
                return element.get('id');
            }, this);
        }, this);

        var index = params.index;
        if (this.lists.length == 1) index = 0;
        return $chk(index) && index >= 0 && index < this.lists.length ? serial[index] : serial;
    }

});

function MibAppEngine() {

    var self = this;

    self.sendAjax = function (url, data, callBack) {
        var request = new XMLHttpRequest();


      //  data = data ? JSON.stringify(data) : null;

        request.open("POST", url, true);
        request.setRequestHeader("X-Requested-With", 'XMLHttpRequest');
        request.overrideMimeType("application/json");
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        // request.setRequestHeader("X-Requested-With",'XMLHttpRequest');
        if (typeof callBack === 'function') {
            request.onreadystatechange = function () {
                if (request.readyState === 4) {
                    if (request.status === 200) {
                        try {
                            var responseJSON = JSON.parse(request.responseText);
                            callBack(responseJSON);
                            return;
                        } catch (e) {
                        }
                        callBack(request.responseText);

                    } else {
                        // alert 'ERREUR'
                    }
                }
            };
        }
        request.send(data);
    }

    self.showLoader = function () {
        $('MIB_loader').setStyle('opacity', 0.7);
    };

    self.hideLoader = function () {
        $('MIB_loader').setStyles.delay(100, $('MIB_loader'), {'visibility': 'hidden'});
    };

    self.findParentBySelector = function (el, selector) {
        if (!Element.prototype.matches) {
            Element.prototype.matches = Element.prototype.msMatchesSelector ||
                Element.prototype.webkitMatchesSelector;
        }

        if (!Element.prototype.closest) {
            Element.prototype.closest = function (s) {
                var el = this;

                do {
                    if (el.matches(s)) return el;
                    el = el.parentElement || el.parentNode;
                } while (el !== null && el.nodeType === 1);
                return null;
            };
        }
        return el.closest(selector);
    }

}

var mibApp = new MibAppEngine();
