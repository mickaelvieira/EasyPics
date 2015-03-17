(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Logo = Backbone.View.extend({

        el: "#logo",

        events: {
            "click #link-logo" : "backToHome"
        },
        initialize: function() {

            this.router = App.Router;
        },
        render: function() {

            return this;
        },
        backToHome: function(e) {

            e.preventDefault();
            this.router.navigate("/", {trigger : true});
        }
    });

    Views.Account = Backbone.View.extend({

        el : "#account",

        opened : false,

        $dropdown : $(".dropdown-menu-account"),

        events : {
            "click .btn-toggle" : "handleDropdown",
            "click .btn-user" : "handleDropdown",
            "click #btn-home" : "backToHome",
            "click #btn-search" : "openSearch"
        },
        initialize: function() {

            this.manager = AppViews.Manager;
            this.router = App.Router;
            this.formSearch = AppViews.FormSearch;

            $(document).on("click", this.close.bind(this));

        },
        render: function() {

            return this;
        },
        handleDropdown: function(e) {

            e.stopPropagation();

            if (this.opened) {
                this.close();
            }
            else {
                this.open();
            }
        },
        open: function() {

            this.$dropdown.css("display", "block");
            this.opened = true;
        },
        close: function() {

            this.$dropdown.css("display", "none");
            this.opened = false;
        },
        backToHome: function(e) {

            e.preventDefault();
            e.stopPropagation();

            this.router.navigate("/", {trigger : true});
            this.close();
        },
        openSearch: function(e) {

            e.preventDefault();
            e.stopPropagation();

            this.manager.set({"FormSearch" : true});
            this.close();

        }
    });

}(window, document, EasyPics));