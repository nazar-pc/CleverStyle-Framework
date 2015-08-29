###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'			: 'cs-nav-pagination'
	'extends'		: 'nav'
	behaviors		: [
		Polymer.cs.behaviors.this
		Polymer.cs.behaviors.tooltip
	]
	hostAttributes	:
		role	: 'group'
	properties		:
		page		:
			notify				: true
			reflectToAttribute	: true
			type				: Number
		pages		:
			reflectToAttribute	: true
			type				: Number
		pages_list	: Array
	observers		: [
		'_refresh(page, pages)'
	]
	_refresh : (page, pages) ->
		if !page || !pages
			return
		pages_list	= []
		render_one	= @_render_one.bind(@, pages_list, page)
		# 11 pages max - show all
		if pages <= 11
			render_one(i) for i in [1..pages]
		# Otherwise if current page is 1..5
		else if page <= 6
			render_one(i) for i in [1..7]
			render_one(0, '...')
			render_one(i) for i in [(pages - 2)..pages]
		# Otherwise if current page is (pages - 4)..pages
		else if page >= pages - 5
			render_one(i) for i in [1..3]
			render_one(0, '...')
			render_one(i) for i in [(pages - 6)..pages]
		# Otherwise
		else
			render_one(i) for i in [1..2]
			render_one(0, '...')
			render_one(i) for i in [(page - 2)..(page + 2)]
			render_one(0, '...')
			render_one(i) for i in [(pages - 1)..pages]
		pages_list[0].first						= true
		pages_list[pages_list.length - 1].last	= true
		@set('pages_list', pages_list)
		return
	_render_one : (pages_list, page, i, text) ->
		pages_list.push(
			text		: text || i
			active		: i == page
			disabled	: !i
		)
	_set_page : (e) ->
		@page = e.model.item.text
		return
	next : ->
		if @page < @pages
			@page++
		return
	prev : ->
		if @page > 1
			@page--
		return
)
