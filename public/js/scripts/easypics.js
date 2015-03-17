window.log = function f(){ log.history = log.history || []; log.history.push(arguments); if(this.console) { var args = arguments, newarr; args.callee = args.callee.caller; newarr = [].slice.call(args); if (typeof console.log === 'object') log.apply.call(console.log, console, newarr); else console.log.apply(console, newarr);}};

// make it safe to use console.log always
(function(a){function b(){}for(var c="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),d;!!(d=c.pop());){a[d]=a[d]||b;}})
(function(){try{console.log();return window.console;}catch(a){return (window.console={});}}());

var EasyPics = {};

(function(window, document, EP, undefined) {

    "use strict";

    EP.App = {};
    EP.Routers = {};
    EP.Collections = {};
    EP.Models = {};
    EP.Views = {};

    var APP = EP.App;

    APP.Collections = {};
    APP.Models = {};
    APP.Views = {};
    APP.Router = {};
    
    APP.Utils = (function(window, document, undefined) {

        function arrayToObject(elements) {

            var obj = {},
                key,
                value;

            _.map(elements, function(elem) {

                key = elem.name;
                value = elem.value;

                if (obj[key] !== undefined) {

                    if (!_.isArray(obj[key])) {
                        obj[key] = [obj[key]];
                    }
                    obj[key].push(value);
                }
                else {
                    obj[key] = value;
                }
            });
            return obj;
        }

        function truncateString(str, len) {

            if (str === undefined) {
                return str;
            }
            if (len === undefined) {
                len = 10;
            }

            if (str.length > len) {
                return str.substr(0, len - 3) + "...";
            }
            return str;
        }

        return {
            arrayToObject : arrayToObject,
            truncateString : truncateString
        };

    }(window, document));

    EP.config = (function(window, document, undefined) {

        var debug = true;
        var protocol = window.location.protocol;
        var host = window.location.host;
        var pathname = window.location.pathname;
        var base = "/";

        var filters = {
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
        };


        /*
        original_width			// int
        original_height			// int
        is_color				// int
        with_flash				// int

        aperture				// varchar
        aperture_value			// float

        exposure				// varchar
        exposuretime			// float
        exposuremode			// int
        exposuremode_value		// varchar
        exposureprogram			// int
        exposureprogram_value	// varchar

        lightsource				// int
        lightsource_value		// varchar

        focallength				// varchar

        whitebalance			// int
        whitebalance_value		// varchar

        iso_speed_rating		// int

        manufacturer 			// string
        model 					// string

        */

        var scripts = {

            commons : [
                'public/js/libs/jquery.js',
                'public/js/libs/jquery.json.js',
                'public/js/libs/underscore.js',
                'public/js/libs/backbone.js',
                'public/js/scripts/base.js',
                'public/js/scripts/models.js',
                'public/js/scripts/collections.js',
                'public/js/scripts/routers.js'
            ],
            Private : [
                'public/js/scripts/interface.js',
                'public/js/scripts/helpers.js',
                'public/js/scripts/panels.js',
                'public/js/scripts/home.js',
                'public/js/scripts/grid.js',
                'public/js/scripts/search.js'
            ],
            Public : [
                'public/js/scripts/gallery.js'
            ],
            Settings : [
                'public/js/scripts/interface.js',
                'public/js/scripts/helpers.js'
            ]

        };

        return {
            scripts : scripts,
            filters : filters,
            protocol : protocol,
            pathname : pathname,
            host : host,
            base : base,
            debug : debug
        };

    }(window, document));

    EP.init = function() {

        var router,
            scripts,
            base,
            js_version;

        function init() {

            if (EP.config === undefined) {
                throw "EasyPics.config is undefined";
            }

            js_version = EP.config.js_version;
            base       = EP.config.base;
            router     = EP.config.router;
            scripts    = EP.config.scripts;

            if (scripts === undefined || scripts[router] === undefined) {
                throw "Invalid scripts specified - Cannot load scripts !!!";
            }

            loadScripts(scripts.commons);
            loadScripts(scripts[router]);
            head.ready(start);
        }

        function loadScripts(scripts) {

            var i = 0,
                l = scripts.length,
                script;

            for (i = 0; i < l; i++) {

                script = scripts[i];
                script = base + script.replace(/\.js$/, js_version + ".js");
                head.js(script);
            }
        }

        function start() {

            if (router === undefined || EP.Routers[router] === undefined) {
                throw "Invalid router specified - Cannot launch application !!!";
            }

            var app = new EP.Routers[router]();
//			Backbone.history.start();
            Backbone.history.start({
                /*pushState: true,*/
                root: "/easypics/"
            });

            // see : http://documentcloud.github.com/backbone/#History
        }

        init();
    };

}(window, document, EasyPics));