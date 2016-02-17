###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'			: 'cs-blogs-post'
	'extends'		: 'article'
	behaviors		: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties		:
		can_edit			: false
		can_delete			: false
		comments_enabled	: false
	ready			: ->
		@jsonld					= JSON.parse(@querySelector('script').innerHTML)
		@$.content.innerHTML	= @jsonld.content
	sections_path	: (index) ->
		@jsonld.sections_paths[index]
	tags_path		: (index) ->
		@jsonld.tags_paths[index]
);
