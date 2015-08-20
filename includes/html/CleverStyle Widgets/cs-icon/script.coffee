###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'				: 'cs-icon'
	properties			:
		icon			: String
		flipX			: false
		flipY			: false
		mono			: false
		rotate			: 0
		spin			: false
		spinStep		: false
		multiple_icons	:
			computed	: '_multiple_icons(icon)'
			type		: Boolean
	ready				: ->
		@textContent = ''
	_multiple_icons		: (icon) ->
		icon.split(' ').length > 1
	icon_class			: (icon, flipX, flipY, mono, rotate, spin, spinStep) ->
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
				icon_class.push(`index ? 'fa-stack-1x fa-inverse' : 'fa-stack-2x'`)
			icon_class.join(' ')
		ret = if multiple_icons
			icons_classes
		else
			icons_classes[0]
		ret
)
