/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
# Chain of promises is used to show/hide notifications sequentially, variable contains always latest promise in chain and is constantly updated
# We'll start doing something only when Web Components are ready
promise					= new Promise(csw.behaviors.ready._when_ready)
csw.behaviors.cs-notify	= [
	csw.behaviors.ready
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
	attached : !->
		@last_node = @parentNode
		if !@parentNode.matches('html')
			document.documentElement.appendChild(@)
			return
		if !@bottom && !@top
			@top	= true
		setTimeout(@~_show)
	_tap : (e) !->
		if e.target == @$.content || e.target == @$.icon
			@_hide()
	_show : !->
		promise := promise.then ~>
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
			new Promise (resolve) !~>
				setTimeout (!~>
					if @timeout
						setTimeout(@~_hide, @timeout * 1000)
					resolve()
				), @_transition_duration()
	_hide : !->
		promise := promise.then ~>
			@show				= false
			interesting_margin	= if @top then 'marginTop' else 'marginBottom'
			@_for_similar (child) !~>
				if (
					parseFloat(child.style[interesting_margin] || 0) > parseFloat(@style[interesting_margin] || 0)
				)
					child._unshift()
			@fire('hide')
			new Promise (resolve) !~>
				setTimeout (!~>
					@parentNode?.removeChild(@)
					resolve()
				), @_transition_duration()
	_for_similar : (callback) !->
		tagName	= @tagName
		bottom	= @bottom
		left	= @left
		right	= @right
		top		= @top
		for child in document.querySelector('html').children
			if (
				child != @ &&
				child.is == @is &&
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
	_transition_duration : ->
		transition-duration = getComputedStyle(@).transition-duration
		if transition-duration.substr(-2) == 'ms'
			parseFloat(transition-duration)
		else
			transition-duration	= parseFloat(transition-duration) * 1000
]
