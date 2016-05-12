`cs.Event` - is system object, that handles events subscribing and dispatching, instance can be obtained in such way:
```javascript
Event	= window.cs.Event;
```

Object is the similar to [$Event](/docs/$Event.md) object on backend, see [examples](/docs/Events.md).

### [Methods](#methods)

<a name="methods" />
###[Up](#) Methods

`Event` object has next public methods:
* on()
* off()
* once()
* fire()

#### on(event : string, callback : function) : cs.Event
Subscribing for event. [More details, and example of use](/docs/Events.md#wiki-subscribing)
Callback might either return boolean value or Promise object.

#### off(event : string, callback : function) : cs.Event
Unsubscribing from event. [More details, and example of use](/docs/Events.md#wiki-subscribing)
Callback might either return boolean value or Promise object.

#### once(event : string, callback : function) : cs.Event
Subscribing for event for single execution. [More details, and example of use](/docs/Events.md#wiki-subscribing)
Callback might either return boolean value or Promise object.

#### fire(event : string, callback : function) : Promise
Dispatching of event. [More details, and example of use](/docs/Events.md#wiki-dispatching)
Will return Promise object, since event handlers might be asynchronous.
