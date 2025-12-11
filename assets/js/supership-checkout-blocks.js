document.addEventListener('wc-blocks-loaded', () => {

    const checkoutAPI = window.wc?.blocksCheckout;
    if (!checkoutAPI) {
        console.warn("SuperShip: Blocks Checkout API not available");
        return;
    }

    console.log("SuperShip: Blocks API Loaded");

    // 1️⃣ Đổi nút Place Order khi chọn SuperShip
    checkoutAPI.registerCheckoutFilters('supership-extension', {
        placeOrderButtonLabel: (defaultValue, extensions) => {
            const shipping = extensions?.cart?.shippingRates?.[0]?.method_id;

            if (shipping === 'supership') {
                return "Đặt đơn SuperShip";
            }
            return defaultValue;
        }
    });


    // 2️⃣ Hook trước khi WooCommerce tạo đơn
    document.addEventListener('wc-blocks-checkout-before-processing', async (event) => {

        const checkout = event.detail;
        const cart = checkout.cart;

        const shipping = cart.shippingRates?.[0]?.method_id;

        if (shipping !== 'supership') return;

        console.log("SuperShip: Preparing API payload...");

        const payload = {
            order_id: checkout.orderId,
            shipping_method: shipping,
            receiver: {
                name: checkout.shippingAddress.first_name + " " + checkout.shippingAddress.last_name,
                phone: checkout.shippingAddress.phone,
                address: checkout.shippingAddress?.['supership/address_detail']||"",
                province_code: checkout.shippingAddress?.['supership/province'] || "",
                district_code: checkout.extensions?.['supership/district'] || "",
                commune_code: checkout.extensions?.['supership/commune'] || "",
            },
            amount: cart.totals.total_price,
            value: cart.totals.total_price,
            weight: cart.itemsWeight,
        };

        try {
            const res = await wp.apiFetch({
                path: '/wp-json/supership/v1/create-order-from-checkout',
                method: 'POST',
                data: payload
            });

            if (!res.success) {
                throw new Error(res.message);
            }

            console.log("SuperShip Created:", res);

        } catch (err) {
            event.preventDefault();
            alert("Không thể tạo đơn SuperShip: " + err.message);
        }
    });

});
document.addEventListener('wc-blocks-loaded', () => {

    if (!window.wc || !window.wc.blocksCheckout) {
        console.warn("WC Blocks not loaded");
        return;
    }
    

    const { checkoutFilters } = window.wc.blocksCheckout;

    // Province → Load districts
    checkoutFilters.addFilter(
        'supership/province',
        'supership-update-district',
        async (value, fields) => {

            if (!value) return fields;
            console.log("Province changed:", value);
            const response = await wp.apiFetch({
                path: `/wp-json/supership/v1/districts?province_code=${value}`
            });

            fields['supership/district'].options = response.options;
            fields['supership/commune'].options = [];
            console.log("Fields object:", fields);
            return fields;
        }
    );

    // District → Load communes
    checkoutFilters.addFilter(
        'supership/district',
        'supership-update-commune',
        async (value, fields) => {
            console.log("District changed:", value);
            if (!value) return fields;

            const response = await wp.apiFetch({
                path: `/wp-json/supership/v1/communes?district_code=${value}`
            });

            fields['supership/commune'].options = response.options;
            console.log("Fields object:", fields);
            return fields;
        }
    );

});
