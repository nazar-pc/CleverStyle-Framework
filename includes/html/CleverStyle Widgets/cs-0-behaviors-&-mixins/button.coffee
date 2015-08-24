###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer.cs.behaviors.button =
	properties	:
		action	:
			type	: String
			value	: 'button_action'
		active	:
			notify				: true
			reflectToAttribute	: true
			type				: Boolean
		bind	:
			observer	: '_bind_changed'
			type		: Object
		empty	:
			reflectToAttribute	: true
			type				: Boolean
		icon	:
			reflectToAttribute	: true
			type				: String
		primary	:
			reflectToAttribute	: true
			type				: Boolean
	ready : ->
		if !@childNodes.length
			@empty = true
	_bind_changed : ->
		if @bind
			# Yeah, a bit tricky here:)
			# Save bind element in local variable and we drop binding to element to avoid memory leaks
			bind_element	= @bind
			@bind			= null
			# Well, we can't bind method of other object to property of current one without losing `this` inside that method
			# That is why we bind element itself to `bind` property and method name to `action` (`button_action` method assumed by default)
			action	= bind_element[@action].bind(bind_element)
			# Add events listeners
			@addEventListener('click', action)
			@addEventListener('tap', action)
			# Also in order to avoid memory leaks we need to know when bind element is detached from DOM
			# If it is detached after one second - we'll drop binding
			observer	= 		(new MutationObserver (mutations) ->
				mutations.forEach (mutation) ->
					# Only removed nodes are interesting
					if !mutation.removedNodes
						return
					for node in mutation.removedNodes
						if node != bind_element
							return
						# If bind element was removed give it 1 second to attache somewhere else
						# Disconnect observer, since we are not interested in old parent node anymore
						observer.disconnect()
						# Wait a second
						setTimeout (->
							# If no parent node -> element is still detached -> drop all event listeners to avoid any connections with that element
							# and thus memory leaks
							if !bind_element.parentNode
								@removeEventListener('click', action)
								@removeEventListener('tap', action)
							else
								# New parent, lets reconfigure observer again
								observer.observe(bind_element.parentNode, {childList : true})
						), 1000
			)
			observer.observe(bind_element.parentNode, {childList : true, subtree: false})
