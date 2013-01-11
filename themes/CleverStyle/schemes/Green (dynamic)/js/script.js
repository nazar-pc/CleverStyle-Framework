/**
 * @package		CleverStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function background() {
	var height = document.body.clientHeight > window.innerHeight ? document.body.clientHeight : window.innerHeight,
		num = Math.round(height/30);
	for(var i = 0; i < num; i++) {
		var size = 100+(Math.random()*50),
			temp1,
			temp2,
			temp3;
		while (true) {
			temp1 = Math.round(Math.random()*255);
			temp2 = Math.round(Math.random()*255);
			temp3 = Math.round(Math.random()*255);
			if (temp1 > 100 || temp2 > 100 || temp3 > 100) {
				break;
			}
		}
		var bokehColour = temp1+','+temp2+','+temp3;
		var element = $('<div class="cs-background">').css({
				'left'			: Math.floor(Math.random()*(window.innerWidth-size))+'px',
				'top'			: Math.floor(Math.random()*(height-size))+'px',
				'width': size+'px',
				'height': size+'px',
				'border-radius': Math.floor(size/2)+'px',
				'border': '1px solid rgba('+bokehColour+', 0.5)',
				'overflow': 'hidden'
		});
		if ($.browser.msie) {
			element.css({
				'background': '-ms-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
			});
		} else if ($.browser.opera) {
			element.css({
				'background': '-o-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
			});
		} else if ($.browser.mozilla) {
			element.css({
				'background': '-moz-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
			});
		} else if ($.browser.chrome || $.browser.webkit) {
			element.css({
				'background': '-webkit-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
			});
		}
		element.appendTo('body');
	}
}
$(function(){
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
	background();
});
$(window).load(
	function(){
		setTimeout(
			function(){
				$('.cs-loading > div').css('opacity', 0);
			},
			1000
		);
	}
);