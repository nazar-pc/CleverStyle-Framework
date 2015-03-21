###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
do (L = cs.Language) ->
	Polymer(
		publish	:
			description	: ''
			address		: ''
			amount		: 0
			label		: ''
		ready	: ->
			$ =>
				@description	= JSON.parse(@description)
				@text			= L.blockchain_payment_scan_or_transfer(@amount, @address)
				$(@$.qr).qrcode('bitcoin:' + @address + '?amount=' + @amount + '&label=' + @label)
	);
