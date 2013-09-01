###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
(($) ->
	###*
	 * Tooltip with Twitter Bootstrap
	 *
	 * Required DOM structure * > label > input:radio, plugin may be applied to any of these elements
	###
	$.fn.cs_tooltip	= ->
		this.tooltip
			html		: true
			container	: 'body'
			placement	: 'auto top'
			delay		: 200
)(jQuery)