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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	permissions			: []
	created				: ->
		@permissions	= JSON.parse(@querySelector('script').innerHTML)
	domReady			: ->
		$(@shadowRoot).cs().tooltips_inside()
)
