<?php
/**
 * WooCommerce GA4 DataLayer (Vilpy) — shipping/cart rules + single purchase (v2 with double-event guards and reflow-safe cart handler)
 */
namespace Vilpy;

if (!defined('ABSPATH')) { exit; }

class WooCommerceDataLayer
{
    const OPTION_KEY   = 'wc-datalayer';
    const NONCE_ACTION = '';
    const AJAX_ADD     = '';
    const AJAX_REMOVE  = '';

    public function __construct()
    {
        if (!get_option(self::OPTION_KEY)) {
            return;
        }
    }

    public function registerHooks($loader)
    {

        if (!class_exists('WooCommerce')) {
            return;
        }

        $datalayerOptie = get_option("wc-datalayer");
        if (!$datalayerOptie) {
            return;
        }
        
        $loader->addAction('wp_head', $this, 'beginDataLayer', 1);
        $loader->addAction('wp_enqueue_scripts', $this, 'enqueue_scripts');

        // Views
        $loader->addAction('woocommerce_after_single_product', $this, 'view_item');
        $loader->addAction('woocommerce_after_shop_loop', $this, 'view_item_list');
        $loader->addAction('wp', $this, 'view_cart');

        // Checkout start (server only)
        $loader->addAction('woocommerce_checkout_init', $this, 'begin_checkout', 10, 1);

        // Purchase
        $loader->addAction('woocommerce_thankyou', $this, 'purchase', 10, 1);

        // Optional custom AJAX
        $loader->addAction('wp_ajax_' . self::AJAX_ADD, $this, 'add_to_cart_ajax');
        $loader->addAction('wp_ajax_nopriv_' . self::AJAX_ADD, $this, 'add_to_cart_ajax');
        $loader->addAction('wp_ajax_' . self::AJAX_REMOVE, $this, 'remove_from_cart_ajax');
        $loader->addAction('wp_ajax_nopriv_' . self::AJAX_REMOVE, $this, 'remove_from_cart_ajax');
        $loader->addFilter('woocommerce_cart_item_remove_link', $this, 'add_product_data_to_remove_link', 10, 2);
    }

    public function beginDataLayer()
    {
        $currency = esc_js(get_woocommerce_currency());
        echo "<script>
            window.dataLayer = window.dataLayer || [];
            window.__vilpyCurrency = '{$currency}';
            window.__vilpyPushed = window.__vilpyPushed || {};
        </script>";
    }

    public function enqueue_scripts()
    {
        // Register a handle to attach inline JS to
        wp_register_script('vilpy-wc-datalayer', '', ['jquery', 'wc-add-to-cart'], null, true);
        wp_enqueue_script('vilpy-wc-datalayer');

        $cfg = [
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce(self::NONCE_ACTION),
            'cartUrl'  => wc_get_cart_url(),
            'currency' => get_woocommerce_currency(),
        ];
        wp_add_inline_script('vilpy-wc-datalayer', 'window.VilpyWCDL=' . wp_json_encode($cfg) . ';', 'before');

        ob_start(); ?>
(function($){
    if (!window.dataLayer) window.dataLayer = [];
    var cfg = window.VilpyWCDL || {};
    var currency = cfg.currency || (window.__vilpyCurrency || 'EUR');

    /* =========================================================
     *  GENERIC HELPERS
     * ========================================================= */

    // Veilig pushen naar dataLayer met eventnaam + payload
    function dlPush(eventName, payload){
        try {
            window.dataLayer.push($.extend({ event: eventName }, payload || {}));
        } catch (e) {}
    }

    // Price parser: "€ 1.234,56" -> 1234.56 (EU-format focus)
    function parsePriceFromText(text){
        if (!text) return undefined;
        var norm = text.replace(/[^\d,.\-]/g, '');

        // verwijder thousand-separator dots (groepjes van 3 cijfers)
        norm = norm.replace(/\.(?=\d{3}(\D|$))/g, '');

        // eerste komma wordt punt
        norm = norm.replace(',', '.');

        var num = parseFloat(norm);
        return isNaN(num) ? undefined : num;
    }

    // Haal de laatst bekende items voor cart/checkout op
    function getCheckoutItemsFromDataLayer(eventName){
        eventName = eventName || 'view_cart';
        var dl = window.dataLayer || [];
        // van achter naar voren (laatste event eerst)
        for (var i = dl.length - 1; i >= 0; i--) {
            var hit = dl[i];
            if (hit && hit.event === eventName && hit.ecommerce && hit.ecommerce.items) {
                return hit.ecommerce.items;
            }
        }
        return [];
    }

    // Publieke helper: altijd items uit __vilpyCheckoutItems of laatste view_cart
    function getCheckoutItems(){
        if (Array.isArray(window.__vilpyCheckoutItems) && window.__vilpyCheckoutItems.length) {
            return window.__vilpyCheckoutItems;
        }
        return getCheckoutItemsFromDataLayer('view_cart');
    }

    // Zoek een item in de checkout items op basis van product-id (item_id)
    function findItemByProductId(pid){
        if (!pid) return null;
        var items = getCheckoutItems();
        var pidStr = String(pid);
        for (var i = 0; i < items.length; i++) {
            if (items[i] && String(items[i].item_id) === pidStr) {
                return items[i];
            }
        }
        return null;
    }

    // Bouw standaard GA4 ecommerce payload voor remove_from_cart
    function buildRemovePayload(pid, name, price, qty){
        // Waar mogelijk aanvullen met bestaande checkout-item
        var matched = findItemByProductId(pid);

        var itemName  = name  || (matched && matched.item_name) || undefined;
        var itemPrice = (price != null) ? Number(price) :
                        (matched && matched.price != null ? Number(matched.price) : undefined);
        var itemQty   = (qty != null && qty !== '') ? Number(qty) :
                        (matched && matched.quantity != null ? Number(matched.quantity) : 1);

        var value = (itemPrice != null && itemQty != null) ? itemPrice * itemQty : undefined;

        return {
            ecommerce: {
                currency: currency,
                value: value,
                items: [{
                    item_id: pid ? String(pid) : undefined,
                    item_name: itemName,
                    quantity: itemQty,
                    price: itemPrice
                }]
            }
        };
    }

    /* =========================================================
     *  GUARDS
     * ========================================================= */

    // Remove guard (voorkomt dubbele remove_from_cart)
    var lastRemovedKey = null;
    function pushRemoveOnce(uniqueKey, payload){
        if (uniqueKey && uniqueKey === lastRemovedKey) return;
        lastRemovedKey = uniqueKey;
        dlPush('remove_from_cart', payload);
    }

    // Cross-guard voor dubbele add_to_cart (Woo event + fallback click)
    var atcGuardTs = 0;

    /* =========================================================
     *  ADD TO CART - Woo core event
     * ========================================================= */

    $(document.body).on('adding_to_cart', function(e, $button, data){
        atcGuardTs = Date.now();
        try {
            var $scope = $button.closest('.product, .summary');

            var name = $scope
                .find('.product_title, .woocommerce-loop-product__title')
                .first()
                .text()
                .trim() || undefined;

            var itemId = (data.variation_id && data.variation_id !== 0)
                ? String(data.variation_id)
                : String(data.product_id);

            var qty   = Number(data.quantity || 1);
            var price = $button.data('price'); // kan undefined zijn

            dlPush('add_to_cart', {
                ecommerce: {
                    currency: currency,
                    value: (price != null) ? Number(price) * qty : undefined,
                    items: [{
                        item_id: itemId,
                        item_name: name,
                        quantity: qty,
                        price: (price != null) ? Number(price) : undefined
                    }]
                }
            });
        } catch (err) {}
    });

    /* =========================================================
     *  ADD TO CART - fallback (custom buttons / thema-knoppen)
     * ========================================================= */

    (function(){
        var addGuard = false; // beschermt tegen double-clicks

        $(document).on('click', '.ajax_add_to_cart, .single_add_to_cart_button, a.add_to_cart_button', function(){
            if (addGuard) return;

            // Cross-guard: als Woo-event net fired, skip fallback
            if (Date.now() - atcGuardTs < 800) return;

            addGuard = true;
            setTimeout(function(){ addGuard = false; }, 500);

            try {
                var $btn   = $(this);
                var $scope = $btn.closest('.product, .summary');

                var pid = $btn.data('product_id') || $btn.val();
                var vid = $btn.data('variation_id') || $('input.variation_id').val() || undefined;

                var qty = Number(
                    ($scope.find('.qty').val() ||
                     $('.qty').first().val() || 1)
                );

                var name = $scope
                    .find('.product_title, .woocommerce-loop-product__title')
                    .first()
                    .text()
                    .trim() || undefined;

                var priceAttr = $btn.data('price');
                var itemId = (vid && Number(vid))
                    ? String(vid)
                    : (pid ? String(pid) : undefined);

                dlPush('add_to_cart', {
                    ecommerce: {
                        currency: currency,
                        value: (priceAttr != null) ? Number(priceAttr) * (qty || 1) : undefined,
                        items: [{
                            item_id: itemId,
                            item_name: name,
                            quantity: qty || 1,
                            price: (priceAttr != null) ? Number(priceAttr) : undefined
                        }]
                    }
                });
            } catch (e) {}
        });
    })();

    /* =========================================================
     *  REMOVE FROM CART - klik op remove links
     * ========================================================= */

    $(document).on('click', '.woocommerce-cart a.remove, .mini_cart_item a.remove', function(){
        try {
            var $btn = $(this);
            var uniqueKey = $btn.data('cart_item_key') || undefined;

            var pid   = $btn.data('product_id');
            var name  = $btn.data('product_name');
            var price = $btn.data('price');
            var qty   = $btn.data('quantity');

            var payload = buildRemovePayload(pid, name, price, qty);
            pushRemoveOnce(uniqueKey, payload);
        } catch (err) {}
    });

    /* =========================================================
     *  REMOVE FROM CART - Woo "removed_from_cart" event
     * ========================================================= */

    $(document.body).on('removed_from_cart', function(e, fragments, cart_hash, $button){
        try {
            var $btn = ($button && $button.length)
                ? $button
                : $(document.activeElement);

            var uniqueKey = $btn && $btn.data('cart_item_key') || null;
            var pid       = $btn && $btn.data('product_id');
            var name      = $btn && $btn.data('product_name');
            var price     = $btn && $btn.data('price');
            var qty       = $btn && $btn.data('quantity');

            var payload = buildRemovePayload(pid, name, price, qty);
            pushRemoveOnce(uniqueKey, payload);
        } catch (err) {}
    });

    /* =========================================================
     *  SELECT_ITEM - klik op product in een lijst
     * ========================================================= */

    (function(){
        $(document).on('click', '.products .product a.woocommerce-LoopProduct-link, .products .product a[href*="/product/"]', function(){
            try {
                var $link = $(this);
                var $prod = $link.closest('.product');

                var name = $prod
                    .find('.woocommerce-loop-product__title')
                    .first()
                    .text()
                    .trim() || undefined;

                var pid = $prod
                    .find('.add_to_cart_button, .ajax_add_to_cart')
                    .data('product_id') || $prod.data('product_id');

                var priceAttr = $prod
                    .find('[data-price]')
                    .first()
                    .data('price');

                var listName = window.__vilpyItemListName || undefined;

                dlPush('select_item', {
                    ecommerce: {
                        item_list_name: listName,
                        items: [{
                            item_id: pid ? String(pid) : undefined,
                            item_name: name,
                            price: (priceAttr != null) ? Number(priceAttr) : undefined
                        }]
                    }
                });
            } catch (e) {}
        });
    })();

    /* =========================================================
     *  SELECT_PROMOTION - coupon gebruikt in cart
     * ========================================================= */

    $(document.body).on('applied_coupon_in_cart', function(e, coupon_code){
        try {
            if (!coupon_code) {
                coupon_code = $('.cart-discount input[name="coupon_code"], .checkout_coupon input[name="coupon_code"]')
                    .val() || undefined;
            }
            if (!coupon_code) return;

            var items = getCheckoutItems().map(function(i){
                return {
                    item_id: i.item_id,
                    item_name: i.item_name,
                    price: i.price,
                    quantity: i.quantity
                };
            });

            dlPush('select_promotion', {
                ecommerce: {
                    promotion_id: coupon_code,
                    promotion_name: coupon_code,
                    items: items
                }
            });
        } catch (err) {}
    });

    /* =========================================================
     *  ADD_SHIPPING_INFO - verzendmethode gekozen/gewijzigd
     * ========================================================= */

    (function(){
        var lastTier = null;

        function currentTier(){
            var $checked = $('input[name^="shipping_method"]:checked');
            if (!$checked.length) return null;

            var text = $checked.closest('li, label').text().trim();
            return text || $checked.val() || null;
        }

        function pushShipping(){
            try {
                var tier = currentTier();
                if (!tier || tier === lastTier) return;
                lastTier = tier;

                var items = getCheckoutItems().map(function(i){
                    return {
                        item_id: i.item_id,
                        item_name: i.item_name,
                        price: i.price,
                        quantity: i.quantity
                    };
                });

                dlPush('add_shipping_info', {
                    ecommerce: {
                        shipping_tier: tier,
                        items: items
                    }
                });

            } catch (e) {}
        }

        $(document).on('change', 'input[name^="shipping_method"]', pushShipping);
    })();

    /* =========================================================
     *  ADD_PAYMENT_INFO - betaalmethode gekozen/gewijzigd
     * ========================================================= */

    (function(){
        var lastPM = null;

        function pushPM(){
            try {
                var pm = $('input[name="payment_method"]:checked').val() || null;
                if (!pm || pm === lastPM) return;

                lastPM = pm;

                var items = getCheckoutItems().map(function(i){
                    return {
                        item_id: i.item_id,
                        item_name: i.item_name,
                        price: i.price,
                        quantity: i.quantity
                    };
                });

                dlPush('add_payment_info', {
                    ecommerce: {
                        payment_type: pm,
                        items: items
                    }
                });
            } catch (e) {}
        }

        $(document).on('change', 'input[name="payment_method"]', pushPM);
    })();

    /* =========================================================
     *  VIEW_CART - cart update (qty change, totals updated)
     * ========================================================= */

    (function(){
        var scheduled = false;
        var lastCartSnapshot = null;

        function toMap(items){
            var map = {};
            if (!Array.isArray(items)) return map;
            items.forEach(function(i){
                if (!i || !i.item_id) return;
                var key = String(i.item_id);
                var qty = Number(i.quantity || 0);
                map[key] = (map[key] || 0) + qty;
            });
            return map;
        }

        function diffAndPushCartChanges(newItems){
            if (!Array.isArray(lastCartSnapshot)) {
                // eerste snapshot, niets diffen
                lastCartSnapshot = newItems;
                return;
            }

            var prev = toMap(lastCartSnapshot);
            var next = toMap(newItems);

            // items met gewijzigde qty of nieuwe items
            Object.keys(next).forEach(function(id){
                var oldQty = prev[id] || 0;
                var newQty = next[id] || 0;
                if (newQty > oldQty) {
                    var diff = newQty - oldQty;
                    dlPush('add_to_cart', {
                        ecommerce: {
                            currency: currency,
                            items: [{
                                item_id: id,
                                quantity: diff
                            }]
                        }
                    });
                } else if (newQty < oldQty) {
                    var diff = oldQty - newQty;
                    dlPush('remove_from_cart', {
                        ecommerce: {
                            currency: currency,
                            items: [{
                                item_id: id,
                                quantity: diff
                            }]
                        }
                    });
                }
            });

            // items die volledig verdwenen zijn
            Object.keys(prev).forEach(function(id){
                if (!(id in next)) {
                    var qty = prev[id];
                    dlPush('remove_from_cart', {
                        ecommerce: {
                            currency: currency,
                            items: [{
                                item_id: id,
                                quantity: qty
                            }]
                        }
                    });
                }
            });

            lastCartSnapshot = newItems;
        }

        function buildAndPush(){
            scheduled = false;
            try {
                var rows  = document.querySelectorAll('.cart_item');
                var items = new Array(rows.length);

                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];

                    var nameEl = row.querySelector('.product-name a') ||
                                 row.querySelector('.product-name');
                    var name = nameEl ? (nameEl.textContent || '').trim() : undefined;

                    var pid = row.getAttribute('data-product_id');
                    if (!pid) {
                        var hiddenPid = row.querySelector('input[name$="[product_id]"]');
                        pid = hiddenPid ? hiddenPid.value : undefined;
                    }

                    var qtyInput = row.querySelector('.qty');
                    var qty = qtyInput ? Number(qtyInput.value || 1) : 1;

                    var priceEl  = row.querySelector('.product-price .amount');
                    var rawPrice = priceEl ? priceEl.textContent : '';
                    var priceNum = parsePriceFromText(rawPrice);

                    items[i] = {
                        item_id: pid ? String(pid) : undefined,
                        item_name: name,
                        price: priceNum,
                        quantity: qty
                    };
                }

                // Sla items globaal op zodat andere events (shipping/payment/remove) ze ook kunnen gebruiken
                window.__vilpyCheckoutItems = items;

                // Diff t.o.v. vorige staat & push add/remove events voor qty wijzigingen
                diffAndPushCartChanges(items);

                dlPush('view_cart', {
                    ecommerce: {
                        currency: currency,
                        value: undefined, // eventueel total toevoegen als je wilt
                        items: items
                    }
                });
            } catch (e) {}
        }

        $(document.body).on('updated_cart_totals', function(){
            if (scheduled) return; // bursts samenvoegen
            scheduled = true;
            // defer naar volgende frame zodat Woo klaar is met layout
            requestAnimationFrame(buildAndPush);
        });
    })();

})(jQuery);

        <?php
        $script = trim(ob_get_clean());
        wp_add_inline_script('vilpy-wc-datalayer', $script, 'after');
    }

    public function purchase($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) { return; }

        $items = [];
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) { continue; }
            $qty = (int) $item->get_quantity();
            $subtotal = (float) $item->get_subtotal();
            $unit_price = $qty > 0 ? ($subtotal / $qty) : 0;

            $items[] = [
                'item_name' => $item->get_name(),
                'item_id'   => $product->get_sku() ?: (string) $product->get_id(),
                'price'     => $unit_price,
                'quantity'  => $qty,
            ];
        }

        $transaction_id = (string) $order->get_order_number();

        $ecommerce = [
            'transaction_id' => $transaction_id,
            'affiliation'    => get_bloginfo('name'),
            'value'          => (float) $order->get_total(),
            'tax'            => (float) $order->get_total_tax(),
            'shipping'       => (float) $order->get_shipping_total(),
            'currency'       => get_woocommerce_currency(),
            'items'          => array_map(function($i){
                return [
                    'item_id'   => $i['item_id'],
                    'item_name' => $i['item_name'],
                    'price'     => $i['price'],
                    'quantity'  => $i['quantity'],
                ];
            }, $items),
        ];

        $coupon_codes = $order->get_coupon_codes();
        $coupon_str   = implode(',', $coupon_codes);
        if ($coupon_str !== '') {
            $ecommerce['coupon'] = $coupon_str;
        }

        $payload = [ 'event' => 'purchase', 'ecommerce' => $ecommerce ];

        echo '<script>
        (function(){
          window.dataLayer = window.dataLayer || [];
          window.__vilpyPushed = window.__vilpyPushed || {};
          var oid = ' . wp_json_encode($transaction_id) . ';
          try {
            var k = "vilpy_purchase_" + oid;
            if (localStorage.getItem(k)) return;
            localStorage.setItem(k, "1");
          } catch(e) {}
          if (window.__vilpyPushed["purchase"]) return;
          window.__vilpyPushed["purchase"] = true;
          window.dataLayer.push(' . wp_json_encode($payload) . ');
        }());
        </script>';
    }

    public function begin_checkout($checkout)
    {
        // FIX: geen scripts injecten in AJAX-responses (update_order_review / checkout submit)
        if (wp_doing_ajax()) {
            return;
        }

        if (WC()->cart->is_empty()) {
            return;
        }

        $items = [];
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product) {
                continue;
            }
            $items[] = [
                'item_name' => $product->get_name(),
                'item_id'   => $product->get_sku() ?: (string) $product->get_id(),
                'price'     => (float) $product->get_price(),
                'quantity'  => (int) $cart_item['quantity'],
            ];
        }

        if (!$items) {
            return;
        }

        $value   = (float) WC()->cart->get_cart_contents_total()
                 + (float) WC()->cart->get_shipping_total()
                 + (float) WC()->cart->get_taxes_total();

        $coupons = WC()->cart->get_applied_coupons();

        $ecommerce = [
            'currency' => get_woocommerce_currency(),
            'value'    => $value,
            'items'    => array_map(function ($i) {
                return [
                    'item_id'   => $i['item_id'],
                    'item_name' => $i['item_name'],
                    'price'     => $i['price'],
                    'quantity'  => $i['quantity'],
                ];
            }, $items),
        ];

        $coupon_str = implode(',', $coupons);
        if ($coupon_str !== '') {
            $ecommerce['coupon'] = $coupon_str;
        }

        $payload = [
            'event'     => 'begin_checkout',
            'ecommerce' => $ecommerce,
        ];

        echo '<script>
    window.dataLayer = window.dataLayer || [];
    window.__vilpyPushed = window.__vilpyPushed || {};
    window.__vilpyCheckoutItems = ' . wp_json_encode($items) . ';
    (function(){
        if (window.__vilpyPushed["begin_checkout"]) return;
        window.__vilpyPushed["begin_checkout"] = true;
        window.dataLayer.push(' . wp_json_encode($payload) . ');
    }());
    </script>';
    }


    public function add_to_cart_ajax()
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity     = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1;
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;

        if (!$product_id) {
            wp_send_json_error(['message' => 'Missing product_id'], 400);
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        if ($added) {
            wp_send_json_success(['cart_item_key' => $added]);
        }

        wp_send_json_error(['message' => 'Could not add to cart'], 500);
    }

    public function remove_from_cart_ajax()
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
        if (!$cart_item_key) {
            wp_send_json_error(['message' => 'Missing cart_item_key'], 400);
        }

        $removed = WC()->cart->remove_cart_item($cart_item_key);
        if ($removed) {
            wp_send_json_success();
        }

        wp_send_json_error(['message' => 'Could not remove item'], 500);
    }

    public function view_item_list()
    {
        if (!is_shop() && !is_product_category() && !is_product_tag() && !is_search()) { return; }

        global $wp_query;
        if (empty($wp_query) || empty($wp_query->posts)) { return; }

        $items = [];
        $ids = wp_list_pluck($wp_query->posts, 'ID');

        foreach ($ids as $pid) {
            $product = wc_get_product($pid);
            if (!$product) { continue; }

            $items[] = [
                'item_name' => $product->get_name(),
                'item_id'   => $product->get_sku() ?: (string) $product->get_id(),
                'price'     => (float) $product->get_price(),
            ];
        }

        if (!$items) { return; }

        $list_id = 0;
        $list_name = 'Shop';
        if (is_product_category() || is_product_tag()) {
            $qo = get_queried_object();
            if ($qo) {
                $list_id = (int) $qo->term_id;
                $list_name = $qo->name ?: $list_name;
            }
        } elseif (is_search()) {
            $list_name = 'Search results';
        }

        $payload = [
            'event'     => 'view_item_list',
            'ecommerce' => [
                'currency'       => get_woocommerce_currency(),
                'item_list_id'   => (string) $list_id,
                'item_list_name' => $list_name,
                'items'          => array_map(function($i){
                    return [
                        'item_id'   => $i['item_id'],
                        'item_name' => $i['item_name'],
                        'price'     => $i['price'],
                    ];
                }, $items),
            ],
        ];

        echo '<script>
        window.dataLayer=window.dataLayer||[];
        window.__vilpyPushed=window.__vilpyPushed||{};
        window.__vilpyItemListName=' . wp_json_encode($list_name) . ';
        window.__vilpyItemListId=' . wp_json_encode((string)$list_id) . ';
        (function(){
            if(window.__vilpyPushed["view_item_list"])return;
            window.__vilpyPushed["view_item_list"]=true;
            window.dataLayer.push(' . wp_json_encode($payload) . ');
        }())
        </script>';
    }

    public function view_item()
    {
        if (!is_singular('product')) { return; }
        global $product;
        if (!$product) { return; }

        $item = [
            'item_name' => $product->get_name(),
            'item_id'   => $product->get_sku() ?: (string) $product->get_id(),
            'price'     => (float) $product->get_price(),
        ];

        $payload = [
            'event'     => 'view_item',
            'ecommerce' => [
                'currency' => get_woocommerce_currency(),
                'items'    => [[
                    'item_id'   => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'price'     => $item['price'],
                ]],
            ],
        ];

        echo '<script>
        window.dataLayer=window.dataLayer||[];
        window.__vilpyPushed=window.__vilpyPushed||{};
        (function(){
            if(window.__vilpyPushed["view_item"])return;
            window.__vilpyPushed["view_item"]=true;
            window.dataLayer.push(' . wp_json_encode($payload) . ');
        }())
        </script>';
    }

    public function view_cart()
    {
        if (!function_exists('is_cart') || !is_cart()) { return; }
        if (WC()->cart->is_empty()) { return; }

        $items = [];
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product) { continue; }
            $items[] = [
                'item_id'   => $product->get_sku() ?: (string) $product->get_id(),
                'item_name' => $product->get_name(),
                'price'     => (float) $product->get_price(),
                'quantity'  => (int) $cart_item['quantity'],
            ];
        }
        if (!$items) { return; }

        $value = (float) WC()->cart->get_cart_contents_total();

        $payload = [
            'event'     => 'view_cart',
            'ecommerce' => [
                'currency' => get_woocommerce_currency(),
                'value'    => $value,
                'items'    => $items,
            ],
        ];

        echo '<script>
        window.dataLayer=window.dataLayer||[];
        window.__vilpyPushed=window.__vilpyPushed||{};
        (function(){
            if(window.__vilpyPushed["view_cart"])return;
            window.__vilpyPushed["view_cart"]=true;
            window.dataLayer.push(' . wp_json_encode($payload) . ');
        }())
        </script>';
    }

    public function add_product_data_to_remove_link($link, $cart_item_key)
    {
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if (!$cart_item) {
            return $link;
        }

        $product = $cart_item['data'];
        if (!$product) {
            return $link;
        }

        $product_id = $product->get_id();
        $product_name = $product->get_name();
        $product_price = $product->get_price();
        $quantity = $cart_item['quantity'];

        $link = str_replace(
            'href=',
            sprintf(
                'data-product_id="%s" data-product_name="%s" data-price="%s" data-quantity="%s" href=',
                esc_attr($product_id),
                esc_attr($product_name),
                esc_attr($product_price),
                esc_attr($quantity)
            ),
            $link
        );

        return $link;
    }
}
