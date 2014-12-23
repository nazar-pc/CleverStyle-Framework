###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
Polymer(
	ready : ->
		@header_title	= @querySelector('h1').innerHTML
		$(@$.images).fotorama(
			data			:
				Array::map.call(@querySelectorAll('#images > img'), (img) ->
					img : img.src
				)
			allowfullscreen	: 'native'
			controlsonstart	: false
			fit				: 'scaledown'
			keyboard		: true
			'loop'			: true
			nav				: 'thumbs'
			ratio			: 4/3
			trackpad		: true
		)
		$this			= $(@)
		@price			= $this.data('price')
		@in_stock		= $this.data('in_stock')
		attributes		= $(@querySelector('#attributes'))
		if attributes.length
			@show_attributes	= true
			attributes
				.find('table')
					.addClass('uk-table uk-table-hover')
					.find('td:first-of-type')
						.addClass('uk-text-bold')
);
