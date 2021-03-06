// Generated by LiveScript 1.5.0
/**
 * @package  Blogs
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
(function(){
  Polymer({
    is: 'cs-blogs-add-edit-post',
    behaviors: [cs.Polymer.behaviors.computed_bindings, cs.Polymer.behaviors.Language('blogs_')],
    properties: {
      post: Object,
      original_title: String,
      sections: Array,
      settings: Object,
      local_tags: String,
      user_id: Number
    },
    observers: ['_add_close_tab_handler(post.*, local_tags)'],
    ready: function(){
      var this$ = this;
      if (!this.id) {
        this.id = false;
      }
      Promise.all([
        this.id
          ? cs.api('get api/Blogs/posts/' + this.id)
          : {
            title: '',
            path: '',
            content: '',
            sections: [0],
            tags: []
          }, cs.api('get				api/Blogs/sections'), cs.api('get_settings	api/Blogs'), cs.api('get				api/System/profile')
      ]).then(function(arg$){
        var sections, settings, profile;
        this$.post = arg$[0], sections = arg$[1], settings = arg$[2], profile = arg$[3];
        this$.original_title = this$.post.title;
        if (this$.post.title) {
          this$.$.title.textContent = this$.post.title;
        }
        this$.local_tags = this$.post.tags.join(', ');
        this$.sections = this$._prepare_sections(sections);
        settings.multiple_sections = settings.max_sections > 1;
        this$.settings = settings;
        this$.user_id = profile.id;
      });
      this.$.title.addEventListener('keydown', bind$(this, '_add_close_tab_handler'));
      this._close_tab_handler = this._close_tab_handler.bind(this);
    },
    _add_close_tab_handler: function(post_change, local_tags){
      if (!post_change || !local_tags) {
        return;
      }
      if (this.user_id && !this._close_tab_handler_installed && !window.onbeforeunload) {
        addEventListener('beforeunload', this._close_tab_handler);
        this._close_tab_handler_installed = true;
      }
    },
    _remove_close_tab_handler: function(){
      if (this._close_tab_handler_installed) {
        removeEventListener('beforeunload', this._close_tab_handler);
        this._close_tab_handler_installed = false;
      }
    },
    _close_tab_handler: function(e){
      return e.returnValue = this.L.sure_want_to_exit.toString();
    },
    _prepare_sections: function(sections){
      var sections_parents, i$, len$, section;
      sections_parents = {};
      for (i$ = 0, len$ = sections.length; i$ < len$; ++i$) {
        section = sections[i$];
        sections_parents[section.parent] = true;
      }
      for (i$ = 0, len$ = sections.length; i$ < len$; ++i$) {
        section = sections[i$];
        section.disabled = sections_parents[section.id];
      }
      return sections;
    },
    _prepare: function(){
      delete this.post.path;
      this.set('post.title', this.$.title.textContent);
      this.set('post.tags', this.local_tags.split(',').map(function(it){
        return String(it).trim();
      }));
    },
    _preview: function(){
      var close_tab_handler_installed, this$ = this;
      close_tab_handler_installed = this._close_tab_handler_installed;
      this._prepare();
      if (!close_tab_handler_installed && this._close_tab_handler_installed) {
        this._remove_close_tab_handler();
      }
      cs.api('preview api/Blogs/posts', this.post).then(function(result){
        result = JSON.stringify(result);
        this$.$.preview.innerHTML = "<cs-blogs-post preview>\n	<script type=\"application/ld+json\">" + result + "</script>\n</cs-blogs-post>";
        document.querySelector('html').scrollTop = this$.$.preview.offsetTop;
      });
    },
    _publish: function(){
      var method, suffix, this$ = this;
      this._prepare();
      this.post.mode = 'publish';
      method = this.id ? 'put' : 'post';
      suffix = this.id ? '/' + this.id : '';
      cs.api(method + " api/Blogs/posts" + suffix, this.post).then(function(result){
        this$._remove_close_tab_handler();
        location.href = result.url;
      });
    },
    _to_drafts: function(){
      var method, suffix, this$ = this;
      this._prepare();
      this.post.mode = 'draft';
      method = this.id ? 'put' : 'post';
      suffix = this.id ? '/' + this.id : '';
      cs.api(method + " api/Blogs/posts" + suffix, this.post).then(function(result){
        this$._remove_close_tab_handler();
        location.href = result.url;
      });
    },
    _delete: function(){
      var this$ = this;
      cs.ui.confirm(this.L.sure_to_delete_post(this.original_title)).then(function(){
        return cs.api('delete api/Blogs/posts/' + this$.post.id);
      }).then(function(){
        this$._remove_close_tab_handler();
        location.href = 'Blogs';
      });
    },
    _cancel: function(){
      this._remove_close_tab_handler();
      history.go(-1);
    }
  });
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
