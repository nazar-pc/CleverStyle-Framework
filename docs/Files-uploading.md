First of all, files uploading is implemented not by system core, but by modules (for instance, Plupload) with `file_upload` functionality.

However, since files uploading is crucial feature for many applications, how files uploading should work is specified here in order to maintain cross-compatible modules.

<a name="frontend" />
### Frontend
On frontend side there is one simple method `cs.file_upload(button, success, error, progress, multi, drop_element)`.

All arguments are optional!

* `button` - DOM element or jQuery object, clicking on which files selection dialog will appear
* `success` - callback that will be called after successful uploading of all files, accepts one argument `files` with array of absolute urls of all uploaded files
* `error` - callback that will be called if error occurred in Plupload, accepts 3 arguments `error`, `jqXHR`, `file` with error text, jqXHR object and file object being uploaded
* `progress` - callback that will be called when file uploading progress changes, accepts 9 arguments `percent`, `size`, `uploaded_size`, `name`, `total_percent`, `total_size`, `total_uploaded`, `current_file`, `total_files` current progress in percents, total file size, size of uploaded part, file name, total percents, size and uploaded size (useful when multiple files being uploaded) and currently uploading file number and total number of files to be uploaded
* `multi` - allows to enable multiple files selection (defaults to `false`)
* `drop_element` - if specified - it will be possible to drop files on that element for uploading (defaults to `button`, first argument)

Method returns object, which can be used for some necessary actions using its methods:
* `stop` - call it if you want to stop upload process at any time
* `destroy` - call it to stop uploading and remove event listeners from `button` or `drop_element` elements

This is basically all on frontend, you just specify where to click or where to drop file and get array of URLs to uploaded files in callback, however, that files will be removed soon if you do not finish uploading process on backend.

<a name="backend" />
### Backend
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
