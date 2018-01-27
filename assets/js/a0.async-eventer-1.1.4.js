/*
 Copyright (c) 2017, Nazar Mokrynskyi
 @license   MIT License, see license.txt
*/
(function(){function b(){if(!(this instanceof b))return new b;this.a={}}b.prototype={on:function(a,c){var d;a&&c&&((d=this.a)[a]||(d[a]=[])).push(c);return this},off:function(a,c){(a=this.a[a])&&a.splice(a.indexOf(c),c?1:a.length);return this},once:function(a,c){var d=this;if(a&&c){var b=function(){if(!b.a)return b.a=!0,d.b(a,b),c.apply(null,arguments)};this.c(a,b)}return this},fire:function(a,b){var d=Promise.resolve();var c=arguments;(this.a[a]||[]).forEach(function(a){d=d.then(function(){var b=
a.call.apply(a,c);return!1===b?Promise.reject():b})});d["catch"](function(a){a instanceof Error&&console.error(a)});return d}};"function"===typeof define&&define.amd?define(function(){return b}):"object"===typeof exports?module.exports=b:this.async_eventer=b}).call(this);
