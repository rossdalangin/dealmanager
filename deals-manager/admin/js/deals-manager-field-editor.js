(function( $ ) {
    'use strict';

    $(function() {
        var container = $('#fields-container');
        if ( ! container.length ) {
            return;
        }
        var template = wp.template('field-item-template');

        // Make fields sortable
        container.sortable({
            handle: '.field-header',
            axis: 'y'
        });

        // Add new field
        $('#add-field').on('click', function(e) {
            e.preventDefault();
            var index = container.find('.field-item').length;
            container.append( template({ index: index }) );
        });

        // Remove field
        container.on('click', '.remove-field', function(e) {
            e.preventDefault();
            if ( confirm('Are you sure you want to remove this field?') ) {
                $(this).closest('.field-item').remove();
            }
        });

        // Toggle field settings
        container.on('click', '.edit-field', function(e) {
            e.preventDefault();
            $(this).closest('.field-item').find('.field-settings').slideToggle();
        });

        // Auto-generate field name from label
        container.on('keyup', '.field-label-input', function() {
            var label = $(this).val();
            var name = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
            $(this).closest('.field-settings').find('.field-name-input').val(name);
        });

        // Update header label and type on change
        container.on('keyup', '.field-label-input', function() {
            var label = $(this).val();
            $(this).closest('.field-item').find('.field-header .field-label').text(label);
        });
        container.on('change', '.field-type-input', function() {
            var type = $(this).val();
            $(this).closest('.field-item').find('.field-header .field-type').text(type);
        });
    });

})( jQuery );
