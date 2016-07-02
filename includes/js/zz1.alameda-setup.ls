/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
requirejs.config(
	baseUrl		: '/'
	paths		:
		jssha			: 'includes/js/modules/jsSHA-2.1.0'
		autosize		: 'includes/js/modules/autosize.min'
		html5sortable	: 'includes/js/modules/html5sortable-0.4.0.min'
	waitSeconds	: 60
)
define('sprintf-js', -> {sprintf, vsprintf})
