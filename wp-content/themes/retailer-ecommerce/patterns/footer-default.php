<?php
/**
 * Title: Default Footer
 * Slug: retailer-ecommerce/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
 */
?>

<!-- wp:group {"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-color has-contrast-background-color has-text-color has-background"><!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"15px","bottom":"15px"}}},"layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide" style="padding-top:15px;padding-bottom:15px"><!-- wp:site-title {"level":0,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base"} /-->

<!-- wp:paragraph {"align":"right","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}}} -->
<p class="has-text-align-right has-link-color">
	<?php
	printf(
		/* Translators: WordPress link. */
		esc_html__( 'Proudly powered by %s', 'retailer-ecommerce' ),
		'<a href="' . esc_url( __( 'https://wordpress.org', 'retailer-ecommerce' ) ) . '" rel="nofollow">WordPress</a>'
	)
	?>
</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->