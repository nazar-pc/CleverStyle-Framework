<?php
/**
 * @author Alan Hardman
 * @author Nazar Mokrynskyi
 * @link   https://github.com/Alanaktion/ansispan-php
 */
/**
 * Converts ANSI text to HTML
 *
 * @param string $text
 *
 * @return string
 */
function ansispan ($text) {
	// Foreground colors
	$foreground = [
		30 => 'black',
		31 => 'red',
		32 => 'green',
		33 => 'yellow',
		34 => 'blue',
		35 => 'magenta',
		36 => 'cyan',
		37 => 'white'
	];
	// Background colors
	$background = [
		40 => 'black',
		41 => 'red',
		42 => 'green',
		43 => 'yellow',
		44 => 'blue',
		45 => 'magenta',
		46 => 'cyan',
		47 => 'white'
	];
	$replace    = [];
	// Replace foreground color codes
	foreach ($foreground as $ansi => $css) {
		$span = "<span style=\"color: $css\">";

		// \x1B[Xm == \x1B[0;Xm sets foreground color to X
		$replace[] = [
			'/\x1B\[(0;)?'.$ansi.'m/',
			$span
		];

		// Combined foreground/background replacement
		foreach ($background as $ansi_ => $css_) {
			$span_ = "$span<span style=\"background-color: $css_\">";

			// \x1B[Xm == \x1B[0;Xm sets background color to X
			$replace[] = [
				'/\x1B\[('.$ansi_.';'.$ansi.'|'.$ansi.';'.$ansi_.')?m/',
				$span_
			];
		}
	}

	// Replace background color codes
	foreach ($background as $ansi => $css) {
		$span = "<span style=\"background-color: $css\">";

		// \x1B[Xm == \x1B[0;Xm sets background color to X
		$replace[] = [
			'/\x1B\[(0;)?'.$ansi.'m/',
			$span
		];
	}

	// \x1B[1m enables bold font, \x1B[22m disables it
	$replace[] = [
		'/\x1B\[1m/',
		'<b>'
	];
	$replace[] = [
		'/\x1B\[22m/',
		'</b>'
	];

	// \x1B[3m enables italics font, \x1B[23m disables it
	$replace[] = [
		'/\x1B\[3m/',
		'<i>'
	];
	$replace[] = [
		'/\x1B\[23m/',
		'</i>'
	];

	// Catch any remaining close tags
	$replace[] = [
		'/\x1B\[0?m/',
		'</span>'
	];

	// Replace "default" codes with closing span
	$replace[] = [
		'/\x1B\[(39)?;?(49)?m/',
		'</span>'
	];

	return preg_replace(
		array_column($replace, 0),
		array_column($replace, 1),
		$text
	);
}
