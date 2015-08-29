###*
 * @package    CleverStyle CMS
 * @subpackage DarkEnergy theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
document.querySelector('.cs-mobile-menu').addEventListener('click', ->
	$(@).nextAll().toggleClass('uk-display-block')
)
