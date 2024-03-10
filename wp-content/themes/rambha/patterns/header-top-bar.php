<?php
 /**
  * Title: Header Top Bar
  * Slug: rambha/header-top-bar
  * Categories: rambha,header
  */
  $rambha_images = array(
    RAMBHA_URL . 'assets/images/icon-phone.png',
    RAMBHA_URL . 'assets/images/icon-envelope.png',
    RAMBHA_URL . 'assets/images/icon-location.png',
);
?>
<!-- wp:group {"style":{"spacing":{"blockGap":"0px","padding":{"top":"10px","bottom":"10px"}}},"backgroundColor":"border-color","className":"home-top-bar","layout":{"type":"constrained"}} -->
<div class="wp-block-group home-top-bar has-border-color-background-color has-background" style="padding-top:10px;padding-bottom:10px"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:group {"align":"full","className":"mobile-aligncenter","layout":{"type":"flex","allowOrientation":false,"justifyContent":"space-between","flexWrap":"nowrap"}} -->
<div class="wp-block-group alignfull mobile-aligncenter"><!-- wp:group {"className":"mobile-aligncenter"} -->
<div class="wp-block-group mobile-aligncenter"><!-- wp:social-links {"iconColor":"background","iconColorValue":"#ffffff","iconBackgroundColor":"primary","iconBackgroundColorValue":"#bb993a","size":"has-small-icon-size","style":{"spacing":{"margin":{"top":"0px","bottom":"0px"},"blockGap":{"top":"20px","left":"20px"}}},"className":"is-style-default","layout":{"type":"flex","justifyContent":"center"}} -->
<ul class="wp-block-social-links has-small-icon-size has-icon-color has-icon-background-color is-style-default" style="margin-top:0px;margin-bottom:0px"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"wordpress"} /-->

<!-- wp:social-link {"url":"#","service":"fivehundredpx"} /-->

<!-- wp:social-link {"url":"#","service":"linkedin"} /-->

<!-- wp:social-link {"url":"#","service":"youtube"} /-->

<!-- wp:social-link {"url":"#","service":"twitter"} /-->

<!-- wp:social-link {"url":"#","service":"vk"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"textColor":"foreground","className":"mobile-aligncenter","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group mobile-aligncenter has-foreground-color has-text-color has-link-color"><!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:image {"id":8088,"sizeSlug":"full","linkDestination":"none","style":{"color":{"duotone":"var:preset|duotone|themeblue-and-themeblue"}},"className":"is-style-default vertical-aligncenter"} -->
<figure class="wp-block-image size-full is-style-default vertical-aligncenter"><img src="<?php echo esc_url($rambha_images[0]); ?>" alt="" class="wp-image-8088"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500"}},"fontSize":"small"} -->
<p class="has-small-font-size" style="font-style:normal;font-weight:500"><?php esc_html_e(' +123 456 789','rambha');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":8087,"sizeSlug":"full","linkDestination":"none","style":{"color":{"duotone":"var:preset|duotone|themeblue-and-themeblue"}},"className":"is-style-default vertical-aligncenter"} -->
<figure class="wp-block-image size-full is-style-default vertical-aligncenter"><img src="<?php echo esc_url($rambha_images[1]); ?>" alt="" class="wp-image-8087"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500"}},"fontSize":"small"} -->
<p class="has-small-font-size" style="font-style:normal;font-weight:500"><a href="<?php esc_attr_e('mailto:support@example.com','rambha');?>"><?php esc_html_e('support@example.com','rambha');?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":8087,"sizeSlug":"full","linkDestination":"none","style":{"color":{"duotone":"var:preset|duotone|themeblue-and-themeblue"}},"className":"is-style-default vertical-aligncenter"} -->
<figure class="wp-block-image size-full is-style-default vertical-aligncenter"><img src="<?php echo esc_url($rambha_images[2]); ?>" alt="" class="wp-image-8087"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500"}},"fontSize":"small"} -->
<p class="has-small-font-size" style="font-style:normal;font-weight:500"><?php esc_html_e('Your Location','rambha');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->