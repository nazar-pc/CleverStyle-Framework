###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
do (cart = cs.shop.cart, L = cs.Language) ->
	Polymer(
		'is'		: 'cs-shop-add-to-cart'
		behaviors	: [cs.Polymer.behaviors.Language]
		properties	:
			item_id		: Number
			in_cart		: 0
		ready		: ->
			@set('in_cart', cart.get(@item_id))
		add			: ->
			@set('in_cart', cart.add(@item_id))
	);
