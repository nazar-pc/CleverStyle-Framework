First of all, files uploading is implemented not by system core, but by modules (for instance, Plupload) with `file_upload` functionality.

However, since files uploading is crucial feature for many applications, how files uploading should work is specified here in order to maintain cross-compatible modules.

[Frontend integration can be found here](/docs/frontend-advanced/Files-uploading.md)

On server side any module should confirm files uploading by adding tag to uploaded file (and should delete tag, when file is not used any more).

Confirmation is implemented through 2 events, that third-party components may fire. Also, any uploaded file may have several tags.

Examples (Blogs module):
```php
<?php
$old_files = find_links($data['content']);
$new_files = find_links($content);
if ($old_files || $new_files) {
    foreach (array_diff($old_files, $new_files) as $file) {
        \cs\Event::instance()->fire(
            'System/upload_files/del_tag',
            [
                'tag' => "Blogs/posts/$id/$L->clang",
                'url' => $file
            ]
        );
    }
    unset($file);
    foreach (array_diff($new_files, $old_files) as $file) {
        \cs\Event::instance()->fire(
            'System/upload_files/add_tag',
            [
                'tag' => "Blogs/posts/$id/$L->clang",
                'url' => $file
            ]
        );
    }
    unset($file);
}
unset($old_files, $new_files);
```
This code compares previous version of post and current for links, removes old files, and adds new ones.
Links that doesn't corresponds to any existed files will be ignored automatically.
`find_links()` is a generic function from [UPF](https://github.com/nazar-pc/Useful-PHP-Functions) that will collect all of the links from specified text, which is fine for this purpose.

```php
<?php
\cs\Event::instance()->fire(
    'System/upload_files/del_tag',
    [
        'tag' => "Blogs/posts/$id%"
    ]
);
```
This code deletes all links, associated with post on any language.

##### System/upload_files/add_tag
Event should be fired in order to add tag to the file. Can be fired on non-uploaded files without any negative consequences, non-uploaded files will be safely ignored.
```
[
	'url' => $url, //Required
	'tag' => $tag  //Required
]
```

* `url` - absolute url to uploaded file, obtained on client-side
* `tag` - tag of the item, which will be associated with this file

##### System/upload_files/del_tag
Event should be fired in order to delete tag from the file or remove tag from all of the files tagged with it (even wildcard syntax is supported). Can be fired on non-uploaded files without any negative consequences, non-uploaded files will be safely ignored.
```
[
    'url' => $url, //Optional
    'tag' => $tag  //Optional
]
```

* `url` - absolute url to uploaded file, obtained on client-side
* `tag` - tag of the item, which is associated with this file, "%" symbol may be used at the end of string to delete all files, that starts from specified string
