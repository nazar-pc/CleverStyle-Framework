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
		can_edit	: false
	ready			: !->
		@jsonld	= JSON.parse(@querySelector('script').innerHTML)
		Promise.all([
			$.ajax(
				url		: 'api/Blogs'
				type	: 'get_settings'
			)
			if cs.is_user then $.getJSON('api/System/profile') else {id : 1}
		]).then ([@settings, profile]) !~>
			@can_edit	= @settings.admin_edit || @jsonld.user == profile.id
	sections_path : (index) ->
		@jsonld.sections_paths[index]
	tags_path : (index) ->
		@jsonld.tags_paths[index]
)
