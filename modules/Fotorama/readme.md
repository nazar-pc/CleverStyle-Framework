Fotorama during next update will need some tweaks:
* Make sure to compile `assets/html/cs-fotorama-styles-wrapper/index.pug` after updating `fotorama.css` near it
* if AMD support not merged yet (https://github.com/artpolikarpov/fotorama/pull/309) then wrap JS with `require(['jquery'], function (jQuery) {..});`
