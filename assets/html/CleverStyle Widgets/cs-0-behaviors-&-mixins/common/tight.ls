/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.tight =
	properties :
		tight		: Boolean
	ready : !->
		if @tight && @?nextSibling.nodeType == Node.TEXT_NODE
			@nextSibling.parentNode.removeChild(@nextSibling)
