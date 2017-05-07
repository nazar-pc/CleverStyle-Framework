First of all, files uploading is not implemented by system core, but might be provided by some modules with `file_upload` functionality.

However, since files uploading is crucial feature for many applications, how files uploading should work is specified here in order to maintain cross-compatible modules.

#### Integration of files uploading (client-side)
Integration on client-side is possible through JavaScript function `cs.file_upload(button, success, error, progress, multi, drop_element)`, you can check for this function in order to determine is necessary functionality is available.

All arguments are optional!

* `button` - DOM element or jQuery object, clicking on which files selection dialog will appear
* `success` - callback that will be called after successful uploading of all files, accepts one argument `files` with array of absolute urls of all uploaded files
* `error` - callback that will be called if error occurred in Plupload, accepts 3 arguments `error`, `xhr`, `file` with error text, xhr object and file object being uploaded
* `progress` - callback that will be called when file uploading progress changes, accepts 9 arguments `percent`, `size`, `uploaded_size`, `name`, `total_percent`, `total_size`, `total_uploaded`, `current_file`, `total_files` current progress in percents, total file size, size of uploaded part, file name, total percents, size and uploaded size (useful when multiple files being uploaded) and currently uploading file number and total number of files to be uploaded
* `multi` - allows to enable multiple files selection (defaults to `false`)
* `drop_element` - if specified - it will be possible to drop files on that element for uploading (defaults to `button`, first argument)

Method returns object, which can be used for some necessary actions using its methods:
* `stop` - call it if you want to stop upload process at any time
* `destroy` - call it to stop uploading and remove event listeners from `button` or `drop_element` elements

This is basically all on frontend, you just specify where to click or where to drop file and get array of URLs to uploaded files in callback, however, that files will be removed soon if you do not finish uploading process on backend.

Example of usage in TinyMCE module (LiveScript):
```livescript
uploader_callback = undefined
button            = document.createElement('button')
uploader          = cs.file_upload?(
    button
    (files) !->
        tinymce.uploader_dialog?.close()
        if files.length
            uploader_callback(files[0])
        uploader_callback := undefined
    (error) !->
        tinymce.uploader_dialog?.close()
        cs.ui.notify(error, 'error')
    (file) !->
        if !tinymce.uploader_dialog
            progress                             = document.createElement('progress', 'cs-progress')
            tinymce.uploader_dialog              = cs.ui.modal(progress)
            tinymce.uploader_dialog.progress     = progress
            tinymce.uploader_dialog.style.zIndex = 100000
            tinymce.uploader_dialog.open()
        tinymce.uploader_dialog.progress.value = file.percent || 1
)
...
    file_picker_callback : uploader && (callback) !->
        uploader_callback := callback
        button.click()
...
```

#### Integration of files uploading (server-side)
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
