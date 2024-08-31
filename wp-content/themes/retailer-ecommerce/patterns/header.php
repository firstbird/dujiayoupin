<?php
/**
 * Title: Header
 * Slug: retailer-ecommerce/header
 * Categories: header
 * Block Types: core/template-part/header
 */
?>


<!-- wp:group {"className":"topheader-area","style":{"spacing":{"padding":{"top":"15px","bottom":"15px"}},"color":{"background":"#ffd900"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group topheader-area has-background" style="background-color:#ffd900;padding-top:15px;padding-bottom:15px"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center","width":"20%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:20%"><!-- wp:group {"className":"social-box","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
<div class="wp-block-group social-box"><!-- wp:social-links {"iconColor":"contrast","iconColorValue":"#000000","size":"has-small-icon-size","className":"is-style-logos-only"} -->
<ul class="wp-block-social-links has-small-icon-size has-icon-color is-style-logos-only"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"twitter"} /-->

<!-- wp:social-link {"url":"#","service":"youtube"} /-->

<!-- wp:social-link {"url":"#","service":"instagram"} /--></ul>
<!-- /wp:social-links -->

<!-- wp:heading {"textAlign":"left","level":6,"style":{"typography":{"fontSize":"15px","fontStyle":"normal","fontWeight":"400","textTransform":"capitalize"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h6 class="wp-block-heading has-text-align-left has-contrast-color has-text-color has-figtree-font-family" style="font-size:15px;font-style:normal;font-weight:400;text-transform:capitalize"><?php echo esc_html('Follow Us!','retailer-ecommerce'); ?></h6>
<!-- /wp:heading --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":11,"width":"25px","height":"auto","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/truck.png" alt="" class="wp-image-11" style="width:25px;height:auto"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"15px","fontStyle":"normal","fontWeight":"400"}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-text-align-left has-contrast-color has-text-color has-figtree-font-family" style="font-size:15px;font-style:normal;font-weight:400"><?php echo esc_html('Lorem Ipsum has been the industrys standard dummy text ever since the 1500s.','retailer-ecommerce'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"8%"} -->
<div class="wp-block-column" style="flex-basis:8%"><!-- wp:shortcode -->
[gtranslate]
<!-- /wp:shortcode --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"8%","className":"currency-box"} -->
<div class="wp-block-column currency-box" style="flex-basis:8%"><!-- wp:shortcode -->
[woocs]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"header-area","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group header-area" style="margin-top:0;margin-bottom:0"><!-- wp:columns {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}}}} -->
<div class="wp-block-columns" style="padding-top:20px;padding-bottom:20px"><!-- wp:column {"verticalAlignment":"center","width":"25%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:25%"><!-- wp:site-title {"style":{"typography":{"fontStyle":"normal","fontWeight":"700","fontSize":"25px"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"searchbox","layout":{"type":"default"}} -->
<div class="wp-block-column searchbox" style="flex-basis:50%"><!-- wp:columns {"className":"main-search-box"} -->
<div class="wp-block-columns main-search-box"><!-- wp:column {"verticalAlignment":"center","width":"40%","className":"header-search"} -->
<div class="wp-block-column is-vertically-aligned-center header-search" style="flex-basis:40%"><!-- wp:categories {"displayAsDropdown":true,"showPostCounts":true,"showOnlyTopLevel":true,"showEmpty":true,"align":"right","className":"header-cat","style":{"layout":{"selfStretch":"fit","flexSize":null},"spacing":{"padding":{"right":"0px","left":"0px"}}}} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"60%"} -->
<div class="wp-block-column" style="flex-basis:60%"><!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search for anything for you...","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"className":"search-box","style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"30%","className":"searchbox","layout":{"type":"default"}} -->
<div class="wp-block-column is-vertically-aligned-center searchbox" style="flex-basis:30%"><!-- wp:columns {"className":"meta-box"} -->
<div class="wp-block-columns meta-box"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"meta-box","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group meta-box"><!-- wp:heading {"textAlign":"right","level":5,"style":{"typography":{"fontSize":"14px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-text-align-right has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:14px;font-style:normal;font-weight:500;text-transform:capitalize"><a href="#"><?php echo esc_html('My Account','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:image {"lightbox":{"enabled":false},"id":33,"width":"20px","height":"auto","scale":"cover","sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full is-resized"><a href="#"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/user.png" alt="" class="wp-image-33" style="object-fit:cover;width:20px;height:auto"/></a></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"meta-box","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group meta-box"><!-- wp:heading {"textAlign":"right","level":5,"style":{"typography":{"fontSize":"14px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize"},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-text-align-right has-contrast-color has-text-color has-link-color has-figtree-font-family" style="font-size:14px;font-style:normal;font-weight:500;text-transform:capitalize"><a href="#"><?php echo esc_html('Wishlist','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:image {"lightbox":{"enabled":false},"id":38,"sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full"><a href="#"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/wishlist-img.png" alt="" class="wp-image-38"/></a></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"meta-box","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group meta-box"><!-- wp:heading {"textAlign":"right","level":5,"style":{"typography":{"fontSize":"14px","fontStyle":"normal","fontWeight":"500","textTransform":"capitalize"}},"fontFamily":"figtree"} -->
<h5 class="wp-block-heading has-text-align-right has-figtree-font-family" style="font-size:14px;font-style:normal;font-weight:500;text-transform:capitalize"><a href="#"><?php echo esc_html('My Cart','retailer-ecommerce'); ?></a></h5>
<!-- /wp:heading -->

<!-- wp:image {"lightbox":{"enabled":false},"id":39,"width":"22px","height":"auto","sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full is-resized"><a href="#"><img src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/cart-image.png" alt="" class="wp-image-39" style="width:22px;height:auto"/></a></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"header-menu","style":{"color":{"background":"#2453d4"},"spacing":{"padding":{"top":"15px","bottom":"15px"}}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group header-menu has-background" style="background-color:#2453d4;padding-top:15px;padding-bottom:15px"><!-- wp:navigation {"textColor":"base","className":"menu-box","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex"}} -->
<!-- wp:navigation-link {"label":"Home","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Collections","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Tools","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Painting","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Plumbing","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Fasteners","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"About Us","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Contact Us","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:group -->