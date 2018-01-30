/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
/**
 * Events system similar to one found on backend, including available methods and arguments order, but instead of returning boolean it returns Promise instance
 * Similarly, callbacks may return either boolean result or no result (just like on backend) or Promise instance or any other object that has compatible `then`
 * method (jQuery Deferred as example)
 */
cs.Event	= async_eventer()
