<?php
/**
 * Title: Hidden No Results Content
 * Slug: rambha/hidden-no-results-content
 * Inserter: no
 */
?>
  <!-- wp:heading {"textAlign":"left","level":3} -->
  <h3 class="has-text-align-left"><?php esc_html_e('No results found','rambha');?></h3>
  <!-- /wp:heading -->

  <!-- wp:paragraph {"align":"left"} -->
  <p class="has-text-align-left"><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.','rambha');?></p>
  <!-- /wp:paragraph -->

  <!-- wp:spacer {"height":"50px"} -->
  <div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
  <!-- /wp:spacer -->

  <!-- wp:search {"label":"Search","showLabel":false,"buttonText":"Search"} /-->