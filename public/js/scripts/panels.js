(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Tools = Views.Base.extend({

        el: "#tools",

        tmplList : $("#tools_album_list_tmpl"),

        events: {
            "click #copy-picture-button" : "handleList"
        },

        initialize: function() {


            this.config       = EP.config;
            this.grid         = AppViews.Grid;
            this.utils        = App.Utils;
            this.manager      = AppViews.Manager;
            this.messenger    = AppViews.Messenger;
            this.formAddAlbum = AppViews.FormAddAlbum;

            this.collection.on("reset", this.renderList, this);
            this.collection.on("add", this.renderList, this);
            this.collection.on("remove", this.renderList, this);
            this.collection.on("model_reset", this.onResetAlbum, this);

            this.selected = -1;
            this.copyUrl  = this.config.base + 'action/copy';
            this.moveUrl  = this.config.base + 'action/move';

            this.$list = this.$("#copy-picture-albums-list");

            this.grid.on("hide", function() {
                this.manager.set({"Tools":false});
            }, this);

            $(document).on("click", function() {
                this.$list.hide();
            }.bind(this));
        },
        hide: function() {
            this.$list.hide();
            this.$el.hide();
            return this;
        },
        renderList: function(albums) {

            var list = [];
            var template = _.template(this.tmplList.html());

            this.collection.each(function(model) {
                if (this.model !== undefined){
                    if (model.get('id') !== this.model.get('id')) {
                        list.push(model.toJSON());
                    }
                }
                else {
                    list.push(model.toJSON());
                }
            }.bind(this));

            this.$list.html(template({
                list : list
            }));

            $("#btn-new-album").off().on("click", this.openFormAlbum.bind(this));
            $("#btn-process-copy").off().on("click", this.processCopy.bind(this));

            this.buttons = $("a.btn-album-list", this.$list);
            this.buttons.on("click", this.setActive.bind(this));
        },
        onResetAlbum: function(album, albums) {

            this.model = album;
            this.renderList();
        },
        setActive: function(e) {

            e.preventDefault();
            e.stopPropagation();

            var $link, target = e.currentTarget;
            var id = $(target).attr("data-album-id");

            if (id !== undefined) {

                this.buttons.removeClass("selected");
                this.buttons.each(function(index, link) {

                    $link = $(link);
                    if ($link.attr("data-album-id") === id) {
                        $link.addClass("selected");
                        this.selected = id;
                    }
                }.bind(this));
            }
        },
        openFormAlbum: function(e) {

            e.stopPropagation();

            this.manager.set({"FormAddAlbum" : true});
        },
        handleList: function(e) {

            e.stopPropagation();

            if (this.$list.css("display") === "block") {
                this.$list.hide();
            }
            else {
                this.$list.show();
            }
        },
        processCopy: function(e) {

            e.stopPropagation();

            var ids = this.grid.getSelectedItemsPictureIds();

            if (ids.length > 0) {

                if (this.selected > 0) {

                    this.messenger.setMessage(true, "Copy...", 0);

                    $.ajax({
                        url : this.copyUrl,
                        type : 'POST',
                        data : {
                            ids : $.toJSON(ids),
                            album : this.selected
                        },
                        success : this.onCopy.bind(this)
                    });
                }
                else {
                    this.messenger.setMessage(false, 'Please select an album');
                }
            }
            else {
                this.messenger.setMessage(false, 'Please select a picture');
            }
        },
        onCopy: function(response) {

            this.messenger.setMessage(response.status, response.messages);

            var album;
            var returnAlbum = response.results.album;
            var returnPictures = response.results.pictures;

            //console.log(isNew);
            console.log(returnAlbum);
            console.log(returnPictures);

            album = this.collection.get(returnAlbum.id);

            album.pictures.reset();
            album.pictures.add(returnPictures);
            album.set("total_pictures", album.pictures.size());
        },
        checkItems: function() {

            var ids = this.grid.getSelectedItemsPictureIds();

            if (ids.length > 0) {
                this.manager.set({"Tools":true});
            }
            else {

                this.manager.set({"Tools":false});
            }
        }
    });

    Views.FormAddAlbum = Views.Base.extend({

        el : "#view-form-add-album",

        events: {
            "click #btn-close a"             : "hide",
            "click #add-album-submit-button" : "saveAlbum"
        },

        initialize: function() {

            this.config    = EP.config;
            this.manager   = AppViews.Manager;
            this.router    = App.Router;
            this.messenger = AppViews.Messenger;
            this.chosen    = AppViews.Chosen;
            this.utils     = App.Utils;

            this.$form     = this.$("#form-add-album");
            this.fieldName = this.$("#album-name");
            this.$overlay  = $("#overlay");

            this.$el.on("click", function(e){
                e.stopPropagation();
            });
        },
        render: function() {

            return this;
        },
        show: function() {

            this.$overlay.addClass("white-opacity-overlay").show();
            this.$el.show();
        },
        hide: function(e) {

            this.$overlay.removeClass("white-opacity-overlay").hide();
            this.$el.hide();
        },
        close: function(e) {

            e.stopPropagation();

            this.manager.set({"FormAddAlbum" : false});

        },
        saveAlbum: function(e) {

            e.preventDefault();

            var albumName = this.fieldName.val();

            if (albumName === "") {
                this.messenger.setMessage(false, "Vous devez fournir un nom");
                return;
            }

            var album = new Models.Album();
            album.save({
                name : albumName

            }, {
                success: this.onAlbumSaved.bind(this)
            });

            console.log(album);

        },
        onAlbumSaved: function(model, response) {

            //console.log(response);

            this.messenger.setMessage(response.status, response.messages);
            this.collection.add(response.results);
        },
        onLoad: function(response) {

            if (response.status) {

                this.html = response.results.html;
                this.loaded = true;
                this.loader.hide();
                this.render();
            }
        }
    });

    Views.FormEditAlbum = Views.Base.extend({

        el: "#grid-footer",

        events: {
            "blur #name-album"         : "onBlur",
            "focus #url-album"         : "selectUrl",
            "click #btn-private-album" : "togglePrivacy",
            "click #btn-public-album"  : "togglePrivacy",
            "click #btn-delete-album"  : "confirmBeforeDelete"
        },

        initialize: function() {

            // TODO désactiver la touche entrer lorsque les champs du formulaire ont le focus

            _.extend(this, Backbone.Events);

            this.duration = 400;
            this.fx = "swing";
            this.opened = false;

            this.manager   = AppViews.Manager;
            this.config    = EP.config;
            this.router    = App.Router;
            this.messenger = AppViews.Messenger;
            this.grid      = AppViews.Grid;
            this.chosen    = AppViews.Chosen;
            this.utils     = App.Utils;

            this.collection.on("model_reset", this.onResetAlbum, this);

            this.$fieldName = this.$("#name-album");
            this.$fieldUrl  = this.$("#url-album");
            this.$btn       = this.$("#btn-handle-edit-album");
            this.$panel     = this.$("#form-edit-album-container");
            this.$privacy   = this.$("#album-privacy");

            this.grid.on("render", function() {
                this.manager.set({"FormEditAlbum":true});
            }, this);
            this.grid.on("hide", function() {
                this.manager.set({"FormEditAlbum":false});
            }, this);

            this.setPosition();

            this.$el.css("visibility", "visible");
            this.$el.on("click", function(e){
                e.stopPropagation();
            });

            $(window).on("resize", this.onResize.bind(this));
        },
        render: function() {

            //console.log(this.config);

            var url = this.config.protocol +"//"+ this.config.host + this.config.base + "a/" + this.model.get("key_url");
            var privacy = parseInt(this.model.get("privacy"), 10);
            privacy = (!isNaN(privacy) && privacy === 1) ? "private" : "public";

            //console.log(this.model.toJSON());

            this.$privacy.removeClass().addClass(privacy);
            this.$fieldName.val(this.model.get("name"));
            this.$fieldUrl.val(url);

            return this;
        },
        show: function() {
            this.bindEvents();
            this.$el.show();
        },
        hide: function() {
            this.unbindEvents();
            this.$el.hide();
        },
        setPosition: function() {

            var screenWidth  = $(window).width();
            var screenHeight = $(window).height();
            var panelWidth   = this.$panel.outerWidth(true);
            var panelHeight  = this.$el.outerHeight(true);

            this.poxY = screenHeight - panelHeight;
            this.$el.css("top", this.poxY + "px");
            this.$el.css("left", "0px");
        },
        onResize: function(e) {
            this.setPosition();
        },
        onResetAlbum: function(album, albums) {

            this.model = album;
            this.render();
        },
        onBlur: function(e) {

            var $target = $(e.currentTarget);
            var name = $target.val();

            this.model.set("name", name);
        },
        selectUrl: function(e) {

            this.$fieldUrl.select();
        },
        togglePrivacy: function(e) {

            e.stopPropagation();

            var privacy = parseInt(this.model.get("privacy"), 10);
            privacy = (isNaN(privacy) || privacy === 1) ? "0" : "1";

            this.model.set("privacy", privacy);
        },
        onChange: function(model, options) {

            console.log("on change into form");

            this.model.save({}, {
                wait : true,
                success: this.onSaveAlbum.bind(this)
            });
        },
        onSaveAlbum: function(model, response) {

            model.update();

            this.messenger.setMessage(response.status, response.messages);
            this.render();
        },
        confirmBeforeDelete: function(e) {

            e.stopPropagation();

            this.chosen.open({
                message : "Do you want to delete this album ?",
                leftValue : "I'm sure",
                rightValue : "No",
                onLeft   : this.deleteAlbum.bind(this)
            });
        },
        deleteAlbum: function() {

            this.messenger.setMessage(true, 'Process delete album...');

            this.model.destroy({
                wait: true, // wait : true - wait server response  before remove model from collection
                success: this.onDeleteAlbum.bind(this)
            });
        },
        onDeleteAlbum: function(model, response) {

            this.messenger.setMessage(response.status, response.messages);
            this.router.navigate("/", {
                trigger : true
            });
        },
        bindEvents: function() {
            this.delegateEvents();
            if (this.model !== undefined) {
                this.model.rebind("change:name", "onChange", this);
                this.model.rebind("change:privacy", "onChange", this);
            }
        },
        unbindEvents: function() {
            this.delegateEvents();
            if (this.model !== undefined) {
                this.model.off("change:name", this.onChange);
                this.model.off("change:privacy", this.onChange);
            }
        }

    });


    /* === | ==========================================================================
    Edit  picture
    =================================================================================== */

    Views.FormEditPicture = Views.Base.extend({

        el: "#view-edit-picture-container",

        formTmpl : $("#form_edit_picture_tmpl"),

        infoTmpl : $("#info_picture_tmpl"),

        thumbTmpl : $("#thumb_picture_tmpl"),

        events : {
            "focus #url" : "selectUrl",
            "click #btn-close a" : "close",
            "click #btn-set-cover-album" : "setCover"
        },

        initialize: function() {

            // TODO désactiver la touche entrer lorsque les champs du formulaire ont le focus

            this.config  = EP.config;
            this.router  = App.Router;
            this.manager = AppViews.Manager;
            this.chosen  = AppViews.Chosen;
            this.utils   = App.Utils;

            this.$overlay        = $("#overlay");
            this.$formContainer  = this.$("#form-edit-picture-container");
            this.$infoContainer  = this.$("#informations-picture-container");
            this.$thumbContainer = this.$("#thumbnail-picture-container");

            this.collection.on("model_reset", this.onResetAlbum, this);
        },
        render: function() {

            var formTemplate  = _.template(this.formTmpl.html());
            var infoTemplate  = _.template(this.infoTmpl.html());
            var thumbTemplate = _.template(this.thumbTmpl.html());
            var json = this.model.toJSON();

            json.url = this.config.protocol +"//"+ this.config.host + this.config.base + "p/" + this.model.get("key_url");
            json.thumb = this.config.base + "image/grid/" + this.model.get("key_url");

            this.$formContainer.html(formTemplate(json));
            this.$infoContainer.html(infoTemplate(json));
            this.$thumbContainer.html(thumbTemplate(json));

            this.$fieldUrl = this.$("#url");
            this.$form = this.$("#form-edit-picture");
            this.coverContainer = $("#container-set-cover-album");

            this.manager.set({"FormEditPicture":true});

            if (this.model.get('key_url') === this.album.get('cover')) {
                this.coverContainer.html("Album cover");
            }
            return this;
        },
        onResetAlbum: function(model, collection) {
            this.album = model;
        },
        setCover: function() {
            this.album.set("cover", this.model.get('key_url'));
            this.coverContainer.html("Album cover");
        },
        selectUrl: function() {
            this.$fieldUrl.select();
        },
        show: function() {
            this.$overlay.addClass("white-opacity-overlay").show();
            this.$el.show();
        },
        close: function(e) {
            e.stopPropagation();

            this.manager.set({"FormEditPicture":false});
        },
        hide: function() {

            var elements = this.$form.serializeArray();
            var model = this.utils.arrayToObject(elements);

            if (_.isArray(model.privacy)) {
                model.privacy = _.max(model.privacy) + ""; // force type
            }
            if (_.isArray(model.visible)) {
                model.visible = _.max(model.visible) + ""; // force type
            }

            //console.log(model);

            if (model.url !== undefined) {
                delete model.url;
            }

            this.model.set(model);

            this.$overlay.removeClass("white-opacity-overlay").hide();
            this.$el.hide();
        }
    });


    /* === | ==========================================================================
    Upload & Import Picture
    =================================================================================== */
    Views.FormAddPicture = Views.Base.extend({

        el : "#form-add-container",

        events: {
            "click #btn-close a"                : "close",
            "click nav.panels-selector ul li a" : "toggleForms"
        },

        initialize: function() {

            this.loaded   = false;
            this.config   = EP.config;
            this.manager  = AppViews.Manager;
            this.$loader  = $("#loader-overlay");
            this.$overlay = $("#overlay");

            this.Uploader = new Views.Uploader({collection: this.collection});
            this.Importer = new Views.Importer({collection: this.collection});

            $("#add-picture-button").bind("click", this.open.bind(this));
        },
        render: function() {

            this.$el.html(this.html);
            this.$el.show();

            this.Uploader.render();
            this.Importer.render();

            this.$uploadTitle  = this.$("#panel-title-upload");
            this.$importTitle  = this.$("#panel-title-import");
            this.$importButton = this.$("#form-import-button");
            this.$importPanel  = this.$("#form-import-panel");
            this.$uploadButton = this.$("#form-upload-button");
            this.$uploadPanel  = this.$("#form-upload-panel");

            return this;
        },
        open: function(e) {
            e.stopPropagation();
            this.manager.set({"FormAddPicture":true});
        },
        close: function(e) {
            e.stopPropagation();
            this.manager.set({"FormAddPicture":false});
        },
        show: function() {

            if (!this.loaded) {
                this.load();
            }
            else {
                this.$overlay.addClass("white-opacity-overlay").show();
                this.$el.show();
            }
        },
        hide: function() {

            this.$loader.hide();
            this.$overlay.removeClass("white-opacity-overlay").hide();
            this.$el.hide();
        },
        load: function() {

            this.url = this.config.base + 'form/add.Picture';
            this.$overlay.addClass("white-opacity-overlay").show();
            this.$loader.show();

            $.ajax({
                url  : this.url,
                type : 'GET',
                data : {},
                success : this.onLoad.bind(this)
            });
        },
        onLoad: function(response) {

            if (response.status) {

                this.html = response.results.html;
                this.loaded = true;
                this.$loader.hide();
                this.render();
            }
        },
        toggleForms: function(e) {

            e.stopPropagation();

            var target = e.currentTarget,
                id = target.id;

            if (id.indexOf("upload") !== -1) {

                this.$uploadTitle.addClass("active");
                this.$importTitle.removeClass("active");
                this.$importButton.removeClass("active");
                this.$uploadButton.addClass("active");
                this.$importPanel.removeClass("active");
                this.$uploadPanel.addClass("active");
            }
            else if (id.indexOf("import") !== -1) {

                this.$importTitle.addClass("active");
                this.$uploadTitle.removeClass("active");

                this.$importButton.addClass("active");
                this.$uploadButton.removeClass("active");
                this.$importPanel.addClass("active");
                this.$uploadPanel.removeClass("active");
            }
        }
    });

    Views.Uploader = Views.Base.extend({

        el : "#form-add-container",

        albumTmpl : $("#form_add_album_list_tmpl"),

        events: {
            "click #form-upload [type=radio]"  : "toggleTypes",
            "click #form-upload-submit-button" : "uploadFile",
            "click #btn-add-upload-field"      : "addField"
        },

        initialize: function() {

            this.config    = EP.config;
            this.router    = App.Router;
            this.manager   = AppViews.Manager;
            this.messenger = AppViews.Messenger;
            this.utils     = App.Utils;

            this.totalFields = 1;

            this.collection.on("add", this.onAddAlbum, this);
            this.collection.on("remove", this.onRemoveAlbum, this);
            this.collection.on("model_reset", this.onResetAlbum, this);
            this.collection.on("model_update", this.onUpdateAlbum, this);
        },
        render: function() {

            this.$upload_id    = this.$("#upload-album-id-container");
            this.$upload_name  = this.$("#upload-album-name-container");
            this.$formUpload   = this.$("#form-upload");
            this.$submitUpload = this.$("#form-import-submit-button");
            this.$albumList    = this.$formUpload.find("#upload_album_id");
            this.$fieldsList   = this.$("#form-upload-fields-list");
            this.$iframe       = this.$("#form-add-iframe");
            this.fieldTmpl     = $("#field_upload_tmpl");

            this.$formUpload.find("[type=radio]").first().attr("checked", true);
            this.renderAlbumList();

            this.$iframe.on("load", this.onUpload.bind(this));

            return this;
        },
        renderAlbumList: function() {

            if (this.$albumList !== undefined) {

                var template = _.template(this.albumTmpl.html());
                var json = this.collection.toJSON();

                this.$albumList.html(template({list : json}));
                this.selectCurrentAlbum();
            }
        },
        onAddAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onRemoveAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onUpdateAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onResetAlbum: function(album, albums) {

            this.model = album;

            this.model.pictures.rebind("reset", "onResetPictures", this);
            this.model.pictures.rebind("remove", "onRemovePicture", this);
            this.model.pictures.rebind("add_all", "onAddAllPictures", this);

            this.selectCurrentAlbum();
        },
        onResetPictures: function(pictures) {
            this.pictures = pictures;
        },
        onAddAllPictures: function(pictures) {
            this.pictures = pictures;
        },
        onAddPicture: function(picture, pictures) {
            this.pictures = pictures;
        },
        onRemovePicture: function(picture, pictures) {
            this.pictures = pictures;
        },
        selectCurrentAlbum: function() {

            if (this.$formUpload !== undefined) {

                var options = this.$albumList.find("option");

                _.map(options, function(option, key) {
                    if (this.model !== undefined && option.value === this.model.get('id')) {
                        this.$albumList[0].selectedIndex = key;
                    }
                }, this);
            }
        },
        addField: function() {

            if (this.totalFields >= 5) {
                return;
            }

            this.totalFields++;

            var template = _.template(this.fieldTmpl.html());
            this.$fieldsList.append(template({
                num : this.totalFields
            }));

            var $links = $("#btn-remove-upload-file-" + this.totalFields);
            $links.on("click", this.removeField.bind(this));

            this.refreshFieldsList();
        },
        removeField: function(e) {

            this.totalFields--;

            var $target    = $(e.currentTarget);
            var num        = $target.attr("data-field-num");
            var $links     = $("#btn-remove-upload-file-" + num);
            var $container = $("#field-upload-container-" + num);

            $links.off("click", this.removeField.bind(this));
            $container.remove();

            this.refreshFieldsList();
        },
        refreshFieldsList: function() {

            var $list = this.$(".upload-element-container");

            if ($list.length !== this.totalFields) {
                throw "Error in total fields";
            }

            var num, container, label, inputFile, inputHidden, link;
            $list.each(function(index, elem) {

                num = index + 1;

                container   = $(elem);
                label       = $("label", container);
                inputFile   = $("input[type=file]", container);
                inputHidden = $("input[type=hidden]", container);
                link        = $("a", container);

                container.attr("id", "field-upload-container-" + num);
                label.attr("for", "upload-file-" + num);
                inputFile.attr("id", "upload-file-" + num).attr("name", "upload_file_" + num);
                inputHidden.attr("id", "MAX_FILE_SIZE-" + num).attr("name", "MAX_FILE_SIZE_" + num);
                link.attr("data-field-num", num).attr("id", "btn-remove-upload-file-" + num);

            });
        },
        toggleTypes: function(e) {

            e.stopPropagation();

            var target = e.currentTarget,
                value = parseInt(target.value, 10);

            if (value === 1) {
                this.$upload_id.hide();
                this.$upload_name.show();
            }
            else {
                this.$upload_id.show();
                this.$upload_name.hide();
            }
        },
        uploadFile: function(e) {

            e.preventDefault();

            this.$formUpload.submit();
        },
        onUpload: function(e) {

            var iframe = e.currentTarget;
            var content = this.getFrameContents(iframe);

            if (content === "") {
                return;
            }

            var album;
            var response       = JSON.parse(content);
            var isNew          = response.results.isNew;
            var returnAlbum    = response.results.album;
            var returnPictures = response.results.pictures;

            this.messenger.setMessage(response.status, response.messages);
            /*console.log(isNew);
            console.log(returnAlbum);
            console.log(returnPictures);*/


            if (isNew) {

                album = new Models.Album();
                album.set(returnAlbum);
                album.pictures.add(returnPictures);
                album.set("total_pictures", album.pictures.size());

                this.collection.add(album);
                this.router.navigate("grid/album/" + album.get('id'), {trigger: true});
            }
            else {

                album = this.collection.get(returnAlbum.id);

                album.pictures.reset();
                album.pictures.add(returnPictures);
                album.set("total_pictures", album.pictures.size());

                if (this.manager.get("Home") || this.model === undefined || album.get('id') !== this.model.get('id')) {
                    this.router.navigate("grid/album/" + album.get('id'), {trigger: true});
                }
                else {
                    this.model.pictures.addAll();
                }
            }
        },
        getFrameContents: function getFrameContents(iframe) {

            var body, text;
            if (iframe.contentDocument) {
                body = iframe.contentDocument.getElementsByTagName('body')[0];
            }
            else if (iframe.contentWindow) {
                body = iframe.contentWindow.document.getElementsByTagName('body')[0];
            }

            if (body.textContent) {
                text = body.textContent;
            }
            else {
                text = $(body).text();
            }

            return text;
        }
    });


    Views.Importer = Views.Base.extend({

        el : "#form-add-container",

        events: {
            "click #form-import [type=radio]"  : "toggleTypes",
            "click #form-import-submit-button" : "importFile",
            "click #btn-reload-file-list"      : "reloadFileList"
        },

        albumTmpl : $("#form_add_album_list_tmpl"),

        fileTmpl : $("#form_add_file_list_tmpl"),

        initialize: function() {

            this.config    = EP.config;
            this.router    = App.Router;
            this.manager   = AppViews.Manager;
            this.messenger = AppViews.Messenger;
            this.utils     = App.Utils;

            this.files = new Collections.Files();
            this.files.on("reset", this.renderFilesList, this);

            this.collection.on("add", this.onAddAlbum, this);
            this.collection.on("remove", this.onRemoveAlbum, this);
            this.collection.on("model_reset", this.onResetAlbum, this);
            this.collection.on("model_update", this.onUpdateAlbum, this);
        },
        render: function() {

            this.$filelist     = this.$("#import-album-file-list");
            this.$import_id    = this.$("#import-album-id-container");
            this.$import_name  = this.$("#import-album-name-container");
            this.$formImport   = this.$("#form-import");
            this.$submitImport = this.$("#form-import-submit-button");
            this.$albumList    = this.$formImport.find("#import_album_id");

            this.$formImport.find("[type=radio]").first().attr("checked", true);
            this.renderAlbumList();
            this.files.fetch();

            return this;
        },
        reloadFileList: function(e) {

            e.stopPropagation();

            this.$filelist.html("Loading...");
            this.files.fetch();
        },
        renderFilesList: function() {

            var template = _.template(this.fileTmpl.html());
            var json = this.files.toJSON();

            this.$filelist.html(template({list : json}));
        },
        renderAlbumList: function() {

            if (this.$albumList !== undefined) {

                console.log("[Importer] -> refresh album list - collection size : "+this.collection.size());

                var template = _.template(this.albumTmpl.html());
                var json = this.collection.toJSON();

                this.$albumList.html(template({list : json}));
                this.selectCurrentAlbum();
            }
        },
        onAddAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onRemoveAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onUpdateAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onResetAlbum: function(album, albums) {

            this.model = album;

            this.model.pictures.rebind("reset", "onResetPictures", this);
            //this.model.pictures.rebind("add", "onAddPicture", this);
            this.model.pictures.rebind("remove", "onRemovePicture", this);
            this.model.pictures.rebind("add_all", "onAddAllPictures", this);

            this.selectCurrentAlbum();
        },
        onResetPictures: function(pictures) {
            this.pictures = pictures;
        },
        onAddAllPictures: function(pictures) {
            this.pictures = pictures;
        },
        onAddPicture: function(picture, pictures) {
            this.pictures = pictures;
        },
        onRemovePicture: function(picture, pictures) {
            this.pictures = pictures;
        },
        selectCurrentAlbum: function() {

            if (this.$formImport !== undefined) {

                var options = this.$albumList.find("option");

                _.map(options, function(option, key) {
                    if (this.model !== undefined && option.value === this.model.get('id')) {
                        this.$albumList[0].selectedIndex = key;
                    }
                }, this);
            }
        },
        toggleTypes: function(e) {

            e.stopPropagation();

            var target = e.currentTarget,
                value = parseInt(target.value, 10);

            if (value === 1) {
                this.$import_id.hide();
                this.$import_name.show();
            }
            else {
                this.$import_id.show();
                this.$import_name.hide();
            }
        },
        importFile: function(e) {

            e.preventDefault();

            var files = [],
                file,
                ckeckboxes = $("input:checked", this.$filelist);

            ckeckboxes.each(function(index, checkbox) {
                file = this.getFile(checkbox.id);
                if (file !== undefined) {
                    files.push(file);
                }
            }.bind(this));

            var object = {};
            var elements = this.$formImport.serializeArray();

            _.map(elements, function(elem) {
                if (object[elem.name] !== undefined) {
                    if (!object[elem.name].push) {
                        object[elem.name] = [object[elem.name]];
                    }
                    object[elem.name].push(elem.value || '');
                }
                else {
                    object[elem.name] = elem.value || '';
                }
            });

            if (files.length === 0) {

                this.messenger.setMessage(false, 'Please select file to import');
                return;
            }

            if (object.import_album_type == 0) {

                if (object.import_album_id == 0) {
                    this.messenger.setMessage(false, 'Please choose an album');
                    return;
                }
            }

            files = $.toJSON(files);
            //object = $.toJSON(object);

            this.messenger.setMessage(true, 'Import in progress', 0);

            //this.$submitImport.attr('disabled', 'disabled').val("Sending");

            $.ajax({
                url : this.config.base + "action/import/",
                type : 'POST',
                data : {
                    files : files,
                    import_album_id   : object.import_album_id,
                    import_album_name : object.import_album_name,
                    import_album_type : object.import_album_type
                    /*datas : object*/
                },
                success : this.onImport.bind(this)
            });

        },
        onImport: function(response) {

            this.messenger.setMessage(response.status, response.messages);

            var album;
            var isNew          = response.results.isNew;
            var returnAlbum    = response.results.album;
            var returnPictures = response.results.pictures;

            console.log(isNew);
            console.log(returnAlbum);
            console.log(returnPictures);

            this.files.fetch();

            if (isNew) {

                album = new Models.Album();
                album.set(returnAlbum);
                album.pictures.add(returnPictures);
                album.set({"total_pictures": album.pictures.size()}, {silent:true});

                //console.log(album.toJSON());

                this.collection.add(album);
                this.router.navigate("grid/album/" + album.get('id'), {trigger: true});
            }
            else {

                album = this.collection.get(returnAlbum.id);

                album.pictures.reset();
                album.pictures.add(returnPictures);
                album.set({"total_pictures": album.pictures.size()}, {silent:true});

                //console.log(album.toJSON());
                //console.log(this.manager.get("Grid"));

                if (this.manager.get("Home") || this.model === undefined || album.get('id') !== this.model.get('id')) {
                    this.router.navigate("grid/album/" + album.get('id'), {trigger: true});
                }
                else {
                    this.model.pictures.addAll();
                }
            }
        },
        getFile: function(id) {
            var file = this.files.find(function(file) {
                return (file.get('id') == id);
            });
            return file.toJSON();
        }
    });

    Views.Social = Views.Base.extend({

        el: "#social",

        events: {
            "click #btn-share-twitter"    : "openTwitter",
            "click #btn-share-facebook"   : "openFacebook",
            "click #btn-publish-twitter"  : "tweetThis",
            "click #btn-publish-facebook" : "shareThis"
        },

        initialize: function() {

            this.config   = EP.config
            this.manager   = AppViews.Manager;
            this.messenger = AppViews.Messenger;

            this.$panel       = this.$("#social-panel");
            this.$container   = this.$("#social-form-container");
            this.$btnTwitter  = this.$("#btn-share-twitter");
            this.$imgTwitter  = this.$("#twitter-image");
            this.$btnFacebook = this.$("#btn-share-facebook");
            this.$imgFacebook = this.$("#facebook-image");
            this.$field       = this.$("#field-social-form");

            this.twitter  = false;
            this.facebook = false;

            if (!this.$btnTwitter.hasClass("disable")) {
                this.twitter = true;
            }
            if (!this.$btnFacebook.hasClass("disable")) {
                this.facebook = true;
            }

            $(document).on("click", function() {
                this.$panel.hide();
            }.bind(this));

            this.collection.on("model_reset", this.onResetAlbum, this);

            console.log(this.twitter);
            console.log(this.facebook);

            //this.loadTwitterInfos();

        },
        loadTwitterInfos: function() {

            $.ajax({
                url : this.config.base + "social/twitter/get",
                type : 'GET',
                data : {},
                success : this.onLoadInformations.bind(this)
            });

        },
        onLoadInformations: function(response) {

            if (response.status) {

                this.$btnTwitter.removeClass("disable");
                this.twitter = response.results;

                if (this.twitter.profile_image_url) {
                    this.$imgTwitter.html("<a href='"+ this.twitter.profil_url +"' target='_blank' ><img src='"+ this.twitter.profile_image_url +"' width='26px' height='26px' ></a>");
                }
            }

            console.log(response);

        },
        updatePanel: function() {



        },
        render: function() {

            var url   = this.config.protocol + "//" + this.config.host + this.config.base + "a/" + this.model.get('key_url');
            var tweet = this.model.get('name') + " " +url;

            this.$field.val(tweet);
        },
        onChangeName: function() {
            this.render();
        },
        onResetAlbum: function(model) {
            this.model = model;
            this.model.rebind("change:name", "onChangeName", this);
            this.render();
        },
        openTwitter: function(e) {

            if (!this.twitter) {
                return;
            }

            var target = e.currentTarget;
            this.$target = $(target);

            this.setPosition();

            this.$container.removeClass().addClass("twitter");
            this.$panel.show();
        },
        openFacebook: function(e) {

            if (!this.facebook) {
            //	return;
            }

            var target = e.currentTarget;
            this.$target = $(target);

            this.setPosition();

            this.$container.removeClass().addClass("facebook");
            this.$panel.show();
        },
        setPosition: function() {

            var wPanel  = this.$panel.outerWidth(true);
            var hPanel  = this.$panel.outerHeight(true);
            var offset  = this.$target.position();
            var wTarget = this.$target.outerWidth(true);
            var hTarget = this.$target.outerHeight(true);

            var x = (offset.left + (wTarget / 2)) - (wPanel / 2);
            var y = offset.top - hPanel - 8;

            /*console.log(offset);
            console.log(wPanel+" - "+hPanel);
            console.log(wTarget+" - "+hTarget);
            console.log(x+" - "+y);*/

            this.$panel.css("top", y + "px").css("left", x + "px");

        },
        tweetThis: function() {

            var tweet = this.$field.val();
            var privacy = parseInt(this.model.get('privacy'), 10);
            //console.log(tweet);

            if (tweet === "") {
                this.messenger.setMessage(false, "Your tweet is empty")
                return
            }
            if (privacy === 1) {
                this.messenger.setMessage(false, "This album is marked as private")
                return
            }

            $.ajax({
                url : this.config.base + "social/twitter/update",
                type : 'POST',
                data : {
                    tweet : tweet
                },
                success : this.onSocial.bind(this)
            });

        },
        shareThis: function() {


        },
        onSocial: function(response) {

            if (response.status) {
                this.$panel.hide();
            }

            this.messenger.setMessage(response.status, response.messages)

            console.log(arguments);
        }
    });

    Views.FormSearch = Views.Base.extend({

        el: "#view-form-search",

        formTmpl: $("#form_search_tmpl"),

        albumTmpl: $("#form_search_album_list_tmpl"),

        events: {
            "click #btn-search" : "search",
            "click #btn-close"  : "close"
        },
        initialize: function() {

            this.$overlay = $("#overlay");
            this.$container = this.$("#form-search-container");

            this.config = EP.config;
            this.manager = AppViews.Manager;
            this.router = App.Router;

            this.collection.on("add", this.onAddAlbum, this);
            this.collection.on("remove", this.onRemoveAlbum, this);
            this.collection.on("reset", this.onResetAlbums, this);
            this.collection.on("model_reset", this.onResetAlbum, this);
            this.collection.on("model_update", this.onUpdateAlbum, this);

            this.render();
        },
        render: function() {

            var template = _.template(this.formTmpl.html());

            this.params = {};

            this.$container.html(template(this.params));

            this.$form         = this.$("#form-search");
            this.$list         = this.$("#search-in");
            this.$colorField   = this.$("#color");
            this.$termsField   = this.$("#terms");
            this.$modeField    = this.$("#exp-mode");
            this.$progField    = this.$("#exp-prog");
            this.$whiteField   = this.$("#whitebalance");
            this.$lightField   = this.$("#lightsource");
            this.$expMinField  = this.$("#exp-min");
            this.$expMaxField  = this.$("#exp-max");
            this.$aperMinField = this.$("#aper-min");
            this.$aperMaxField = this.$("#aper-max");
            this.$isoMinField  = this.$("#iso-min");
            this.$isoMaxField  = this.$("#iso-max");

            this.renderAlbumList();

            return this;
        },
        renderAlbumList: function() {

            var template = _.template(this.albumTmpl.html());
            var json = this.collection.toJSON();

            this.$list.html(template({list : json}));
            this.selectCurrentAlbum();

        },
        onAddAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onRemoveAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onUpdateAlbum: function(album, albums) {
            this.renderAlbumList();
        },
        onResetAlbums: function(albums) {
            this.renderAlbumList();
        },
        onResetAlbum: function(album, albums) {
            this.model = album;
            this.selectCurrentAlbum();
        },
        selectCurrentAlbum: function() {

            var options = this.$list.find("option");

            _.map(options, function(option, key) {

                if (this.model !== undefined && option.value === this.model.get('id')) {
                    this.$list[0].selectedIndex = key;
                }
            }, this);
        },
        close: function(e) {

            e.stopPropagation();

            this.manager.set({"FormSearch":false});
        },
        show: function() {

            this.$overlay.addClass("white-opacity-overlay").show();
            this.$el.show();
            return this;
        },
        hide: function(e) {

            //

            this.$overlay.removeClass("white-opacity-overlay").hide();
            this.$el.hide();
            return this;
        },
        search: function() {

            var searchIn = this.$list.val();
            var color    = this.$colorField.val();
            var terms    = this.$termsField.val();
            var exp_mode = this.$modeField.val();
            var exp_prod = this.$progField.val();
            var white    = this.$whiteField.val();
            var light    = this.$lightField.val();
            var expMin   = this.$expMinField.val();
            var expMax   = this.$expMaxField.val();
            var aperMin  = this.$aperMinField.val();
            var aperMax  = this.$aperMaxField.val();
            var isoMin   = this.$isoMinField.val();
            var isoMax   = this.$isoMaxField.val();

            var url = "";

            this.params = {
                search_in : searchIn,
                terms     : terms,
                color     : color,
                flash     : "",
                aperture  : aperMin + "-" + aperMax,
                exp_time  : expMin + "-" + expMax,
                exp_mode  : exp_mode,
                exp_prod  : exp_prod,
                light     : light,
                white     : white,
                iso       : isoMin + "-" + isoMax
            };

            /*var filters = {
        search_in : /([0-9]+)/,
        terms     : /([a-zA-Z0-9]+)/,
        color     : /([0-9]+)/,
        flash     : /([0-9]+)/,
        aperture  : /([0-9\.]+)-([0-9\.]+)/,
        exp_time  : /([0-9\.]+)-([0-9\.]+)/,
        exp_mode  : /([0-9]+)/,
        exp_prod  : /([0-9]+)/,
        light     : /([0-9]+)/,
        white     : /([0-9]+)/,
        iso       : /([0-9]+)-([0-9]+)/
    };*/


            _.forEach(this.params, function(value, key, obj) {

                if (url !== "") {
                    url += "!";
                }
                if (!value.match(this.config.filters[key])) {
                    value = "";
                    obj[key] = null;
                }
                url += key +":"+ value;

            }.bind(this));

            url = encodeURIComponent(url);

            this.$overlay.removeClass("white-opacity-overlay").hide();
            this.$el.hide();

            console.log(this.params);

            this.router.navigate("search/" + url, {trigger : true});
        }

    });

    Views.Displayer = Views.Base.extend({

        el: "#displayer",

        events: {},

        initialize: function() {

            this.config  = EP.config;
            this.manager = AppViews.Manager;
            this.grid    = AppViews.Grid;
            this.$btn    = $("#btn-display-gallery", this.$el);

            this.collection.on("model_reset", this.onResetAlbum, this);

            this.grid.on("render", function() {
                this.manager.set({"Displayer":true});
            }, this);
            this.grid.on("hide", function() {
                this.manager.set({"Displayer":false});
            }, this);

        },
        onResetAlbum: function(album, collection) {

            this.model = album;
            this.url   = this.copyUrl = this.config.base + 'pub/#gallery/album/'+this.model.get('id');

            this.render();
        },
        render: function() {
            this.$btn.attr("href", this.url);
            return this;
        }
    });

}(window, document, EasyPics));