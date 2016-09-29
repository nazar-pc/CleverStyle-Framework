/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-static-pages-admin-pages-add-edit-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		category		: Number
		page			: Object
		original_title	: String
		categories		: Array
	observers	: [
		'_category_changed(category)'
	]
	ready : !->
		Promise.all([
			if @id then cs.api('get api/Static_pages/admin/pages/' + @id) else {
				category	: 0
				title		: ''
				path		: ''
				content		: ''
				interface	: 1
			}
			cs.api('get api/Static_pages/admin/categories')
		]).then ([@page, categories]) !~>
			if @category
				@set('page.category', @category)
			@original_title	= @page.title
			@categories		= categories
	_category_changed : !->
		if @page
			@set('page.category', @category)
	_save : !->
		method	= if @id then 'put' else 'post'
		suffix	= if @id then '/' + @id else ''
		cs.api("#method api/Static_pages/admin/pages#suffix", @page).then (result) !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
