###*
 * @package   Picturefill
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License
###
Polymer(
	ready	: ->
		setTimeout (=>
			picturefill(
				elements : [@querySelector('img')]
			)
		), 0
)
