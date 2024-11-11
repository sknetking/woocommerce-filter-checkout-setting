jQuery(document).ready(function ($) {
    function filterProducts() {
        var form = $('#product-filter-form');
        var data = form.serialize();

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: data + '&action=filter_products',
            success: function (response) {
                $('#filtered-products').html(response);
                $('#clear-filters').show(); // Show "Clear All Filters" button
            }
        });
    }

    // Trigger filter on input change
    $('#product-filter-form input').change(function () {
        filterProducts();
    });

    // Clear filters
    $('#clear-filters').click(function () {
        $('#product-filter-form')[0].reset();
        filterProducts();
        $(this).hide(); // Hide the clear button after resetting
    });
});