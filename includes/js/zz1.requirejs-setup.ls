/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# First of all let's define bundled libraries so that they can be used as AMD modules
define('jquery', -> jQuery)
define('htm5sortable', -> sortable)
define('sprintf-js', -> {sprintf, vsprintf})
# jsSHA loaded on demand only
requirejs.config(
	baseUrl	: '/'
	paths	:
		jssha		: 'includes/js/modules/jsSHA-2.0.2'
		autosize	: 'includes/js/modules/autosize.min'
)
