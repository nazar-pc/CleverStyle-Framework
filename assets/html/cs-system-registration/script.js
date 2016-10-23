// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer({
    is: 'cs-system-registration',
    behaviors: [cs.Polymer.behaviors.Language('system_profile_')],
    attached: function(){
      this.$.email.focus();
    },
    _registration: function(e){
      e.preventDefault();
      cs.registration(this.$.email.value);
    }
  });
}).call(this);