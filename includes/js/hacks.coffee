###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
###
 # Fix for jQuery and use of window.getDefaultComputedStyle with Polymer Platform
 # Otherwise throws "TypeError: Argument 1 of Window.getDefaultComputedStyle does not implement interface Element"
 #
 # Since jQuery works perfectly without this method and method was removed from specification we can intentionally remove it here
###
delete window.getDefaultComputedStyle
