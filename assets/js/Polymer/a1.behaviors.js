// Generated by LiveScript 1.5.0
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  var normalize_bool, styles, ref$;
  normalize_bool = function(value){
    return value && value !== '0';
  };
  styles = {};
  ((ref$ = window.cs || (window.cs = {})).Polymer || (ref$.Polymer = {})).behaviors = {
    /**
     * Simplified access to translations in Polymer elements
     *
     * This will add `Language` property (and its short alias `L`) alongside with `__()` method which can be used for formatted translations
     * Also might be called as function with prefix
     */
    Language: function(){
      var x$;
      function Language(prefix){
        var x$;
        x$ = Object.create(Language);
        x$.properties = x$.properties;
        x$._set_language_properties = x$._set_language_properties;
        x$._compute__ = x$._compute__;
        x$.ready = function(){
          var this$ = this;
          cs.Language(prefix).ready().then(function(L){
            this$._set_language_properties(L);
          });
        };
        return x$;
      }
      x$ = Language;
      x$.properties = {
        Language: {
          readOnly: true,
          type: Object
        },
        L: {
          readOnly: true,
          type: Object
        },
        __: {
          type: Function,
          computed: '_compute__(Language)'
        }
      };
      x$.ready = function(){
        var this$ = this;
        cs.Language.ready().then(function(L){
          this$._set_language_properties(L);
        });
      };
      x$._compute__ = function(L){
        return function(key){
          if (arguments.length === 1) {
            return L.get(key);
          } else {
            return L.format.apply(L, arguments);
          }
        };
      };
      x$._set_language_properties = function(L){
        this._setLanguage(L);
        this._setL(L);
      };
      return x$;
    }()
    /**
     * Some useful computed bindings methods
     */,
    computed_bindings: {
      'if': function(condition, then_, otherwise, prefix, postfix){
        otherwise == null && (otherwise = '');
        prefix == null && (prefix = '');
        postfix == null && (postfix = '');
        return '' + prefix + (condition ? then_ : otherwise) + postfix;
      },
      join: function(array, separator){
        return array.join(separator !== undefined ? separator : ',');
      },
      concat: function(thing, another){
        return Array.prototype.slice.call(arguments).join('');
      },
      and: function(x, y, z){
        return !!Array.prototype.slice.call(arguments).reduce(function(x, y){
          return normalize_bool(x) && normalize_bool(y);
        });
      },
      or: function(x, y, z){
        return !!Array.prototype.slice.call(arguments).reduce(function(x, y){
          return normalize_bool(x) || normalize_bool(y);
        });
      },
      xor: function(x, y, z){
        return Array.prototype.slice.call(arguments).reduce(function(x, y){
          return !normalize_bool(x) !== !normalize_bool(y);
        });
      },
      equal: function(a, b, strict){
        if (strict) {
          return a === b;
        } else {
          return a == b;
        }
      }
    },
    inject_light_styles: {
      attached: function(){
        var head, custom_style_element, this$ = this;
        if (this._styles_dom_module_added) {
          return;
        }
        this._styles_dom_module_added = true;
        if (!styles[this._styles_dom_module]) {
          head = document.querySelector('head');
          head.insertAdjacentHTML('beforeend', "<custom-style><style include=\"" + this._styles_dom_module + "\"></style></custom-style>");
          custom_style_element = head.lastElementChild;
          cs.ui.ready.then(function(){
            Polymer.updateStyles();
            styles[this$._styles_dom_module] = custom_style_element.firstElementChild.textContent.split(':not([style-scope]):not(.style-scope)').join('');
            head.removeChild(custom_style_element);
            this$.insertAdjacentHTML('beforeend', "<style>" + styles[this$._styles_dom_module] + "</style>");
          });
        } else {
          this.insertAdjacentHTML('beforeend', "<style>" + styles[this._styles_dom_module] + "</style>");
        }
      }
    }
  };
}).call(this);
