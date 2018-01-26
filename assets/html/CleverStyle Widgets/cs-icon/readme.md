#### Font Awesome tweaks:
* `@font-family` declaration is moved from `fontawesome-min.min.css` to `index.pug` (since `@font-face` doesn't work from within Shadow DOM)
* TODO: font file is split into multiple files by unicode ranges (up to 50 icons in each file) using [https://github.com/nazar-pc/unicode-range-splitter]
