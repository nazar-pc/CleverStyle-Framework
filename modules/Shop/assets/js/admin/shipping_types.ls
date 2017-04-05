/**
 * @package   Shop
 * @shipping-type  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$ <-! require(['jquery'], _)
<-! $
make_modal	= (L, title, action) ->
	$(cs.ui.simple_modal("""<cs-form><form>
		<h3 class="cs-text-center">#title</h3>
		<label>#{L.title}</label>
		<cs-input-text><input name="title" required></cs-input-text>
		<label>#{L.price}</label>
		<cs-input-text><input name="price" type="number" min="0" value="0" required></cs-input-text>
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
		<cs-button primary><button type="submit">#action</button></cs-button>
	</form></cs-form>"""))
$('html')
	.on('mousedown', '.cs-shop-shipping-type-add', !->
		cs.Language('shop_').ready().then (L) !->
			$modal = make_modal(L, L.shipping_type_addition, L.add)
			$modal.find('form').submit ->
				cs.api('post api/Shop/admin/shipping_types', @)
					.then -> cs.ui.alert(L.added_successfully)
					.then(location~reload)
				false
	)
	.on('mousedown', '.cs-shop-shipping-type-edit', !->
		id = $(@).data('id')
		Promise.all([
			cs.api("get api/Shop/admin/shipping_types/#id")
			cs.Language('shop_').ready()
		]).then ([shipping_type, L]) !->
			$modal = make_modal(L, L.shipping_type_edition, L.edit)
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
		cs.Language('shop_').ready().then (L) !->
			cs.ui.confirm(L.sure_want_to_delete)
				.then -> cs.api("delete api/Shop/admin/shipping_types/#id")
				.then -> cs.ui.alert(L.deleted_successfully)
				.then(location~reload)
	)
