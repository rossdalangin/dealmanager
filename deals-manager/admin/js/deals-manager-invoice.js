(function( $ ) {
	'use strict';

	$(function() {
		console.log('Deals Manager Invoice script loaded.');

		var container = $('#line-items-container');
		if ( ! container.length ) {
			console.error('Line items container not found.');
			return;
		}
		console.log('Line items container found:', container);

		if (typeof wp === 'undefined' || typeof wp.template === 'undefined') {
			console.error('wp.template is not available. Check if wp-util is enqueued.');
			return;
		}

		var template = wp.template('line-item-template');
		console.log('Line item template found.');

		// Calculate totals on page load
		calculate_totals();

		$('#add-line-item').on('click', function(e) {
			e.preventDefault();
			console.log('Add Item button clicked.');
			var index = container.find('.line-item').length;
			console.log('New item index:', index);
			container.append( template({ index: index }) );
			console.log('New item appended.');
		});

		container.on('click', '.remove-line-item', function(e) {
			e.preventDefault();
			console.log('Remove Item button clicked.');
			$(this).closest('.line-item').remove();
			calculate_totals();
		});

		container.on('change keyup', '.line-item-quantity, .line-item-price', function() {
			var row = $(this).closest('.line-item');
			var quantity = row.find('.line-item-quantity').val();
			var price = row.find('.line-item-price').val();
			var total = ( quantity * price ).toFixed(2);
			row.find('.line-item-total').text(total);
			calculate_totals();
		});

		function calculate_totals() {
			var subtotal = 0;
			container.find('.line-item').each(function() {
				var quantity = $(this).find('.line-item-quantity').val();
				var price = $(this).find('.line-item-price').val();
				subtotal += ( quantity * price );
			});
			$('#invoice-subtotal').text( subtotal.toFixed(2) );
			console.log('Totals calculated. Subtotal:', subtotal.toFixed(2));
		}
	});

})( jQuery );
