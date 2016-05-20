// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  var registerFeatures_original;
  registerFeatures_original = Polymer.Base._registerFeatures;
  Polymer.Base._addFeature({
    _registerFeatures: function(){
      (this.behaviors || (this.behaviors = [])).push({
        ready: function(){
          var t, this$ = this;
          t = setTimeout(function(){
            if (this$.is) {
              this$.setAttribute('cs-resolved', '');
            }
          });
          cs.ui.ready.then(function(){
            clearTimeout(t);
            if (this$.is) {
              this$.removeAttribute('cs-resolved');
            }
          });
        }
      });
      registerFeatures_original.call(this);
    }
  });
}).call(this);
