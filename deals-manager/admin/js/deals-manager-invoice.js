(function( $ ) {
	'use strict';

	$(function() {
		var container = $('#line-items-container');
		if ( ! container.length ) {
			return;
		}
		var template = wp.template('line-item-template');

		// Calculate totals on page load
		calculate_totals();

		$('#add-line-item').on('click', function(e) {
			e.preventDefault();
			var index = container.find('.line-item').length;
			container.append( template({ index: index }) );
		});

		container.on('click', '.remove-line-item', function(e) {
			e.preventDefault();
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
		}
	});

})( jQuery );
