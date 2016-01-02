###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-shop-category-item'
	'extends'	: 'article'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		href		: String
		price		: String
		item_id		: Number
		in_stock	: String
	ready		: ->
		do (img = @querySelector('#img')) =>
			@$.img.src		= img.src
			@$.img.title	= img.title
		@set('href', @querySelector('#link').href)
		@set('price', sprintf(cs.shop.settings.price_formatting, @price))
);
