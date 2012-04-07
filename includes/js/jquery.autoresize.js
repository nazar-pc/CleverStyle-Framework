/*
 * jQuery autoResize (textarea auto-resizer)
 * @copyright James Padolsey http://james.padolsey.com
 * @version 1.04
 */
(function($){
    $.fn.autoResize = function() {
        this.filter('textarea').each(function(){
            var textarea = $(this).css({resize:'none','overflow-y':'hidden'}), origHeight = textarea.height(),
                clone = (function(){
                    var props = ['height','width','lineHeight','textDecoration','letterSpacing'], propOb = {};
                    $.each(props, function(i, prop){
                        propOb[prop] = textarea.css(prop);
                    });
                    return textarea.clone().removeAttr('id').removeAttr('name').css({
                        position: 'absolute', top: 0, left: -9999
                    }).css(propOb).attr('tabIndex','-1').insertBefore(textarea);
                })(),
                lastScrollTop = null,
                updateSize = function() {
                    clone.height(0).val($(this).val()).scrollTop(10000);
                    var scrollTop = Math.max(clone.scrollTop(), origHeight), toChange = $(this).add(clone);
                    if (lastScrollTop === scrollTop) { return; }
                    lastScrollTop = scrollTop;
                    if (scrollTop >= 500) {
                        $(this).css('overflow-y','');
                        return;
                    }
                   toChange.height(scrollTop);
                };
            textarea.unbind('.dynSiz').bind('keyup.dynSiz', updateSize).bind('keydown.dynSiz', updateSize).bind('change.dynSiz', updateSize);
        });
        return this;
    };
})(jQuery);