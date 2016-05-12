`$Text` - is system object, that provides interface for working with multilingual texts. Instance can be obtained in such way:
```php
<?php
$Text	= \cs\Text::instance();
```

### [Methods](#methods)

<a name="methods" />
###[Up](#) Methods

`$Text` object has next public methods:
* get()
* search()
* set()
* del()
* process()

Each method accepts database id as first parameter. This database should contain tables `[prefix]text` and `[prefix]text_data` with actual structure in order to work properly (primary database already have these tables).

Group - usually module name, or module name plus some section. Label - usually id of some item or section + id. Such structure is made for simpler managing of texts.

#### get($database : int, $group : string, $label : string, $id = null : int|null, $store_in_cache = false : bool) : false|string
Get text by group and label or id. In most cases it is not needed because of `$Text->process()` method.

#### search($database : int, $group : string, $label : string, $text : string) : array[]|false
Search for text regardless language.

#### set($database : int, $group : string, $label : string, $text : string) : false|string
Set text for specified group and label.

#### del($database : int, $group : string, $label : string) : bool
Delete text for specified group and label.

#### process($database : int, $data : string|string[], $store_in_cache = false : bool) : false|string|string[]
Process text, and replace {¶([0-9]+)} on real text, is used before showing multilingual information.

Example how it works:
```php
<?php
$Text		= \cs\Text::instance();
//Set text by group and label
$result		= $Text->set($this->cdb(), 'Blogs/sections/title', $id, $text);
//Process text
$content	= $Text->process($this->cdb(), $result);
```
