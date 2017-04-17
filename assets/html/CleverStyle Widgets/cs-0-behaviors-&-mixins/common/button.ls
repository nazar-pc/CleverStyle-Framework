/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.{}cs.{}behaviors.button =
	properties	:
		active		:
			notify				: true
			reflectToAttribute	: true
			type				: Boolean
		icon-before	:
			type				: String
		icon-after	:
			type				: String
		icon		:
			type				: String
		primary		:
			reflectToAttribute	: true
			type				: Boolean
	ready : !->
		icon-before	= @icon-before || @icon
		if icon-before
			@firstElementChild.insertAdjacentHTML(
				'afterbegin'
				"""<cs-icon icon="#icon-before" mono></cs-icon> """
			)
		if @icon-after
			@firstElementChild.insertAdjacentHTML(
				'beforeend'
				"""<cs-icon icon="#{@icon-after}" mono></cs-icon> """
			)
