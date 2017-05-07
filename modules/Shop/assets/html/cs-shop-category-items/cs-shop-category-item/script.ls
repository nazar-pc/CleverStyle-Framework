/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-category-item'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	properties	:
		href		: String
		price		: String
		item_id		: Number
		in_stock	: String
	ready : !->
		img											= @querySelector('#img')
		@$.img.src									= img.src
		@$.img.title								= img.title
		@shadowRoot.querySelector('h1').innerHTML	= @querySelector('h1').innerHTML
		@$.description.innerHTML					= @querySelector('#description').innerHTML
		@set('href', @querySelector('#link').href)
		{sprintf} <~! require(['sprintf-js'])
		@set('price', sprintf(cs.shop.settings.price_formatting, @price))
);
