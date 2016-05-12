Languages are presented in form of PHP file and/or JSON file, that contains translations in JSON format. For simplier editing JSON files may contain comments, they are placed in separate lines and starts from double slash //

Example of JSON language file:
```json
{
	//This is comment, it will be ignored
	"some_words": "Some words"
}
```
Example of PHP language file:
```php
<?php
$L				= \cs\Language::instance();
//Translations can be set separately
$L->some_words	= 'Some words';
//Or in form of arrays
$L->set([
	'one_more_phrase' => 'One more phrase',
	'The last phrase' => 'The last phrase'
]);
```
All core translations are stored in directory **core/languages**. Translations of components usually are located in **languages** directory of component location of server.