<?php
/**
 * Title: Product
 * Slug: retailer-ecommerce/product
 * Categories: product
 * Block Types: core/template-part/product
 */
?>

<!-- wp:group {"style":{"typography":{"fontStyle":"normal","fontWeight":"800"},"spacing":{"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"base","layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group has-base-background-color has-background" style="margin-top:0;margin-bottom:0;font-style:normal;font-weight:800"><!-- wp:spacer {"height":"25px"} -->
<div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":4,"style":{"typography":{"fontStyle":"normal","fontWeight":"700","textTransform":"capitalize","letterSpacing":"1px","fontSize":"25px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<h4 class="wp-block-heading has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:25px;font-style:normal;font-weight:700;letter-spacing:1px;text-transform:capitalize"><?php echo esc_html('Top Selling Product','retailer-ecommerce'); ?></h4>
<!-- /wp:heading -->

<!-- wp:columns {"className":"product-main"} -->
<div class="wp-block-columns product-main"><!-- wp:column {"className":"product-box"} -->
<div class="wp-block-column product-box"><!-- wp:image {"id":65,"scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center","className":"product-img"} -->
<figure class="wp-block-image aligncenter size-full product-img"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/product-1.png" alt="" class="wp-image-65" style="object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"product-content","layout":{"type":"constrained"}} -->
<div class="wp-block-group product-content"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"12px","fontStyle":"normal","fontWeight":"500","letterSpacing":"1px"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"fontFamily":"figtree"} -->
<h6 class="wp-block-heading has-figtree-font-family" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-size:12px;font-style:normal;font-weight:500;letter-spacing:1px"><?php echo esc_html('COLLECTION NAME','retailer-ecommerce'); ?></h6>
<!-- /wp:heading -->

<!-- wp:heading {"level":5,"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}},"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize","letterSpacing":"2px"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500;letter-spacing:2px;text-transform:capitalize"><a href="#"><?php echo esc_html('Hardware Product Title Here','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:group {"className":"price-box","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group price-box"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"16px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500"><?php echo esc_html('$99.99','retailer-ecommerce'); ?> <span><?php echo esc_html('$299.99','retailer-ecommerce'); ?></span></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"base","style":{"spacing":{"padding":{"left":"10px","right":"10px","top":"7px","bottom":"7px"}},"color":{"background":"#2453d4"},"border":{"radius":"6px"},"typography":{"fontSize":"10px","fontStyle":"normal","fontWeight":"500","textTransform":"uppercase","letterSpacing":"1px"}},"fontFamily":"figtree"} -->
<div class="wp-block-button has-custom-font-size has-figtree-font-family" style="font-size:10px;font-style:normal;font-weight:500;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-base-color has-text-color has-background wp-element-button" href="#" style="border-radius:6px;background-color:#2453d4;padding-top:7px;padding-right:10px;padding-bottom:7px;padding-left:10px"><?php echo esc_html('Add To Cart','retailer-ecommerce'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"product-box"} -->
<div class="wp-block-column product-box"><!-- wp:image {"id":40,"scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center","className":"product-img"} -->
<figure class="wp-block-image aligncenter size-full product-img"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/product-2.png" alt="" class="wp-image-40" style="object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"product-content","layout":{"type":"constrained"}} -->
<div class="wp-block-group product-content"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"12px","fontStyle":"normal","fontWeight":"500","letterSpacing":"1px"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"fontFamily":"figtree"} -->
<h6 class="wp-block-heading has-figtree-font-family" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-size:12px;font-style:normal;font-weight:500;letter-spacing:1px"><?php echo esc_html('COLLECTION NAME','retailer-ecommerce'); ?></h6>
<!-- /wp:heading -->

<!-- wp:heading {"level":5,"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}},"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize","letterSpacing":"2px"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500;letter-spacing:2px;text-transform:capitalize"><a href="#"><?php echo esc_html('Hardware Product Title Here','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:group {"className":"price-box","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group price-box"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"16px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500"><?php echo esc_html('$99.99','retailer-ecommerce'); ?> <span><?php echo esc_html('$299.99','retailer-ecommerce'); ?></span></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"base","style":{"spacing":{"padding":{"left":"10px","right":"10px","top":"7px","bottom":"7px"}},"color":{"background":"#2453d4"},"border":{"radius":"6px"},"typography":{"fontSize":"10px","fontStyle":"normal","fontWeight":"500","textTransform":"uppercase","letterSpacing":"1px"}},"fontFamily":"figtree"} -->
<div class="wp-block-button has-custom-font-size has-figtree-font-family" style="font-size:10px;font-style:normal;font-weight:500;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-base-color has-text-color has-background wp-element-button" href="#" style="border-radius:6px;background-color:#2453d4;padding-top:7px;padding-right:10px;padding-bottom:7px;padding-left:10px"><?php echo esc_html('Add To Cart','retailer-ecommerce'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"product-box"} -->
<div class="wp-block-column product-box"><!-- wp:image {"id":39,"scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center","className":"product-img"} -->
<figure class="wp-block-image aligncenter size-full product-img"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/product-3.png" alt="" class="wp-image-39" style="object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"product-content","layout":{"type":"constrained"}} -->
<div class="wp-block-group product-content"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"12px","fontStyle":"normal","fontWeight":"500","letterSpacing":"1px"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"fontFamily":"figtree"} -->
<h6 class="wp-block-heading has-figtree-font-family" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-size:12px;font-style:normal;font-weight:500;letter-spacing:1px"><?php echo esc_html('COLLECTION NAME','retailer-ecommerce'); ?></h6>
<!-- /wp:heading -->

<!-- wp:heading {"level":5,"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}},"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize","letterSpacing":"2px"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500;letter-spacing:2px;text-transform:capitalize"><a href="#"><?php echo esc_html('Hardware Product Title Here','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:group {"className":"price-box","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group price-box"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"16px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500"><?php echo esc_html('$99.99','retailer-ecommerce'); ?> <span><?php echo esc_html('$299.99','retailer-ecommerce'); ?></span></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"base","style":{"spacing":{"padding":{"left":"10px","right":"10px","top":"7px","bottom":"7px"}},"color":{"background":"#2453d4"},"border":{"radius":"6px"},"typography":{"fontSize":"10px","fontStyle":"normal","fontWeight":"500","textTransform":"uppercase","letterSpacing":"1px"}},"fontFamily":"figtree"} -->
<div class="wp-block-button has-custom-font-size has-figtree-font-family" style="font-size:10px;font-style:normal;font-weight:500;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-base-color has-text-color has-background wp-element-button" href="#" style="border-radius:6px;background-color:#2453d4;padding-top:7px;padding-right:10px;padding-bottom:7px;padding-left:10px"><?php echo esc_html('Add To Cart','retailer-ecommerce'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"product-box"} -->
<div class="wp-block-column product-box"><!-- wp:image {"id":38,"scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center","className":"product-img"} -->
<figure class="wp-block-image aligncenter size-full product-img"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/product-4.png" alt="" class="wp-image-38" style="object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"product-content","layout":{"type":"constrained"}} -->
<div class="wp-block-group product-content"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"12px","fontStyle":"normal","fontWeight":"500","letterSpacing":"1px"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"fontFamily":"figtree"} -->
<h6 class="wp-block-heading has-figtree-font-family" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-size:12px;font-style:normal;font-weight:500;letter-spacing:1px"><?php echo esc_html('COLLECTION NAME','retailer-ecommerce'); ?></h6>
<!-- /wp:heading -->

<!-- wp:heading {"level":5,"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}},"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize","letterSpacing":"2px"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500;letter-spacing:2px;text-transform:capitalize"><a href="#"><?php echo esc_html('Hardware Product Title Here','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:group {"className":"price-box","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group price-box"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"16px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:16px;font-style:normal;font-weight:500"><?php echo esc_html('$99.99','retailer-ecommerce'); ?> <span><?php echo esc_html('$299.99','retailer-ecommerce'); ?></span></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"base","style":{"spacing":{"padding":{"left":"10px","right":"10px","top":"7px","bottom":"7px"}},"color":{"background":"#2453d4"},"border":{"radius":"6px"},"typography":{"fontSize":"10px","fontStyle":"normal","fontWeight":"500","textTransform":"uppercase","letterSpacing":"1px"}},"fontFamily":"figtree"} -->
<div class="wp-block-button has-custom-font-size has-figtree-font-family" style="font-size:10px;font-style:normal;font-weight:500;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-base-color has-text-color has-background wp-element-button" href="#" style="border-radius:6px;background-color:#2453d4;padding-top:7px;padding-right:10px;padding-bottom:7px;padding-left:10px"><?php echo esc_html('Add To Cart','retailer-ecommerce'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"25px"} -->
<div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->