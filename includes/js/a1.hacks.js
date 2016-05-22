// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  var content_loaded, ref$, value, date;
  content_loaded = function(){
    if (document.body.hasAttribute('unresolved')) {
      document.body.setAttribute('unresolved-transition', '');
    }
  };
  switch (document.readyState) {
  case 'complete':
  case 'interactive':
    content_loaded();
    break;
  default:
    addEventListener('DOMContentLoaded', content_loaded);
  }
  document.addEventListener('WebComponentsReady', function(){
    Polymer.updateStyles();
    if (document.body.hasAttribute('cs-unresolved')) {
      document.body.setAttribute('cs-unresolved-transition', '');
      document.body.removeAttribute('cs-unresolved');
    }
    setTimeout(function(){
      document.body.removeAttribute('unresolved-transition');
      document.body.removeAttribute('cs-unresolved-transition');
    }, 250);
  });
  if (!((ref$ = window.WebComponents) != null && ref$.flags)) {
    addEventListener('load', function(){
      setTimeout(function(){
        document.dispatchEvent(new CustomEvent('WebComponentsReady', {
          bubbles: true
        }));
      });
    });
  }
  if (document.cookie.indexOf('shadow_dom=1') === -1) {
    value = 'registerElement' in document && 'import' in document.createElement('link') && 'content' in document.createElement('template') ? 1 : 0;
    date = new Date();
    date.setTime(date.getTime() + 30 * 24 * 3600 * 1000);
    document.cookie = ("shadow_dom=" + value + "; path=/; expires=") + date.toGMTString();
  }
}).call(this);
