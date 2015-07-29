###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
Polymer(
	L		: L
	publish	:
		group_id	: null
		group_title	: ''
		description	: ''
	save	: ->
		$.ajax(
			url		: 'api/System/admin/groups' + (if @group_id then '/' + @group_id else '')
			type	: if @group_id then 'put' else 'post'
			data	:
				id			: @group_id
				title		: @group_title
				description	: @description
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
)
