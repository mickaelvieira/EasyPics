(function(e){function h(){i||(i=!0,f(s,function(a){g(a)}))}function t(a,b){var c=e.createElement("script");c.type="text/"+(a.type||"javascript");c.src=a.src||a;c.async=!1;c.onreadystatechange=c.onload=function(){var a=c.readyState;!b.done&&(!a||/loaded|complete/.test(a))&&(b.done=!0,b())};(e.body||r).appendChild(c)}function l(a,b){if(a.state==m)return b&&b();if(a.state==u)return d.ready(a.name,b);if(a.state==v)return a.onpreload.push(function(){l(a,b)});a.state=u;t(a.url,function(){a.state=m;b&&b();
f(j[a.name],function(a){g(a)});n()&&i&&f(j.ALL,function(a){g(a)})})}function z(a){a.state===void 0&&(a.state=v,a.onpreload=[],t({src:a.url,type:"cache"},function(){A(a)}))}function A(a){a.state=B;f(a.onpreload,function(a){a.call()})}function n(a){var a=a||o,b,c;for(c in a){if(a.hasOwnProperty(c)&&a[c].state!=m)return!1;b=!0}return b}function k(a){return Object.prototype.toString.call(a)=="[object Function]"}function f(a,b){if(a){typeof a=="object"&&(a=[].slice.call(a));for(var c=0;c<a.length;c++)b.call(a,
a[c],c)}}function p(a){var b;if(typeof a=="object")for(var c in a)a[c]&&(b={name:c,url:a[c]});else b=a.split("/"),b=b[b.length-1],c=b.indexOf("?"),b={name:c!=-1?b.substring(0,c):b,url:a};return(a=o[b.name])&&a.url===b.url?a:o[b.name]=b}function g(a){a._done||(a(),a._done=1)}var r=e.documentElement,w,i,s=[],x=[],j={},o={},q=e.createElement("script").async===!0||"MozAppearance"in e.documentElement.style||window.opera,y=window.head_conf&&head_conf.head||"head",d=window[y]=window[y]||function(){d.ready.apply(null,
arguments)},B=1,v=2,u=3,m=4;q?d.js=function(){var a=arguments,b=a[a.length-1],c={};k(b)||(b=null);f(a,function(d,e){d!=b&&(d=p(d),c[d.name]=d,l(d,b&&e==a.length-2?function(){n(c)&&g(b)}:null))});return d}:d.js=function(){var a=arguments,b=[].slice.call(a,1),c=b[0];if(!w)return x.push(function(){d.js.apply(null,a)}),d;c?(f(b,function(a){k(a)||z(p(a))}),l(p(a[0]),k(c)?c:function(){d.js.apply(null,b)})):l(p(a[0]));return d};d.ready=function(a,b){if(a==e)return i?g(b):s.push(b),d;k(a)&&(b=a,a="ALL");
if(typeof a!="string"||!k(b))return d;var c=o[a];if(c&&c.state==m||a=="ALL"&&n()&&i)return g(b),d;(c=j[a])?c.push(b):j[a]=[b];return d};d.ready(e,function(){n()&&f(j.ALL,function(a){g(a)});d.feature&&d.feature("domloaded",!0)});if(window.addEventListener)e.addEventListener("DOMContentLoaded",h,!1),window.addEventListener("load",h,!1);else if(window.attachEvent){e.attachEvent("onreadystatechange",function(){e.readyState==="complete"&&h()});q=1;try{q=window.frameElement}catch(C){}!q&&r.doScroll&&function(){try{r.doScroll("left"),
h()}catch(a){setTimeout(arguments.callee,1)}}();window.attachEvent("onload",h)}!e.readyState&&e.addEventListener&&(e.readyState="loading",e.addEventListener("DOMContentLoaded",handler=function(){e.removeEventListener("DOMContentLoaded",handler,!1);e.readyState="complete"},!1));setTimeout(function(){w=!0;f(x,function(a){a()})},300)})(document);
