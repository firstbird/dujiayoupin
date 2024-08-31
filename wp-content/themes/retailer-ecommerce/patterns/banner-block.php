<?php
/**
 * Title: Banner Block
 * Slug: retailer-ecommerce/banner-block
 * Categories: banner
 * Block Types: core/template-part/banner-block
 */
?>

<!-- wp:cover {"url":"<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/banner-main-image.png","id":7,"dimRatio":0,"overlayColor":"black","isUserOverlayColor":true,"minHeight":620,"minHeightUnit":"px","tagName":"main","className":"wp-block-group alignfull","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<main class="wp-block-cover wp-block-group alignfull" style="margin-top:0;margin-bottom:0;min-height:620px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-7" alt="" src="<?php echo esc_url(get_template_directory_uri()) ?>/assets/images/banner-main-image.png" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:columns {"verticalAlignment":null,"align":"wide","className":"slider-banner","textColor":"base"} -->
<div class="wp-block-columns alignwide slider-banner has-base-color has-text-color"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:heading {"style":{"typography":{"fontSize":"32px","fontStyle":"normal","fontWeight":"700"}},"textColor":"contrast","fontFamily":"figtree"} -->
<h2 class="wp-block-heading has-contrast-color has-text-color has-figtree-font-family" style="font-size:32px;font-style:normal;font-weight:700"><?php echo esc_html('High quality Hardware Tools ','retailer-ecommerce'); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"14px","fontStyle":"normal","fontWeight":"400"}},"textColor":"contrast","fontFamily":"figtree"} -->
<p class="has-text-align-left has-contrast-color has-text-color has-figtree-font-family" style="font-size:14px;font-style:normal;font-weight:400"><?php echo esc_html('Lorem Ipsum is simply dummy text of the printing and','retailer-ecommerce'); ?><br><?php echo esc_html('typesetting industry. Lorem Ipsum has been the industry\'s','retailer-ecommerce'); ?><br><?php echo esc_html('standard dummy text.','retailer-ecommerce'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"base","style":{"spacing":{"padding":{"left":"var:preset|spacing|30","right":"var:preset|spacing|30","top":"8px","bottom":"8px"}},"color":{"background":"#2453d4"},"border":{"radius":"6px"},"typography":{"fontSize":"12px","fontStyle":"normal","fontWeight":"700","textTransform":"uppercase","letterSpacing":"1px"}},"fontFamily":"figtree"} -->
<div class="wp-block-button has-custom-font-size has-figtree-font-family" style="font-size:12px;font-style:normal;font-weight:700;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-base-color has-text-color has-background wp-element-button" href="#" style="border-radius:6px;background-color:#2453d4;padding-top:8px;padding-right:var(--wp--preset--spacing--30);padding-bottom:8px;padding-left:var(--wp--preset--spacing--30)"><strong><?php echo esc_html('Shop Collection','retailer-ecommerce'); ?></strong></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div></main>
<!-- /wp:cover -->