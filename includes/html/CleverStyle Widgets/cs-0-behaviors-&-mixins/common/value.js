// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer.cs.behaviors.value = {
    listeners: {
      change: '_changed',
      input: '_changed'
    },
    _changed: function(){
      this.fire('value-changed');
    }
  };
}).call(this);
