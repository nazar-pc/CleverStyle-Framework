#### Font Awesome tweaks:
* `@font-family` declaration is moved from `font-awesome.min.css` to `index.pug` (since `@font-face` doesn't work from within Shadow DOM)
* `font-awesome.min.css` is renamed into `_font-awesome.min.scss` and is included by `style.scss` (Polymer's limitation, should ideally accept 2 stylesheets: https://github.com/Polymer/polymer/issues/4587)
* font file is split into multiple files by unicode ranges (up to 50 icons in each file) using [https://github.com/nazar-pc/unicode-range-splitter]
