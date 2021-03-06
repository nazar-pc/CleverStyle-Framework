// Generated by LiveScript 1.5.0
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
(function(){
  Polymer({
    is: 'cs-system-sign-in',
    behaviors: [cs.Polymer.behaviors.Language('system_profile_')],
    ready: function(){
      cs.Event.fire('cs-system-sign-in', this);
    },
    attached: function(){
      setTimeout(bind$(this.$.login, 'focus'));
    },
    _sign_in: function(e){
      e.preventDefault();
      cs.sign_in(this.$.login.value, this.$.password.value);
    },
    _restore_password: function(){
      cs.ui.simple_modal("<cs-system-restore-password-form/>");
    }
  });
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
