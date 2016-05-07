// Generated by LiveScript 1.4.0
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer({
    'is': 'cs-blogs-post',
    'extends': 'article',
    behaviors: [cs.Polymer.behaviors.Language('blogs_')],
    properties: {
      can_edit: false
    },
    ready: function(){
      var this$ = this;
      this.jsonld = JSON.parse(this.children[0].innerHTML);
      Promise.all([
        $.ajax({
          url: 'api/Blogs',
          type: 'get_settings'
        }), cs.is_user
          ? $.getJSON('api/System/profile')
          : {
            id: 1
          }
      ]).then(function(arg$){
        var profile;
        this$.settings = arg$[0], profile = arg$[1];
        this$.can_edit = this$.settings.admin_edit || this$.jsonld.user === profile.id;
      });
    },
    sections_path: function(index){
      return this.jsonld.sections_paths[index];
    },
    tags_path: function(index){
      return this.jsonld.tags_paths[index];
    }
  });
}).call(this);
