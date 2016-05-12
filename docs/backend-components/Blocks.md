Blocks are components of simplest construction. They are used for displaying information on the sidebars of web site.

There are 5 main positions for blocks:
* Top
* Right
* Bottom
* Left
* Floating

First 4 positions are obvious, they mean displaying of block around main content. Last position is really floating, it is used to insert block inside content with the help of inserting **<!--block#{block_num_here}-->** into raw html content.

Block may be presented by php files or just HTML specified in administration area of web site. In case of php file - it will be included, output will be used as block content. Obviously, file may contain only HTML without any php code.

All php blocks are located in directory **components/blocks** with names **block.{block_name}.php**. Usually blocks are used to display some information of modules, for example last news.