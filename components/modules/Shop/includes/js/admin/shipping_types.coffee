###*
 * @package   Shop
 * @shipping-type  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	make_modal = (title, action) ->
		$.cs.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p>
				#{L.shop_title}: <input name="title">
			</p>
			<p>
				#{L.shop_price}: <input name="price" type="number" min="0">
			</p>
			<p>
				#{L.shop_phone_needed}:
				<label><input type="radio" name="phone_needed" value="1" checked> #{L.yes}</label>
				<label><input type="radio" name="phone_needed" value="0"> #{L.no}</label>
			</p>
			<p>
				#{L.shop_address_needed}:
				<label><input type="radio" name="address_needed" value="1" checked> #{L.yes}</label>
				<label><input type="radio" name="address_needed" value="0"> #{L.no}</label>
			</p>
			<p>
				#{L.shop_description}: <textarea name="description"></textarea>
			</p>
			<p>
				<button class="uk-button" type="submit">#{action}</button>
			</p>
		</form>""")
	$('html')
		.on('mousedown', '.cs-shop-shipping-type-add', ->
			modal = make_modal(L.shop_shipping_type_addition, L.shop_add)
			modal.find('form').submit ->
				$.ajax(
					url     : 'api/Shop/admin/shipping_types'
					type    : 'post'
					data    : $(@).serialize()
					success : ->
						alert(L.shop_added_successfully)
						location.reload()
				)
				return false
		)
		.on('mousedown', '.cs-shop-shipping-type-edit', ->
			id = $(@).data('id')
			$.getJSON("api/Shop/admin/shipping_types/#{id}", (shipping_type) ->
				modal = make_modal(L.shop_shipping_type_edition, L.shop_edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/shipping_types/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_edited_successfully)
							location.reload()
					)
					return false
				modal.find('[name=title]').val(shipping_type.title)
				modal.find('[name=price]').val(shipping_type.price)
				modal.find("[name=phone_needed][value=#{shipping_type.phone_needed}]").prop('checked', true)
				modal.find("[name=address_needed][value=#{shipping_type.address_needed}]").prop('checked', true)
				modal.find('[name=description]').val(shipping_type.description)
			)
		)
		.on('mousedown', '.cs-shop-shipping-type-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/shipping_types/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
