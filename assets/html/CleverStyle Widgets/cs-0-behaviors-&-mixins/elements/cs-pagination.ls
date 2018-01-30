/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.cs-pagination = [
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
	_pages_list : (page, pages) ->
		if !page || !pages
			return
		pages_list	= []
		render_one	= @_render_one.bind(@, pages_list, page)
		# 11 pages max - show all
		if pages <= 11
			for i from 1 to pages
				render_one(i)
		# Otherwise if current page is 1 to 5
		else if page <= 6
			for i from 1 to 7
				render_one(i)
			render_one(0, '...')
			for i from (pages - 2) to pages
				render_one(i)
		# Otherwise if current page is (pages - 4) to pages
		else if page >= pages - 5
			for i from 1 to 3
				render_one(i)
			render_one(0, '...')
			for i from (pages - 6) to pages
				render_one(i)
		# Otherwise
		else
			for i from 1 to 2
				render_one(i)
			render_one(0, '...')
			for i from (page - 2) to (page + 2)
				render_one(i)
			render_one(0, '...')
			for i from (pages - 1) to pages
				render_one(i)
		pages_list[0].first						= true
		pages_list[pages_list.length - 1].last	= true
		pages_list
	_render_one : (pages_list, page, i, text) !->
		pages_list.push(
			text		: text || i
			active		: i == page
			disabled	: !i
		)
	_set_page : (e) !->
		@page = e.model.item.text
	next : !->
		if @page < @pages
			@page++
	prev : !->
		if @page > 1
			@page--
]
