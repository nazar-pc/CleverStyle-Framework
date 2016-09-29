/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-static-pages-admin-categories-add-edit-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		category		: Object
		original_title	: String
		categories		: Array
	ready : !->
		Promise.all([
			if @id then cs.api('get api/Static_pages/admin/categories/' + @id) else {
				title		: ''
				path		: ''
				parent		: 0
			}
			cs.api('get api/Static_pages/admin/categories')
		]).then ([@category, categories]) !~>
			@original_title	= @category.title
			@categories		= categories
	_save : !->
		method	= if @id then 'put' else 'post'
		suffix	= if @id then '/' + @id else ''
		cs.api("#method api/Static_pages/admin/categories#suffix", @category).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
