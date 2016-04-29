/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is				: 'cs-blogs-admin-sections-add-edit-form'
	behaviors		: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties		:
		section			: Object
		original_title	: String
		sections		: Array
	ready : !->
		Promise.all([
			if @id then $.getJSON('api/Blogs/admin/sections/' + @id) else {
				title		: ''
				path		: ''
				parent		: 0
			}
			$.getJSON('api/Blogs/admin/sections')
		]).then ([@section, sections]) !~>
			@original_title	= @section.title
			@sections		= sections
	_save : !->
		$.ajax(
			url		: 'api/Blogs/admin/sections' + (if @id then '/' + @id else '')
			data	: @section
			type	: if @id then 'put' else 'post'
			success	: (result) !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
		)
)
