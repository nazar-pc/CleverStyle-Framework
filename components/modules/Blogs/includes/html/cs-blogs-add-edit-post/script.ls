/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language('blogs_')
Polymer(
	is				: 'cs-blogs-add-edit-post'
	behaviors		: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties		:
		add				: false
		post			: Object
		original_title	: String
		sections		: Array
		settings		: Object
		local_tags		: String
		user_id			: Number
		delete_button	: Boolean
	observers		: [
		'_add_close_tab_handler(post.*, local_tags)'
	]
	ready : !->
		@add	= !@id
		Promise.all([
			if @id then $.getJSON('api/Blogs/posts/' + @id) else {
				title		: ''
				path		: ''
				content		: ''
				sections	: []
				tags		: []
			}
			$.getJSON('api/Blogs/sections')
			$.ajax(
				url		: 'api/Blogs'
				type	: 'get_settings'
			)
			if cs.is_user then $.getJSON('api/System/profile') else {id : 1}
		]).then ([@post, sections, settings, profile]) !~>
			@original_title				= @post.title
			@$.title.textContent		= @post.title
			@local_tags					= @post.tags.join(', ')
			@sections					= @_prepare_sections(sections)
			settings.multiple_sections	= settings.max_sections > 1
			@settings					= settings
			@delete_button				= !@add || settings.can_delete_posts
			@user_id					= profile.id
		@$.use_pagebreak.innerHTML	= L.post_use_pagebreak
		@$.title.addEventListener('keydown', @~_add_close_tab_handler)
	_add_close_tab_handler : !->
		# user_id presence means that element was initialized properly
		if @user_id && !@_close_tab_handler_installed && !window.onbeforeunload
			addEventListener('beforeunload', @_close_tab_handler)
			@_close_tab_handler_installed	= true
	_remove_close_tab_handler : !->
		if @_close_tab_handler_installed
			removeEventListener('beforeunload', @_close_tab_handler)
			@_close_tab_handler_installed	= false
	_close_tab_handler : (e) !->
		e.returnValue = L.sure_want_to_exit
	_prepare_sections : (sections) ->
		sections_normalized	= {}
		sections_parents	= []
		for section in sections
			sections_normalized[section.id] = section
			sections_parents.push(section.parent)
		for section in sections
			section.disabled	= sections_parents.indexOf(section.id) != -1
			if section.parent > 0
				section.title	= sections_normalized[section.parent].title + ' :: ' + section.title
		sections.sort (a, b) ->
			a.title > b.title
	_prepare : !->
		delete @post.path
		@set('post.title', @$.title.textContent)
		@set(
			'post.tags'
			@local_tags.split(',').map ->
				String(it).trim()
		)
	_preview : !->
		@_prepare()
		$.ajax(
			url		: 'api/Blogs/posts'
			data	: @post
			type	: 'preview'
			success	: (result) !~>
				@$.preview.innerHTML	= result
				$('html, body')
					.stop()
					.animate(
						scrollTop	: @$.preview.offsetTop
						500
					)
		)
	_publish : !->
		@_prepare()
		@post.mode	= 'publish'
		$.ajax(
			url		: 'api/Blogs/posts' + (if @id then '/' + @id else '')
			data	: @post
			type	: if @id then 'put' else 'post'
			success	: (result) !~>
				@_remove_close_tab_handler()
				location.href = result.url
		)
	_to_drafts : !->
		@_prepare()
		@post.mode	= 'draft'
		$.ajax(
			url		: 'api/Blogs/posts' + (if @id then '/' + @id else '')
			data	: @post
			type	: if @id then 'put' else 'post'
			success	: (result) !~>
				@_remove_close_tab_handler()
				location.href = result.url
		)
	_delete : !->
		cs.ui.confirm(
			L.sure_to_delete_post(@original_title)
			!~>
				$.ajax(
					url		: 'api/Blogs/posts/' + @post.id
					type	: 'delete'
					success	: (result) !~>
						@_remove_close_tab_handler()
						location.href = 'Blogs'
				)
		)
	_cancel : !->
		@_remove_close_tab_handler()
		history.go(-1)
)
