/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-blogs-admin-posts-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		posts		: Array
		sections	: Object
	ready : !->
		cs.api('get api/Blogs/admin/sections').then (sections) !~>
			normalized_sections	= {}
			for section in sections
				normalized_sections[section.id]	= section
			@sections	= normalized_sections
			@_reload_posts()
	_reload_posts : !->
		cs.api('get api/Blogs/admin/posts').then (posts) !~>
			for post in posts
				for index, section of post.sections
					post.sections[index] = @sections[section]
			@set('posts', posts)
	_delete : (e) !->
		cs.ui.confirm(@L.sure_to_delete_post(e.model.item.title))
			.then -> cs.api('delete api/Blogs/admin/posts/' + e.model.item.id)
			.then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				@_reload_posts()
)
