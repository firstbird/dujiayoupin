(function($) {
    console.log('NBD Cart JS loaded');
    function checkLastDesignId() {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            const maxWaitTime = 10000; // 3秒
            const checkInterval = 100; // 每100ms检查一次
            
            const intervalId = setInterval(() => {
                // 获取最后一个design_id
                const lastDesignId = $('.wp-block-woocommerce-cart-items-block').find('.wc-block-cart-items__row')
                    .last()
                    .find('.wc-block-components-product-details__design-id .wc-block-components-product-details__value')
                    .text();
                    
                // console.log('Checking last design_id:', lastDesignId);
                
                if (lastDesignId) {
                    // 找到有效的design_id，停止检查
                    clearInterval(intervalId);
                    resolve(true);
                } else if (Date.now() - startTime >= maxWaitTime) {
                    // 超时，停止检查
                    clearInterval(intervalId);
                    console.warn('Timeout waiting for design_id');
                    resolve(false);
                }
            }, checkInterval);
        });
    }
    async function handleCartUpdate(eventName) {
        console.log('Cart event triggered:', eventName);
        const hasDesignId = await checkLastDesignId();
    
        if (!hasDesignId) {
            return;
        }
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_nbd_cart_data'
            },
            success: function(response) {
                console.log('js Cart data:', response.data);
                if (response.success) {
                    // 更新购物车项图片
                    $('.wp-block-woocommerce-cart-items-block').find('.wc-block-cart-items__row').each(function() {
                        const $cartItem = $(this);
                        console.log('Cart item:', $cartItem.html());
                        var design_id = $cartItem.find('.wc-block-components-product-details__design-id .wc-block-components-product-details__value').text();

                        // 如果找不到，尝试备用选择器
                        if (!design_id) {
                            design_id = $cartItem.find('.product-details__design-id .wc-block-components-product-details__value').text();
                        }
                        
                        console.log('Cart item view design_id:', design_id);
                        // console.log('Cart item data:', response.data);
                        if (design_id != '' && response.data) {
                            // cartItemKey = cartItemKey.trim().replace(/\s+/g, '');
                            // const cartItemData = response.data;
                            // const firstKey = cartItemData['cart_item_key'];
                            // const firstValue = cartItemData['cart_image'];
                            let matchedItem = response.data.find(item => {
                                // 假设每个cart item都有一个唯一的key
                                //return item['cart_item_key'] === cartItemKey;
                                return item['design_id'] === design_id;
                            });
                            if (matchedItem) {
                                console.log('found item cart image: ', matchedItem['cart_image'], ' cart_item_key: ', matchedItem['cart_item_key']);
                                let baseUrl = window.location.protocol + '//' + window.location.host + '/';
                                href = baseUrl + '?task=edit'
                                    + '&product_id=' + matchedItem['product_id']
                                    + '&nbd_item_key=' + matchedItem['nbd_item_key']
                                    + '&cik=' + matchedItem['cart_item_key']  // 购物车项key
                                    + '&design_id=' + matchedItem['design_id']
                                    + '&view=m'                // 视图参数
                                    + '&rd=cart';             // 返回购物车
                                // href += '&nbo_cart_item_key=' + cartItemKey;
                                // href += '&design_id=' + matchedItem['design_id'];
                                $cartItem.find('.wc-block-cart-item__cart-key .wc-block-cart-item__cart-key-image').attr('src',
                                    matchedItem['cart_image']+ '?t=' + new Date().getTime());
                                // $cartItem.find('.wc-block-cart-item__cart-key .wc-block-cart-item__cart-key-title').text('用户定制预览');
                                $cartItem.find('.wc-block-cart-item__cart-key-title').empty()
                                    // 添加纯文本
                                    .append('用户定制预览   ')  // 注意后面加了空格
                                    // 添加链接
                                    .append(
                                        $('<a>', {
                                            href: href,  // 这里需要设置实际的编辑链接
                                            text: '返回修改',
                                            class: 'nbd-edit-design-link',
                                            click: function(e) {
                                                e.preventDefault();
                                                // 这里可以添加编辑设计的处理逻辑
                                                console.log('Edit design clicked');
                                                // 先解绑之前的事件处理器
                                                $(this).off('click contextmenu');

                                                // 获取修改链接
                                                var href = $(this).attr('href');

                                                // 添加新的事件处理
                                                $(this).on('click', function(e) {
                                                    e.preventDefault(); // 阻止默认行为
                                                    
                                                    // 统一处理链接
                                                    var modifyUrl = new URL(href);
                                                    
                                                    // 确保所有必要的参数都存在
                                                    if (!modifyUrl.searchParams.has('task')) {
                                                        modifyUrl.searchParams.append('task', 'edit');
                                                    }
                                                    if (!modifyUrl.searchParams.has('design_id')) {
                                                        var design_id = $cartItem.find('.product-details__design-id .wc-block-components-product-details__value').text();
                                                        modifyUrl.searchParams.append('design_id', design_id);
                                                    }
                                                    
                                                    // 根据点击方式处理
                                                    if (e.ctrlKey || e.metaKey || e.which === 2) {
                                                        // Ctrl+点击 或 中键点击 - 在新标签页打开
                                                        window.open(modifyUrl.toString(), '_blank');
                                                    } else {
                                                        // 普通点击 - 在当前页面打开
                                                        window.location.href = modifyUrl.toString();
                                                    }
                                                    
                                                    return false;
                                                });

                                                // 右键菜单处理
                                                $(this).on('contextmenu', function() {
                                                    // 更新href属性，确保右键菜单显示正确的链接
                                                    $(this).attr('href', new URL(href).toString());
                                                });

                                                // 直接触发点击事件
                                                $(this).trigger('click');
                                            }
                                        })
                                    );
                                // $cartItem.find('.wc-block-cart-item__cart-key').append($customImageDiv);
                            } else {
                                console.log('no matched item found design_id: ', design_id);
                            }
                        }
                    });
                }
            }
        });

    }
    var ajaxurl = nbds_frontend.ajax_url || '/wp-admin/admin-ajax.php';
    $(document.body).on('wc_fragments_refreshed', function() {
        handleCartUpdate('wc_fragments_refreshed');
    });
    $(document.body).on('woocommerce_cart_updated', function() {
        handleCartUpdate('woocommerce_cart_updated');
    });
    $(document.body).on('woocommerce_cart_emptied', function() {
        handleCartUpdate('woocommerce_cart_emptied');
    });
    $(document.body).on('woocommerce_cart_item_removed', function() {
        handleCartUpdate('woocommerce_cart_item_removed');
    });
    $(document.body).on('woocommerce_cart_item_restored', function() {
        handleCartUpdate('woocommerce_cart_item_restored');
    });
    $(document.body).on('woocommerce_cart_item_set_quantity', function() {
        handleCartUpdate('woocommerce_cart_item_set_quantity');
    });
    $(document.body).on('woocommerce_cart_item_quantity_updated', function() {
        handleCartUpdate('woocommerce_cart_item_quantity_updated');
    });
    // 监听页面刷新
    $(window).on('load', function() {
        handleCartUpdate('laod');
    });
    
    // 添加样式
    $('<style>')
        .text(`
            .wc-block-cart-item__custom-image {
                margin-top: 10px;
            }
            .wc-block-cart-item__custom-image img {
                max-width: 100px;
                height: auto;
            }
        `)
        .appendTo('head');
        
})(jQuery);