`$L` - is system object, that provides multilingual interface functionality, instance can be obtained in such way:
```php
<?php
$L	= \cs\Language::instance();
```

`$L` is used only because it is short for frequent usage

### [Methods](#methods) [Properties](#properties) [Interfaces](#interfaces) [Events](#events) [\cs\Language\Prefix class](#prefix-class)

<a name="methods" />
###[Up](#) Methods

`$L` object has next public methods:
* init()
* url_language()
* get()
* set()
* format()
* time()
* to_locale()
* change()

#### init()
Method for initialization, is used by system.

#### url_language($url = false : false|string) : false|string
Does URL have language prefix. If there is language prefix - language will be returned, `false` otherwise.

#### get($item : bool|string, $language = false : false|string, $prefix = '' : string) : string
Get translation.
```php
<?php
$L		= \cs\Language::instance();
$module_name	= $L->get('module_name');
```

Also there is simplified way to get translation - to get it as property of object:
```php
<?php
$L		= \cs\Language::instance();
$module_name	= $L->module_name;
```

#### set($item :array|string, $value = null : null|string)
Method is used to set translation.

#### format($name : string, $arguments : string[], $language = false : false|string, $prefix = '' : string) : string
Method is used for formatted translation. Example:

translation (in json file)

```
"hello":"Hello, %s!"
```
usage
```php
<?php
$L	= \cs\Language::instance();
$L->format('hello', ['my friend']);
```

Translation string should be formatted according to [sprintf()](http://www.php.net/manual/en/function.sprintf.php) PHP function.

Also there is simplified way to get formatted string - to get it as result of calling of object function:
```php
<?php
$L	= \cs\Language::instance();
$L->hello('my friend');
```

This way is more natural. You can specify as much arguments as you need.

#### time($in : int, $type : string) : string
Is used to convert seconds to string representation of specified type.

Of course, it works with string endings (to realize this properly, `$L->time` property is used).

#### to_locale($data : string|string[], $short_may = false : bool) : string
Converts date (only date as for now) obtained with *date()* PHP function to locale translation (translates months names and days of week).

#### change($language : string) : bool
Method for language changing (usually is called by system automatically, and set language to user specific).

<a name="properties" />
###[Up](#) Properties

`$L` object has next public properties:

* clanguage
* time
* clanguage_en*
* clang*
* cregion*
* content_language*
* _datetime_long*
* _datetime*
* _date*
* _time*

\* actually are not properties, they are translations, but are widely used and should be mentioned here.

#### clanguage
Current language (in original writing):
* English
* Русский
* Українська

#### time
May be set to closure to be used by `$L->time()` instead of built-in mechanism, to provide proper endings of words for some languages.

#### clanguage_en
English lowercase variant of language name:
* english
* russian
* ukrainian

#### clang
ISO 639-1 language code:
* en
* ru
* uk

#### cregion
ISO 3166-1 Alpha-2 region code:
* us
* ru
* ua

### content_language
Content language that is primarily used in HTTP header `Content-Language`, but might also be used in other places. If not given in translations, defaults to `clang`.

#### _datetime_long
Long format of date and time for `date()` PHP function

#### _datetime
Short format of date and time for `date()` PHP function

#### _date
Short format of date for `date()` PHP function

#### _time
Short format of time for `date()` PHP function

<a name="interfaces" />
###[Up](#) Interfaces

* JsonSerializable

#### JsonSerializable
Object implements `JsonSerializable` interface and allows getting of all translations of current language in JSON format (it is used by system in order to have access to translations in JavaScript).

<a name="events" />
###[Up](#) Events

`$L` object supports only one event:
* System/general/languages/load

#### System/general/languages/load
This event is running at language changing to allow third-party components add their own translations. Array:

	[
		'clanguage'			=> clanguage
		'clang'				=> clang
		'cregion'			=> cregion
		'clanguage_en'		=> clanguage_en
	]
is set as parameter for event, all necessary language and locale aspects are given with event to determine exact translation.

<a name="prefix-class" />
###[Up](#) \cs\Language\Prefix class

This class is used for simplified work with languages, when using common prefix.
It includes methods for getting of translations just like `cs\Language` class, but automatically adds prefix specified in constructor to every item.
In case of prefixed usage full keys are still available, but prefixed keys are preferred in case of conflict.
Next methods are available:

* get
* format
* time *
* to_locale *

\* prefixes are not added because there is no need for that.
