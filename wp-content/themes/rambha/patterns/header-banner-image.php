<?php

/**
 * Title: Header Banner Image
 * Slug:rambha/header-banner-image
 * Categories:rambha,header,featured
 */
  $rambha_images = array(
    RAMBHA_URL . 'assets/images/header-banner.jpg'
);
?>
<!-- wp:group {"style":{"spacing":{"margin":{"top":"0px"},"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"className":"bannerimage","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group bannerimage" style="margin-top:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cover {"url":"<?php echo esc_url($rambha_images[0]); ?>","id":53,"dimRatio":40,"focalPoint":{"x":0.76,"y":0.27},"minHeight":900,"minHeightUnit":"px","align":"wide","style":{"color":{}},"className":"banner-image-cover","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-cover alignwide banner-image-cover" style="min-height:900px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-53" alt="" src="<?php echo esc_url($rambha_images[0]); ?>" style="object-position:76% 27%" data-object-fit="cover" data-object-position="76% 27%"/><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"rambha-animation fadeInDown","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group rambha-animation fadeInDown"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"uppercase"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="text-transform:uppercase"><?php esc_html_e('Develop and Create great sites','rambha');?></h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"banner-p","layout":{"type":"constrained","contentSize":"75%"}} -->
<div class="wp-block-group banner-p"><!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color has-link-color"><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation','rambha');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary","textColor":"white","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"border":{"radius":"0px"},"typography":{"fontStyle":"normal","fontWeight":"500","letterSpacing":"1px","textTransform":"uppercase"}},"fontSize":"small","fontFamily":"rubik"} -->
<div class="wp-block-button has-custom-font-size has-rubik-font-family has-small-font-size" style="font-style:normal;font-weight:500;letter-spacing:1px;text-transform:uppercase"><a class="wp-block-button__link has-white-color has-primary-background-color has-text-color has-background has-link-color wp-element-button" style="border-radius:0px"><?php esc_html_e('Read More','rambha');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->