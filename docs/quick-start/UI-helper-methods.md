While [CleverStyle Widgets](/docs/quick-start/CleverStyle-Widgets.md) provide set of elements, for some use cases using plain elements is a bit too low-level and simpler wrappers might be very useful.

For this purposes CleverStyle Framework provides few convenient methods and properties under `cs.ui` namespace:
* `cs.ui.modal()`
* `cs.ui.simple_modal()`
* `cs.ui.alert()`
* `cs.ui.confirm()`
* `cs.ui.notify()`
* `cs.ui.ready`

#### cs.ui.modal(content : {HTMLElement}|{jQuery}|{String}) : HTMLElement
Generic interface for creating modal from string or element as its content.
Modal element will be attached to `document.documentElement` and returned.

#### cs.ui.simple_modal(content : {HTMLElement}|{jQuery}|{String}) : HTMLElement
Even simpler interface for creating modal from string or element as its content.
Modal will be opened right after creation and destroyed when closed.
Modal element will be attached to `document.documentElement` and returned.

#### cs.ui.alert(content : {HTMLElement}|{jQuery}|{String}) : HTMLElement
Interface for creating modal from string or element as its content specifically for replacing default `window.alert()` method.
Modal will be opened right after creation and destroyed when closed, also will have "OK" button which is focused automatically.
Modal element will be attached to `document.documentElement` and returned.

#### cs.ui.confirm(content : {HTMLElement}|{jQuery}|{String}, ok_callback : {Function} [, cancel_callback : {Function}]) : HTMLElement
Interface for creating modal from string or element as its content specifically for replacing default `window.confirm()` method.
Modal will be opened right after creation and destroyed when closed, also will have "OK" and "Cancel" buttons, "OK" is focused automatically.
Depending on button clicked `ok_callback` or `cancel_callback` will be called.
Modal element will be attached to `document.documentElement` and returned.

#### cs.ui.notify(content : {HTMLElement}|{jQuery}|{String} [, delay : {Number}][, type : {String}]]) : HTMLElement
Interface for creating notifications from string or element as its content.
`delay` and `type` arguments are both optional and can go in any order.
`delay` is number in seconds after which notification will be closed automatically (assumed 0 by default, namely shown until closed manually).
`type` is used to change notification appearance (by default generic appearance is used), one of:
* `success`
* `warning`
* `error`
Notification element will be attached to `document.documentElement` and returned.

#### cs.ui.ready : Promise
Promise, which is resolved when all Web Components are ready to be used
