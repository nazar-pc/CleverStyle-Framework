/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-shop-item'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	properties	:
		header_title	: ''
		item_id			: Number
		price			: String
		in_stock		: Number
	ready : !->
		@$.description.innerHTML	= @querySelector('#description').innerHTML
		@$.attributes.innerHTML		= @querySelector('#attributes')?.innerHTML
		@set('header_title', @querySelector('h1').textContent)
		require(['jquery', 'sprintf-js']).then ([$, {sprintf}]) !~>
			@set('price', sprintf(cs.shop.settings.price_formatting, @price))
			attributes	= $(@$.attributes)
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
