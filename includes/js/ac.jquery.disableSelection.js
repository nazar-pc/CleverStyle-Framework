/**
 * jQuery Plugin disableSelection
 * Disable selection of text content within the set of matched elements.
 *
 * @return {Object} jQuery
 * @chainable
 */
(function ($){
	$.fn.disableSelection = function () {
		return this.on(
			($.support.selectstart ? 'selectstart' : 'mousedown') + '.disableSelection',
			function (event) {
				event.preventDefault();
		});
	};
	$.fn.enableSelection = function () {
		return this.off('.disableSelection');
	};
})(jQuery);
