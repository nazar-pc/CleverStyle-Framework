As for now - there are only one type of templates - for blocks.

Template is used to customize view of [blocks components](/docs/Blocks).

All templates are located in directory **templates/blocks**, and have names **block.{template_name}.html** or **block.{template_name}.php**

Template is regular html or php file. It will be included, and in output:
* `<!--title-->` will be replaced by block title
* `<!--content-->` will be replaced by block content
