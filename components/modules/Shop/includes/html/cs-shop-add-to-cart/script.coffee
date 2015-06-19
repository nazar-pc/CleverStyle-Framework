###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
do (cart = cs.shop.cart, L = cs.Language) ->
	Polymer(
		in_cart		: 0
		L			: L
		domReady	: ->
			$this		= $(@)
			@item_id	= $this.data('id')
			@in_cart	= cart.get(@item_id)
			UIkit.tooltip(
				@$.in_cart
				animation	: true
				delay		: 200
			)
		add			: ->
			@in_cart	= cart.add(@item_id)
	);
