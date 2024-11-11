<?php
// WooCommerce hooks and opening content wrappers (standard in archive-product.php)
do_action( 'woocommerce_before_main_content' );

// Display your custom product filter shortcode
echo do_shortcode('[product_filter]');

// WooCommerce closing content wrappers
do_action( 'woocommerce_after_main_content' );
?>