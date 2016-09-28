/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-static-pages-admin-categories-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		categories	: Array
	ready : !->
		@_reload_categories()
	_reload_categories : !->
		cs.api('get api/Static_pages/admin/categories').then (categories) !~>
			@set('categories', categories)
)
