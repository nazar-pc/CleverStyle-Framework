var loading_interval, loading_angle = 0;
	function background() {
		var height = document.body.clientHeight > window.innerHeight ? document.body.clientHeight : window.innerHeight;
		num = Math.round(height/30);
		for(var i = 0; i < num; i++) {
			bokehSize = 100+(Math.random()*50);
			x = true;
			while (x) {
				temp1 = Math.round(Math.random()*255);
				temp2 = Math.round(Math.random()*255);
				temp3 = Math.round(Math.random()*255);
				if (temp1 > 100 || temp2 > 100 || temp3 > 100) {
					x = false;
				}
			}
			bokehColour = temp1+','+temp2+','+temp3;
			l = Math.floor(Math.random()*(window.innerWidth-bokehSize));
			t = Math.floor(Math.random()*(height-bokehSize));
			bokeh = $("<div>")
				.addClass("cs-background")
				.css({
						'left': l+'px',
						'top': t+'px',
						'width': bokehSize+'px',
						'height': bokehSize+'px',
						'border-radius': Math.floor(bokehSize/2)+'px',
						'-moz-border-radius': Math.floor(bokehSize/2)+'px',
						'border': '1px solid rgba('+bokehColour+', 0.5)',
						'overflow': 'hidden'
					});
			if ($.browser.msie && $.browser.version >= 10) {//TODO new gradient rules CSS3
				bokeh.css({
					'background': '-ms-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
				});
			} else if ($.browser.opera) {
				bokeh.css({
					'background': '-o-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
				});
			} else if ($.browser.mozilla) {
				bokeh.css({
					'background': '-moz-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
				});
			} else if ($.browser.webkit) {
				bokeh.css({
					'background': '-webkit-radial-gradient(contain, rgba('+bokehColour+', 0.7), rgba('+bokehColour+',0.3))'
				});
			}
			bokeh.appendTo("#background");
		}
	}
	$(function(){
		var header_visible = getcookie('header_visible'), header_elements = $('header > *, #body'),
			header_move_allow = true,
			header_offset = 125,
			header_delay = 250;
		if (header_visible == 'hide') {
			header_elements.animate({'marginTop': '-='+header_offset+'px'}, 0);
		} else {
			header_visible = 'show';
		}
		$('#header_zone').mouseover(
			function () {
				if (header_visible == 'hide' && header_move_allow) {
					header_move_allow = false;
					header_elements.animate({'marginTop': '+='+header_offset+'px'}, header_delay, 'swing', function () {
						header_visible = 'show';
						setcookie('header_visible', header_visible);
						header_move_allow = true;
					});
				}
			}
		);
		$('#header_black').mouseout(
			function () {
				if (header_visible == 'show' && header_move_allow) {
					header_move_allow = false;
					header_elements.animate({'marginTop': '-='+header_offset+'px'}, header_delay, 'swing', function () {
						header_visible = 'hide';
						setcookie('header_visible', header_visible);
						header_move_allow = true;
					});
				}
			}
		);
		$('#body').mousemove(
			function () {
				if (header_visible == 'show' && header_move_allow) {
					header_move_allow = false;
					header_elements.animate({'marginTop': '-='+header_offset+'px'}, header_delay, 'swing', function () {
						header_visible = 'hide';
						setcookie('header_visible', header_visible);
						header_move_allow = true;
					});
				}
			}
		);
		if ($.browser.msie) {
			$('#loading').attr('style', '-ms-transform: rotate('+(++loading_angle)+'deg)');
			loading_interval = setInterval(function () {$('#loading').attr('style', '-ms-transform: rotate('+(loading_angle += 3)+'deg)');}, 50);
		} else {
			$('#loading').css(
				{
					"-ms-transform" : 'rotate('+(++loading_angle)+'deg)',
					"-moz-transform" : 'rotate('+(++loading_angle)+'deg)',
					"-o-transform" : 'rotate('+(++loading_angle)+'deg)',
					"-webkit-transform" : 'rotate('+(++loading_angle)+'deg)',
					"transform" : 'rotate('+(++loading_angle)+'deg)'
				}
			);
			loading_interval = setInterval(function () {$('#loading').css({"-moz-transform" : 'rotate('+(++loading_angle)+'deg)', "-o-transform" : 'rotate('+(++loading_angle)+'deg)', "-webkit-transform" : 'rotate('+(++loading_angle)+'deg)', "transform" : 'rotate('+(++loading_angle)+'deg)'});}, 50);
		}
		$('#loading > div').css('opacity', 1);
		background();
	});
	$(window).load(
		function(){
			setTimeout(
				function(){
					$('#loading > div').css('opacity', 0);
					setTimeout(
						function(){
							clearInterval(loading_interval);
						}, 1000
					);
				}, 1000
			);
		}
	);