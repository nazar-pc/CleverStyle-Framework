/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-item'
	extends		: 'section'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	properties	:
		header_title	: ''
		item_id			: Number
		price			: String
		in_stock		: Number
	ready : !->
		@set('header_title', @querySelector('h1').textContent)
		require(['jquery', 'sprintf-js']).then ([$, {sprintf}]) !~>
			@set('price', sprintf(cs.shop.settings.price_formatting, @price))
			attributes	= $(@querySelector('#attributes'))
			if attributes.length
				@show_attributes	= true
				attributes
					.find('table')
						.addClass('cs-table')
						.attr('list', '')
						.find('td:first-of-type')
							.addClass('cs-text-bold')
			$(@$.images)
				.append(
					$(@querySelectorAll('#videos > a')).each !->
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
