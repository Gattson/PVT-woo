<?php
/**
 * Plugin Name: Product Variation Table - Novo
 * Description: Displays a variation table for variable products with quantity selectors and add-to-cart buttons.
 * Version: 1.0
 * Author: Joseph Saad
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue CSS and JS
function pvt_enqueue_assets() {
    if ( is_product() ) {
        wp_enqueue_style( 'pvt-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
        wp_enqueue_script( 'pvt-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', ['jquery'], null, true );

        // Localize WooCommerce AJAX URL
        wp_localize_script( 'pvt-script', 'pvt_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'pvt_add_to_cart' )
        ] );
    }
}
add_action( 'wp_enqueue_scripts', 'pvt_enqueue_assets' );

// Add settings page to admin menu
function pvt_add_settings_page() {
    add_options_page(
        'Variation Table Settings',
        'Variation Table',
        'manage_options',
        'pvt-settings',
        'pvt_render_settings_page'
    );
}
add_action( 'admin_menu', 'pvt_add_settings_page' );

function pvt_register_settings() {
    register_setting('pvt_settings_group', 'pvt_enable_table');
    register_setting('pvt_settings_group', 'pvt_button_text');
    register_setting('pvt_settings_group', 'pvt_show_sku');
    register_setting('pvt_settings_group', 'pvt_show_price');
    register_setting('pvt_settings_group', 'pvt_show_stock');
	register_setting('pvt_settings_group', 'pvt_show_add_to_cart');

}
add_action( 'admin_init', 'pvt_register_settings' );

function pvt_render_settings_page() {
    ?>
    <div class="wrap">
        <h2>Variation Table Settings</h2>
        <div class="notice notice-info" style="margin-bottom: 20px; padding: 15px;">
            <h3>How to Use the Variation Table</h3>
            <p>To display the variation table on a product page, add the shortcode below to the product description or a shortcode-enabled area:</p>
            <code>[variation_table]</code>
            <p> Make sure the product is a <strong>variable product</strong> and the table is enabled in the settings below.</p>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('pvt_settings_group'); ?>
            <?php do_settings_sections('pvt_settings_group'); ?>

            <label>
                <input type="checkbox" name="pvt_enable_table" value="1" <?php checked(1, get_option('pvt_enable_table'), true); ?> />
                Enable Variation Table
            </label>
            <br><br>

            <label>
                Button Text:
                <input type="text" name="pvt_button_text" value="<?php echo esc_attr(get_option('pvt_button_text', 'Add')); ?>" />
            </label>
            <br><br>

            <h3>Visible Columns</h3>
            <label><input type="checkbox" name="pvt_show_sku" value="1" <?php checked(1, get_option('pvt_show_sku'), true); ?> /> SKU </label><br>
            <label><input type="checkbox" name="pvt_show_price" value="1" <?php checked(1, get_option('pvt_show_price'), true); ?> /> Price </label><br>
            <label><input type="checkbox" name="pvt_show_stock" value="1" <?php checked(1, get_option('pvt_show_stock'), true); ?> /> Stock Status </label><br>
			<label><input type="checkbox" name="pvt_show_add_to_cart" value="1" <?php checked(1, get_option('pvt_show_add_to_cart'), true); ?> /> Show “Add to Cart” Column </label><br>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Shortcode function
function custom_variation_table_shortcode() {
    if ( ! get_option('pvt_enable_table') ) {
        return '';
    }

    global $product;

    if ( ! is_product() || ! $product->is_type( 'variable' ) ) {
        return '<p>This widget only works on variable products.</p>';
    }

    $variations = $product->get_available_variations();
    $button_text = get_option('pvt_button_text', 'Add');

    ob_start();
    include plugin_dir_path( __FILE__ ) . 'includes/variation-table-template.php';
    return ob_get_clean();
}
add_shortcode( 'variation_table', 'custom_variation_table_shortcode' );


// Bulk add to cart function
add_action('wp_ajax_pvt_bulk_add_to_cart', 'pvt_handle_bulk_add_to_cart');
add_action('wp_ajax_nopriv_pvt_bulk_add_to_cart', 'pvt_handle_bulk_add_to_cart');

function pvt_handle_bulk_add_to_cart() {
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        wp_send_json_error(['message' => 'No items provided']);
    }

    $added = false;

    foreach ($_POST['items'] as $item) {
        $product_id = intval($item['product_id']);
        $variation_id = intval($item['variation_id'] ?? 0);
        $quantity = intval($item['quantity']);
        $nonce = sanitize_text_field($item['_wpnonce']);
        $variation = isset($item['variation']) ? array_map('sanitize_text_field', $item['variation']) : [];

        if (!wp_verify_nonce($nonce, 'pvt_add_to_cart')) {
            continue;
        }

        if ($product_id > 0 && $quantity > 0) {
            $result = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
            if ($result) {
                $added = true;
            }
        }
    }

    if ($added) {
         WC()->cart->calculate_totals();
		/*wc_set_cart_cookies();*/

		if (!defined('DOING_AJAX')) {
			define('DOING_AJAX', true);
		}

		wp_send_json_success([
			'added' => true,
			'cart_hash' => WC()->cart->get_cart_hash(),
			'fragments' => apply_filters('woocommerce_add_to_cart_fragments', [])

		]);

    } else {
        wp_send_json_error(['message' => 'Nothing was added to cart']);
    }
}
