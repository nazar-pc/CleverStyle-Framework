--FILE--
<?php
include __DIR__.'/../../unit.php';

var_dump('Text mode');
var_dump(
	xap(
		'<p>Paragraph <b>Bold</b></p><iframe src="https://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>'
	)
);

var_dump('No HTML tags');
var_dump(
	xap(
		'<p>Paragraph <b>Bold</b></p><iframe src="https://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>',
		false
	)
);

var_dump('With HTML tags');
var_dump(
	xap(
		'<p>Paragraph <b>Bold</b></p><iframe src="https://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>',
		true
	)
);

var_dump('With HTML tags (iframe allowed)');
var_dump(
	xap(
		[
			'<p>Paragraph <b>Bold</b></p><iframe src="http://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>',
			'<p>Paragraph <b>Bold</b></p><iframe src="https://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>',
			'<p>Paragraph <b>Bold</b></p><iframe src="//example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>',
			'<p>Paragraph <b>Bold</b></p><iframe src="javascript:alert(\'xss\')" height="200" width="300" data-custom="xyz" style="border: 1px solid black;">Iframe content</iframe>'
		],
		true,
		true
	)
);
?>
--EXPECT--
string(9) "Text mode"
string(207) "&lt;p&gt;Paragraph &lt;b&gt;Bold&lt;/b&gt;&lt;/p&gt;&lt;iframe src="https://example.com/video?xyz" height="200" width="300" data-custom="xyz" style="border: 1px solid black;"&gt;Iframe content&lt;/iframe&gt;"
string(12) "No HTML tags"
string(28) "Paragraph BoldIframe content"
string(14) "With HTML tags"
string(42) "<p>Paragraph <b>Bold</b></p>Iframe content"
string(31) "With HTML tags (iframe allowed)"
array(4) {
  [0]=>
  string(204) "<p>Paragraph <b>Bold</b></p><iframe frameborder="0" allowfullscreen sandbox="allow-same-origin allow-forms allow-popups allow-scripts" height="200" width="300" src="http://example.com/video?xyz"></iframe>"
  [1]=>
  string(205) "<p>Paragraph <b>Bold</b></p><iframe frameborder="0" allowfullscreen sandbox="allow-same-origin allow-forms allow-popups allow-scripts" height="200" width="300" src="https://example.com/video?xyz"></iframe>"
  [2]=>
  string(199) "<p>Paragraph <b>Bold</b></p><iframe frameborder="0" allowfullscreen sandbox="allow-same-origin allow-forms allow-popups allow-scripts" height="200" width="300" src="//example.com/video?xyz"></iframe>"
  [3]=>
  string(28) "<p>Paragraph <b>Bold</b></p>"
}
