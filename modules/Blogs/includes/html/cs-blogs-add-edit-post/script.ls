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
		post			: Object
		original_title	: String
		sections		: Array
		settings		: Object
		local_tags		: String
		user_id			: Number
	observers		: [
		'_add_close_tab_handler(post.*, local_tags)'
	]
	ready : !->
		if !@id
			@id = false
		Promise.all([
			if @id then cs.api('get api/Blogs/posts/' + @id) else {
				title		: ''
				path		: ''
				content		: ''
				sections	: [0]
				tags		: []
			}
			cs.api('get				api/Blogs/sections')
			cs.api('get_settings	api/Blogs')
			cs.api('get				api/System/profile')
		]).then ([@post, sections, settings, profile]) !~>
			@original_title				= @post.title
			if @post.title
				@$.title.textContent	= @post.title
			@local_tags					= @post.tags.join(', ')
			@sections					= @_prepare_sections(sections)
			settings.multiple_sections	= settings.max_sections > 1
			@settings					= settings
			@user_id					= profile.id
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
		sections_parents	= {}
		for section in sections
			sections_parents[section.parent] = true
		for section in sections
			section.disabled	= sections_parents[section.id]
		sections
	_prepare : !->
		delete @post.path
		@set('post.title', @$.title.textContent)
		@set(
			'post.tags'
			@local_tags.split(',').map ->
				String(it).trim()
		)
	_preview : !->
		close_tab_handler_installed = @_close_tab_handler_installed
		@_prepare()
		if !close_tab_handler_installed && @_close_tab_handler_installed
			@_remove_close_tab_handler()
		cs.api('preview api/Blogs/posts', @post).then (result) !~>
			result					= JSON.stringify(result)
			@$.preview.innerHTML	= """
			<article is="cs-blogs-post" preview>
				<script type="application/ld+json">#result</script>
			</article>
			"""
			# TODO: Add nice scroll without jQuery and much code:)
			document.querySelector('html').scrollTop	= @$.preview.offsetTop
	_publish : !->
		@_prepare()
		@post.mode	= 'publish'
		method		= if @id then 'put' else 'post'
		suffix		= if @id then '/' + @id else ''
		cs.api("#method api/Blogs/posts#suffix", @post).then (result) !~>
			@_remove_close_tab_handler()
			location.href = result.url
	_to_drafts : !->
		@_prepare()
		@post.mode	= 'draft'
		method		= if @id then 'put' else 'post'
		suffix		= if @id then '/' + @id else ''
		cs.api("#method api/Blogs/posts#suffix", @post).then (result) !~>
			@_remove_close_tab_handler()
			location.href = result.url
	_delete : !->
		cs.ui.confirm(L.sure_to_delete_post(@original_title))
			.then ~> cs.api('delete api/Blogs/posts/' + @post.id)
			.then !~>
				@_remove_close_tab_handler()
				location.href = 'Blogs'
	_cancel : !->
		@_remove_close_tab_handler()
		history.go(-1)
)
