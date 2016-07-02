`$App` - is system object, that provides functionality of application execution (blocks and module page generation, etc.), instance can be obtained in such way:
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
Executes blocks and module page generation, typically called by system

<a name="properties" />
###[Up](#) Properties

`$App` object has next public properties:
* controller_path

#### $controller_path : string[]
Path that will be used by controller to render page (read only)

<a name="events" />
###[Up](#) Events

`$App` object supports next events:
* System/App/render/before
* System/App/execute_router/before
* System/App/execute_router/after
* System/App/block_render
* System/App/render/after

#### System/App/render/before
Fired before module and blocks being rendered.

#### System/App/execute_router/before
Event is executed before router execution, allows to override default router execution entirely

#### System/App/execute_router/after
Event is executed after router execution, allows to customize results of rendering

#### System/App/block_render
This event is used for custom rendering (or even rendering skipping) for certain blocks. Array:

```
[
    'index'        => $index,        //Block index
    'blocks_array' => &$blocks_array //Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
]
```
is set as parameter for event. *&$blocks_array* reference is used for storing of rendered blocks, so, rendered block should be as added to corresponding position element of this array (in html string form). Also after custom rendering closure for event should return boolean `false` to stop further block rendering.

#### System/App/render/after
Fired after module and blocks being rendered.
