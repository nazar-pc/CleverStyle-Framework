// Generated by LiveScript 1.5.0
/**
 * @package  Composer
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
(function(){
  Polymer({
    is: 'cs-composer',
    behaviors: [cs.Polymer.behaviors.Language('composer_')],
    properties: {
      action: String,
      force: Boolean,
      'package': String,
      status: String
    },
    ready: function(){
      var method, data, this$ = this;
      cs.Event.once('admin/Composer/canceled', function(){
        this$._stop_updates = true;
      });
      method = this.action === 'uninstall' ? 'delete' : 'post';
      data = {
        name: this['package'],
        force: this.force
      };
      Promise.all([cs.api(method + " api/Composer", data), cs.Language.ready()]).then(function(arg$){
        var result;
        result = arg$[0];
        this$._stop_updates = true;
        this$._save_scroll_position();
        this$.status = (function(){
          switch (result.code) {
          case 0:
            return this.L.updated_successfully;
          case 1:
            return this.L.update_failed;
          case 2:
            return this.L.dependencies_conflict;
          }
        }.call(this$));
        if (result.description) {
          this$.$.result.innerHTML = result.description;
          this$._restore_scroll_position();
        }
        if (!result.code) {
          setTimeout(function(){
            cs.Event.fire('admin/Composer/updated');
          }, 2000);
        }
      })['catch'](function(){
        this$._stop_updates = true;
        this$.status = this$.L.update_failed;
      });
      setTimeout(bind$(this, '_update_progress'), 1000);
    },
    _update_progress: function(){
      var this$ = this;
      cs.api('get api/Composer').then(function(data){
        if (this$._stop_updates) {
          return;
        }
        this$._save_scroll_position();
        this$.$.result.innerHTML = data;
        this$._restore_scroll_position();
        setTimeout(bind$(this$, '_update_progress'), 1000);
      });
    },
    _save_scroll_position: function(){
      var ref$;
      this._scroll_after = false;
      if ((ref$ = this.parentElement.$) != null && ref$.content) {
        this._scroll_after = this.parentElement.$.content.scrollHeight - this.parentElement.$.content.offsetHeight === this.parentElement.$.content.scrollTop;
      }
    },
    _restore_scroll_position: function(){
      if (this._scroll_after) {
        this.parentElement.$.content.scrollTop = this.parentElement.$.content.scrollHeight - this.parentElement.$.content.offsetHeight;
      }
    }
  });
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
