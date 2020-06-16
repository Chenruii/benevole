function BlogManageEngine() {

    var self = this;

    self.baseFields = null;
    self.partDefintions = {};
    self.additionalParts = [];
    self.formEngine = null;
    self.formId = null;
    self.formData = null;

    self.init = function () {
        self.formEngine = window.forms[self.formId] || null;
        if(! self.formEngine){
            console.error('Le gestion de formulaires window.forms');
            return;
        }
        self.baseFields = self.formEngine.fieldsElements;
        self.formData = self.formEngine.datas;
        var addPartsBtn = document.querySelectorAll('.BlogPart-add');
        if(addPartsBtn){
            addPartsBtn.forEach(function(el){
                el.addEventListener('click', self.onAddElement);
            })
        }
    };

    self.onAddElement = function () {
        var type = $(this).attr('data-type');
        if (type) {
            self.addElement(type);
        }
    };

    self.addElement = function (type) {
        console.log('addElement')
        // if (!self.partDefinitions || !self.partDefinitions[type]) {
        //     console.error('self.partDefinitions ne contient pas de définition pour :  ' + type);
        //     return;
        // }
        self.additionalParts.push(self.partDefinitions[type]);
        self.formEngine.refreshRender();
    };

    self.render = function () {




        self.formEngine.render();
    }


}