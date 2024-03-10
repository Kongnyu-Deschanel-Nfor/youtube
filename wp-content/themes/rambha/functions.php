<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Rambha
 * @since 1.0.0
 */

/**
 * Enqueue the style.css file.
 *
 * @since 1.0
 */
function rambha_styles() {
	wp_enqueue_style(
		'rambha-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style('rambha-animate', get_template_directory_uri().'/assets/wow/css/animate.min.css');
	wp_enqueue_script('wow', get_template_directory_uri().'/assets/wow/js/wow.min.js', array('jquery'));
	wp_enqueue_script('wow-settings', get_template_directory_uri().'/assets/wow/js/wow-settings.js', array('jquery'));

	wp_enqueue_script('waypoints', get_template_directory_uri().'/assets/counter/jquery.waypoints.js', array('jquery'), false, true);
	wp_enqueue_script('counterup', get_template_directory_uri().'/assets/counter/jquery.counterup.js', array('jquery'), false, true);
	wp_enqueue_script('rambha-counter-settings', get_template_directory_uri().'/assets/counter/counter-settings.js', array('jquery'), false, true);

}
add_action( 'wp_enqueue_scripts', 'rambha_styles' );

define( 'RAMBHA_URL', trailingslashit( get_template_directory_uri() ) );

if ( ! function_exists( 'rambha_setup' ) ) {
	function rambha_setup() {

		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

		add_theme_support( 'block-nav-menus' );

	}

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 */
	load_theme_textdomain( 'rambha', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );
}
add_action( 'after_setup_theme', 'rambha_setup' );

// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';
//load Welcome Notice
require get_template_directory() . '/inc/admin/admin-notice.php';
//load Theme Info
require get_template_directory() . '/inc/admin/theme-info.php';

// Theme Activation Notice
add_action( 'admin_notices', 'rambha_activation' );

// Theme Ignore
add_action( 'admin_init', 'rambha_ignore' );
function rambha_ignore() {
  global $current_user;
  $user_id   = $current_user->ID;
  $theme_data  = wp_get_theme();

  if ( isset( $_GET[ sanitize_key( $theme_data->get( 'TextDomain' ) ) . '_notice_ignore' ] ) && '0' == $_GET[ sanitize_key( $theme_data->get( 'TextDomain' ) ) . '_notice_ignore' ] ) {
    add_user_meta( $user_id, sanitize_key( $theme_data->get( 'TextDomain' ) ) . '_notice_ignore', 'true', true );
  }
}

function rambha_admin_css (){

  wp_enqueue_style( 'rambha-admin-css', get_template_directory_uri() . '/inc/admin/admin.css' );

}

add_action( 'admin_enqueue_scripts', 'rambha_admin_css' );