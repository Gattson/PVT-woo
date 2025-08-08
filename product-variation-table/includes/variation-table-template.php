<div class="variation-table-container">
    <table class="variation-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Attributes</th>
                <th>Status</th>
                <th>Qty</th>
				
                <?php if ( get_option('pvt_show_sku') ) : ?><th>SKU</th><?php endif; ?>
                <?php if ( get_option('pvt_show_price') ) : ?><th>Price</th><?php endif; ?>
                <?php if ( get_option('pvt_show_stock') ) : ?><th>Stock</th><?php endif; ?>
				<?php if ( get_option('pvt_show_add_to_cart') ) : ?><th>Add</th><?php endif; ?>


                
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $variations as $variation ) :
                $variation_obj = wc_get_product( $variation['variation_id'] );

                if ( ! $variation_obj->is_in_stock() ) {
                    continue;
                }

                $image = wp_get_attachment_image( $variation_obj->get_image_id(), 'thumbnail' );
                $name = $variation_obj->get_name();
                $stock_status = $variation_obj->is_in_stock() ? 'In Stock' : 'Out of Stock';

                $attributes = [];
                foreach ( $variation_obj->get_attributes() as $key => $value ) {
                    $taxonomy = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
                    $attributes[] = $taxonomy . ': ' . $value;
                }
            ?>
            <tr data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
			data-variation_id="<?php echo esc_attr( $variation_obj->get_id() ); ?>"
			data-nonce="<?php echo wp_create_nonce('pvt_add_to_cart'); ?>">

                <td><?php echo $image; ?></td>
                <td><?php echo esc_html( $name ); ?></td>
                <td><?php echo esc_html( implode(', ', $attributes) ); ?>
				<?php foreach ( $variation['attributes'] as $attr_name => $attr_value ) : ?>
				<input type="hidden"
					data-attribute_name="<?php echo esc_attr( $attr_name ); ?>"
					value="<?php echo esc_attr( $attr_value ); ?>" />
				<?php endforeach; ?>
				</td>
                <td><?php echo esc_html( $stock_status ); ?></td>
                <td>
                    <div class="qty-wrapper">
                        <button class="qty-minus">−</button>
                        <input type="number" value="0" class="qty" min="0" />
                        <button class="qty-plus">+</button>
                    </div>
                </td>

                <?php if ( get_option('pvt_show_sku') ) : ?>
                    <td><?php echo esc_html( $variation_obj->get_sku() ); ?></td>
                <?php endif; ?>

                <?php if ( get_option('pvt_show_price') ) : ?>
                    <td><?php echo $variation_obj->get_price_html(); ?></td>
                <?php endif; ?>

                <?php if ( get_option('pvt_show_stock') ) : ?>
                    <td><?php echo esc_html( $variation_obj->get_stock_quantity() ); ?></td>
                <?php endif; ?>

                <?php if ( get_option('pvt_show_add_to_cart') ) : ?>
					<td>
						<button class="add-to-cart-button"
								data-product_id="<?php echo $variation_obj->get_id(); ?>"
								data-nonce="<?php echo wp_create_nonce('pvt_add_to_cart'); ?>">
							<?php echo esc_html( get_option('pvt_button_text', 'Add') ); ?>
						</button>
					</td>
				<?php endif; ?>
				

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
	<div class="bulk-add-wrapper" style="margin-top: 20px; text-align: right;">
					<button id="bulk-add-to-cart" class="add-to-cart-button" style="min-width: auto;"> Add Selected to Cart </button>
	</div>
</div>
