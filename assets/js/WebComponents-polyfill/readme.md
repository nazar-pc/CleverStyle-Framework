Custom build of WebComponents.js is full build with following things removed (which allows to make it smaller for modern browsers, available in [special branch](https://github.com/nazar-pc/webcomponentsjs/tree/for-modern-browsers)):
* WeakMap, MutationObserver, URL polyfills (are included separately only for Edge since other browsers doesn't need it)
* unresolved.js (provided in CSS form by CleverStyle Framework)
* lang.js (we do not support so old iOS devices)

Following additional patches currently applied on top of upstream version:
* https://github.com/webcomponents/webcomponentsjs/pull/589
* https://github.com/webcomponents/webcomponentsjs/pull/642
