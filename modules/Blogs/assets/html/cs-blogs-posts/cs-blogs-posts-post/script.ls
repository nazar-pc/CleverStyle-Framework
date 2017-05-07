/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-blogs-posts-post'
	behaviors	: [
		cs.Polymer.behaviors.computed_bindings
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		post		: {}
		settings	: Object
	ready : !->
		cs.Event.fire('System/content_enhancement', {element: @$.content})
		cs.api('get_settings api/Blogs').then (@settings) !~>
	sections_path : (index) ->
		@post.sections_paths[index]
)
