/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-icon = [
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	observers			: [
		'_icon_changed(icon, flipX, flipY, mono, rotate, spin, spinStep)'
	]
	properties			:
		icon			:
			reflectToAttribute	: true
			type				: String
		flipX			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		flipY			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		mono			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		rotate			:
			reflectToAttribute	: true
			type				: Number
			value				: false
		spin			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		spinStep		:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
	ready : !->
		@scopeSubtree(@$.content, true)
		@hidden = @icon == undefined
	_icon_changed : (icon, flipX, flipY, mono, rotate, spin, spinStep) !->
		if !icon
			@hidden = true
			return
		else if @hidden
			@hidden = false
		content			= ''
		icons			= icon.split(' ')
		multiple_icons	= icons.length > 1
		for icon, index in icons
			icon_class	= "fa fa-#icon"
			if flipX
				icon_class	+= ' fa-flip-horizontal'
			if flipY
				icon_class	+= ' fa-flip-vertical'
			if mono
				icon_class	+= ' fa-fw'
			if rotate
				icon_class	+= " fa-rotate-#rotate"
			if spin
				icon_class	+= ' fa-spin'
			if spinStep
				icon_class	+= ' fa-pulse'
			if multiple_icons
				icon_class	+= if index then ' fa-stack-1x fa-inverse' else ' fa-stack-2x'
			content += """<i class="#icon_class"></i>"""
		if multiple_icons
			content	= """<span class="fa-stack">#content</span>"""
		@$.content.innerHTML = content
]
