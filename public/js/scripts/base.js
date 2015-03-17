(function(window, document, EP, undefined) {

    "use strict";

    var App         = EP.App;
    var Routers     = EP.Routers;
    var Views       = EP.Views;
    var Models      = EP.Models;
    var Collections = EP.Collections;

    var AppViews    = App.Views;

    Collections.Base = Backbone.Collection.extend({

        curr : -1,
        prev : -1,
        next : -1,

        initialize: function() {},

        setCurrModel : function(model_id) {

            var model = this.findModelById(model_id);
            if (model !== undefined) {
                this.curr = this.indexOf(model);
                this.trigger("model_reset", model, this);
            }
            else {
                this.curr = this.prev = this.next = -1;
            }
            return model;
        },
        getCurrModel : function() {

            if (this.curr < 0) {
                return;
            }
            return this.at(this.curr);
        },
        getPrevModel : function() {

            if (this.curr < 0) {
                return;
            }

            this.prev = this.curr - 1;

            if (this.prev < 0) {
                this.prev = this.size() - 1;
            }
            return this.at(this.prev);
        },
        getNextModel : function() {

            if (this.curr < 0) {
                return;
            }

            this.next = this.curr + 1;

            if (this.next >= this.size()) {
                this.next = 0;
            }
            return this.at(this.next);
        },
        findModelById : function(model_id) {

            var model = this.find(function(model) {
                return (model.get('id') === model_id)
            }, this);
            return model;
        },
        addAll: function() {
            this.trigger("add_all", this);
        },
        modelUpdated: function(model, options) {
            this.trigger("model_update", model, this);
        },
        rebind: function(event, func, scope) {

            if (typeof event !== "string") {
                throw "event is not a string";
            }
            if (typeof func !== "string") {
                throw "func is not a string";
            }

            var callback = scope[func];

            if (typeof callback !== "function") {
                throw "Callback is not a function";
            }
                //console.log(object);
                //console.log(event+" => "+func);

            this.off(event, callback);
            this.on(event, callback, scope);

            return this;
        }
    });

    Models.Base = Backbone.Model.extend({

        rebind: function(event, func, scope) {

            if (typeof event !== "string") {
                throw "event is not a string";
            }
            if (typeof func !== "string") {
                throw "func is not a string";
            }

            var callback = scope[func];

            if (typeof callback !== "function") {
                throw "Callback is not a function";
            }
                //console.log(object);
                //console.log(event+" => "+func);

            this.off(event, callback);
            this.on(event, callback, scope);

            return this;
        },
        update: function() {
            this.trigger("update", this);
        }
    });

    Views.Base = Backbone.View.extend({

        //http://lostechies.com/derickbailey/2011/09/15/zombies-run-managing-page-transitions-in-backbone-apps/

        beforeOpen: function() {},
        onOpen: function() {},
        afterOpen: function() {},

        beforeClose: function() {},
        onClose: function() {},
        afterClose: function() {},


        unrender: function() {
            this.remove();
            this.unbind();
        },

        show: function() {
            this.$el.show();
            return this;
        },
        hide: function() {
            this.$el.hide();
            return this;
        }
    });

    Views.Manager = Backbone.View.extend({

        /*
        TODO :

        Backbone Views Process :

        The Views.Manager calls open/close
        
        beforeOpen -> open -> render -> show -> onShow -> afterOpen
        beforeClose -> close -> Hide -> onHide -> unrender -> afterClose
        
        Can we think about a queue process ? Parallel / Sequence process

        */
        initialize: function() {

            this.store = {};
        },
        set: function(obj) {

            var view, func;

            _.map(obj, function(state, nameView, obj) {

                //console.log("=========> set View ["+nameView+"] - State ["+state+"]");

                //console.log("====> View ["+nameView+"] exists ? " + (AppViews[nameView] !== undefined));

                if (AppViews[nameView] !== undefined) {

                    view = AppViews[nameView];
                    func = (state) ? "show" : "hide";

                    //console.log("====> Function ["+func+"] exists ? " + (typeof view[func] === "function"));

                    if (func === 'open' && typeof view['beforeOpen'] === "function") {
                        view['beforeOpen']();   
                    }
                    if (func === 'close' && typeof view['beforeClose'] === "function") {
                        view['beforeOpen']();   
                    }

                    if (typeof view[func] === "function") {
                        view[func]();
                    }

                    if (func === 'open' && typeof view['afterOpen'] === "function") {
                        view['afterOpen']();   
                    }				
                    if (func === 'close' && typeof view['afterClose'] === "function") {
                        view['afterClose']();   
                    }

                    this.store[nameView] = state;

                }
            }, this);
        },

        open: function(obj) {

            var view;

            if (typeof obj === "string") {
                obj = [obj];
            }

            _.map(obj, function(nameView, key, obj) {
                
                //console.log("=========> set View ["+nameView+"] - State ["+state+"]");
                
                //console.log("====> View ["+nameView+"] exists ? " + (AppViews[nameView] !== undefined));
                
                if (AppViews[nameView] !== undefined) {
                    
                    view = AppViews[nameView];
                    //func = (state) ? "show" : "hide";

                    //console.log("====> Function ["+func+"] exists ? " + (typeof view[func] === "function"));
                    
                    if (typeof view.beforeOpen === "function") {
                        view.beforeOpen();   
                    }                                 
                    if (typeof view.open === "function") {
                        view.open();   
                    }
                    
                    this.store[nameView] = true;
                    
                    if (typeof view.onOpen === "function") {
                        view.onOpen();   
                    }                
                    if (typeof view.afterOpen === "function") {
                        view.afterOpen();   
                    }
                }
            }, this);

        },

        close: function(obj) {
            
            var view;
            
            if (typeof obj === "string") {
                obj = [obj];  
            }
                      
            _.map(obj, function(nameView, key, obj) {
                
                //console.log("=========> set View ["+nameView+"] - State ["+state+"]");
                
                //console.log("====> View ["+nameView+"] exists ? " + (AppViews[nameView] !== undefined));
                
                if (AppViews[nameView] !== undefined) {
                    
                    view = AppViews[nameView];
                   // func = (state) ? "show" : "hide";

                    //console.log("====> Function ["+func+"] exists ? " + (typeof view[func] === "function"));
                    
                    if (typeof view.beforeClose === "function") {
                        view.beforeClose();   
                    }                                 
                    if (typeof view.close === "function") {
                        view.close();   
                    }
                    
                    this.store[nameView] = false;
                    
                    if (typeof view.onClose === "function") {
                        view.onClose();   
                    }                  
                    if (typeof view.afterClose === "function") {
                        view.afterClose();   
                    }
                }
            }, this);
            
        },
        isOpen: function() {

            var opened;
            if (this.store[nameView] !== undefined) {
                opened = this.store[nameView];
            }
            return opened;
        },

        get : function(nameView) {

            if (this.store[nameView] !== undefined) {
                return this.store[nameView];
            }
            return false;
        }
    });

    Views.Loader = Views.Base.extend({

        el : "#loader-overlay",

        initialize: function() {},

        events : {},

        render: function() {
            return this;
        }
    });

}(window, document, EasyPics));