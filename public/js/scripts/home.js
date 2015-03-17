(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Home = Views.Base.extend({

        el: "#home-content",

        containerTmpl : $("#album_item_container_tmpl"),

        initialize: function() {

            _.extend(this, Backbone.Events);

            this.items = [];
            this.config = EP.config;
            this.manager = AppViews.Manager;
            this.collection.on("add", this.onAddAlbum, this);
            this.collection.on("remove", this.onRemoveAlbum, this);
            this.collection.on("reset", this.onResetAlbums, this);
        },
        render: function() {

            this.$el.empty();

            var div,
                item,
                grid = 1,
                count = 0;

            var template  = _.template(this.containerTmpl.html());
            var container = $(template({grid : grid}));
            var total     = this.collection.size();

            if (!this.manager.get("Home")) {
                return;
            }

            this.destroyItems();
            this.loaded = 0;

            this.collection.map(function(album, index) {

                count++;

                div = $('<div class="grid_3 album-item-container">');
                item = new Views.HomeItem({
                    model: album,
                    collection: this.collection
                });
                item.on("image_loaded", this.onImageLoaded, this);
                item.setElement(div).render();


                this.items.push(item);
                container.append(div);

                if (count % 4 === 0) {

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
        show: function() {

            if (this.collection.size() > 0) {
                this.render();
            }
            else {
                this.collection.fetch();
            }
            this.$el.show();
            return this;
        },
        hide: function() {

            this.$el.hide();
            this.destroyItems();

            return this;
        },
        onImageLoaded: function() {

            this.loaded++;

            if (this.loaded == this.items.length) {
                this.manager.set({
                    "Loader" : false
                });
            }
        },
        onResetAlbums: function() {

            if (this.collection.size() === 0) {
                this.manager.set({
                    "Loader" : false
                });
            }
            this.render();
        },
        onAddAlbum: function(album, albums) {
            this.render();
        },
        onRemoveAlbum: function(album, albums) {
            this.render();
        },
        destroyItems: function() {

            var i = 0,
                l = this.items.length;

            //console.log("DESTROY HOME ITEMS !!! ==> "+l);

        //	console.log(this.items);

            for (i = 0; i < l; i++) {
                this.items[i].destroy();
                delete this.items[i];
            }
            this.items = [];
        }
    });

    Views.HomeItem = Views.Base.extend({

        tmpl: $("#album_item_tmpl"),

        events: {
            "click .link-edit-album"   : "navigate",
            "click .btn-select-album"  : "navigate",
            "click .btn-delete-album"  : "confirmBeforeDelete",
            "click .btn-private-album" : "togglePrivacy",
            "click .btn-public-album"  : "togglePrivacy"
        },

        initialize: function() {

            _.extend(this, Backbone.Events);

            this.config    = EP.config;
            this.utils     = App.Utils;
            this.router    = App.Router;
            this.messenger = AppViews.Messenger;
            this.chosen    = AppViews.Chosen;

            this.model.on("change:privacy", this.onChangePrivacy, this);
            this.model.on("change:total_pictures", this.renderTotal, this);
        },
        render: function() {

            var template = _.template(this.tmpl.html());
            var json = this.model.toJSON();
            json.name = this.utils.truncateString(_.escape(json.name), 28);
            json.privacy = parseInt(json.privacy, 10);



            //console.log("RENDER ITEMS");

            this.$el.html(template(json));

            this.$link    = this.$("a.btn-select-album");
            this.$total   = this.$(".album-item-total-picture");
            this.$privacy = this.$(".album-privacy");

            this.renderTotal();
            this.renderPrivacy();

            //console.log(this.model.get('cover'));

            if (this.model.get('cover') !== "") {
                this.load();
            }
            else {
                this.trigger("image_loaded");
            }
            return this;
        },
        renderTotal: function() {

            var total = parseInt(this.model.get("total_pictures"), 10);
            var total_html = "";

            if (!isNaN(total)) {
                total_html = (total > 1) ? total + " pictures" : total + " picture";
            }
            this.$total.html(total_html);
        },
        renderPrivacy: function() {

            var privacy = parseInt(this.model.get("privacy"), 10);

            if (isNaN(privacy) || privacy === 1) {
                this.$privacy.removeClass("album-privacy-public").addClass("album-privacy-private");
            }
            else {
                this.$privacy.removeClass("album-privacy-private").addClass("album-privacy-public");
            }
        },
        load: function() {

            var img = new Image();
            var source = this.model.getCoverSrc();

            //console.log(source);

            img.src = source;
            img.alt = this.model.get('name');
            img.id = "item-home-img-" + this.model.get('id');

            this.$img = $(img);

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
        },
        navigate: function(e) {

            e.stopPropagation();

            this.router.navigate("grid/album/" + this.model.get("id"), {trigger: true});
        },
        togglePrivacy: function(e) {

            e.stopPropagation();

            var privacy = parseInt(this.model.get("privacy"), 10);
            privacy = (!isNaN(privacy) && privacy === 1) ? "0" : "1";

            this.model.set("privacy", privacy);
        },
        onChange: function(model, options) {

            this.render();
        },
        onChangePrivacy: function(model, options) {

            console.log("on change into home");

            this.model.save({}, {
                wait : true,
                success: this.onSaveAlbum.bind(this)
            });
        },
        onSaveAlbum: function(model, response) {

            this.renderPrivacy();
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
                wait    : true, // wait : true - wait server response  before remove model from collection
                success : this.onDeleteAlbum.bind(this)
            });
        },
        onDeleteAlbum: function(model, response) {
            this.messenger.setMessage(response.status, response.messages);
        },
        destroy: function() {
            this.undelegateEvents();
            this.model.off("change:privacy", this.onChangePrivacy);
            this.model.off("change:total_pictures", this.renderTotal);
        }
    });

}(window, document, EasyPics));