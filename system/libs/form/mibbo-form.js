


if(typeof window.console === "undefined" ){
    window.console = {
        log : function(){},
        error : function(){}
    }
}

function FormEngine(){

    var self = this ;
    self.url = null;
    self.defintion = null;
    self.fields = null;
    self.datas = {};
    self.key = null;
    self.tools = new ToolsEngine();
    self.sources =null;
    self.formElement  = null;
    self.submitElement  = null;
    self.fieldsElements  = null;
    self.sortables = {};
    self.changingFiles = false ;

    var app = window.mibApp;

    self.init=function(domId , key, definition, data){
        self.key = key||'-1';
        self.url = (definition && definition.url ) ? definition.url : '';
        self.fields =  (definition && definition.fields )  ?definition.fields : {};
        self.data = data || {};
        self.formElement = document.getElementById(domId);
        if (self.formElement){
            self.fieldsElements  = self.formElement.querySelectorAll('.Form-field[data-field]');
        }
        self.formElement.addEventListener('submit',self.submitForm);
    };

    self.submitForm= function(ev){
        for ( instance in CKEDITOR.instances )
        {
            CKEDITOR.instances[instance].updateElement();
        }
      //  const editorData = editor.getData();
    };

    self.setValues = function(datas){
        self.datas = datas || {};
    };

    self.render= function(){

        if (!self.fieldsElements || !self.fieldsElements.length)
            return ;

        for(var i =0 ; i<self.fieldsElements.length; i++){
            var type =  self.fieldsElements[i].getAttribute('data-type');
            var field =  self.fieldsElements[i].getAttribute('data-field');
            var format =  self.fieldsElements[i].getAttribute('data-format');
            self.renderField(self.fieldsElements[i], field,type,format);
        }
    };

    self.refreshRender = function(){
        self.deleteAllFields();
        if (!self.fields || !self.fields.length)
            return ;

        for( var i =0 ; i<self.fields.length; i++){
            var div = document.createElement('div');
            div.classList.append('Form-field');
            div.setAttribute('data-type',self.fields[i].type);
            div.setAttribute('data-field',self.fields[i].key);
            div.setAttribute('data-format',self.fields[i].format);
            self.formElement.append(div)
        }
        self.fieldsElements  = self.formElement.querySelectorAll('.Form-field[data-field]');
        self.render();
    };

    self.appendFieldsFromDef = function(){
        var f = self.formElement.querySelectorAll('.Form-field[data-field]');
        if(f) {
            f.forEach(function(el){
                el.parentNode.removeChild(el);
            });
        }
    };

    self.deleteAllFields = function(){
        var f = self.formElement.querySelectorAll('.Form-field[data-field]');
        if(f) {
            f.forEach(function(el){
                el.parentNode.removeChild(el);
            });
        }
    };

    self.renderField= function(el, field,type,format){
        var fieldDef   = self.fields[field] ||{};
        if(!fieldDef || !fieldDef.key)
            return ;

        fieldDef.controlattr = self.getFieldAttributes(fieldDef,fieldDef.validators ,type,format);
        fieldDef.controlcss = self.getFieldCss(fieldDef,fieldDef.validators ,type,format);
        fieldDef.fieldCss= '';
        if (fieldDef.validators && fieldDef.validators.mandatory){
            fieldDef.fieldCss += ' Form-field--mandatory ';
        }

        fieldDef.value =  (self.datas[field]) ? self.datas[field]: '';
        if(['image','gallery','file','filelist'].indexOf(fieldDef.type)!==-1){
            fieldDef.uploadUrl= self.url+'/'+self.datas['_id']+'/addFile/'+field+'?id='+self.datas['_id']||''+'&lang='+self.datas['_lang']||'';
        }

        var tpl = self.tools.getTemplate('mibboform-'+fieldDef.type);
        self.tools.hydrateElement(el, tpl,fieldDef);
        var fieldEl  = (el.children && el.children.length) ? el.children[0]: null;
        if (fieldEl){
            switch (fieldDef.type) {
                case 'select':
                    self.postRenderSelect(el.querySelector('select'),fieldDef);
                    break;
                case 'choicelist':
                    self.postRenderChoiceList(el.querySelector('.Form-control'),fieldDef,fieldDef.value)
                    break;
                case 'paragraph':
                    self.postRenderParagraph(el.querySelector('textarea'),fieldDef);
                    break;
                case 'gallery':
                case 'filelist':
                    self.postRenderGallery(el.querySelector('.Form-control'),fieldDef);
                    break;
                case 'image':
                case 'file':
                    self.postRenderImage (el.querySelector('.Form-control'),fieldDef);
                    break;
            }
            if(fieldDef.help){
                var span = document.createElement('span');
                span.innerText  = fieldDef.help;
                span.classList.add('Form-help');
                el.appendChild(span)
            }
            self.processBehaviors(fieldDef);
        }
    };

    self.getFieldAttributes= function(fieldDef, validators, type,format){
        var controlattr ='';

        if(validators && validators.mandatory)
            controlattr+= ' required ';

        if (type==='number' && format==='float'){
            controlattr+= ' step="0.01" ';
        }
        if (type==='number' && (validators  && ( validators.min ||  validators.max))){
            if (fieldDef.validators.min){
                controlattr+= ' min="'+validators.min+'" ';
            }
            if (fieldDef.validators.max){
                controlattr+= ' max="'+validators.max+'" ';
            }
        }
        return controlattr;
    };

    self.getFieldCss= function(fieldDef, validators, type,format){
        var controlcss ='';

        if(validators && validators.mandatory){
            controlcss+= ' Form-field--mandatory ';
        }
        return controlcss;
    };

    self.postRenderSelect= function(fieldEl , fieldDef){
        if (fieldDef){
            var value =  (self.datas[fieldDef.key]) ? self.datas[fieldDef.key]: '';
            if (fieldDef.source==='dynamic' || !fieldDef.source){
                if (self.sources && self.sources[fieldDef.key]){
                    self.tools.hydrateCombo(fieldEl,self.sources[fieldDef.key],value,' ');
                }
            }else {
                self.tools.hydrateCombo(fieldEl,fieldDef.source,value,' ');
            }
        }
    };

    self.postRenderParagraph= function(fieldEl , fieldDef){
        fieldEl.id = self.randomId();
        if(typeof self.customizeCkEditor ==='function'){
            self.customizeCkEditor.call(self);
        }

        CKEDITOR.replace(fieldEl.id );
    };

    self.postRenderChoiceList= function(fieldEl , fieldDef,value){
        var tplItem= self.tools.getTemplate('mibboform-choicelist--item');
        if (fieldDef&& fieldDef.source){
            var value =  (self.datas[fieldDef.key]) ? self.datas[fieldDef.key]: [];
            var source = [];
            if (fieldDef.source==='dynamic'){
                if (self.sources && self.sources[fieldDef.key]){
                    source = self.sources[fieldDef.key];
                }
            }else {
                source = fieldDef.source;
            }

            if (source && source.length){
                for(var i = 0 ; i < source.length; i++){
                    var item = {id:source[i].id,label:source[i].label,key:fieldDef.key,selected:value.indexOf(source[i].id)!==-1?'true':'false'}
                    self.tools.appendElement(fieldEl, tplItem,item);
                }
            }

        }
    };

    self.notify= function(message){
        var growl = new MIB_Growl({className: 'growl', top: 68});
        growl.alert(message, 'Alerte', {type : 'error'});

    }

    self.randomId = function() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < 15; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    };

    /* ---------- files IMAGES -----------------------------------*/

    self.postRenderImage= function(fieldEl , fieldDef){
        if(self.datas[fieldDef.key]){
            self.addImage(fieldDef.key,fieldDef.type,self.datas[fieldDef.key],true);
        }
        var addBtn =  fieldEl.querySelectorAll('.js-addFile');
        if(addBtn && addBtn.length ){
            for(var i=0; i < addBtn.length;i++){
                addBtn[i].removeEventListener('click',self.sendAddFileAction);
                addBtn[i].addEventListener('click',self.sendAddFileAction);
            }
        }

    };

    self.postRenderGallery= function(fieldEl , fieldDef){

        var i;

        if(self.datas[fieldDef.key] && self.datas[fieldDef.key].length){

            for( i= 0 ; i < self.datas[fieldDef.key].length ; i++){

                self.addImage(fieldDef.key,fieldDef.type,self.datas[fieldDef.key][i],false);
            }
        }
        var addBtn =  fieldEl.querySelectorAll('.js-addFile');
        if(addBtn && addBtn.length ){
            for( i=0; i < addBtn.length;i++){
                addBtn[i].removeEventListener('click',self.sendAddFileAction);
                addBtn[i].addEventListener('click',self.sendAddFileAction);
            }
        }

        self.makeSortableGallery(fieldDef.key,fieldEl);


    };

    self.makeSortableGallery= function(key, fieldEl){

        if( !self.sortables[key]){
            var complete = function(element){
                console.log('sortables complete fired', self.changingFiles)
                if( self.changingFiles)
                    return;

                console.log('sortables complete fired and launched');
                var url = element.getElement('.js-editFile').getAttribute('data-url');
                url = (url||'').replace('{action}','sortFile');
                var order =  self.sortables[key].serialize(function(element, index){
                    return { filename :  element.getProperty('data-filename'), index : index , uniqId :  element.getProperty('data-uniqid') };
                })

                app.sendAjax(url, "order="+JSON.stringify(order),function(response){
                    console.log('ok tri')
                });
            };
            var sortableOptions = {clone :true,opacity :.5,revert : true,onComplete : complete};
            self.sortables[key] = new Sortables(fieldEl.querySelector('.Form-images'),sortableOptions);
        }
    };

    self.sendRemoveFileAction = function(ev){
        ev.preventDefault();
        ev.stopImmediatePropagation();
        var image = this.getAttribute('data-image');
        var key = this.getAttribute('data-key');
        self.tools.showLoader();
        var url = self.url  + '/'+ this.getAttribute('data-url');
        app.sendAjax(url,null,function(response){
            self.tools.hideLoader();
            self.removeImage(key, image)
        });
    };

    self.showEditFileAction = function(ev){
        var image = this.getAttribute('data-image');
        var key = this.getAttribute('data-key');
        var uniqId = this.getAttribute('data-uniqid');

        var th = app.findParentBySelector(ev.target ,'.Form-thumb');

        var title =  th.querySelector('[data-bind="title"]').innerText;
        var legend =  th.querySelector('[data-bind="legend"]').innerText;
        var url = this.getAttribute('data-url');
        url = (url||'').replace('{action}','editFile');
        // console.log('showEditFileAction',image,key,uniqId,legend,title);

        var acpbox = new MIB_ACPbox({className: 'acp'});
        acpbox.url = url;
        //    console.log('showEditFileAction','url',url)
        acpbox.promptForm('Editer un fichier', "Editer un fichier", {
            type: 'hidden', // Sélecteur de fichier
            fields : {'title':"Titre", 'legend':'Légende'},
            fieldValues : {'title':title, 'legend':legend},
            onClose: function() {
                //     console.log('showEditFileAction','onClose')
                var formUpload = acpbox.form;

                var action = '{{tpl:MIBpage base_url}}/admin/json/' + acpbox.url;
                var sep = (action.indexOf('?')===-1) ? '?':'&';
                action += sep + formUpload.toQueryString();
                // console.log( action);
                app.sendAjax(action,{}, self.EditFileActionCallBack(image,key,uniqId));
                $('MIB_loader').setStyle('opacity',0.7);

            }.bind(this)
        });


    }

    self.EditFileActionCallBack = function(image,key){
        return  function(response){

            if(typeof response == 'string'){
                response = JSON.parse(response);
            }
            self.tools.hideLoader();
            if(response && response.filesUploaded  && response.filesUploaded.length){
                self.replaceImage(key, response.filesUploaded[0])
            }

            if(response.value){
                response.title = response.title || null;
                response.message = response.value;
                $growl(response.message, response.title, {
                    duration: (response.options.duration || null),
                    type: (response.options.type || null),
                    color: (response.options.color || null)
                });
            }


        }
    };

    self.sendAddFileAction = function(ev){
        ev.preventDefault();
        ev.stopImmediatePropagation();

        if(!self.datas['_id']){
            self.notify('Veuillez sauvez vos données avant d\'ajouter des fichiers');
            return ;
        }


        var image = this.getAttribute('data-image');
        var key = this.getAttribute('data-key');
        var url =  this.getAttribute('data-url');

        var fieldDef = self.fields[key] || null;
        if(!fieldDef){
            alert("erreur dans l'envoi du fichier");
            return;
        }


        var acpbox = new MIB_ACPbox({className: 'acp'});
        acpbox.url = url;
        acpbox.uploadForm('Ajouter un fichier', "Ajouter un fichier", {
            type: 'file', // Sélecteur de fichier
            fields : {'title':"Titre", 'legend':'Légende'},
            onClose: function(value) {
                if(value) { // Une valeur a été entrée
                    var formUpload = acpbox.form;
                    var inputUpload= acpbox.input;

                    var action = '{{tpl:MIBpage base_url}}/admin/json/' + acpbox.url;
                    var sep = (action.indexOf('?')===-1) ? '?':'&';
                    action += sep + formUpload.toQueryString();

                    inputUpload.set('name', key);
                    formUpload.set('action', action);
                    formUpload.set({
                        'enctype': 'multipart/form-data',
                        'method': 'post',
                        'target': 'MIB_upload_iframe'
                    });

                    // Prépare l'iframe
                    $('MIB_upload_iframe').removeEvents();
                    $('MIB_upload_iframe').addEvent('load', function() {
                        var response;

                        // On récupère les info envoyés dans l'iframe
                        if($('MIB_upload_iframe').contentDocument)
                            response = $('MIB_upload_iframe').contentDocument;
                        else if($('MIB_upload_iframe').contentWindow)
                            response = $('MIB_upload_iframe').contentWindow.document;
                        else
                            response = window.frames['MIB_upload_iframe'].document;

                        if(response && response.body.innerHTML)
                            response = JSON.decode(response.body.innerHTML);
                        else
                            response.error = true;

                        response.options = response.options || {};
                        MIB_Bo.jsontoaction(response);

                        $('MIB_loader').setStyles.delay(100, $('MIB_loader'), {'visibility':'hidden'});
                    }.bind(this));
                    formUpload.submit();
                    $('MIB_loader').setStyle('opacity',0.7);
                }
            }.bind(this)
        });
    };

    self.addImage = function(key,type,image,empty,added){
        var cont = self.formElement.querySelector(['.Form-field[data-field="'+key+'"] .Form-images']);
        var isList = type !=='image' && type !=='file';

        if(cont){
            type = (type ==='image' || type ==='gallery') ? 'image' : 'file';
            var tpl =   self.tools.getTemplate('mibboform-'+type+'-item');
            image.key = key ;
            if(empty)
                self.tools.hydrateElement(cont,tpl,image);
            else
                self.tools.appendElement(cont,tpl,image);
            var i=0;
            var removeBtn =  cont.querySelectorAll('.js-removeFile');
            if(removeBtn && removeBtn.length ){
                for( i=0; i < removeBtn.length;i++){
                    removeBtn[i].removeEventListener('click',self.sendRemoveFileAction);
                    removeBtn[i].addEventListener('click',self.sendRemoveFileAction);
                }
            }
            var editBtn =  cont.querySelectorAll('.js-editFile');
            if(editBtn && editBtn.length ){
                for( i=0; i < editBtn.length;i++){
                    editBtn[i].removeEventListener('click',self.showEditFileAction);
                    editBtn[i].addEventListener('click',self.showEditFileAction);
                }
            }

            if(isList && added && self.sortables[key]){
                var selector = '.Form-thumb:last-child';
                self.sortables[key].addItems(cont.getElement(selector));
            }

        }
    };

    self.removeImage = function(key,image){
        var el = self.formElement.querySelector(['.Form-field[data-field="'+key+'"] [data-filename="'+image+'"]']);
        el.parentNode.removeChild(el);
    };

    self.replaceImage = function(key,image){
        var el = self.formElement.querySelector(['.Form-field[data-field="'+key+'"] .Form-thumb[data-filename="'+image.name+'"]']);
        if(el){
            el.querySelector('[data-bind="legend"]').innerHTML = image.legend;
            el.querySelector('[data-bind="title"]').innerHTML = image.title;
        }

    };

    self.addFileCallBack= function(response){

        if(typeof response == 'string'){
            response = JSON.parse(response);
        }


        if(response && response.filesUploaded){

            var field = self.fields[response.fieldKey];
            if(!field)
                return ;
            var empty = field['type']  === 'image' || field['type']  === 'file' ;

            for(var i = 0 ; i < response.filesUploaded.length ; i++){
                // var data = self.clone(self.datas[fieldDef.key][i]);
                // data.index = i ;
                self.addImage(response.fieldKey,self.fields[response.fieldKey].type,   response.filesUploaded[i],empty,true)

            }
        }
    };

    self.removeFileCallBack= function(response){
        if(response && response.fileRemoved){
            self.changingFiles = true ;
            self.removeImage(response.fieldKey,  response.fileRemoved)
            self.changingFiles = false;
        }
    };

    self.saveDataCallBack= function(response){
        if(!self.datas['_id']){
            self.datas['_id'] = response.id;
        }
        var addFiles = self.formElement.querySelectorAll('.js-addFile');
        if(addFiles && addFiles.length){
            for(var i = 0 ; i <  addFiles.length ; i++){
                var url =  addFiles[i].getAttribute('data-url');
                url =  (url) ? url.replace('?id=undefined','?id='+response.id) : url;
                addFiles[i].setAttribute('data-url',url);
            }
        }
    };

    /* ----------------------- Behaviors ---------------------------------------*/

    self.processBehaviors = function(fieldDef){
        if(fieldDef && fieldDef.behaviors){
            for(var p in fieldDef.behaviors){
                var method = 'processBehavior'+p.capitalize();
                if( typeof self[method]==="function"){
                    self[method](fieldDef, fieldDef.behaviors[p]);
                }
            }
        }
    };

    self.processBehaviorSlug= function(fieldDef, params){
        if(!params || fieldDef.type!=='text')
            return;

        var sourceFieldKey =     params;
        var sourceField =  self.formElement.querySelector('#field-'+sourceFieldKey);
        if(!sourceField)
            return;

        var targetField =  self.formElement.querySelector('#field-'+fieldDef.key);
        if(!sourceField || !targetField)

        return;
        targetField.setAttribute('readonly','');
        sourceField.addEventListener('change',function(){
            var value = this.value;
            targetField.value = self.slugify(value);
        })
    };

    self.processBehaviorCopy= function(fieldDef, params){

        if(!params)
            return;

        var p =  params.split('|');
        var part= '';
        if(p.length==2){
            part= p[1];
        }
        params = p[0];


        var sourceFieldKey =     params;
        var sourceField =  self.formElement.querySelector('#field-'+sourceFieldKey);
        if(!sourceField)
            return;

        var targetField =  self.formElement.querySelector('#field-'+fieldDef.key);
        if(!sourceField || !targetField)
            return;

        targetField.setAttribute('readonly','');
        sourceField.addEventListener('change',function(){
            var value = this.value;
           switch (part) {
               case'year':
                    var v = value ? value.substr(0,4):null;
                    value = v;
                   break;


           }
            targetField.value = value;
        })
    };

    self.slugify= function(val){

        str = val.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;'";
        var to   = "aaaaeeeeiiiioooouuuunc-------";
        for (var i=0, l=from.length ; i<l ; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes

        return str;
    };
}

function ToolsEngine() {

    var self = this;

    self.customFunctions = {};

    self.templates = {};

    self.getTemplate = function (type) {
        if (self.templates[type]) {
            return self.templates[type];
        }

        var tplEl = document.getElementById('tpl-' + type);
        var tpl =  (tplEl) ? tplEl.innerHTML : '';
        self.templates[type] = tpl;
        return tpl;
    };

    self.format = function (value, format,property, args) {
        var date;
        switch (format) {
            case "classNameProperty":
                return  (value) ? prop : "";
            case "hiddenempty":
                return  (value) ? "" : "hidden";
            case "hiddennotempty":
                return  (value) ? "hidden" : "";
            case 'date':
                if (!value)
                    return '';
                date = new Date(value);
                if (!(date instanceof Date))
                    return '';
                return date.toLocaleDateString();
            case 'datetime':
                if (!value)
                    return '';
                date = new Date(value);
                if (!(date instanceof Date))
                    return '';
                return date.toLocaleDateTimeString();
            case 'float':
                return self.formatNumber(value, 2);
            case 'integer':
                return self.formatNumber(value,0);
            case 'money':
                return self.formatNumber(value, 2, '€');
            case 'percent':
                return self.formatNumber(value, 2,'%');
        }

    };

    self.formatNumber = function (num, precision, sign) {
        if (isNaN(num)) {
            num = parseFloat(num);
        }
        if (isNaN(num) || num === null) {
            num = 0;
        }
        var toFix = typeof precision === 'undefined' ? 2 : parseFloat(precision);
        var prefix = (sign) ? ("&nbsp;" + sign) : '';
        var str = num
            .toFixed(toFix) // always two decimal digits
            .replace(".", ",") // replace decimal point character with ,
            .replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ");

        return str + prefix;
        // use [ ] as a separator
    };

    self.resolveFunction = function (func, matches, funcReplacements, item) {
        var args, property, str, odvalue, prop;

        // gestion des fonction spécifiques  -----------
        if (self.customFunctions && self.customFunctions[func] && typeof self.customFunctions[func]==='function' ) {
            args = matches[2];
            args = args.split('|');
            var customval = self.customFunctions[func](item, args);
            odvalue = matches[0].replace('#', '\\\#').replace('|', '\\\|');
            funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: customval };
            return;
        }
        // gestion des fonctions standard ----
        switch (func) {
            case 'attr':
                args = matches[2];
                args = args.split('|');
                if (args.length === 3) {
                    var attr = args[0];
                    prop = args[1];
                    var compareValue = args[2];

                    var test = false ;
                    if(compareValue==='falsy'){
                        test  = !item || !item[prop] || item[prop]==='false';
                    }else{
                        test  = item && item[prop] && compareValue == item[prop]
                    }
                    // ReSharper disable once CoercedEqualsUsing
                    var newval = test ? attr : '';
                    if (attr === 'visible') {
                        // cas spécifique on utilise l'attribut hidden mais en inverse
                        // ReSharper disable once CoercedEqualsUsing
                        newval = test ? '' : 'hidden';
                    }
                    odvalue = matches[0].replace(new RegExp("\\\#", 'g'), '\\\#').replace(new RegExp("\\\|", 'g'), '\\\|');
                    funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: newval }
                }
                break;
            case 'default':
                args = matches[2];
                args = args.split('|');
                str = '';
                if (args.length === 2) {
                    prop = args[0];
                    var dflt = args[1];
                    str = item[prop] ? item[prop] : dflt;
                }
                odvalue = matches[0].replace('#', '\\\#').replace('|', '\\\|');
                funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: str };
                break;
            case 'ifEmpty':
                args = matches[2];
                args = args.split('|');
                str = '';
                if (args.length === 2) {
                    prop = args[0];
                    var defaultProp = args[1];
                    str = item[prop] ? item[prop] : ((item[defaultProp]) ? item[defaultProp] : '' ) ;
                }
                odvalue = matches[0].replace('#', '\\\#').replace('|', '\\\|');
                funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: str };
                break;
            case 'format':
                var newValue = '';
                args = matches[2];
                args = args.split('|');
                if (args.length === 2) {
                    property = args[0];
                    var format = args[1];
                    if (typeof  item[property] !== 'undefined') {
                        newValue = self.format(item[property], format, property, args);
                    }
                }
                odvalue = matches[0].replace('#', '\\\#').replace('|', '\\\|');
                funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: newValue };

                break;
            case 'generateId':
                var baseId = matches[2];
                if (typeof funcReplacements[baseId] === 'undefined') {
                    var odvalueG = matches[0].replace('#', '\\\#');
                    funcReplacements[baseId] = { oldValue: odvalueG, newValue: baseId + '-' + self.randomString(8) }
                }
                break;
            case 'truncate':
                args = matches[2];
                args = args.split('|');
                str = '';
                if (args.length === 2) {
                    property = args[0];
                    var truncateLength = args[1];

                    if (item && typeof item[property] === 'string') {
                        str = item[property];
                        if (str.length > truncateLength) {
                            str = str.substring(0, truncateLength - 3) + '...';
                        }
                    }
                }
                odvalue = matches[0].replace('#', '\\\#').replace('|', '\\\|');
                funcReplacements[self.randomString(8)] = { oldValue: odvalue, newValue: str };

                break;


        }
    };

    self.hydrateElement = function (cont , tpl, item, bindFunction) {

        tpl = self.hydrateText(tpl, item);
        cont.innerHTML= tpl;
        if (typeof bindFunction === 'function')
            bindFunction(cont);

    };

    self.appendElement = function (cont , tpl, item, bindFunction) {

        tpl = self.hydrateText(tpl, item);
        var div= document.createElement('div')
        div.innerHTML= tpl;
        for(var i = 0; i <div.children.length;i++ ){
            cont.appendChild(div.children[i]);
        }
        div = null;
        if (typeof bindFunction === 'function')
            bindFunction(cont);

    };

    self.hydrateText = function (tpl, item) {
        var funcReplacements = {};
        if (!tpl) {
            return '';
        }
        if (typeof item === 'object') {

            for (var prop in item) {
                if (!item.hasOwnProperty(prop))
                    continue;

                var val = (item[prop] === null) ? '' : item[prop];
                tpl = tpl.replace(new RegExp('{{' + prop + '}}', 'g'), val);
            }
            // tpl =  self.replaceText(tpl, item);
        }
        // on recherche les functions  pattern {{ func#arg }}
        var regex = new RegExp(/{{\s*([a-zA-Z0-9]*)\#([a-zA-Z0-9\|\/\._\- ]*)\s*}}/g);// https://regex101.com/r/yLyMij/4/
        var matches;
        while ((matches = regex.exec(tpl)) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (!matches || matches.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            if (matches && matches.length > 1) {
                var func = matches[1];
                self.resolveFunction(func, matches, funcReplacements, item);
            }
        }
        // remplacement des functions generatedId
        //var oldValues = [];
        for (var key in funcReplacements) {
            if (!funcReplacements.hasOwnProperty(key))
                continue;
            tpl = tpl.replace(new RegExp(funcReplacements[key].oldValue, 'g'), funcReplacements[key].newValue);
            //oldValues.push(funcReplacements[key].oldValue);
        }
        return tpl;
    };

    self.replaceText = function (text, item) {
        return self.hydrateText(text, item);
    };

    self.randomString=function(length) {
        return Math.round((Math.pow(36, length + 1) - Math.random() * Math.pow(36, length))).toString(36).slice(1);
    }

    self.hydrateCombo = function (comboElement, items, selectedId, labelBlank) {
        if (!comboElement)
            return;

        /// on supprime toutes les options déjà présente
        while (comboElement.firstChild) {
            comboElement.removeChild(comboElement.firstChild);
        }
        // on crée la méthode pour créer les options
        var createOption = function(value, label,selected){
            var opt = document.createElement('option');
            opt.value =value;
            opt.innerText = label;
            if (selected)
                opt.setAttribute('selected','');
            return opt;
        };

        if (typeof labelBlank !=='undefined') {
            comboElement.appendChild(createOption('',labelBlank));
        }

        if (items && items.length) {
            for (var i = 0; i < items.length; i++) {
                var selected =false ;
                if (selectedId && selectedId.push){ // cas de valeur multiple
                     selected = (selectedId &&  selectedId.indexOf(items[i].id) !== -1) ;
                }else {
                     selected = (selectedId && items[i].id == selectedId) ;
                }
                comboElement.appendChild(createOption(items[i].id,items[i].label,selected));
            }
        }
    }

    self.showLoader=function(){
        $('MIB_loader').setStyle('opacity',0.7);
    };

    self.hideLoader=function(){
        $('MIB_loader').setStyles.delay(100, $('MIB_loader'), {'visibility':'hidden'});
    };


    self.clone = function(obj){
        if (null == obj || "object" != typeof obj)
            return obj;

        var copy = obj.constructor();
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
        }
        return copy;



    }


}

