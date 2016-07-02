/**
 * @package   Shop
 * @shipping-type  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$ <-! require(['jquery'], _)
<-! $
L			= cs.Language('shop_')
make_modal	= (title, action) ->
	$(cs.ui.simple_modal("""<form is="cs-form">
		<h3 class="cs-text-center">#title</h3>
		<label>#{L.title}</label>
		<input is="cs-input-text" name="title" required>
		<label>#{L.price}</label>
		<input is="cs-input-text" name="price" type="number" min="0" value="0" required>
		<label>#{L.phone_needed}</label>
		<div>
			<label is="cs-label-button"><input type="radio" name="phone_needed" value="1" checked> #{L.yes}</label>
			<label is="cs-label-button"><input type="radio" name="phone_needed" value="0"> #{L.no}</label>
		</div>
		<label>#{L.address_needed}</label>
		<div>
			<label is="cs-label-button"><input type="radio" name="address_needed" value="1" checked> #{L.yes}</label>
			<label is="cs-label-button"><input type="radio" name="address_needed" value="0"> #{L.no}</label>
		</div>
		<label>#{L.description}</label>
		<textarea is="cs-textarea" autosize name="description"></textarea>
		<br>
		<button is="cs-button" primary type="submit">#action</button>
	</form>"""))
$('html')
	.on('mousedown', '.cs-shop-shipping-type-add', !->
		$modal = make_modal(L.shipping_type_addition, L.add)
		$modal.find('form').submit ->
			cs.api('post api/Shop/admin/shipping_types', @)
				.then -> cs.ui.alert(L.added_successfully)
				.then(location~reload)
			false
	)
	.on('mousedown', '.cs-shop-shipping-type-edit', !->
		id = $(@).data('id')
		cs.api("get api/Shop/admin/shipping_types/#id").then (shipping_type) !->
			$modal = make_modal(L.shipping_type_edition, L.edit)
			$modal.find('form').submit ->
				cs.api("put api/Shop/admin/shipping_types/#id", @)
					.then -> cs.ui.alert(L.edited_successfully)
					.then(location~reload)
				false
			$modal.find('[name=title]').val(shipping_type.title)
			$modal.find('[name=price]').val(shipping_type.price)
			$modal.find("[name=phone_needed][value=#{shipping_type.phone_needed}]").prop('checked', true)
			$modal.find("[name=address_needed][value=#{shipping_type.address_needed}]").prop('checked', true)
			$modal.find('[name=description]').val(shipping_type.description)
	)
	.on('mousedown', '.cs-shop-shipping-type-delete', !->
		id = $(@).data('id')
		cs.ui.confirm(L.sure_want_to_delete)
			.then -> cs.api("delete api/Shop/admin/shipping_types/#id")
			.then -> cs.ui.alert(L.deleted_successfully)
			.then(location~reload)
	)
