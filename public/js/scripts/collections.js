(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    Collections.Albums = Collections.Base.extend({

        index: 0,

        model_id: 0,

        model: Models.Album,

        initialize: function() {

            this.config = EP.config;
            this.index = this.indexOf(this.model);

            this.on("reset", this.onReset, this);
            this.on("add", this.onAdd, this);
        },
        parse: function(response) {

            if (response.status !== undefined) {

                this.status = response.status;
                this.messages = response.messages;

                return response.results;
            }
            else {
                return response;
            }
        },
        url: function() {
            return this.config.base + this.config.module + "/album/";
        },
        comparator: function(model) {
            return -model.get("timestamp");
        },
        onReset: function(albums) {
            this.each(function(album) {
                album.on("update", this.modelUpdated, this);
            }, this);
        },
        onAdd: function(album, albums) {
            album.on("update", this.modelUpdated, this);
        }
    });

    Collections.Pictures = Collections.Base.extend({

        index: 0,

        model_id: 0,

        model: Models.Picture,

        initialize: function() {

            this.config = EP.config;
            this.on("reset", this.onReset, this);
            this.on("add", this.onAdd, this);
        },
        parse: function(response) {

            if (response.status !== undefined) {

                this.status = response.status;
                this.messages = response.messages;

                return response.results;
            }
            else {
                return response;
            }
        },
        url: function() {
            return this.config.base + this.config.module + "/pictures/" + this.album.get('id');
        },
        comparator: function(model) {
            return model.get("timestamp");
        },
        onReset: function(pictures) {
            this.each(function(picture) {
                picture.album = this.album;
                picture.on("change", this.modelHasChanged, this);
            }, this);
        },
        onAdd: function(picture, pictures) {
            picture.album = this.album;
            picture.on("change", this.modelHasChanged, this);
        }

    });

    Collections.Params = Backbone.Collection.extend({

        model : Models.Param,

        initialize: function() {


        }

    });

    Collections.Search = Collections.Base.extend({

        model: Models.Picture,

        collection: Collections.Params,

        initialize: function() {

            this.params = ""
            this.config = EP.config;
            this.collection = new Collections.Params();

            this.on("add_all", this.getParams, this);
        },
        parse: function(response) {

            if (response.status !== undefined) {

                this.status = response.status;
                this.messages = response.messages;

                return response.results;
            }
            else {
                return response;
            }
        },
        url: function() {
            return this.config.base + this.config.module + "/search/?" + this.params;
        },
        getParams: function(collection) {

            this.params = "";
            this.collection.map(function(model) {

                console.log(model.toJSON());

                if (model.get('value') !== null) {
                    if (this.params !== "") {
                        this.params += "&";
                    }
                    this.params += model.get('type') + "=" + model.get('value');
                }
            }, this);

            console.log("SEARCH PARAMS > "+this.params);
        }
    });

    Collections.Images = Backbone.Collection.extend({

        model: Models.Image,

        initialize: function() {

            this.loaded = 0;
            this.on("add", this.onAdd, this);
        },
        onAdd: function(model, collection) {
            model.on("loaded", this.onLoad, this);
        },
        loadImages: function() {
            this.map(function(image) {
                image.load();
            }, this);
        },
        onLoad: function() {

            this.loaded++;

            if (this.loaded === this.size()) {
                this.trigger("images_loaded", this);
            }
        }
    });

    Collections.Files = Backbone.Collection.extend({

        model: Models.File,

        initialize: function() {
            this.config = EP.config;
        },
        parse: function(response) {

            if (response.status !== undefined) {

                this.status = response.status;
                this.messages = response.messages;

                return response.results;
            }
            else {
                return response;
            }
        },
        url : function() {
            return this.config.base + "action/folder";
        }

    });

}(window, document, EasyPics));