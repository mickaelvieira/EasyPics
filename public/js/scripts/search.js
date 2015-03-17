(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Search = Views.Base.extend({

        el: "#search-content",

        containerTmpl : $("#search_item_container_tmpl"),

        initialize: function() {

            _.extend(this, Backbone.Events);

            this.config = EP.config;
            this.manager = AppViews.Manager;
            this.router = App.Router;

            this.items = [];
        },
        show: function() {

            this.bindEvents();
            this.collection.fetch();

            this.$el.show();
            this.trigger("show");

            return this;
        },
        hide: function() {

            this.unbindEvents();
            this.destroyItems();
            this.$el.hide().empty();

            this.trigger("hide");

            return this;
        },
        onResetSearch: function() {
            this.render();
        },
        render: function() {

            console.log("[Search] : Render Search Grid");

            this.$el.empty();

            var div, item, grid = 1, count = 0;
            var template = _.template(this.containerTmpl.html());
            var container = $(template({grid : grid}));
            var total = this.collection.size();

            console.log('GRID IS ACTIVE '+this.manager.get("Grid"));

            if (!this.manager.get("Search")) {
                return;
            }


            this.destroyItems();

            //console.log(this.items);

            this.collection.map(function(picture, index) {

                //console.log(picture);

                count++;

                div = $('<div draggable="true" class="grid_6_1 picture-item-container">');

                item = new Views.SearchItem({
                    model: picture,
                    collection: this.pictures
                });
                item.setElement(div).render();

                this.items.push(item);
                container.append(div);

                if (count % 6 === 0) {

                    grid++;

                    this.$el.append(container);
                    container = $(template({grid : grid}));
                    count = 0;
                }
                else if (index === total - 1) {

                    this.$el.append(container);
                    count = 0;
                }
            }, this);

            this.trigger("render");

            return this;
        },
        destroyItems: function() {

            var i = 0,
                l = this.items.length;

            for (i = 0; i < l; i++) {
                this.items[i].destroy();
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
            this.collection.on("reset", this.onResetSearch, this);
        },
        unbindEvents: function() {
            this.collection.off("reset", this.onResetSearch, this);
        }
    });

    Views.SearchItem = Views.Base.extend({

        tmpl: $("#item_search_tmpl"),

        events: {
            "click .btn-select-picture" : "selectPicture",
            "click .btn-edit-picture"   : "editPicture",
            "click .btn-delete-picture" : "confirmBeforeDelete"
        },
        initialize: function() {

            this.selected = false;

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
            json.title = _.escape(json.title);
            json.description = _.escape(json.description);

            this.$el.html(template(json));
            this.$img = this.$(".picture-img");

            this.load();

            return this;
        },
        load: function() {

            var img = new Image();
            img.src = this.model.getPictureSrc("grid");

            $(img).on("load", this.onLoad.bind(this));
        },
        onLoad: function() {
            this.hide();
        },
        changeImage: function() {

            this.$img.attr('alt', this.model.get('title'));
            this.$img.attr('src', this.model.getPictureSrc("grid"));
            this.show();
        },
        show: function() {
            this.$img.fadeIn(400);
            this.$el.css("backgroundImage", "none");
        },
        hide: function() {
            this.$img.fadeOut(400, this.changeImage.bind(this));
        },
        selectPicture: function(e) {

            if (this.selected) {
                this.$img.removeClass("selected");
                this.selected = false
            }
            else {
                this.$img.addClass("selected");
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
            this.model.destroy({wait : true}); // wait : true server response  before remove from collection
        },
        destroy: function() {

            this.undelegateEvents();
            this.model.off("change", this.onChangePicture, this);
        }
    });

}(window, document, EasyPics));