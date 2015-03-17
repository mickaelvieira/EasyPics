(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Views.Gallery = Views.Base.extend({

        store : [],

        el: "#gallery-content",

        events: {
            "click #gallery-prev-link" : "goPrev",
            "click #gallery-next-link" : "goNext"
        },

        initialize: function() {

            this.config = EP.config;
            this.router = App.Router;
            this.utils = App.Utils;
            this.manager = AppViews.Manager;

            this.player = AppViews.Player;
            this.player.gallery = this;

            this.model.loaded = false;

            this.hidden    = false;
            this.autorun   = false;
            this.direction = 1;
            this.maxLoaded = 3;
            this.fx        = "swing";
            this.delay     = 400;
            this.hSelector = 100;

            this.player = AppViews.Player;
            this.player.gallery = this;
            this.player.autorun = this.autorun;

            this.$popup       = $("#popup");
            this.$action      = $("#actions");
            this.$selector    = this.$("#selector");
            this.$information = this.$("#gallery-informations-container");
            this.$content     = this.$("#content-image");
            this.$container   = this.$("#gallery-container");
            this.$btnLeft     = this.$("#gallery-prev-link");
            this.$btnRight    = this.$("#gallery-next-link");

            this.wBtnLeft  = this.$btnLeft.outerWidth(true);
            this.wBtnRight = this.$btnRight.outerWidth(true);
            this.hBtnLeft  = this.$btnLeft.outerHeight(true);
            this.hBtnRight = this.$btnRight.outerHeight(true);

            this.setPosition();

            this.collection.on("model_reset", this.onResetPicture, this);

            $(document).on("mousemove", this.onMove.bind(this));
            $(window).on("resize", this.onResize.bind(this));
        },
        show: function() {

            if (!this.model.loaded) {
                this.model.fetch({
                    error : this.onErrorAlbum.bind(this),
                    success : this.onResetAlbum.bind(this)
                });
            }
            else {
                this.start();
            }

            this.$el.show();
            return this;
        },
        onErrorAlbum: function(model, response, options) {
            //this.router.navigate("error", {trigger: true});
        },
        onResetAlbum: function(album, albums) {

            this.collection.album = this.model;
            this.collection.fetch({
                success : this.onResetPictures.bind(this)
            });
        },
        onResetPictures: function(collection, response) {

            this.model.loaded   = true;
            this.model.pictures = this.collection;

            this.manager.set({
                "Selector" : true,
                "Informations" : true
            });

            if (this.model.pictures.size() === 0) {
                throw "collection picture is empty";
                return;
            }

            this.start();
        },
        start: function() {

            if (this.router.picture_id === undefined) {
                this.router.picture_id = this.model.pictures.first().get('id');
            }
            this.picture = this.collection.setCurrModel(this.router.picture_id);

            if (this.picture === undefined) {
                throw "picture is undefined";
                return;
            }

            this.prevUrl = this.model.getPrevUrl();
            this.nextUrl = this.model.getNextUrl();
            this.preload();
        },
        render: function() {

            var image = this.images.get(this.picture.get('id'));
            var element = image.element;

            this.$content.html(element);
            this.setDimensions();
            this.$content.fadeIn();

            if (this.autorun) {
                this.player.play();
            }
            return this;
        },
        setPosition: function(anim) {

            var screenWidth  = $(window).width();
            var screenHeight = $(window).height() - 24;

            this.$el.css("width", screenWidth + "px");
            this.$el.css("height", screenHeight + "px");

            this.wContainer = screenWidth;
            this.hContainer = screenHeight;

            this.$container.css("width", this.wContainer + "px");
            this.$container.css("height", this.hContainer + "px");

            var yBtnLeft  = (this.hContainer / 2) - (this.hBtnLeft / 2);
            var yBtnRight = (this.hContainer / 2) - (this.hBtnRight / 2);
            var xBtnRight = screenWidth - this.wBtnRight;

            if (anim) {

                this.$btnRight.stop().animate({
                    left: xBtnRight + "px",
                    top: yBtnRight + "px"
                }, this.delay, this.fx);

                this.$btnLeft.stop().animate({"top": yBtnLeft + "px"}, this.delay, this.fx);
            }
            else {
                this.$btnRight.css("left", xBtnRight + "px");
                this.$btnLeft.css("top", yBtnLeft + "px");
                this.$btnRight.css("top", yBtnRight + "px");
            }

        },
        setDimensions: function(anim) {

            var image    = this.images.get(this.picture.get('id'));
            var $element = image.$element;
            var width    = image.get("width");
            var height   = image.get("height");

            if(height == 0) {
                throw "division by zero";
            }

            var r = width / height;
            var w = this.hContainer * r;
            var h = this.hContainer;
            var m = (w / 2) * -1;

            //console.log("-> calculate : " + w + "/" + h + " marg : "+m);

            if (anim) {
                $element.stop().animate({
                    width : w + "px",
                    height : h + "px"
                }, this.delay, this.fx);
            }
            else {
                $element.css("width", w + "px").css("height", h + "px");
            }
        },
        preload: function() {

            var i = 0,
                image,
                picture,
                total = this.collection.size(),
                index = this.collection.curr;

            this.maxLoaded = (this.collection.size() >= this.maxLoaded) ? this.maxLoaded : this.collection.size();
            this.images = new Collections.Images();

            for (var i = 0; i < this.maxLoaded; i++) {

                if (index < 0) {
                    index = total - 1;
                }
                else if (index >= total) {
                    index = 0;
                }

                picture = this.collection.at(index);
                image = new Models.Image({
                    id  : picture.get('id'),
                    width  : picture.get('optimized_width'),
                    height : picture.get('optimized_height'),
                    title   : picture.get('title'),
                    src    : picture.getPictureSrc("full")
                });

                if (this.picture.get('id') === picture.get('id')) {
                    image.on("loaded", this.onImageLoaded, this);
                }
                this.images.add(image);

                index = index + (1 * this.direction);
            }
            this.images.loadImages();

            return this;
        },
        onImageLoaded: function() {

            this.manager.set({
                "Loader" : false
            });
            this.$content.fadeOut(400, this.render.bind(this));
        },
        onResize: function() {
            this.setPosition(true);
            this.setDimensions(true);
        },
        onMove: function() {
            this.calculateInactive();
        },
        calculateInactive: function() {

            var delay = 6000;

            if (this.timer !== null) {
                clearTimeout(this.timer);
                this.timer = null;
            }

            this.timer = window.setTimeout(this.hideElement.bind(this), delay);
            this.showElements();
        },
        showElements: function() {

            if (this.hidden) {

                this.hidden = false;
                this.$btnLeft.fadeIn();
                this.$btnRight.fadeIn();
                this.$selector.fadeIn();

                this.$action.fadeIn();

                if (this.manager.get('Informations')) {
                    this.$information.fadeIn();
                }
                if (this.manager.get('Popup')) {
                    this.$popup.fadeIn();
                }

            }
        },
        hideElement: function() {

            if (!this.hidden) {

                this.hidden = true;
                this.$btnLeft.fadeOut();
                this.$btnRight.fadeOut();
                this.$selector.fadeOut();
                this.$action.fadeOut();

                if (this.manager.get('Informations')) {
                    this.$information.fadeOut();
                }
                if (this.manager.get('Popup')) {
                    this.$popup.fadeOut();
                }
            }
        },
        goPrev: function(e) {

            if (e !== undefined) {

                e.stopPropagation();

                this.calculateInactive();
                this.autorun = false;
                this.player.stop();
            }

            this.direction = -1;
            this.router.navigate(this.prevUrl, {trigger: true});
        },
        goNext: function(e) {

            if (e !== undefined) {

                e.stopPropagation();

                this.calculateInactive();
                this.autorun = false;
                this.player.stop();
            }

            this.direction = 1;
            this.router.navigate(this.nextUrl, {trigger: true});
        }
    });

    Views.Popup = Views.Base.extend({

        el: "#popup",

        events: {},


        initialize: function() {

            this.wLi = 58;

            this.$container = this.$("#popup-container");

            //console.log(this.width);
            //console.log(this.height);

        },
        render: function() {

            this.$container.html(this.img);

            return this;
        },
        show: function() {

            //console.log(this.model.toJSON());

            //this.target = target;
            this.load();

            //this.source =
            //this.render();
            this.$el.show();
            this.setPosition();

        },
        hide: function() {
            this.$container.empty();
            this.$el.hide();
        },
        setPosition: function() {

            this.width  = this.$el.outerWidth(true);
            this.height = this.$el.outerHeight(true);

            var $target = $(this.target);
            var offset = $target.offset();
            var x = (offset.left + (this.wLi / 2)) - (this.width / 2);
            var y = offset.top - this.height;

            this.$el.css("top", y + "px").css("left", x+ "px");
        },
        load: function(picture) {

            this.img = new Image();
            this.img.src = this.model.getPictureSrc("selector");
            //img.id = "selector-img-picture-" + picture.get('id');

            $(this.img).on("load", this.onLoad.bind(this));
        },
        onLoad: function(e) {
            this.render();
        }
    });

    Views.Selector = Views.Base.extend({

        el: "#selector",

        itemTmpl : $("#selector_item_tmpl"),

        events: {
            "click .selector-btn-picture" : "navigate",
            "click #selector-prev-link" : "clickPrev",
            "click #selector-next-link" : "clickNext"
        },

        initialize: function() {

            this.config  = EP.config;
            this.router  = App.Router;
            this.utils   = App.Utils;
            this.popup   = AppViews.Popup;
            this.manager = AppViews.Manager;

            this.$mask     = this.$("#selector-mask");
            this.$list     = this.$("#selector-item-container");
            this.$btnLeft  = this.$("#selector-prev-link");
            this.$btnRight = this.$("#selector-next-link");

            this.wLi      = 58;
            this.maxThumb = 0;
            this.store    = [];

            this.collection.on("model_reset", this.onResetPicture, this);

            $(window).on("resize", this.onResize.bind(this));
        },
        show: function() {

            this.$el.show();
            this.setPosition();
            this.render();

            return this;
        },
        setPosition: function() {

            var screenWidth  = $(window).width();
            var screenHeight = $(window).height();

            var wBtnLeft  = this.$btnLeft.outerWidth(true);
            var wBtnRight = this.$btnRight.outerWidth(true);

            this.wMask    = screenWidth - wBtnLeft - wBtnRight - 40;
            this.maxThumb = Math.floor(this.wMask / this.wLi);

            this.wMask = this.maxThumb * this.wLi;
            this.$mask.css("width", this.wMask + "px");
            this.$el.css("width", screenWidth + "px");

            this.wList = (this.wLi * this.collection.size());

            this.left  = 0;
            this.right = (- this.wList + this.wMask);
        },
        onResetPicture: function(picture, pictures) {

            this.picture = picture;
            this.setActive();
        },
        render: function() {

            var template = _.template(this.itemTmpl.html());
            var json, pos = 0;

            this.collection.map(function(picture) {

                json = picture.toJSON();
                this.$list.append(template(json));

                this.store.push({
                    id     : parseInt(json.id, 10),
                    pos    : pos,
                    pix    : picture,
                    loaded : false
                });

                pos = pos + this.wLi;
            }, this);

            this.$links = this.$("a.selector-btn-picture");
            this.$list.css("width", this.wList + "px");

            return this;
        },
        navigate: function(e) {

            this.manager.set({"Popup" : false});

            var target = e.currentTarget;
            var linkId = target.id;
            var picture_id = parseInt(linkId.substr(linkId.lastIndexOf("-") + 1), 10);
            var url = "gallery/album/" + this.model.get('id') + "/photo/" + picture_id;



            this.router.navigate(url, {trigger:true});
        },
        openPopup: function(e) {

            e.stopPropagation();

            var target = e.currentTarget;
            var linkId = target.id;
            var picture_id = parseInt(linkId.substr(linkId.lastIndexOf("-") + 1), 10);

            var picture = this.collection.get(picture_id);

            //console.log(picture.toJSON());


            this.popup.model = picture;
            this.popup.target = target;
            this.manager.set({"Popup" : true});

            //this.popup.show(target);
        },
        closePopup: function(e) {

            e.stopPropagation();
            //console.log(this.popup);

            this.manager.set({"Popup" : false});

        },
        clickPrev: function() {

            var currPos = this.$list.position().left;

            this.nextPos = (currPos) + (this.maxThumb * this.wLi);
            this.move();
        },
        clickNext: function() {

            var currPos = this.$list.position().left;

            this.nextPos = currPos + (this.maxThumb * this.wLi * -1);
            this.move();
        },
        move: function() {

            var vLeft  = "visible", vRight = "visible";

            this.minPos = (this.$links.length > this.maxThumb) ? ((this.$links.length * this.wLi) - (this.maxThumb * this.wLi)) * -1 : 0;
            this.maxPos = 0;

            if (this.nextPos >= this.maxPos) {
                this.nextPos = this.maxPos;
                vLeft = "hidden";
            }
            if (this.nextPos <= this.minPos) {
                this.nextPos = this.minPos;
                vRight = "hidden";
            }

            this.$btnLeft.css("visibility", vLeft);
            this.$btnRight.css("visibility", vRight);
            this.$list.off().animate({"left" : this.nextPos}, 400, this.onMove.bind(this));
        },
        onMove: function() {

            var first = (this.nextPos / this.wLi) * -1;
            var last = first + this.maxThumb;

            _.each(this.store, function(obj, index) {
                if (index >= first && index < last) {
                    if (!obj.loaded) {
                        this.load(obj.pix);
                    }
                }
            }, this);
        },
        load: function(picture) {

            var img = new Image();
            img.src = picture.getPictureSrc("thumb");
            img.id = "selector-img-picture-" + picture.get('id');

            $(img).on("load", this.onLoad.bind(this));

            $(img).on("mouseover", this.openPopup.bind(this));
            $(img).on("mouseout", this.closePopup.bind(this));

        },
        onLoad: function(e) {

            var img = e.currentTarget;
            var imgId = img.id;
            var picture_id = parseInt(imgId.substr(imgId.lastIndexOf("-") + 1), 10);
            var datas = this.getDatas(picture_id);
            datas.loaded = true;

            if (!isNaN(picture_id)) {
                this.$("#selector-btn-picture-" + picture_id).html(img);
            }
        },
        setActive: function() {

            var element = this.$("#selector-btn-picture-" + this.picture.get('id'));

            if (this.$links === undefined) {
            //	return;
            }

            this.$links.removeClass("selected");
            element.addClass("selected");

            this.datas = this.getDatas(parseInt(this.picture.get('id'), 10));
            this.nextPos = this.datas.pos * -1;
            this.move();
        },
        onResize: function() {
            this.setPosition();
        },
        getDatas: function(id) {
            var datas;
            datas = _.find(this.store, function(obj) {
                if (obj.id === id) {
                    return obj;
                }
            }, this);
            return datas;
        }
    });

    Views.Player = Views.Base.extend({

        el : "#player",

        events: {
            "click #btn-player" : "toogle"
        },

        initialize: function() {

            this.timer    = null;
            this.duration = 8000;
            this.autorun  = true;

            this.$btn = this.$("#btn-player");
        },
        render: function() {

            if (this.autorun) {
                this.$btn.addClass("playing");
            }
            else {
                this.$btn.removeClass("playing");
            }
            return this;
        },
        toogle: function() {

            if (!this.autorun) {
                this.play();
            }
            else {
                this.stop();
            }
        },
        play: function() {
            this.autorun = true;
            this.gallery.autorun = true;
            this.render();
            this.timer = setTimeout(this.change.bind(this), this.duration);
        },
        stop: function() {
            this.autorun = false;
            this.gallery.autorun = false;
            this.render();
            this.killTimer();
        },
        change: function() {
            this.gallery.goNext();
        },
        killTimer: function() {
            if (this.timer !== null) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        }
    });

    Views.Actions = Views.Base.extend({

        el : "#actions",

        events : {
            "click #btn-infos" : "displayInfos",
            "click #btn-users" : "goToPrivate"
        },

        initialize: function() {

            this.manager = AppViews.Manager;
            this.config  = EP.config;

            this.fx       = "swing";
            this.delay    = 400;
            this.decalage = 120;
            this.width    = this.$el.outerWidth(true);
            this.height   = this.$el.outerHeight(true);

            this.render();

            $(window).on("resize", this.onResize.bind(this));
        },
        render: function() {

            this.setPosition();
            return this;
        },
        show: function() {
            this.$el.fadeIn();
            return this;
        },
        hide: function() {
            this.$el.fadeOut();
            return this;
        },
        setPosition: function(anim) {

            var screenWidth  = $(window).width();
            var screenHeight = $(window).height();
            var posX = screenWidth - this.width - this.decalage;

            if (anim) {
                this.$el.animate({
                    left : posX + "px"
                }, this.delay, this.fx);
            }
            else {
                this.$el.css("left", posX + "px");
            }
        },
        displayInfos: function() {

            console.log("displayInfos");

            if (this.manager.get("Informations")) {
                this.manager.set({
                    "Informations" : false
                });
            }
            else {
                this.manager.set({
                    "Informations" : true
                });
            }
        },
        goToPrivate: function() {
            var url = this.config.protocol + "//" + this.config.host + this.config.base + "#grid/album/" + this.model.get('id');
            document.location.href = url;
        },
        onResetPicture: function(picture, pictures) {
            this.picture = picture;
        },
        onResize: function() {
            this.setPosition(true);
        }
    });

    Views.Informations = Views.Base.extend({

        el : "#gallery-informations-container",

        infosTmpl : $("#informations_tmpl"),

        events : {},

        initialize: function() {

            this.$container = this.$("#gallery-informations");
            this.collection.on("model_reset", this.onResetPicture, this);
        },
        render: function() {

            var album     = this.model.toJSON();
            var picture   = this.picture.toJSON();
            picture.index = this.collection.curr + 1;

            var template = _.template(this.infosTmpl.html());
            this.$container.html(template({
                album : album,
                picture : picture
            }));

            return this;
        },
        show: function() {
            this.$el.fadeIn();
            return this;
        },
        hide: function() {
            this.$el.fadeOut();
            return this;
        },
        onResetPicture: function(picture, pictures) {
            this.picture = picture;
            this.render();
        }
    });

}(window, document, EasyPics));