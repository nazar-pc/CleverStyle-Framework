Steps to create the simplest module:

1. Create module subdirectory in **components/modules**
2. Create index.html or index.php inside created directory with any content
3. Go to Administration >> Components >> Modules
4. Click **Update modules list**, created module will appear in modules list
5. Install module
6. Enable module
7. Click on module name, appeared in main menu
8. That's it! You have created module for CleverStyle Framework

Note: if you want to create module with php file, output should be done in next way:
```php
<?php
$Page = \cs\Page::instance();
$Page->content('Some content here');
```
Any other output will be ignored.
