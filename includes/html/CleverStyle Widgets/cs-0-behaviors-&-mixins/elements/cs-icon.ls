/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-icon = [
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	hostAttributes		:
		hidden	: true
	observers			: [
		'_icon_changed(icon)'
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
		multiple_icons	:
			computed	: '_multiple_icons(icon, flipX, flipY, mono, rotate, spin, spinStep)'
			type		: Array
		single_icon		:
			computed	: '_single_icon(icon, flipX, flipY, mono, rotate, spin, spinStep)'
			type		: String
	_icon_changed : (icon) !->
		if !icon
			@setAttribute('hidden', '')
		else
			@removeAttribute('hidden')
	_multiple_icons : (icon, flipX, flipY, mono, rotate, spin, spinStep) ->
		if icon.split(' ').length > 1
			@icon_class(icon, flipX, flipY, mono, rotate, spin, spinStep)
		else
			[]
	_single_icon : (icon, flipX, flipY, mono, rotate, spin, spinStep) ->
		if icon.split(' ').length > 1
			''
		else
			@icon_class(icon, flipX, flipY, mono, rotate, spin, spinStep)
	icon_class : (icon, flipX, flipY, mono, rotate, spin, spinStep) ->
		icons			= icon.split(' ')
		multiple_icons	= icons.length > 1
		icons_classes	= for icon, index in icons
			icon_class	= ['fa fa-' + icon]
			if flipX
				icon_class.push('fa-flip-horizontal')
			if flipY
				icon_class.push('fa-flip-vertical')
			if mono
				icon_class.push('fa-fw')
			if rotate
				icon_class.push('fa-rotate-' + rotate)
			if spin
				icon_class.push('fa-spin')
			if spinStep
				icon_class.push('fa-pulse')
			if multiple_icons
				icon_class.push(if index then 'fa-stack-1x fa-inverse' else 'fa-stack-2x')
			icon_class.join(' ')
		if multiple_icons
			icons_classes
		else
			icons_classes[0]
]
