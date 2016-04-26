/**
 * Baton Conductor
 */

var baton_conductor = baton_conductor || {};

( function ( wp, $ ) {
	"use strict";

	var api = wp.customize;

	/**
	 * Document Ready
	 */
	$( function() {
		var $baton_conductor_number = $( '.baton-conductor-number' ),
			$baton_conductor_flexbox_range = $( '.baton-conductor-flexbox-columns-range' ),
			$baton_conductor_output = $( '.baton-conductor-output' ),
			baton_conductor_section = api.section( 'baton_conductor' ),
			baton_conductor_disabled_control = api.control( 'baton_conductor[disabled]' );

		// Baton Conductor Section
		if ( baton_conductor_section ) {
			// Append <sup> label to titles
			baton_conductor_section.container.find( '.accordion-section-title, .customize-section-title h3' ).append( '<sup>' + baton_conductor.customizer.section_sup_label + '</sup>' );
		}

		// Baton Conductor numbers, only allow numerical characters into input boxes
		$baton_conductor_number.on( 'keyup', function( e ) {
			var $this = $( this ), numeric_value = $this.val().replace( /[^0-9]/g, '' );

			if (  $this.val() != numeric_value ) {
				$this.val( numeric_value );
			}
		} );

		// On flexbox column change (jQuery "input" event)
		$baton_conductor_flexbox_range.on( 'input', function() {
			var $this = $( this );

			// Adjust the value
			$this.next( '.baton-conductor-flexbox-columns-value' ).html( $this.val() );
		} );

		// Initialize Backbone Views on Conductor Widgets (on initial page load)
		// Create a new output view and store it in widget data
		$baton_conductor_output.data( 'baton-conductor-output', new baton_conductor.views.output( {
			el: $baton_conductor_output, // Attach this view to the widgets output list
			collection: new baton_conductor.collections.output() // New collection
		} ) );

		// Baton Conductor Disabled Control
		if ( baton_conductor_disabled_control ) {
			// Append <sup> label to title
			baton_conductor_disabled_control.container.find( '.customize-control-title' ).append( ' <sup>' + baton_conductor.customizer.section_sup_label + '</sup>' + ' ' + baton_conductor.customizer.control_enabled_label );
		}
	} );


	/*******************
	 * Backbone Models *
	 *******************/

	baton_conductor.models = {
		output: Backbone.Model.extend( {
			// Model defaults
			defaults: {
				priority: baton_conductor.output.priority_step_size, // Default is 10
				id: false,
				label: false,
				type: false,
				visible: true
			}
		} )
	};


	/************************
	 * Backbone Collections *
	 ************************/

	baton_conductor.collections = {
		output: Backbone.Collection.extend( {
			model: baton_conductor.models.output
		} )
	};


	/******************
	 * Backbone Views *
	 ******************/

	// Baton Conductor Output
	baton_conductor.views = {
		output: Backbone.View.extend( {
			el: '.baton-conductor-output',
			$output_list: false,
			$output_list_items: false,
			$widget: false,
			collection: new baton_conductor.collections.output(),
			events: {
				// Labels (editable input elements)
				'click .baton-conductor-output-element-label-editable.editable-input': 'editElementLabel',

				'keypress .baton-conductor-output-element-label-editable.editable-input input': 'saveElementLabel',
				'click .baton-conductor-output-element-label-editable.editable-input .baton-conductor-save': 'saveElementLabel', // Save
				'click .baton-conductor-output-element-label-editable.editable-input .baton-conductor-discard': 'saveElementLabel', // Discard

				// Options (editable select elements)
				'click .baton-conductor-output-element-label-editable.editable-select': 'editElementOption',

				'click .baton-conductor-output-element-label-editable.editable-select .baton-conductor-save': 'saveElementOption', // Save
				'click .baton-conductor-output-element-label-editable.editable-select .baton-conductor-discard': 'saveElementOption', // Discard

				// Link
				'click .baton-conductor-link' : 'toggleLink', // Link

				// Visibility
				'click .baton-conductor-output-element-controls .baton-conductor-visibility' : 'toggleVisibility', // Visibility

				// jQuery Sortable
				'sortstop .baton-conductor-output-list' : 'sortableStop' // jQuery Sortable Stop
			},
			// jQuery Sortable options
			sortable_options: {
				handle: '.dashicons-sort',
				axis: 'y', // Vertically
				cursor: 'move',
				placeholder: 'ui-state-placeholder'
			},
			initialize: function() {
				var self = this;

				// Bind "this" to all functions/callbacks
				_.bindAll( this,
					'render',
					'editElementLabel',
					'saveElementLabel',
					'editElementOption',
					'saveElementOption',
					'toggleLink',
					'toggleVisibility',
					'sortableStop',
					'destroy' );

				// Store a reference to the widget
				this.$widget = this.$el.parents( '.widget' );

				// Store a reference to the output element list
				this.$output_list = this.$el.find( '.baton-conductor-output-list' );

				// Store a reference to the output element list
				this.$output_list_items = this.$output_list.find( 'li' );

				/*
				 * Backbone Models
				 */
				this.$output_list_items.each( function() {
					var $self = $( this ), model = new baton_conductor.models.output( {
						priority: ( ( $self.index() + 1 ) * baton_conductor.output.priority_step_size ), // Default is 10
						id: $self.attr( 'data-id' ),
						label: $self.attr( 'data-label' ),
						type: $self.attr( 'data-type' ),
						link: ( $self.attr( 'data-link' ) === 'true' ),
						visible: ( $self.attr( 'data-visible' ) === 'true' )
					} );

					// Add the new model to the collection
					self.collection.add( model );
				} );

				/*
				 * jQuery Sortable - Initialize jQuery Sortable
				 */
				this.$output_list.sortable( this.sortable_options );
			},
			editElementLabel: function( event ) {
				var $el = $( event.currentTarget );

				$el.addClass( 'editing' );
				$el.find( 'input' ).focus().attr( 'data-current', $el.find( 'input').val() );
			},
			editElementOption: function( event ) {
				var $el = $( event.currentTarget );

				$el.addClass( 'editing' );
				$el.find( 'select' ).attr( 'data-current', $el.find( 'select' ).val() );
			},
			saveElementLabel: function( event ) {
				var $el = $( event.currentTarget ),
					$output_element,
					escaped_val,
					original,
					$input;

				// Enter (save)
				if ( event.type === 'keypress' && event.which === 13 ) {
					$output_element = $el.closest( '.baton-conductor-output-element' );
					escaped_val = _.escape( $el.val() );

					// Remove editing class from label wrapper
					$el.parents( '.baton-conductor-output-element-label' ).removeClass( 'editing' );

					// Set the current label value
					if ( escaped_val.length ) {
						$output_element.attr( 'data-label', escaped_val ).find( '.label' ).html( escaped_val );
					}
					// No label entered, revert back to original
					else {
						original = $el.attr( 'data-original' );

						// Reset back to the original value
						$el.val( '' );
						$output_element.attr( 'data-label', original ).find( '.label' ).html( original );
					}

					// Update the sortable data
					this.sortableStop( false, false );

					//event.preventDefault();
				}

				// Click (save or discard)
				if ( event.type === 'click' ) {
					$output_element = $el.closest( '.baton-conductor-output-element' );
					$input = $el.parent().find( 'input' );
					escaped_val = _.escape( $input.val() );

					// Remove editing class from label wrapper
					$el.parents( '.baton-conductor-output-element-label' ).removeClass( 'editing' );

					// Save
					if ( $el.hasClass( 'baton-conductor-save' ) ) {
						// Set the current label value
						if ( escaped_val.length ) {
							$output_element.attr( 'data-label', escaped_val ).find( '.label' ).html( escaped_val );
						}
						// No label entered, revert back to original
						else {
							original = $input.attr( 'data-original' );

							// Reset back to the original value
							$el.val( '' );
							$output_element.attr( 'data-label', original ).find( '.label' ).html( original );
						}

						// Update the sortable data
						this.sortableStop( false, false );
					}

					// Discard
					if ( $el.hasClass( 'baton-conductor-discard' ) ) {
						// Reset back to the original value
						$input.val( $input.attr( 'data-current' ) );
					}

					// Prevent Default and Propagation
					event.preventDefault();
					event.stopPropagation();
				}
			},
			saveElementOption: function( event ) {
				var $el = $( event.currentTarget ),
					$output_element = $el.closest( '.baton-conductor-output-element' ),
					$select = $el.parent().find( 'select' ),
					escaped_val = _.escape( $select.val()),
					$selected = $select.find( ':selected' );

					// Remove editing class from label wrapper
					$el.parents( '.baton-conductor-output-element-label' ).removeClass( 'editing' );

					// Save
					if ( $el.hasClass( 'baton-conductor-save' ) ) {
						// Set the current value
						if ( escaped_val.length ) {
							$output_element.attr( 'data-value', escaped_val ).attr( 'data-label', $selected.attr( 'data-label' ) ).find( '.label' ).html( $selected.attr( 'data-label' ) );
						}
						// No value entered, revert back to original
						else {
							var original = $select.attr( 'data-original' );

							// Reset back to the original value
							$select.val( original );
							$output_element.attr( 'data-value', original );
						}

						// Update the sortable data
						this.sortableStop( false, false );
					}

					// Discard
					if ( $el.hasClass( 'baton-conductor-discard' ) ) {
						// Reset back to the original value
						$select.attr( 'data-current', $output_element.attr( 'data-value' ) );
						$select.val( $select.attr( 'data-current' ) );
					}

					// Prevent Default and Propagation
					event.preventDefault();
					event.stopPropagation();
			},
			toggleLink: function( event ) {
				var $el = $( event.currentTarget ), $parent = $el.parents( '.baton-conductor-output-element' ),
					model = this.collection.findWhere( { id: $parent.attr( 'data-id' ) } );

				// Toggle the link class
				$parent.toggleClass( 'link' );

				// Update the model and the data-visible attr
				if ( $parent.hasClass( 'link' ) ) {
					$parent.attr( 'data-link', 'true' );
					model.set( 'link', true );
				}
				else {
					$parent.attr( 'data-link', 'false' );
					model.set( 'link', false );
				}

				// Update the sortable data
				this.sortableStop( false, false );
			},
			toggleVisibility: function( event ) {
				var $el = $( event.currentTarget ), $parent = $el.parents( '.baton-conductor-output-element' ),
					model = this.collection.findWhere( { id: $parent.attr( 'data-id' ) } );

				// Toggle the visible class
				$parent.toggleClass( 'visible' );

				// Update the model and the data-visible attr
				if ( $parent.hasClass( 'visible' ) ) {
					$parent.attr( 'data-visible', 'true' );
					model.set( 'visible', true );
				}
				else {
					$parent.attr( 'data-visible', 'false' );
					model.set( 'visible', false );
				}

				// Update the sortable data
				this.sortableStop( false, false );
			},
			// When jQuery Sortable has stopped
			sortableStop: function( event, ui ) {
				var $baton_conductor_output_data = this.$el.find( '.baton-conductor-output-data' ),
					data = {}, self = this;

				// Clear the collection
				this.collection.reset();

				// Reset the output list items
				this.$output_list_items = this.$output_list.find( 'li' );

				// Each output element
				this.$output_list_items.each( function() {
					var $self = $( this ), model = new baton_conductor.models.output( {
						priority: ( ( $self.index() + 1 ) * baton_conductor.output.priority_step_size ), // Default is 10
						id: $self.attr( 'data-id' ),
						label: $self.attr( 'data-label' ),
						type: $self.attr( 'data-type' ),
						visible: ( $self.attr( 'data-visible' ) === 'true' )
					} ),
						index = $self.index(), priority = $self.attr( 'data-priority' ),
						new_priority = ( ( index + 1 ) * baton_conductor.output.priority_step_size ),
						value = $self.attr( 'data-value' ),
						link = $self.attr( 'data-link' );

					// Adjust priority
					$self.attr( 'data-priority', new_priority );
					model.set( 'priority', new_priority );

					// Store data in array
					data[model.get( 'priority' ).toString()] = {
						'id': model.get( 'id' ),
						'priority': model.get( 'priority' ),
						'label': model.get( 'label' ),
						'type': model.get( 'type' ),
						'visible': model.get( 'visible' )
					};

					// Add value data
					if ( typeof value !== undefined && value !== false ) {
						model.set( 'value', value );

						data[model.get( 'priority' ).toString()].value = value;
					}

					// Add link data
					if ( typeof link !== undefined && link !== false ) {
						model.set( 'link', ( link !== 'false' ) ? link : false );

						data[model.get( 'priority' ).toString()].link = ( link !== 'false' ) ? link : false;
					}

					// Add the new model to the collection
					self.collection.add( model );
				} );

				// Add data string to widget (hidden input elements do not automatically trigger the "change" method)
				$baton_conductor_output_data.val( JSON.stringify( data ) ).trigger( 'change' );
			},
			// Completely destroy this view and all event handlers
			destroy: function() {
				this.undelegateEvents();
				this.remove();
			}
		} )
	};
}( wp, jQuery ) );