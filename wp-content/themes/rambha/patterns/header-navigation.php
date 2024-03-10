<?php
 /**
  * Title: Top Header & Navigation
  * Slug: rambha/header-navigation
  * Categories: rambha,header,featured
  */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"0px","right":"0px"}},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"primary","textColor":"background","className":"home-header-nav","layout":{"inherit":true,"type":"constrained"}} -->
<div class="wp-block-group home-header-nav has-background-color has-primary-background-color has-text-color has-background has-link-color" style="padding-top:10px;padding-right:0px;padding-bottom:10px;padding-left:0px"><!-- wp:group {"align":"wide","className":"block-navigation","layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide block-navigation"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"className":"mobile-aligncenter","layout":{"type":"flex","justifyContent":"left","flexWrap":"wrap"}} -->
<div class="wp-block-group mobile-aligncenter"><!-- wp:site-logo {"width":49,"shouldSyncIcon":true,"style":{"color":{"duotone":["#000000","#9eef7c"]}}} /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"},"fontSize":"tiny"} -->
<div class="wp-block-group has-tiny-font-size"><!-- wp:site-title {"style":{"typography":{"fontStyle":"normal","fontWeight":"500","fontSize":"1.6rem","letterSpacing":"2px","textTransform":"capitalize"}}} /-->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e('Welcome to Block Theme','rambha');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|50"},"layout":{"selfStretch":"fill","flexSize":null}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:navigation {"textColor":"background","overlayBackgroundColor":"background","overlayTextColor":"background","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|60"},"typography":{"fontStyle":"normal","fontWeight":"500"},"layout":{"selfStretch":"fill","flexSize":null}},"fontSize":"small"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-background-color has-text-color wp-element-button" href="#" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)"><?php esc_html_e('Get Involved','rambha');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->