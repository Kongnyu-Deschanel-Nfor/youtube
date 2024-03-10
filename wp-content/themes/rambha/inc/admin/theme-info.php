<?php
/**
 * Add the about page under appearance.
 *
 * Display the details about the theme information
 *
 * @package rambha
 */
?>
<?php
// About Information
add_action( 'admin_menu', 'rambha_about' );
function rambha_about() {         
        add_theme_page( esc_html__('About Theme', 'rambha'), esc_html__('Rambha Theme', 'rambha'), 'edit_theme_options', 'rambha-about', 'rambha_about_page');   
}

// CSS for About Theme Page
function rambha_admin_theme_style($hook) {

        if ( 'appearance_page_rambha-about' != $hook ) {
        return;
    }

   wp_enqueue_style('rambha-admin-style', get_template_directory_uri() . '/inc/admin/theme-info.css');
}
add_action('admin_enqueue_scripts', 'rambha_admin_theme_style');

function rambha_about_page() {
$theme = wp_get_theme();
$pro_purchase_url = "https://themecanary.com/pricing/rambha/";
$doc_url = "https://themecanary.com/documentation/rambha/";
$demo_url = "https://demo.themecanary.com/rambha/#demos";
$support_url = "https://wordpress.org/support/theme/rambha/";

$theme_name = esc_html( $theme->Name );
$free_theme_name = str_replace( ' Pro', '',$theme_name );
?>
<div class="rambha-wrapper">
  <div id="theme-info-page">
    <div class="admin-banner">
      <div class="banner-left">
        <h2>
          <?php echo esc_html( $theme->Name ); ?>
        </h2>
        <p>
          <?php esc_html_e( 'Multipurpose WordPress Block Based Theme', 'rambha' ); ?>
        </p>
      </div>
      <div class="banner-right">
        <div class="rambha-buttons">
          <a href="<?php echo esc_url($doc_url); ?>" class="rambha-admin-button demo" target="_blank" rel="noreferrer">
            <?php esc_html_e( 'Documentation', 'rambha' ); ?>
          </a>
          <a href="<?php echo  esc_url($demo_url); ?>" class="rambha-admin-button documentation" target="_blank" rel="noreferrer">
            <?php esc_html_e( 'Demo', 'rambha' ); ?>
          </a>
          <a href="<?php echo  esc_url($pro_purchase_url); ?>" class="rambha-admin-button upgrade-to-pro" target="__blank">
            <?php echo esc_html( 'Upgrade Pro', 'rambha' ); ?>
          </a>
        </div>
      </div>
    </div>
    <div class="feature-list">
          <div class="rambha-about-container compare-table">
              <h3>
                <?php echo esc_html( $free_theme_name ); ?>
                <?php esc_html_e( 'Free Vs Pro', 'rambha' ); ?>
              </h3>
              <table>
                <thead>
                  <tr>
                    <th>
                      <?php esc_html_e( 'Features', 'rambha' ); ?>
                    </th>
                    <th>
                      <?php echo esc_html( $theme->Name ); ?> <?php esc_html_e( 'Free', 'rambha' ); ?>
                    </th>
                    <th>
                      <?php echo esc_html( $theme->Name ); ?> <?php esc_html_e( 'Pro', 'rambha' ); ?>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Easy Setup', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Responsive', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                    
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Advance Color Options', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( '800+ Fonts', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Slider Options ', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Customizer', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Front/ Home page posts categories', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Boxed Layout', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Sidebar', 'rambha' ); ?>
                    </td>
                    <td><?php esc_html_e('Right Sidebar','rambha'); ?></span>
                    </td>
                    <td><?php esc_html_e('Right/Left/ Fullwidth/ No Sidebar','rambha'); ?></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Back to Top', 'rambha' ); ?>
                    </td>
                    <td><span class="dashicons dashicons-no"></span>
                    </td>
                    <td><span class="dashicons dashicons-yes"></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Styles', 'rambha' ); ?>
                    </td>
                    <td><?php esc_html_e('5','rambha'); ?></span>
                    </td>
                    <td><?php esc_html_e('14','rambha'); ?></span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php esc_html_e( 'Templates', 'rambha' ); ?>
                    </td>
                    <td><?php esc_html_e('10','rambha'); ?></span>
                    </td>
                    <td><?php esc_html_e('13','rambha'); ?></span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <?php esc_html_e( 'Patterns', 'rambha' ); ?>
                    </td>
                    <td><?php esc_html_e('14','rambha'); ?></span>
                    </td>
                    <td><?php esc_html_e('23','rambha'); ?></span>
                    </td>
                  </tr>
                </tbody>
              </table>
          </div>
      <div class="about-us">
        <div class="our-product"><span><i class="fa fa-star" aria-hidden="true"></i> <i class="fa fa-star" aria-hidden="true"></i> <i class="fa fa-star" aria-hidden="true"></i> <i class="fa fa-star" aria-hidden="true"></i> <i class="fa fa-star" aria-hidden="true"></i></span>
          <h3>
            <?php esc_html_e( 'Love our product?', 'rambha' ); ?>
          </h3>
          <h4>
            <?php esc_html_e( 'Motivate 5 Star rating', 'rambha' ); ?>
          </h4>
          <a href="https://wordpress.org/support/theme/rambha/reviews/?filter=5" class="rambha-admin-button" target="__blank">
            <?php esc_html_e( 'Rate Us', 'rambha' ); ?>
          </a>
        </div>
        <div class="our-product">
          <h3>
            <?php esc_html_e( 'Still have any question?', 'rambha' ); ?>
          </h3>
          <p>
          <?php esc_html_e( 'Don\'t hesitate to ask', 'rambha' ); ?>
          </p>
          <a href="<?php echo esc_url($support_url); ?>" class="rambha-admin-button" target="_blank">
            <?php esc_html_e( 'Get Support', 'rambha' ); ?>
          </a>
        </div>
        <div class="rambha-buttons">
          <a href="<?php echo esc_url($pro_purchase_url); ?>" class="rambha-admin-button upgrade-to-pro" rel="noreferrer" target="_blank"><i class="fa fa-paint-brush"></i>
            <?php printf( esc_html( 'Get Rambha Pro', 'rambha' ), $theme->Name ); ?>
          </a>
          <a href="<?php echo esc_url($doc_url); ?>" class="rambha-admin-button premium-button documentation" target="_blank" rel="noreferrer"><i class="fa fa-book"></i>
            <?php esc_html_e( 'Documentation', 'rambha' ); ?>
          </a>
        </div>
      </div>
    </div>
</div>

<?php }