Custom build of WebComponents.js is full build with following things removed (which allows to make it smaller for modern browsers):
* WeakMap, MutationObserver, URL polyfills (are included separately only for IE/Edge since other browsers doesn't need it)
* unresolved.js (provided in CSS form by CleverStyle CMS)
* lang.js (we do not support so old iOS devices)
