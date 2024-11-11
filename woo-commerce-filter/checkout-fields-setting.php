<?php

add_action('admin_menu', 'checkout_fields_options_page');

function checkout_fields_options_page() {
    add_menu_page(
        'Checkout Fields Settings',
        'Checkout Fields',
        'manage_options',
        'checkout-fields-settings',
        'render_checkout_fields_options_page',
        'dashicons-admin-generic',
        20
    );
}
function render_checkout_fields_options_page() {
    // Fetch saved options
    $fields_options = get_option('checkout_fields_options', []);

    // Default fields (you can add more fields as needed)
    $checkout_fields = [
        'billing_first_name'=>'First Name',
		'billing_last_name'=>'Last Name',
		'billing_company'=>'Company Name',
		'billing_address_1'=>'Adresss 1',
		'billing_address_2'=>'Adress 2',
		'billing_city'=>'City',
		'billing_postcode'=>'Zip',
		'billing_country'=>'Country',
		'billing_state'=>'State',
		'billing_email'=>'Email Address',
		'billing_phone'=>'Phone',
		//'order_comments'=>'Aditional Notes',
    ];
    ?>
   <div class="wrap">
        <h1>Checkout Fields Settings</h1>
		<mark>
			Don't make required if Fields hidden.  use this shortcode for show filter - <b> [product_filter] </b>
	   </mark>
        <form method="post" action="options.php">
            <?php settings_fields('checkout_fields_options'); ?>

            <table class="form-table">
                <tr>
                    <th>Field Name</th>
                    <th>Hide</th>
                    <th>Required</th>
                    <th>Label</th>
                    <th>Placeholder</th>
                </tr>
                <?php foreach ($checkout_fields as $field_key => $field_label) : 
                    $show_field = !empty($fields_options[$field_key]['show']);
                    $required_field = !empty($fields_options[$field_key]['required']);
                    $label = $fields_options[$field_key]['label'] ?? $field_label;
                    $placeholder = $fields_options[$field_key]['placeholder'];
                ?>
                <tr>
                    <td><?php echo esc_html($field_label); ?></td>
                    <td>
                        <input type="checkbox" name="checkout_fields_options[<?php echo esc_attr($field_key); ?>][show]" value="1" <?php checked($show_field); ?> />
                    </td>
                    <td>
                        <input type="checkbox" name="checkout_fields_options[<?php echo esc_attr($field_key); ?>][required]" value="1" <?php checked($required_field); ?> />
                    </td>
                    <td>
                        <input type="text" name="checkout_fields_options[<?php echo esc_attr($field_key); ?>][label]" value="<?php echo esc_attr($label); ?>" placeholder="Enter custom label" />
                    </td>
                    <td>
                        <input type="text" name="checkout_fields_options[<?php echo esc_attr($field_key); ?>][placeholder]" value="<?php echo esc_attr($placeholder); ?>" placeholder="Enter custom placeholder" />
                    </td>
                </tr>
                <?php endforeach; ?>
				   <!-- Additional Notes Field with Label and Placeholder -->
				<tr> <td> <h3>It's pro feature <a href='#'>Buy Now </a> </h3> </td> </tr>
                <tr>
                    <td>Additional Notes</td>
                    <td>
                        <input type="checkbox" name="checkout_fields_options[additional_notes][show]" value="1" <?php checked(!empty($fields_options['additional_notes']['show'])); ?> />
                    </td>
                    <td>
                        <input type="checkbox" name="checkout_fields_options[additional_notes][required]" value="1" <?php checked(!empty($fields_options['additional_notes']['required'])); ?> />
                    </td>
                    <td>
                <input type="text" name="checkout_fields_options[additional_notes][label]" value="<?php echo esc_attr($fields_options['additional_notes']['label'] ?? ''); ?>" placeholder="Additional Notes Label" />
                    </td>
                           <td>
                      <input type="text" name="checkout_fields_options[additional_notes][placeholder]" value="<?php echo esc_attr($fields_options['additional_notes']['placeholder'] ?? ''); ?>" placeholder="Additional Notes Placeholder" />
                    </td>
				</tr>
			</table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php

		
}


add_action('admin_init', 'register_checkout_fields_options');

function register_checkout_fields_options() {
    register_setting('checkout_fields_options', 'checkout_fields_options');
}


add_filter('woocommerce_checkout_fields', 'modify_checkout_fields_based_on_settings');

add_filter('woocommerce_checkout_fields', 'modify_checkout_fields_based_on_settings');

function modify_checkout_fields_based_on_settings($fields) {
    $fields_options = get_option('checkout_fields_options', []);

    // Loop through the fields to apply settings
    foreach ($fields_options as $field_key => $options) {
        // Determine the field's type (billing, shipping, or order)
        if (isset($fields['billing'][$field_key])) {
            $field_group = 'billing';
        } elseif (isset($fields['shipping'][$field_key])) {
            $field_group = 'shipping';
        } elseif (isset($fields['order'][$field_key])) {
            $field_group = 'order';
        } else {
            continue;
        }

        // Show/Hide Field
        if (!empty($options['show'])) {
            unset($fields[$field_group][$field_key]);
            continue;
        }

        // Required Field
        $fields[$field_group][$field_key]['required'] = !empty($options['required']);

        // Custom Label
        if (!empty($options['label'])) {
            $fields[$field_group][$field_key]['label'] = esc_html($options['label']);
        }

        // Custom Placeholder
        if (!empty($options['placeholder'])) {
            $fields[$field_group][$field_key]['placeholder'] = esc_html($options['placeholder']);
        }
    }
	
	 if (isset($fields_options['additional_notes'])) {
        if (!empty($fields_options['additional_notes']['show'])) {
            unset($fields['order']['order_comments']);
			echo "<style> .woocommerce-additional-fields {display:none;}</style>";
        } else {
            // Set the Additional Notes field as required
            $fields['order']['order_comments']['required'] = !empty($fields_options['additional_notes']['required']);
            
            // Set custom label and placeholder if provided
            if (!empty($fields_options['additional_notes']['label'])) {
                $fields['order']['order_comments']['label'] = esc_html($fields_options['additional_notes']['label']);
            }
            if (!empty($fields_options['additional_notes']['placeholder'])) {
                $fields['order']['order_comments']['placeholder'] = esc_html($fields_options['additional_notes']['placeholder']);
            }
        }
    }
	

    return $fields;
}
