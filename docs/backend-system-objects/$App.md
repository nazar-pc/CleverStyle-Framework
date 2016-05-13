`$App` - is system object, that provides functionality of application execution (plugins processing, blocks and module page generation, etc.), instance can be obtained in such way:
```php
<?php
$App = \cs\App::instance();
```

### [Methods](#methods) [Properties](#properties) [Events](#events)

<a name="methods" />
###[Up](#) Methods

`$App` object has next public methods:
* execute()

#### execute()
Executes plugins processing, blocks and module page generation, typically called by system

<a name="properties" />
###[Up](#) Properties

`$App` object has next public properties:
* controller_path

#### $controller_path : string[]
Path that will be used by controller to render page (read only)

<a name="events" />
###[Up](#) Events

`$App` object supports next events:
* System/App/block_render
* System/App/construct
* System/App/load/before
* System/App/load/after

#### System/App/block_render
This event is used for custom rendering (or even rendering skipping) for certain blocks. Array:

```
[
    'index'        => $index,        //Block index
    'blocks_array' => &$blocks_array //Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
]
```
is set as parameter for event. *&$blocks_array* reference is used for storing of rendered blocks, so, rendered block should be as added to corresponding position element of this array (in html string form). Also after custom rendering closure for event should return boolean `false` to stop further block rendering.

#### System/App/construct
This event is used mainly by modules, and executes in constructor right before plugins inclusion.

#### System/App/render/before
This event is used mainly by plugins, fired before module and blocks being rendered.

#### System/App/render/after
This event is used mainly by plugins, fired after module and blocks being rendered.
