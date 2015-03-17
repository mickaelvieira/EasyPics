(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;


    Routers.Public = Backbone.Router.extend({

        initialize: function() {

            console.log(EP);


            this.route(/^$/, "index", this.indexAction);
            this.route(/^$/, "error", this.errorAction);
            this.route(/^gallery\/album\/([0-9]+)/, "gallery", this.galleryAction);
            this.route(/^gallery\/album\/([0-9]+)\/photo\/([0-9]+)/, "gallery", this.galleryAction);

            App.Router = this;

            /* === | ===========================================================
             * INSTANCE MODELS + COLLECTIONS
             * =================================================================
             * */
            AppViews.Manager = new Views.Manager();
            AppViews.Loader = new Views.Loader();
            /* === | ===========================================================
             * INSTANCE VIEWS
             * =================================================================
             * */

            App.Models.Album = new Models.Album();
            App.Collections.Pictures = new Collections.Pictures();

            this.Album = App.Models.Album;
            this.Pictures = App.Collections.Pictures;

            AppViews.Informations = new Views.Informations({
                model: this.Album,
                collection: this.Pictures
            });
            AppViews.Actions = new Views.Actions({
                model: this.Album,
                collection: this.Pictures
            });
            AppViews.Popup = new Views.Popup();
            AppViews.Player = new Views.Player();
            AppViews.Gallery = new Views.Gallery({
                model: this.Album,
                collection: this.Pictures
            });

            AppViews.Selector = new Views.Selector({
                model: this.Album,
                collection: this.Pictures
            });


            this.manager = AppViews.Manager;
            this.config = EP.config;
        },
        indexAction: function() {



        },
        galleryAction: function(album_id, picture_id) {

            this.album_id = album_id;
            this.picture_id = picture_id;

            this.Album.set('id', album_id);
            this.manager.set({
                "Loader" : true,
                "Gallery" : true
            });
        },
        errorAction: function() {

            this.manager.set({
                "Gallery" : false
            });
        }
    });

    Routers.Private = Backbone.Router.extend({

        initialize: function() {

            console.log(EP);

            /* === | ===========================================================
             * ROUTES DEFINITION
             * =================================================================
             * */
            this.route(/^$/, "index", this.indexAction);
            this.route(/^grid\/album\/([0-9]+)/, "grid", this.gridAction);
            this.route("search/:search", "search", this.searchAction);

            App.Router = this;



            /* === | ===========================================================
             * INSTANCE MODELS + COLLECTIONS
             * =================================================================
             * */
            AppViews.Manager = new Views.Manager();
            AppViews.Loader = new Views.Loader();


            App.Models.Message = new Models.Message();
            App.Models.Album = new Models.Album();
            App.Collections.Albums = new Collections.Albums();
            App.Collections.Search = new Collections.Search();

            this.Album = App.Models.Album;
            this.Albums = App.Collections.Albums;
            this.Search = App.Collections.Search;

            /* === | ===========================================================
             * INSTANCE VIEWS HELPERS
             * =================================================================
             * */
            AppViews.Chosen = new Views.Chosen();
            AppViews.Messenger = new Views.Messenger({
                model: App.Models.Message
            });

            /* === | ===========================================================
             * INSTANCE MAIN VIEWS
             * =================================================================
             * */
            AppViews.Home = new Views.Home({
                collection: this.Albums
            });
            AppViews.Grid = new Views.Grid({
                collection: this.Albums
            });
            AppViews.Search = new Views.Search({
                collection: this.Search
            });

            /* === | ===========================================================
             * INSTANCE INTERFACE VIEWS
             * =================================================================
             * */
            AppViews.Logo = new Views.Logo();
            AppViews.Account = new Views.Account();

            /* === | ===========================================================
             * INSTANCE VIEWS PANELS
             * =================================================================
             * */
            AppViews.FormEditPicture = new Views.FormEditPicture({
                collection: this.Albums
            });
            AppViews.FormAddAlbum = new Views.FormAddAlbum({
                collection: this.Albums
            });
            AppViews.FormEditAlbum = new Views.FormEditAlbum({
                collection: this.Albums
            });
            AppViews.FormAddPicture = new Views.FormAddPicture({
                collection : this.Albums
            });
            AppViews.FormSearch = new Views.FormSearch({
                collection : this.Albums
            });
            AppViews.Tools = new Views.Tools({
                collection: this.Albums
            });
            AppViews.Displayer = new Views.Displayer({
                collection: this.Albums
            });
            AppViews.Social = new Views.Social({
                collection: this.Albums
            });
            this.manager = AppViews.Manager;
            this.config = EP.config;
        },

        indexAction: function(action) {

            //console.log("INDEX ACTION");

            this.manager.set({
                "Loader" : true,
                "Search" : false,
                "Grid" : false,
                "Home" : true
            });
        },
        gridAction: function(album_id) {

            //console.log("GRID ACTION");

            this.album_id = album_id;
            this.manager.set({
                "Loader" : true,
                "Search" : false,
                "Home" : false,
                "Grid" : true
            });
        },
        searchAction: function(search) {

            //console.log("SEARCH ACTION");

            if (search === undefined) {
                search = "";
            }

            search = decodeURIComponent(search);


            var searches = search.split("!");
            var i = 0,
                l = searches.length;

            /*var params = {
                terms : null,
                search_in : null,
                color : null,
                exp : null,
                aper : null,
                iso : null
            };*/

            var type,
                value,
                split;

            this.Search.reset();
            this.Search.collection.reset();

            //console.log(searches);

            for (i = 0; i < l; i++) {

                split = searches[i].split(":");

                if (split.length === 2) {

                    type = split[0];
                    value = split[1];
                    console.log(type);
                    console.log(value);
                    console.log(value.match(EP.config.filters[type]));

                    if (EP.config.filters[type] !== undefined && value.match(EP.config.filters[type])) {

                        this.Search.collection.add({
                            type : type,
                            value : value
                        });

                        //params[type] = value;
                    }
                }
            }

            this.Search.addAll();
            //this.Search.on("reset", this.displaySearch, this);
            //this.Search.fetch();


            this.manager.set({
                "Loader" : false,
                "Grid" : false,
                "Home" : false,
                "Search" : true
            });

        },
        displaySearch: function() {

            //console.log(arguments);

            /*this.loader.hide();
            this.displayer.hide();
            this.tools.hide();
            this.grid.hide();
            this.home.hide();	*/
        }

    });

    Routers.Settings = Backbone.Router.extend({

        initialize: function() {

            //console.log("init Settings Router");

            this.route(/^$/, "index", this.indexAction);

            AppViews.Manager = new Views.Manager();

            this.manager = AppViews.Manager;

            /*AppViews.Manager = new Models.Manager({
                defaults : {
                    "FormSearch" : false,
                    "Messenger" : false,
                    "Chosen" : false,
                    "Account" : false
                }
            });*/

            App.Router = this;
            AppViews.Chosen = new EP.Helpers.Chosen();
            App.Message = new Models.Message();
            AppViews.Messenger = new EP.Helpers.Messenger({model: App.Message});
            AppViews.Account = new Views.Account();
        },
        indexAction: function() {



        }
    });

}(window, document, EasyPics));