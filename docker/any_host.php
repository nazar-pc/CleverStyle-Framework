<?php
namespace cs;
Event::instance()->on(
	'System/Config/init/after',
	function () {
		$Config = Config::instance();
		if (!in_array($_SERVER['HTTP_HOST'], $Config->mirrors['http'])) {
			$Config->core['url'][]           = 'http://'.$_SERVER['HTTP_HOST'];
			$Config->core['cookie_domain'][] = explode(':', $_SERVER['HTTP_HOST'], 2)[0];
			$Config->save();
		}
	}
);
