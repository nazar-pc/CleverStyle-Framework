/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Variable is used to handle few notify elements in concurrent conditions, allows them to show/hide sequentially
in_progress	= false
Polymer.cs.behaviors.cs-notify = [
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
		# Put notify opening into stack of functions to call
		setTimeout(@_show.bind(@), 0)
	_tap : (e) !->
		if e.target == @$.content || e.target == @$.icon
			@_hide()
	_transitionend : !->
		if !@show
			@parentNode?.removeChild(@)
		if @timeout
			setTimeout(@_hide.bind(@), @timeout * 1000)
			@timeout = 0
		if in_progress == @
			in_progress	= false
	_show : !->
		if !in_progress
			in_progress = @
		else
			setTimeout(@_show.bind(@), 100)
			return
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
		if !in_progress
			in_progress = @
		else
			setTimeout(@_hide.bind(@), 100)
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
