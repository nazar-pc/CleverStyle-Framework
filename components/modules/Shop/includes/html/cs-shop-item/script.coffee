###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	L		: cs.Language
	ready	: ->
		@header_title	= @querySelector('h1').innerHTML
		$this			= $(@)
		@item_id		= $this.data('id')
		@price			= sprintf(cs.shop.settings.price_formatting, $this.data('price'))
		@in_stock		= $this.data('in_stock')
		attributes		= $(@querySelector('#attributes'))
		if attributes.length
			@show_attributes	= true
			attributes
				.find('table')
					.addClass('uk-table uk-table-hover')
					.find('td:first-of-type')
						.addClass('uk-text-bold')
		$(@$.images)
			.append(
				$(@querySelectorAll('#videos > a')).each ->
					$this	= $(@)
					if $this.children('img')
						$this.attr('data-video', 'true')
			)
			.append(
				@querySelectorAll('#images > img')
			)
			.fotorama(
				allowfullscreen	: 'native'
				controlsonstart	: false
				fit				: 'contain'
				keyboard		: true
				nav				: 'thumbs'
				ratio			: 4/3
				trackpad		: true
				width			: '100%'
			)
)
