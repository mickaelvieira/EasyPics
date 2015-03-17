(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Grid = Views.Base.extend({

        el: "#grid-content",

        containerTmpl : $("#picture_item_container_tmpl"),

        initialize: function() {

            _.extend(this, Backbone.Events);

            //console.log(App.Router);
            //console.log(AppRouter);
            console.log(AppViews);
            console.log(App.Router);

            this.config = EP.config;
            this.manager = AppViews.Manager;
            this.router = App.Router;
            this.chosen = AppViews.Chosen;
            this.utils = App.Utils;

            this.cols = 0;
            this.rows = 1;
            this.maxRows = 1;
            this.items = [];
            this.formEditPicture = AppViews.FormEditPicture;
            //this.formAddPicture = AppViews.FormAddPicture;
            this.displayer = AppViews.Displayer;
            this.tools = AppViews.Tools;

            $(document).on("scroll", this.onScroll.bind(this));
        },
        show: function() {

            this.bindEvents();

            this.getMaxRows();
            this.cols = 0;
            this.rows = 1;

            this.$el.show().empty();
            this.trigger("show");

            if (this.collection.size() > 0) {
                this.display();
            }
            else {
                this.collection.fetch();
            }



            return this;
        },
        hide: function() {

            this.unbindEvents();
            this.destroyItems();
            this.$el.empty().hide();

            this.trigger("hide");

            return this;
        },
        display: function() {

            //console.log("[Grid] : display action - model is '"+this.model+"' - album id '"+this.router.album_id+"' ");

            if (this.model === undefined || this.router.album_id !== this.model.get('id')) {

                this.model = this.collection.setCurrModel(this.router.album_id);

                //console.log("Model has changed "+this.model.get('id'));

                if (this.model === undefined) {
                    throw "Album is undefined"; // TODO comment en production server
                    this.router.navigate("/", {trigger:true});
                }

                //console.log("Model collection size "+this.model.pictures.size());

                if (this.model.pictures.size() === 0) {
                    this.model.pictures.fetch();
                }
                else {
                    this.pictures = this.model.pictures;
                    this.render();
                }

            }
            else {
                this.render();
            }
        },
        onResetAlbums: function() {
            this.display();
        },
        onResetAlbum: function(album, albums) {

            this.model = album;

            this.model.pictures.rebind('reset', 'onResetPictures', this);
            this.model.pictures.rebind('remove', 'onRemovePicture', this);
            //this.model.pictures.rebind('add', 'onAddPicture', this);
            this.model.pictures.rebind('add_all', 'onAddAllPictures', this);
        },
        onResetPictures: function(pictures) {
            this.pictures = pictures;
            this.render();
        },
        onAddAllPictures: function(pictures) {
            this.pictures = pictures;
            this.show();
        },
        onAddPicture: function(picture, pictures) {
            this.pictures = pictures;
            this.show();
        },
        onRemovePicture: function(picture, pictures) {


            console.log(picture);
            console.log(pictures);

            this.pictures = pictures;
            this.show();
        },
        render: function() {

            //console.log("render GRID model ID "+this.model.get('id')+" collection size "+this.model.pictures.size());

            var div,
                item,
                render,
                rows = 1,
                cols = 0;

            var template  = _.template(this.containerTmpl.html());
            var container = $(template({grid : rows}));
            var total     = this.pictures.size();

            //console.log("View state "+this.manager.get("Grid"));

            if (!this.manager.get("Grid")) {
                return;
            }

            this.loaded = 0;

            if (this.model.pictures.size() === 0) {
                this.manager.set({"Loader" : false});
            }

            //console.log("COLS "+this.cols);
            //console.log("COLS "+this.rows);
            //console.log("Pictures size "+this.pictures.size());

            if (this.cols === this.pictures.size()) {
                return;
            }

            if (this.cols === 0) {
                this.trigger("render");
            }

            this.pictures.map(function(picture, index) {

                //console.log(picture);

                cols++;

            //	console.log("Rows "+rows+" Cols "+cols);

                render = false;
                if (rows >= this.rows && rows <= this.maxRows) {
                    render = true;
                }

                //console.log("Render "+render);

                if (render) {

                    this.cols++;

                    div = $('<div class="grid_6_1 picture-item-container">');

                    item = new Views.GridItem({
                        model: picture,
                        collection: this.pictures
                    });
                    item.setElement(div).render();
                    item.on("image_loaded", this.onImageLoaded, this);

                    this.items.push(item);
                    container.append(div);

                    //console.log(div.html());

                }

                if (cols % 6 === 0) {

                    rows++;
                    cols = 0;

                    if (render) {

                        this.rows++;
                        this.$el.append(container);
                        container = $(template({grid : rows}));
                    }
                }
                else if (index === total - 1) {

                    if (render) {
                        this.rows++;
                        this.$el.append(container);
                    }

                    cols = 0;
                }
            }, this);

            //console.log(this.$el.css("display"));
            //console.log(this.$el.attr("id"));
            //console.log(this.$el.html());

            return this;
        },
        onScroll: function getScrollPosition(e) {

            this.getMaxRows();
            this.render();
        },
        getMaxRows: function() {

            var wHeight = $(window).height();
            var scrollTop = $(document).scrollTop();

            this.maxRows = Math.floor((wHeight + scrollTop) / 180);
        },
        onImageLoaded: function() {

            this.loaded++;

            if (this.loaded === this.items.length) {
                this.manager.set({
                    "Loader" : false
                });
            }
        },
        destroyItems: function() {

            var i = 0,
                l = this.items.length;

            console.log("destroyItems" + l);

            for (i = 0; i < l; i++) {
                this.items[i].destroy();
                this.items[i].remove();
                delete this.items[i];
            }
            this.items = [];
        },
        getSelectedItemsPictureIds: function() {

            var pictures_id = [];

            _.each(this.items, function(item, index) {
                if (item.selected) {
                    pictures_id.push(item.model.get('id'));
                }
            }, this);
            return pictures_id;
        },
        bindEvents: function() {
            this.collection.on("reset", this.onResetAlbums, this);
            this.collection.on("model_reset", this.onResetAlbum, this);
        },
        unbindEvents: function() {
            this.collection.off("reset", this.onResetAlbums, this);
            this.collection.off("model_reset", this.onResetAlbum, this);
        }
    });

    Views.GridItem = Views.Base.extend({

        tmpl: $("#item_picture_tmpl"),

        events: {
            "click .btn-select-picture" : "selectPicture",
            "click .btn-view-gallery"   : "viewGallery",
            "click .btn-edit-picture"   : "editPicture",
            "click .btn-delete-picture" : "confirmBeforeDelete"
        },
        initialize: function() {

            _.extend(this, Backbone.Events);

            this.selected = false;

            this.config = EP.config;
            this.utils = App.Utils;
            this.router = App.Router;
            this.messenger = AppViews.Messenger;
            this.chosen = AppViews.Chosen;

            this.tools = AppViews.Tools;
            this.formEditPicture = AppViews.FormEditPicture;

            this.model.on("change", this.onChangePicture, this)
        },
        render: function() {

            var template = _.template(this.tmpl.html());
            var json = this.model.toJSON();

            json.source = this.model.getPictureSrc("grid");
            json.name = _.escape(json.name);
            json.title = this.utils.truncateString(_.escape(json.title), 14);
            json.description = _.escape(json.description);

            this.$el.html(template(json));
            this.$link = this.$("a.btn-select-picture");
            this.load();

            return this;
        },
        load: function() {

            var img = new Image();
            img.src = this.model.getPictureSrc("grid");
            img.alt = this.model.get("title");
            img.id = "item-picture-img-" + this.model.get('id');


            this.$img = $(img);
            this.$img.addClass("picture-img");
            this.$img.on("load", this.onLoad.bind(this));
            this.$img.on("error", this.onError.bind(this));

        },
        onLoad: function(e) {

            var img = e.currentTarget;

            this.$link.html(img);
            this.trigger("image_loaded");
            this.show();
        },
        onError: function(e) {

            this.trigger("image_loaded");
        },
        show: function() {
            this.$img.fadeIn(400);
            this.$el.css("backgroundImage", "none");
        },
        viewGallery: function() {

            var album = this.model.album;
            var url = this.config.base + "pub/#gallery/album/" + album.get('id') + "/photo/" + this.model.get('id');

            document.location.href = url;
        },
        selectPicture: function(e) {

            if (this.selected) {
                this.$link.removeClass("selected");
                this.selected = false
            }
            else {
                this.$link.addClass("selected");
                this.selected = true;
            }
            this.tools.checkItems();
        },
        editPicture: function(e) {

            e.stopPropagation();

            this.formEditPicture.model = this.model;
            this.formEditPicture.render();
        },
        onChangePicture: function(model, options) {

            //wait : true, utile si les nouveaux attributs sont passés à la méthode save et pas via set

            this.model.save({}, {
                success: this.onSavePicture.bind(this)
            });
        },
        onSavePicture: function(model, response) {

            this.messenger.setMessage(response.status, response.messages);
            this.render();
        },
        confirmBeforeDelete: function(e) {

            e.stopPropagation();

            this.chosen.open({
                message : "Do you want to delete this picture ?",
                leftValue : "I'm sure",
                rightValue : "No",
                onLeft : this.deletePicture.bind(this)
            });
        },
        deletePicture: function() {

            this.messenger.setMessage(true, 'Process delete picture...');
            this.model.destroy(); // wait : true server response  before remove from collection
        },
        destroy: function() {

            console.log("destroy" + this.model.get('id'));

            this.undelegateEvents();
            this.model.off("change", this.onChangePicture, this);
        }
    });

}(window, document, EasyPics));