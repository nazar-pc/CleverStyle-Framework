/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Chain of promises is used to show/hide notifications sequentially, variable contains always latest promise in chain and is constantly updated
promise		= Promise.resolve()
Polymer.cs.behaviors.cs-notify = [
	Polymer.cs.behaviors.ready
	Polymer.cs.behaviors.this
	properties	:
		bottom	:
			reflectToAttribute	: true
			type				: Boolean
		content	: String
		error	:
			reflectToAttribute	: true
			type				: Boolean
		left	:
			reflectToAttribute	: true
			type				: Boolean
		noIcon	:
			reflectToAttribute	: true
			type				: Boolean
		right	:
			reflectToAttribute	: true
			type				: Boolean
		show	:
			reflectToAttribute	: true
			type				: Boolean
		success	:
			reflectToAttribute	: true
			type				: Boolean
		timeout	: Number
		top		:
			reflectToAttribute	: true
			type				: Boolean
		warning	:
			reflectToAttribute	: true
			type				: Boolean
	listeners	:
		'content.tap'	: '_tap'
		transitionend	: '_transitionend'
	attached : !->
		@last_node = @parentNode
		if @parentNode.tagName != 'HTML'
			document.documentElement.appendChild(@)
			return
		if !@bottom && !@top
			@top	= true
		# Hack for the case when notification is present in original page source code, so we need to wait until all Web Components are loaded
		@_when_ready(@~_schedule_show)
	_schedule_show : !->
		promise := promise.then ~>
			new Promise (resolve) !~>
				@resolve = resolve
				@_show()
	_schedule_hide : !->
		promise := promise.then ~>
			new Promise (resolve) !~>
				@resolve = resolve
				@_hide()
	_tap : (e) !->
		if e.target == @$.content || e.target == @$.icon
			@_schedule_hide()
	_transitionend : !->
		@resolve?()
		if !@show
			@parentNode?.removeChild(@)
			return
		if @timeout
			setTimeout(@~_schedule_hide, @timeout * 1000)
			@timeout = 0
	_show : !->
		if @content
			@innerHTML = @content
		@_for_similar (child) !~>
			interesting_margin	= if @top then 'marginTop' else 'marginBottom'
			if (
				child != @ &&
				parseFloat(child.style[interesting_margin] || 0) >= parseFloat(@style[interesting_margin] || 0)
			)
				child._shift()
		@_initialized	= true
		@show			= true
		@fire('show')
	_hide : !->
		if !@show
			return
		@show				= false
		interesting_margin	= if @top then 'marginTop' else 'marginBottom'
		@_for_similar (child) !~>
			if (
				parseFloat(child.style[interesting_margin] || 0) > parseFloat(@style[interesting_margin] || 0)
			)
				child._unshift()
		@fire('hide')
	_for_similar : (callback) !->
		tagName	= @tagName
		bottom	= @bottom
		left	= @left
		right	= @right
		top		= @top
		for child in @parentNode.children
			if (
				child.show &&
				child.tagName == tagName &&
				child.bottom == bottom &&
				child.left == left &&
				child.right == right &&
				child.top == top
			)
				callback(child)
	_shift : !->
		style = getComputedStyle(@)
		if @top
			@style.marginTop = parseFloat(style.marginTop) + parseFloat(style.height) + 'px'
		else
			@style.marginBottom = parseFloat(style.marginBottom) + parseFloat(style.height) + 'px'
	_unshift : !->
		style = getComputedStyle(@)
		if @top
			@style.marginTop = parseFloat(style.marginTop) - parseFloat(style.height) + 'px'
		else
			@style.marginBottom = parseFloat(style.marginBottom) - parseFloat(style.height) + 'px'
]
