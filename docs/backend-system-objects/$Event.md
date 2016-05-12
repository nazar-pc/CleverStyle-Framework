`$Event` - is system object, that handles events subscribing and dispatching, instance can be obtained in such way:
```php
<?php
$Event	= \cs\Event::instance();
```

### [Methods](#methods)

<a name="methods" />
###[Up](#) Methods

`$Event` object has next public methods:
* on()
* off()
* once()
* fire()

#### on($event : string, $callback : callable) : bool
Subscribing for event. [More details, and example of use](/docs/Events.md#wiki-subscribing)

#### off($event : string, $callback : callable) : bool
Unsubscribing from event. [More details, and example of use](/docs/Events.md#wiki-subscribing)

#### once($event : string, $callback : callable) : bool
Subscribing for event for single execution. [More details, and example of use](/docs/Events.md#wiki-subscribing)

#### fire($event : string, $param1 = null : mixed|null, $_ = null : mixed|null) : bool
Dispatching of event. [More details, and example of use](/docs/Events.md#wiki-dispatching)
