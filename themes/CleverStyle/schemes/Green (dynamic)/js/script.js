/**
 * @package		CleverStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function (){
	$('body').append([
		$('<div class="cs-loading"></div>').append([
			'<div class="cs-n1"></div>',
			'<div class="cs-n2"></div>',
			'<div class="cs-n3"></div>',
			'<div class="cs-n4"></div>',
			'<div class="cs-n5"></div>',
			'<div class="cs-n6></div>',
			'<div class="cs-n7"></div>'
		]),
		'<div class="cs-header-zone"></div>'
	]);
	var header_visible		= getcookie('header_visible'),
		header_elements		= $('body > header > *'),
		body				= $('#body'),
		header_move_allow	= true,
		header_offset		= 125,
		header_delay		= 250,
		allow_enter			= false;
	if (getcookie('header_visible') == 'hide') {
		header_elements.css('margin-top', '-='+header_offset+'px');
		body.css('margin-top', '-='+header_offset+'px');
	} else {
		header_visible = 'show';
	}
	setTimeout(
		function () {
			allow_enter = true;
		},
		200
	);
	$('.cs-header-zone').mouseenter(
		function () {
			if (allow_enter && header_visible == 'hide' && header_move_allow) {
				header_move_allow = false;
				header_elements.animate({'marginTop': '+='+header_offset+'px'}, header_delay, 'swing', function () {
					header_visible = 'show';
					setcookie('header_visible', header_visible);
					header_move_allow = true;
				});
				body.animate({'marginTop': '+='+header_offset+'px'}, header_delay);
			}
		}
	);
	$('header').mouseleave(
		function () {
			if (header_visible == 'show' && header_move_allow) {
				header_move_allow = false;
				header_elements.animate({'marginTop': '-='+header_offset+'px'}, header_delay, 'swing', function () {
					header_visible = 'hide';
					setcookie('header_visible', header_visible);
					header_move_allow = true;
				});
				body.animate({'marginTop': '-='+header_offset+'px'}, header_delay);
			}
		}
	);
	$('.cs-loading > div').css('opacity', 1);
});
$(window).load(
	function (){
		setTimeout(
			function (){
				$('.cs-loading > div').css('opacity', 0);
			},
			1000
		);
	}
);