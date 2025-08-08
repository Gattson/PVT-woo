document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.qty-wrapper').forEach(wrapper => {
        const minus = wrapper.querySelector('.qty-minus');
        const plus = wrapper.querySelector('.qty-plus');
        const input = wrapper.querySelector('.qty');

        minus.addEventListener('click', e => {
            e.stopPropagation();
            let current = parseInt(input.value);
            if (current > 0) {
                input.value = current - 1;
            }
        });

        plus.addEventListener('click', e => {
            e.stopPropagation();
            let current = parseInt(input.value);
            input.value = current + 1;
        });
    });

    document.querySelectorAll('.add-to-cart-button').forEach(btn => {
        btn.addEventListener('click', () => {
            const qtyInput = btn.closest('tr').querySelector('.qty');
            const qty = parseInt(qtyInput.value);
            const productId = btn.getAttribute('data-product_id');
            const nonce = btn.getAttribute('data-nonce');

            if (qty > 0) {
                jQuery.post(pvt_ajax.ajax_url, {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: qty,
                    _wpnonce: nonce
                }, function(response) {
                    if (response && response.fragments) {
                        alert('Added to cart!');
                        jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                });
            } else {
                alert('Please select a quantity.');
            }
        });
    });
	
	document.getElementById('bulk-add-to-cart')?.addEventListener('click', () => {
    const rows = document.querySelectorAll('.variation-table tbody tr');
    const items = [];

    rows.forEach(row => {
        const qtyInput = row.querySelector('.qty');
        const qty = parseInt(qtyInput?.value || '0');
        const productId = row.getAttribute('data-product_id');
        const variationId = row.getAttribute('data-variation_id');
        const nonce = row.getAttribute('data-nonce');

        const variationData = {};
        row.querySelectorAll('[data-attribute_name]').forEach(attr => {
            const name = attr.getAttribute('data-attribute_name');
            const value = attr.getAttribute('value'); //  FIXED
            if (name && value) {
                variationData[name] = value;
            }
        });

        if (qty > 0 && productId && nonce) {
            items.push({
                product_id: productId,
                variation_id: variationId || '',
                variation: variationData,
                quantity: qty,
                _wpnonce: nonce
            });
        }
    });

		if (items.length > 0) {
			console.log('Bulk items to add:', items); //  DEBUG

			jQuery.post(pvt_ajax.ajax_url, {
				action: 'pvt_bulk_add_to_cart',
				items: items
			}, function(response) {
				if (response && response.success) {
					jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
					alert('Selected items added to cart!');
				} else {
					alert(response.message || 'Error adding items to cart.');
				}
			}).fail(() => {
		alert('AJAX request failed. Please check your connection.');
});

		} else {
			alert('Please enter quantities before adding.');
		}
	});



});
