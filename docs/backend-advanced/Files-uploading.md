First of all, files uploading is implemented not by system core, but by modules (for instance, Plupload) with `file_upload` functionality.

However, since files uploading is crucial feature for many applications, how files uploading should work is specified here in order to maintain cross-compatible modules.

On backend side there are 2 events that are used to associate uploaded file with some tag.

The idea here is to keep tracking on all files, that is why if file is kept if only associated with at least one tag.

First event is `System/upload_files/add_tag`, usage:
```php
<?php
\cs\Event::instance()->run(
	'System/upload_files/add_tag',
	[
		'tag'	=> "Items/10",
		'url'	=> $uploaded_file_url
	]
);
```

Where usually `tag` starts with module name which uploaded file and some identifier that uniquely connects with some item in module.

Both `tag` and `url` are required.

Second event is `System/upload_files/del_tag`, usage:
```php
<?php
\cs\Event::instance()->run(
	'System/upload_files/del_tag',
	[
		'tag'	=> "Items/10",
		'url'	=> $uploaded_file_url
	]
);
```

Where arguments are the same, but both `tag` and `url` are optional, so you can:
* remove all files for specified tag (for instance, all images of some article)
* remove file regardless of tag where it is used (rarely used)
* remove tag for specific file (for example, if some article was edited and image is not used there anymore)

For searching files in any HTML code next RegExp usually is used:
```php
<?php
preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $some_text, $found_files);
```

It will work fine, because mentioned events will ignore all URLs which are not actually uploaded files registered in system.
