// Generated by LiveScript 1.5.0
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
(function(){
  Polymer({
    is: 'cs-system-admin-blocks-form',
    behaviors: [cs.Polymer.behaviors.computed_bindings, cs.Polymer.behaviors.Language('system_admin_blocks_')],
    properties: {
      block: Object,
      index: Number,
      types: Array
    },
    observers: ['_type_change(block.type)'],
    ready: function(){
      var this$ = this;
      cs.api('types api/System/admin/blocks').then(function(types){
        this$.types = types;
        if (this$.index) {
          return cs.api('get api/System/admin/blocks/' + this$.index).then(function(block){
            block.type = block.type || this$.types[0];
            if (block.active === undefined) {
              block.active = 1;
            } else {
              block.active = parseInt(block.active);
            }
            return block;
          });
        } else {
          return {
            active: 1,
            content: '',
            type: 'html',
            expire: {
              state: 0
            }
          };
        }
      }).then(function(block){
        this$.block = block;
      });
    },
    _type_change: function(type){
      var x$;
      if (type === undefined) {
        return;
      }
      x$ = this.shadowRoot;
      x$.querySelector('.html').hidden = type !== 'html';
      x$.querySelector('.raw_html').hidden = type !== 'raw_html';
    },
    _save: function(){
      var index, method, suffix, this$ = this;
      index = this.index;
      method = index ? 'put' : 'post';
      suffix = index ? "/" + index : '';
      cs.api(method + " api/System/admin/blocks" + suffix, this.block).then(function(){
        cs.ui.notify(this$.L.changes_saved, 'success', 5);
      });
    }
  });
}).call(this);
