Fotorama during next update will need some tweaks:
* `fotorama.css` needs to be renamed to `_fotorama.scss` in order to be patched for Web Components support
* if AMD support not merged yet (https://github.com/artpolikarpov/fotorama/pull/309) then wrap JS with `require(['jquery'], function (jQuery) {..});`
