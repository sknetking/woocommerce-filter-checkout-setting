<?php
/**
 * Plugin Name: WooCommerce-pro-filter
 * Plugin URI: https://woocommerce-filter.com/
 * Description: An ecommerce toolkit that helps you sell anything. Beautifully. use this shortcode for show filter - [product_filter] 
 * Version: 9.3.3
 * Author: Shyam
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 * Requires at least: 6.5
 * Requires PHP: 7.4
 *
 * Required- WooCommerce
 */

defined( 'ABSPATH' ) || exit;


function custom_product_filter_scripts() {
    wp_enqueue_style('custom-css',plugin_dir_url( __FILE__ ).'/style.css');
	
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-product-filter-ajax',plugin_dir_url( __FILE__ ).'/custom-product-filter.js', array('jquery'), null, true);
    wp_localize_script('custom-product-filter-ajax', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'custom_product_filter_scripts');

include "checkout-fields-setting.php";

function custom_product_filter_shortcode() {
    ob_start();

    echo '<div id="filter-wrapper" style="display: flex;">';
    
    // Filter section
    echo '<div id="product-filters" style="width: 25%; padding-right: 20px;">';
    echo '<form id="product-filter-form">';

    // Price filter
    echo '<h4>Filter by Price</h4>';
    echo '<div class="filter-price">';
    echo '<input type="number" name="min_price" placeholder="Min Price" />';
    echo '<input type="number" name="max_price" placeholder="Max Price" />';
    echo '</div>';

    // Product attribute filters
    $attributes = wc_get_attribute_taxonomies();
    foreach ($attributes as $attribute) {
        $taxonomy = 'pa_' . $attribute->attribute_name;
        $terms = get_terms($taxonomy);

        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<div class="filter-' . esc_attr($attribute->attribute_name) . '">';
            echo '<h4>' . esc_html($attribute->attribute_label) . '</h4>';

            foreach ($terms as $term) {
                echo '<label>';
                echo '<input type="checkbox" class="filter-attribute" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" />';
                echo esc_html($term->name);
                echo '</label>';
            }

            echo '</div>';
        }
    }

    echo '<button type="button" id="clear-filters" style="display: none;">Clear All Filters</button>';
    echo '</form>';
    echo '</div>';

    // Product listings section
    echo '<div id="filtered-products" style="width: 75%;">';
    echo do_shortcode('[products]'); // Display initial product list
    echo '</div>';

    echo '</div>';

    return ob_get_clean();
}

add_shortcode('product_filter', 'custom_product_filter_shortcode');


function custom_plugin_override_woocommerce_template( $template, $template_name, $template_path ) {
    // Check if we're loading the archive-product.php template
    if ( 'archive-product.php' === $template_name ) {
        // Define the path to your custom template in the plugin
        $plugin_template = plugin_dir_path( __FILE__ ) . 'woocommerce/archive-product.php';
        
        // Check if the custom template exists in the plugin, then use it
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }

    // Return the original template if our custom one is not used
    return $template;
}
add_filter( 'woocommerce_locate_template', 'custom_plugin_override_woocommerce_template', 10, 3 );


function custom_product_filter_callback() {
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 9,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND']
    ];

    // Price filter
    if (!empty($_POST['min_price']) || !empty($_POST['max_price'])) {
        if (!empty($_POST['min_price'])) {
            $args['meta_query'][] = [
                'key' => '_price',
                'value' => $_POST['min_price'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }

        if (!empty($_POST['max_price'])) {
            $args['meta_query'][] = [
                'key' => '_price',
                'value' => $_POST['max_price'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }
    }

    // Attribute filters
    $attributes = wc_get_attribute_taxonomies();
    foreach ($attributes as $attribute) {
        $taxonomy = 'pa_' . $attribute->attribute_name;
        if (!empty($_POST[$taxonomy])) {
            $args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $_POST[$taxonomy],
                'operator' => 'IN'
            ];
        }
    }

    $query = new WP_Query($args);
?>
<div class="woocommerce columns-4 ">
	<ul class="products columns-4">
	<?php 
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            wc_get_template_part('content', 'product');			
		endwhile;
		 ?>
	</ul>
</div>
	<?php
    } else {
        echo '<p>No products found.</p>';
    }

    wp_reset_postdata();
    die();
}
add_action('wp_ajax_filter_products', 'custom_product_filter_callback');
add_action('wp_ajax_nopriv_filter_products', 'custom_product_filter_callback');
