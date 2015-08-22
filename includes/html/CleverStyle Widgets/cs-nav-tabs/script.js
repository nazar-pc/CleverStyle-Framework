// Generated by CoffeeScript 1.9.3

/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

(function() {
  Polymer({
    'is': 'cs-nav-tabs',
    'extends': 'nav',
    properties: {
      active: {
        observer: 'active_changed',
        type: Number
      }
    },
    ready: function() {
      this.addEventListener('tap', this.click.bind(this));
      this.addEventListener('click', this.click.bind(this));
      if (!this.querySelector('button[active]')) {
        return this.active = 0;
      }
    },
    click: function(e) {
      var button, buttons, i, index, len, results;
      buttons = this.querySelectorAll('button');
      results = [];
      for (index = i = 0, len = buttons.length; i < len; index = ++i) {
        button = buttons[index];
        if (button === e.target) {
          results.push(this.active = index);
        } else {
          results.push(void 0);
        }
      }
      return results;
    },
    active_changed: function() {
      var button, buttons, i, index, len, results;
      buttons = this.querySelectorAll('button');
      results = [];
      for (index = i = 0, len = buttons.length; i < len; index = ++i) {
        button = buttons[index];
        if (index === this.active) {
          results.push(button.active = true);
        } else {
          results.push(button.active = false);
        }
      }
      return results;
    }
  });

}).call(this);