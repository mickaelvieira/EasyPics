(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Messenger = Views.Base.extend({

        timer: null,

        duration: 2500,

        el: "#message-view",


        events: {
            "click #message-close" : "close"
        },

        initialize: function() {

            this.container = $("#message-container", this.$el);
            this.model.on('change', this.render, this);


            this.$iconInfo = this.$("#icon-info");
            this.$iconError = this.$("#icon-error");
        },
        render: function() {

            /*console.log("========== render message ============");
            console.log(this.model.get('status'));
            console.log(this.model.get('message'));
            console.log(this.model.get('type'));
            console.log("=====================================");*/

            this.status = true;
            this.type = 'info';
            this.$el.removeClass();

            this.status = this.model.get('status') || false;
            this.type = (this.status) ? 'info' : 'error';

            this.$el.addClass(this.type);


            /*if (this.model.get('type') !== undefined) {
                this.$el.addClass(this.model.get('type'));
            }*/


            //console.log(this.model.get('message'));

            if (this.model.get('message') !== undefined) {

                var message = "";
                var messages = this.model.get('message');

                if (_.isArray(messages)) {
                    _.map(messages, function(m){
                        message += m + "<br>";
                    });
                }
                else {
                    message = messages;
                }
                this.container.html(message);
            }

            this.open()
            this.killTimer();
            //this.duration = (this.model.get('duration') !== undefined) ? parseInt(this.model.get('duration'), 10) : 5000;


            if (!isNaN(this.duration) && this.duration > 0) {
                this.timer = window.setTimeout(this.close.bind(this), this.duration);
            }
            this.model.clear({
                silent: true
            });

            return this;
        },
        open: function() {
            this.$el.fadeIn();
        },
        close: function() {

            this.killTimer();
            this.$el.fadeOut();
        },
        setDuration: function(duration) {

            this.duration = duration;
            return this;
        },
        setMessage: function(status, message, duration) {

            this.model.set({
                status : status,
                message : message,
                duration : (duration !== undefined) ? duration : 5000
            });

            return this;
        },
        killTimer: function() {

            if (this.timer !== null) {
                window.clearTimeout(this.timer);
                this.timer = null;
            }
        }
    });

    /* === | ==========================================================================
    Chosen View
    =================================================================================== */

    Views.Chosen = Views.Base.extend({

        el: "#chosen",

        options: {},

        defaults: {
            message    : "Confirm this action",
            leftValue  : 'Yes',
            rightValue : 'No',
            onLeft     : function(){},
            onRight    : function(){}
        },

        events: {
            "click #chosen-button-left" : "onClickLeft",
            "click #chosen-button-right" : "onClickRight"
        },

        initialize: function() {

            this.$overlay          = $("#overlay");
            this.$container        = this.$("#chosen-buttons-container");
            this.$buttonLeft       = this.$("#chosen-button-left");
            this.$buttonRight      = this.$("#chosen-button-right");
            this.$messageContainer = this.$("#chosen-message-container");
        },
        render: function() {

            this.$messageContainer.html(this.options.message);
            this.$buttonLeft.html(this.options.leftValue);
            this.$buttonRight.html(this.options.rightValue);

            return this;
        },
        onClickLeft: function(e) {

            e.stopPropagation();

            if (typeof this.options.onLeft === "function") {
                this.options.onLeft();
            }

            this.close();
        },
        onClickRight: function(e) {

            e.stopPropagation();

            if (typeof this.options.onRight === "function") {
                this.options.onRight();
            }
            this.close();
        },
        open: function(options) {

            this.options = _.defaults(options, this.defaults);

            this.$overlay.addClass("black-opacity-overlay").show();
            this.$el.show();
            this.render();

            return this;
        },
        close: function() {

            this.$overlay.removeClass("black-opacity-overlay").hide();
            this.$el.hide();

            return this;
        }
    });

    /*var Resizer = (function(){

        var duration = 400,
            gridOriginalWidth = 76,
            containerMinMarge = 6,
            gridMarge = 12,
            nbGrid = 12;
        var containerWidth, gridWidth, containerWidth, w;

        function position() {
            containerWidth = nbGrid * (gridOriginalWidth + (2 * gridMarge));
            gridWidth = (($(window).width() - (2 * containerMinMarge)) / 12) - (2 * gridMarge);
            containerWidth = (12 * (gridWidth + (2 * gridMarge)));
        }

        function init() {

            position();

            //var containerWidth = nbGrid * (gridOriginalWidth + (2 * gridMarge));
            //var gridWidth = (($(window).width() - (2 * containerMinMarge)) / 12) - (2 * gridMarge);
            //var containerWidth = (12 * (gridWidth + (2 * gridMarge)));

            if (containerWidth < 1200) {
                $(".container_12").css("marginLeft", containerMinMarge + "px").css("marginRight", containerMinMarge + "px");
            }
            else {
                $(".container_12").css("marginLeft", "auto").css("marginRight", "auto");
            }

            $(".container_12").css("width", containerWidth + "px");

            for (var i = 1; i <= 12; i++) {
                w = (i * gridWidth) + ((i - 1) * 2 * gridMarge);
                $(".grid_" + i).css("width", w + "px");
            }
            $(window).on("resize", resize);
        }

        function resize(){

            position();

            //var containerWidth = nbGrid * (gridOriginalWidth + (2 * gridMarge));
            //var gridWidth = (($(window).width() - (2 * containerMinMarge)) / 12) - (2 * gridMarge);
            //var containerWidth = (12 * (gridWidth + (2 * gridMarge)));

            if (containerWidth < 1200) {
                $(".container_12").css("marginLeft", containerMinMarge + "px")
                                    .css("marginRight", containerMinMarge + "px");
            }
            else {
                $(".container_12").css("marginLeft", "auto")
                                    .css("marginRight", "auto");
            }

            $(".container_12").stop(true).animate({width : containerWidth + "px"}, duration);

            for (var i = 1; i <= 12; i++) {
                w = (i * gridWidth) + ((i - 1) * 2 * gridMarge);
                $(".grid_" + i).stop(true).animate({width : w + "px"}, duration);
            }
        }
        //init();

    }());
    */
}(window, document, EasyPics));