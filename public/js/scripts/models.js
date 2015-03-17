(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;


    Models.Message = Models.Base.extend({

        initialize: function() {
            this.config = EP.config;
        }

    });

    Models.Picture = Models.Base.extend({

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
        url: function() {
            return this.config.base + this.config.module + "/picture/" + this.get('id');
        },
        getPictureUrl: function(mode) {
            return this.config.base + "#" + mode + "/album/" + this.album.get('id') + "/photo/" + this.get('id');
        },
        getPictureSrc: function(type) {
            return this.config.base + "image/" + type + "/" + this.get('key_url');
        }
    });

    Models.Album = Models.Base.extend({

        urlRoot : "album/",

        initialize: function() {

            this.config = EP.config;
            this.pictures = new Collections.Pictures();
            this.pictures.album = this;
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

            if (this.get('id') !== undefined) {
                return this.config.base + this.config.module + "/album/" + this.get('id');
            }
            else {
                return this.config.base + this.config.module + "/album/";
            }
        },
        getAlbumUrl: function(mode) {
            return this.config.base + "#" + mode + "/album/" + this.get('id');
        },
        getNextUrl: function() {

            var nextPicture = this.pictures.getNextModel();
            if (nextPicture !== undefined) {
                return "gallery/album/" + this.get('id') + "/photo/" + nextPicture.get('id');
            }
            /*else {
                throw "Next Picture is undefined";
            }	*/
        },
        getPrevUrl: function() {

            var prevPicture = this.pictures.getPrevModel();
            if (prevPicture !== undefined) {
                return "gallery/album/" + this.get('id') + "/photo/" + prevPicture.get('id');
            }
            /*else {
                throw "Prev Picture is undefined";
            }	*/
        },
        getCoverSrc: function() {
            return this.config.base + "image/home/" + this.get('cover');
        }

    });

    Models.File = Models.Base.extend({



    });

    Models.Image = Models.Base.extend({

        initialize: function() {

            this.element = new Image();
            this.element.width = this.get('width');
            this.element.height = this.get('height');
            this.element.alt = this.get('title');
            this.$element = $(this.element);
        },
        load: function() {

            this.element.src = this.get('src');
            this.$element.on("load", this.onLoad.bind(this));
            this.$element.on("error", this.onError.bind(this));
        },
        onLoad: function() {
            this.trigger("loaded", this, this.element);
        },
        onError: function() {
            this.trigger("loaded", this, this.element);
        }
    });

    Models.Param = Models.Base.extend({});

    /*return {
        Album   : Album,
        Picture : Picture,
        Message : Message,
        Param   : Param,
        File    : File
    }*/
}(window, document, EasyPics));