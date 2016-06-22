/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'			: 'cs-blogs-post'
	'extends'		: 'article'
	behaviors		: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties		:
		can_edit		: false
		can_delete		: false
		show_comments	: false
		preview			: false
		url_prefix		: if location.pathname.indexOf('/' + cs.Language.clang) == 0 then '/' + cs.Language.clang else ''
	ready			: !->
		@jsonld	= JSON.parse(@children[0].innerHTML)
		cs.api([
			'get_settings	api/Blogs'
			'get			api/System/profile'
		]).then ([@settings, profile]) !~>
			@can_edit		= !@preview && (@settings.admin_edit || @jsonld.user == profile.id)
			@can_delete		= !@preview && @settings.admin_edit
			@show_comments	= !@preview && @settings.comments_enabled
	sections_path : (index) ->
		@jsonld.sections_paths[index]
	tags_path : (index) ->
		@jsonld.tags_paths[index]
	_delete : !->
		cs.ui.confirm(@L.sure_to_delete_post(@jsonld.title))
			.then ~> cs.api('delete api/Blogs/posts/' + @jsonld.id)
			.then (result) !~>
				@_remove_close_tab_handler()
				location.href = 'Blogs'
)
