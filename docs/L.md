`L` - is system object, that provides multilingual interface functionality, instance can be obtained in such way:
```javascript
L	= window.cs.Language;
```
Also there is possibility to simplify getting translations with common prefix:
```javascript
L	= window.cs.Language(prefix);
```
In case of prefixed usage full keys are still available, but prefixed keys are preferred in case of conflict.

Object is similar to [$L](/docs/$L.md) object on backend, but have less number of methods.

`L` is used only because it is short for frequent usage

### [Methods](#methods) [Properties](#properties)

<a name="methods" />
###[Up](#) Methods

`L` object has next public methods:
* get()
* format()

#### get(item : string) : string
Get translation.
```javascript
L	= cs.Language;
L.get('module_name');
```

Also there is simplified way to get translation - to get it as property of object:
```javascript
L	= cs.Language;
L.module_name;
```

#### format(name : string, arguments : string[]) : string
Method is used for formatted translation. Example:

translation (in json file)

	"hello":"Hello, %s!"
usage
```javascript
L	= cs.Language;
L.format('hello', ['my friend']);
```

Translation string should be formatted according to [sprintf()](http://www.php.net/manual/en/function.sprintf.php) PHP function.

Also there is simplified way to get formatted string - to get it as result of calling of object function:
```javascript
L	= cs.Language;
L.hello('my friend')
```

This way is more natural. You can specify as much arguments as you need.

<a name="properties" />
###[Up](#) Properties

`L` object has next public properties:

* clanguage
* clanguage_en
* clang
* content_language
* locale
* _datetime_long
* _datetime
* _date
* _time

#### clanguage
Current language (in original writing):
* English
* Русский
* Українська

#### clanguage_en
English lowercase variant of language name:
* english
* russian
* ukrainian

#### clang
Short English lowercase variant of language name:
* en
* ru
* uk

#### content_language
Short English lowercase variant of content language name (is used for *Content-Language* header):
* en
* ru
* uk

#### locale
Locale:
* en_US
* ru_RU
* uk_UA

#### _datetime_long
Long format of date and time for `date()` PHP function

#### _datetime
Short format of date and time for `date()` PHP function

#### _date
Short format of date for `date()` PHP function

#### _time
Short format of time for `date()` PHP function
