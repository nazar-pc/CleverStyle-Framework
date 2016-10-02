`$Page` - is system object, that provides functionality of page generation: content, meta-tags, errors pages, etc. Instance can be obtained in such way:
```php
<?php
$Page = \cs\Page::instance();
```

### [Methods](#methods) [Properties](#properties) [Events](#events) [\cs\Page\Assets_processing class](#includes-processing-class) [\cs\Page\Meta class](#meta-class)

<a name="methods" />
###[Up](#) Methods

`$Page` object has next public methods:
* content()
* title()
* success()
* notice()
* warning()
* error()
* canonical_url()
* json()
* css()
* html()
* js()
* config()
* link()
* atom()
* rss()
* replace()
* render()

#### content($add : string, $level = false : bool|int) : cs\Page
Is used to add content on page.

#### title($add : text, $replace = false : bool) : cs\Page
Is used to add (or replace) page title.

#### success($success_text : string) : cs\Page
Display success message on the top of page.

#### notice($notice_text : string) : cs\Page
Display notice message on the top of page.

#### warning($warning_text : string) : cs\Page
Display warning message on the top of page.

#### error($custom_text = null : null|string, $json = false : bool)
Is used to display error page, and stop further execution, if error code was given with `error_code()` function - that value will be used as HTTP error code, otherwise *500* assumed.

#### canonical_url($url : string) : cs\Page
Is used for manual specifying of canonical URL of page.

#### json($content : mixed) : cs\Page
Is used mainly in API, shows specified data (array/string/number) in json format.

#### css($add : string|string[]) : cs\Page
Is used to add links to css files to the page:
```php
<?php
\cs\Page::instance()->css('themes/CleverStyle/css/general.css');
```

#### html($add : string|string[]) : cs\Page
Is used to add links to Web Component (Polymer elements) files to the page:
```php
<?php
\cs\Page::instance()->html('themes/CleverStyle/html/general.html');
```

#### js($add : string|string[]) : cs\Page
Is used to add links to js files to the page:
```php
<?php
\cs\Page::instance()->js('themes/CleverStyle/js/general.js');
```

#### config($config_structure : mixed, $target : string) : cs\Page
Add config on page to make it available on frontend:
```php
<?php
\cs\Page::instance()->config([
    'max_file_size' => $Config->module('Plupload')->max_file_size
], 'cs.plupload');
```

#### link($data : array) : cs\Page
Is used for adding `<link>` tags.

#### atom($href : string, $title = 'Atom Feed' : string) : cs\Page
Is used for simple adding of atom feed (actually, wrapper for `$Page->link()` method)

#### rss($href : string, $title = 'RSS Feed' : string) : cs\Page
Is used for simple adding of rss feed (actually, wrapper for `$Page->link()` method)

#### replace($search : string|string[], $replace = '' : string|string[]) : cs\Page
Is used for replacing anything in source code of finally generated page. Supports regular expressions.

#### render()
Renders current page with interface (if needed), typically called by system

<a name="properties" />
###[Up](#) Properties

`$Page` object has next public properties:

* Content
* pre_Html
* Html
* Description
* Title
* Head
* pre_Body
* Left
* Top
* Right
* Bottom
* post_Body
* level
* interface

#### Content, pre_Html, Html, Description, Title, Head, pre_Body, Left, Top, Right, Bottom, post_Body
All this properties are used to store generated data, and substituting into template. If there is need to add some not foreseen tags or modify - these properties may be used.

#### level
This is array with code intending values for each elements, that will be substituted into template:
```
$level = [
    'Head'        => 0,
    'pre_Body'    => 0,
    'Left'        => 2,
    'Top'        => 2,
    'Content'    => 3,
    'Bottom'    => 2,
    'Right'        => 2,
    'post_Body'    => 0
]
```
Values may be redefined if it is needed.

#### interface
If *false* - page interface will not be displayed, just content (typical example - API responses).

<a name="events" />
###[Up](#) Events

`$Page` object supports next events:
* System/Page/render/before
* System/Page/render/after
* System/Page/rebuild_cache
* System/Page/requirejs

#### System/Page/display/before
This event is used for adding scripts, styles and some modifications with content and executed before page generation. Usually is used by modules in order to add their styles and scripts.

#### System/Page/display/after
This event is used for modifying final page right before output.

#### System/Page/rebuild_cache
Is used for rebuilding of JavaScript/CSS cache, executed after system cache generation.

#### System/Page/requirejs
Is used for supplying additional aliases for AMD modules and/or additional directories where to search for Bower/NPM packages.

```
System/Page/requirejs
[
  'paths'                 => &$paths,                // The same as `paths` in requirejs.config()
  'directories_to_browse' => &$directories_to_browse // Where to look for AMD modules (typically bower_components and node_modules directories)
]
```

Usage example (from Composer assets module):
```php
<?php
\cs\Event::instance()->on(
    'System/Page/requirejs',
    function ($data) {
        $data['directories_to_browse'][] = STORAGE.'/Composer/vendor/bower-asset';
        $data['directories_to_browse'][] = STORAGE.'/Composer/vendor/npm-asset';
    }
);
```

<a name="assets-processing-class" />
###[Up](#) \cs\Page\Assets_processing class
Class includes few methods used for processing CSS and HTML files before putting into cache. Is used by `cs\Page` and most of times there is no need to use it manually.

This class has next public static methods:
* css()
* js()
* html()

#### css($data : string, $file : string) : string
Is used to process css files and embed css imports, images, fonts into one resulting css file. It is used to minimize number of requests to server, also, resulting file may be compressed with gzip.

#### css($data : string) : string
Is used to process js files, provides simple minification by removing new lines and unnecessary spaces, but doesn't change code itself, so it works very fast.

#### html($data : string, $file : string, $base_filename : string, $destination : bool|string) : string
Is used to process html files with Web Components and analyses file for scripts and styles, combines them into resulting files in order to optimize loading process.
Optionally vulcanization can be used.

<a name="meta-class" />
###[Up](#) \cs\Page\Meta class
Class is Singleton and includes few methods used for generating necessary meta tags (especially Open Graph protocol tags).

This class has next public methods:
* image()
* __call()
* render()

#### image($images : string|string[]) : cs\Page\Meta
Common wrapper to add all necessary meta tags with images

#### __call($type : string, $params : mixed[]) : cs\Page\Meta
Common wrapper for generation of various Open Graph protocol meta tags. Meta tags intended to be generated using magic methods:
```php
<?php
$Meta = \cs\Page\Meta::instance();
$Meta->blog();
```
results
```html
<meta content="blog" property="og:type">
```
```php
<?php
$Meta = \cs\Page\Meta::instance();
$Meta
    ->article()
    ->article('published_time', date('Y-m-d', $post['date'] ?: time()))
    ->article('tag', ['magic'. 'awesome']);
```
results
```html
<meta content="article" property="og:type">
<meta content="2014-10-02" property="article:published_time">
<meta content="magic" property="article:tag">
<meta content="awesome" property="article:tag">
```

#### render()
Generates Open Graph protocol information, and puts it into HTML.
Usually called by system itself, there is no need to call it manually.

Also class has next public properties:
* head_prefix
* no_head

#### head_prefix
Is used for specifying of *prefix* attribute of `<head>`, that may be needed sometimes, this help avoid conflicts, when prefix is set by system too (for example for Open Graph protocol).

#### no_head
If set to *true* - `<head>` tag will not be generated by system (for example, when there is such tag in template).

