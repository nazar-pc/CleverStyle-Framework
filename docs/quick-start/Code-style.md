### Code style guide
First of all, if you use PhpStorm IDE, make sure you are using code style configuration file and inspections configuration available in repository (`.idea` directory), it will allow you to format code automatically to comply with most of following rules.

#### General
* Yoy should start file with full `<?php`, not shorten `<?`
* You should not write `?>` at the end of php files, it is not required
* Short tags `<?='Hello, my friend!'?>` are allowed in html, when it is necessary

#### Identifiers and case
* `Class_name`
* `function_name`
* `method_name`
* `CONSTANT_NAME`
* `$some_variable`
* `$Class_name` - usually, variable with instance of object should have the same name as class
* `$class_property`
* `true`, `false`, `null`
* CSS ids and classes use `-` as separator instead of `_`: `cs-left`, names of custom elements also follow the same convention

#### Maximum Line Length
Up to 160 symbols. More and more people have displays with high resolution, and it should not be a problem to have such length of line.

#### Line Termination
Line termination follows the Unix text file convention. Lines must end with a single linefeed (LF) character. Linefeed characters are represented as ordinal 10, or hexadecimal 0x0A.

#### Comments
Comments with less importance may be one-line comments started with `//`, but it is better to use multi-line comments, because they get more attention from those, who will read the code:
```
/**
 * Comment here
 */
```
When writing documentation, please use [PHPDocumentor’s](http://www.phpdoc.org/docs/latest/for-users/phpdoc-reference.html.md) or [JSDoc](https://en.wikipedia.org/wiki/JSDoc) syntax.

#### Spaces and wrapping
Indentation should consist of 1 tab. 1 tab = 4 spaces (should be configured in you editor's settings).

Symbol of equality `=` for every assignment on the same code level is aligned with spaces to improve readability (automatically done by IDE using code style in repository):
```php
<?php
$current_page = 1;
$total_pages  = 10;
$next         = 2;
```
Similarly for arrays:
```php
<?php
$array	= [
	'key'		=> 'value',
	'another_key'	=> 'value'
];
```

If number of function parameters is large, or parameters are not variables, but result of other functions, you can write parameters in several lines with alignment to improve readability:
```php
<?php
$str = str_replace(
	[
		'a',
		'b',
		'd'
	],
	[
		'e',
		'f',
		'g'
	],
	'adiabatic'
);
```
Function (method) name is followed by space on declaration, but doesn't during calling:
```php
<?php
function foo () {
	//Code
}
foo();
```
#### Braces
Array braces should be in shorten form `$array = [];` instead of `$array = array();`, if it is not external third-party library.

Curly braces for *if*, *for*, *while*, etc. are written in the same line as keyword, and must be used always:
```php
<?php
if (...) {
	//Code
} elseif (...) {
	//Code
} else {
	//Code
}
```
#### Visibility and type
Visibility may be omitted for method, if it is public, because it is public by default. Visibility is written before method type:
```php
<?php
class Example {
	protected static function processing () {
		//Code
	}
}
```
#### Namespaces
#####PHP
For components:
```
\cs\modules\Module_name
\cs\plugins\Plugin_name
```
For engines, following namespaces are used (derivative from names of core classes):
```
\cs\Cache\Engine_name
\cs\DB\Engine_name
\cs\Storage\Engine_name
```
If several classes from other namespaces are used, get them by one `use` with alignment of each class on new line:
```
use
	\cs\modules\Blogs\Blogs,
	\cs\modules\Test\Test;
```
Following this convention will allow to use built-in class autoloader, for instance, `\cs\modules\Module_name\Class_name` class intended to be in `components/modules/Module_name/Class_name.php`.

##### JavaScript
System functions/variables are defined inside `window.cs`, `$.cs` and `$().cs` (for jQuery).
Components must create namespace inside `window.cs` namespace, for example `window.cs.plupload`.

##### CSS
All ids and classes must start with `cs`, components must add their namespace inside:
* `cs-left`
* `cs-blogs-post-latest`

#### Classes and functions vs. plain code
File should either declare class of functions or cause side-effect, not both at the same file.
There may be several functions in the same file, but there should be only one class to work properly with classes autoloading.

#### Web Components
Names of Web Components elements should start with `cs-` also and contain component name in logs or short form, or use some other prefix without `cs-`:
* `cs-blogs-post-latest`
* `e-profile-select`

General names like `list-item` are not welcomed.

#### SQL queries
All SQL keywords must be in uppercase:
* `SELECT`
* `INSERT`
* `FROM`

It is recommended to write every next logical part of query on new line, and also write every new table field on new line if there several:
```sql
SELECT
	`id`,
	`title`
FROM `[prefix]blogs_posts`
WHERE `id` = 5
LIMIT 1`
```

Also, every field/table name must be escaped with `` ` ``
