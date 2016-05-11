`$Menu` -  system object, that is used in administration for generating second and third level of menu:
```php
<?php
$Menu	= \cs\Menu::instance();
```

### [Methods](#methods) [Events](#events)

<a name="methods" />
###[Up](#) Methods

`$Menu` object has only one public method:
* get_menu()
* add_section_item()
* add_item()

#### get_menu() : string
Just returns HTML code of generated menu, is called by system itself when needed

#### add_section_item ($module : string, $title : string, $href = false : bool|string, $attributes = [] : array) : string
Add second-level item into menu.
All third-level items which start with the same `$href` will be inside this second-level menu item.

Example of usage:
```php
<?php
$Menu	= \cs\Menu::instance();
$Menu->add_section_item('System', 'General', "admin/System/general");
```

#### add_item ($module : string, $title : string, $href = false : bool|string, $attributes = [] : array) : string
Add third-level item into menu (second-level when there is corresponding section items)

Example of usage (together with second-level menu):
```php
<?php
$Menu	= \cs\Menu::instance();
$Menu->add_section_item('System', 'General', "admin/System/general");
$Menu->add_item('System', 'Optimization', "admin/System/general/optimization");
```


<a name="events" />
###[Up](#) Events

`$Menu` object supports next event:
* admin/System/Menu

Event is called from `get_menu()` methods when it is necessary to generate menu, no arguments passed into callback.
