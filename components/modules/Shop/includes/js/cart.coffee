###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
cs.shop.cart	= do ->
	items_storage	=
		get	: ->
			if data = cs.getcookie('shop_cart_items')
				JSON.parse(data)
			else
				{}
		set	: (items) ->
			cs.setcookie(
				'shop_cart_items'
				JSON.stringify(items)
				new Date / 1000 + 86400	# +24h from now
			)
	get_items	= ->
		items_storage.get()
	get_item	= (id) ->
		items[id] || 0
	add_item	= (id) ->
		if items[id]
			++items[id]
		else
			items[id]	= 1
		items_storage.set(items)
		items[id]
	set_item	= (id, units) ->
		items[id]	= units
		items_storage.set(items)
	del_item	= (id) ->
		delete items[id]
		items_storage.set(items)
	clean		= ->
		cs.setcookie('shop_cart_items')
		items	= {}
	items	= get_items()
	return {
		get_all	: get_items
		get		: get_item
		add		: add_item
		set		: set_item
		del		: del_item
		clean	: clean
	}
