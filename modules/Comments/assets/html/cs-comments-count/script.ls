/**
 * @package  Comments
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-comments-count'
	properties	:
		module	: String
		item	: Number
		count	: Number
	ready : !->
		cs.api('get api/Comments/count', {@module, @item}).then (@count) !~>
)
