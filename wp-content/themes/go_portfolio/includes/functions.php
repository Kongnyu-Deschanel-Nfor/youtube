<?php		
/**
 * Common functions
 *
 * @package   Go Portfolio - WordPress Responsive Portfolio 
 * @author    Granth <granthweb@gmail.com>
 * @link      https://granthweb.com
 * @copyright 2019 Granth
 */

/**
 * Clean input fields
 */
 
function go_portfolio_clean_input( $input_data=array(), $html_allowed_keys=array(), $trash_keys=array() ) {
	foreach( $input_data as $data_key=>$data_value ) {
		if ( is_array( $data_value ) ) {
			 go_portfolio_clean_input( $data_value, $html_allowed_keys, $trash_keys );
		} elseif ( in_array( $data_key, $trash_keys ) ) {
				unset( $input_data[$data_key] );
				continue;
		} else {
				$input_data[$data_key]=stripslashes( trim( $input_data[$data_key] ) );
			if ( empty( $html_allowed_keys ) || !in_array( $data_key, $html_allowed_keys ) ) { 
				$input_data[$data_key] = sanitize_text_field( $input_data[$data_key] );
			}
		}
	}
	return $input_data;
}

/**
 * Custom excerpt function
 */

function go_portfolio_wp_trim_excerpt( $text, $excerpt_word_count=25,  $excerpt_end = '...', $strip_shortcodes=true, $strip_html=true, $allowed_tags='' ) {
		
	/* Delete all shortcodes */
	if ( $strip_shortcodes ) { $text = strip_shortcodes( $text ); };
 
	$text = wpautop( $text );
	$text = do_shortcode( shortcode_unautop( $text ) );
	$text = str_replace( ']]>', ']]&gt;', $text );

	/* Strip tags */
	$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text); 
	if ( $strip_html ) { $text = strip_tags( $text, $allowed_tags ); }
	$words = preg_split( "/[\n\r\t ]+/", $text, $excerpt_word_count + 1, PREG_SPLIT_NO_EMPTY );
	
	if ( count( $words ) > $excerpt_word_count ) {
		array_pop( $words );
		$text = implode( ' ', $words );
		$text = $text . $excerpt_end;
	} else {
		$text = implode( ' ', $words );
	}
	
	/* Fix broken HTML */
	if ( $strip_html === false && $text != '' ) {
		
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$charset = get_option( 'blog_charset', 'UTF-8' );
			$text = mb_convert_encoding( $text, 'HTML-ENTITIES', $charset );
		}
		
		if ( class_exists( 'DOMDocument' ) ) {
			$doc = new DOMDocument();
			$doc->encoding = $charset;
			//$doc->recover = true;
			//$doc->strictErrorChecking = false;
			// PHP 5.4 @$doc->loadHTML( $text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD )
			@$doc->loadHTML( $text );
			$text = $doc->saveHTML( $doc->getElementsByTagName('body')->item(0) );
			$text = preg_replace( '/<\/?body>/', '', $text  );
		}

	}																				
	
	return $text;
}

/**
 * Get attachment url by id
 */

function gw_get_attachment_id_from_url( $attachment_url = '' ) {

    global $wpdb;
    $attachment_id = false;

    // If there is no url, return.
    if ( '' == $attachment_url )
        return;

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        
		$attachment_url = preg_replace( '/(\.(jpg|jpeg|png|gif))(.*)/i', '$1', $attachment_url );

		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

    }

    return $attachment_id;
} 

?>
