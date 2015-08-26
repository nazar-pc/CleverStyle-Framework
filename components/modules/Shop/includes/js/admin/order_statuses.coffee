###*
 * @package   Shop
 * @order-status  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	make_modal = (types, title, action) ->
		types	=
			for index, type of types
				"""<option value="#{index}">#{type}</option>"""
		types	= types.join('')
		modal	= $(cs.ui.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p>
				#{L.shop_title}: <input name="title" required>
			</p>
			<p>
				#{L.shop_color}: <input name="color"><input type="color">
			</p>
			<p>
				#{L.shop_order_status_type}: <select is="cs-select" name="type" required>#{types}</select>
			</p>
			<p>
				#{L.shop_send_update_status_email}:
				<label><input type="radio" name="send_update_status_email" value="1" checked> #{L.yes}</label>
				<label><input type="radio" name="send_update_status_email" value="0"> #{L.no}</label>
			</p>
			<p>
				#{L.shop_comment_used_in_email}: <textarea is="cs-textarea" autosize name="comment"></textarea>
			</p>
			<p>
				<button class="uk-button" type="submit">#{action}</button>
			</p>
		</form>"""))
		modal.find('[type=color]').change ->
			$this	= $(@)
			$this.prev().val(
				$this.val()
			)
		modal
	$('html')
		.on('mousedown', '.cs-shop-order-status-add', ->
			$.getJSON('api/Shop/admin/order_statuses/types', (types) ->
				modal = make_modal(types, L.shop_order_status_addition, L.shop_add)
				modal.find('form').submit ->
					$.ajax(
						url     : 'api/Shop/admin/order_statuses'
						type    : 'post'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_added_successfully)
							location.reload()
					)
					return false
			)
		)
		.on('mousedown', '.cs-shop-order-status-edit', ->
			id = $(@).data('id')
			$.when(
				$.getJSON('api/Shop/admin/order_statuses/types')
				$.getJSON("api/Shop/admin/order_statuses/#{id}")
			).done (types, type) ->
				modal = make_modal(types[0], L.shop_order_status_edition, L.shop_edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/order_statuses/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_edited_successfully)
							location.reload()
					)
					return false
				type	= type[0]
				modal.find('[name=title]').val(type.title)
				modal.find('[name=color]').val(type.color)
				modal.find('[type=color]').val(type.color)
				modal.find('[name=type]').val(type.type)
				modal.find("[name=send_update_status_email][value=#{type.send_update_status_email}]").prop('checked', true)
				modal.find('[name=comment]').val(type.comment)
		)
		.on('mousedown', '.cs-shop-order-status-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/order_statuses/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
