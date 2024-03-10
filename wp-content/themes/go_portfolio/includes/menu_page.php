<?php
/**
 * Main page for in admin area
 * Portfolio creator page
 *
 * @package   Go Portfolio - WordPress Responsive Portfolio 
 * @author    Granth <granthweb@gmail.com>
 * @link      https://granthweb.com
 * @copyright 2019 Granth
 */

$screen = get_current_screen();

/* Get templates, styles & general settings db data */
$templates = get_option( self::$plugin_prefix . '_templates' );
$styles = get_option( self::$plugin_prefix . '_styles' );
$general_settings = get_option( self::$plugin_prefix . '_general_settings' );

/* Get cpts db data */
$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );
if ( isset ( $custom_post_types ) && !empty( $custom_post_types ) ) {
	foreach ( $custom_post_types as $cpt_key => $custom_post_type ) {
		$portfolio_cpts[$cpt_key] = $custom_post_type['slug'];
	}
}

/* Get portfolios db data */
$portfolios = get_option( self::$plugin_prefix . '_portfolios' );

/* Handle post */
if ( !empty( $_POST ) && check_admin_referer( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ) ) {

	$reponse = array();
	$referrer=$_POST['_wp_http_referer'];
	
	/* Clean post fields */
	$_POST = go_portfolio_clean_input( $_POST, 
		array(
			'template-data',
			'style-data',
			'excerpt-allowed-tags'	
		),
		array(
			'go-portfolio-nonce',
			'_wp_http_referer',
		)
	);
	
	/* Default Page POST */
	if ( isset( $_POST['action-type'] ) && isset( $_POST['cpt-item'] ) ) {

		$uniqid = !empty( $_POST['cpt-item'] ) ? sanitize_key( $_POST['cpt-item'] ) : '';
		
		/* Edit action */
		if ( $_POST['action-type'] == 'edit' ) {
			
			if ( empty( $_POST['cpt-item'] ) ) {
				wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&edit=new' ) );
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&edit='.$uniqid ) );
			}
			
		/* Clone action */
		} elseif ( $_POST['action-type'] == 'clone' && !empty( $uniqid ) ) {
				
			/* Do stuff */
			$new_uniqid = uniqid();
			$new_portfolios = $portfolios;
			$new_portfolios[$new_uniqid] = $new_portfolios[$uniqid];
			
			$new_portfolios[$new_uniqid]['uniqid'] = $uniqid;
			$new_portfolios[$new_uniqid]['name'] = $new_portfolios[$new_uniqid]['name'] . ' copy ' . $uniqid;
			$new_portfolios[$new_uniqid]['id'] = $new_portfolios[$new_uniqid]['id'] . ' copy ' . $uniqid;		
						
			/* Save data to db */
			if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
				if ( $new_portfolios != $portfolios ) { 
					update_option ( self::$plugin_prefix . '_portfolios', $new_portfolios );
				}

				/* Set the reponse message */
				$response['result'] = 'success';
				$response['message'][] = __( 'The Portfolio has been successfully cloned.', 'go_portfolio_textdomain' );
				update_option( md5( $screen->id . '-response' ), $response, false );
			}

			/* Redirect */
			wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&updated=true' ) );
			exit;	
			
		/* Delete action */
		} elseif ( $_POST['action-type'] == 'delete' && !empty( $uniqid ) ) {
				
			/* Do stuff */
			$new_portfolios = $portfolios;
			unset( $new_portfolios[$uniqid] );
			
			/* Save data to db */
			if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
				if ( $new_portfolios != $portfolios ) { 
					update_option ( self::$plugin_prefix . '_portfolios', $new_portfolios );
				}
				
				/* Set the reponse message */
				$response['result'] = 'success';
				$response['message'][] = __( 'The Portfolio been successfully deleted.', 'go_portfolio_textdomain' );
				update_option( md5( $screen->id . '-response' ), $response, false );
			}
			
			/* Redirect */
			wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&updated=true' ) );
			exit;
			
		}
	
	}
	
	/* Edit Custom Post Type Page POST -  verfy data and save to db */
	if ( isset( $_POST['uniqid'] ) ) {		
		$uniqid = !empty( $_POST['uniqid'] ) ? sanitize_key( $_POST['uniqid'] ) : '';
		$new_portfolios = $portfolios;
		$new_portfolio = $_POST;
		$new_portfolio['id'] = sanitize_key( $new_portfolio['id'] );
		
		/* Delete trash data */
		if ( isset( $new_portfolio['action'] ) ) { unset( $new_portfolio['action'] ); }
		if ( isset( $new_portfolio['ajax'] ) ) { unset( $new_portfolio['ajax'] ); }		

		/* Do stuff - verify post data */
		if ( !empty( $new_portfolio ) ) {
			if ( !isset( $new_portfolio['name'] ) || empty( $new_portfolio['name'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'Portfolio name is empty!', 'go_portfolio_textdomain' );						
			} elseif ( isset( $portfolios ) && !empty( $portfolios ) ) {		
				foreach ( $portfolios as $portfolio ) {
					if ( $new_portfolio['name'] == $portfolio['name'] && !isset( $portfolios[$uniqid] ) ) {
						$response['result'] = 'error';
						$response['message'][] = __( 'Portfolio name already exists!', 'go_portfolio_textdomain' );
						break;
					}
				}
			}
			
			if ( !isset( $new_portfolio['id'] ) || empty( $new_portfolio['id'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'Portfolio ID is empty!', 'go_portfolio_textdomain' );						
			} elseif ( isset( $portfolios ) && !empty( $portfolios ) ) {		
				foreach ( $portfolios as $portfolio ) {
					if ( $new_portfolio['id'] == $portfolio['id'] && !isset( $portfolios[$uniqid] ) ) {
						$response['result'] = 'error';
						$response['message'][] = __( 'Portfolio ID already exists!', 'go_portfolio_textdomain' );
						break;
					}
				}
			}

			if ( !isset( $new_portfolio['post-type'] ) || empty( $new_portfolio['post-type'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'You didn\'t select post ype for portfolio!', 'go_portfolio_textdomain' );						
			} else {
				$args = array(
				   'public'   => true,
				   '_builtin' => false
				);
				$registered_post_types = get_post_types( $args, 'objects' );
				$registered_post_types_list[] = 'post';
				$registered_post_types_list[] = 'attachment';
				$registered_post_types_list[] = 'page';
				foreach ( $registered_post_types as $pt_key => $registered_post_type ) {
					$registered_post_types_list[] = $pt_key;
				}
				if ( !in_array ($new_portfolio['post-type'], $registered_post_types_list ) ) {
					$response['result'] = 'error';
					$response['message'][] = __( 'The selected post type is not registered!', 'go_portfolio_textdomain' );
				}				
			}

		}
		
		if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
			
			/* Delete unnecessary template data */
			if ( isset( $new_portfolio['template-data'] ) && isset( $templates[$new_portfolio['template']]['data'] ) ) {

				$comp_template_default = trim( $templates[$new_portfolio['template']]['data'] );
				$comp_template_default = preg_replace( '/\s\s+/', ' ', $comp_template_default );
				$comp_template_default = preg_replace( '/\r\n+/', '', $comp_template_default );
				
				$comp_template_custom = trim( $new_portfolio['template-data'] );
				$comp_template_custom = preg_replace( '/\s\s+/', ' ', $comp_template_custom );
				$comp_template_custom = preg_replace( '/\r\n+/', '', $comp_template_custom );				
				
				if ( empty( $new_portfolio['template-data'] ) || $comp_template_default == $comp_template_custom ) {
					unset( $new_portfolio['template-data'] );
				} 
			} else {
				$response['result'] = 'error';
				$response['message'][] = __( 'Template data is missing!', 'go_portfolio_textdomain' );					
			}

			/* Delete unnecessary style data */
			if ( isset( $new_portfolio['style-data'] ) && isset( $styles[$new_portfolio['style']]['data'] ) ) {

				$comp_style_default = trim( $styles[$new_portfolio['style']]['data'] );
				$comp_style_default = preg_replace( '/\s\s+/', ' ', $comp_style_default );
				$comp_style_default = preg_replace( '/\r\n+/', '', $comp_style_default );				
				
				$comp_style_custom = trim( $new_portfolio['style-data'] );
				$comp_style_custom = preg_replace( '/\s\s+/', ' ', $comp_style_custom );
				$comp_style_custom = preg_replace( '/\r\n+/', '', $comp_style_custom );	

				if ( empty( $new_portfolio['style-data'] ) || $comp_style_default == $comp_style_custom ) {
					unset( $new_portfolio['style-data'] );
				}
			} else {
				$response['result'] = 'error';
				$response['message'][] = __( 'Style data is missing!', 'go_portfolio_textdomain' );					
			}
							
			/* Delete unnecessary effect data */
			if ( isset( $new_portfolio['effect-data']) && empty( $new_portfolio['effect-data'] ) ) { unset( $new_portfolio['effect-data'] ); } 
			
			$new_portfolios[$uniqid]=$new_portfolio;

		}
		
		/* Save data to db */
		if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
			$new_portfolios[$uniqid]=$new_portfolio;
			update_option ( self::$plugin_prefix . '_portfolios', $new_portfolios );
			
			if ( !isset( $portfolios[$uniqid] ) ) { $referrer = preg_replace( '/&edit=new/', '&edit='. $uniqid, $referrer ); }

			$response['result'] = 'success';
			$response['message'][] = sprintf( __( 'Portfolio has been successfully updated.<br>Your shortcode is:</strong><br>[go_portfolio id="%1$s"]', 'go_portfolio_textdomain' ), $new_portfolio['id'] );
			
		}
		/* Redirect */
		$referrer = preg_match( '/&updated=true$/', $referrer ) ? $referrer : $referrer. '&updated=true';		
		if ( !isset( $_POST['ajax'] ) ) {
			
			update_option( md5( $screen->id . '-response' ), $response, false );
			update_option( md5( $screen->id . '-data' ), $new_portfolio, false );			 
			wp_redirect( $referrer );
			exit;						
		} elseif ( !isset( $response['result'] ) || $response['result'] != 'error' && !isset( $portfolios[$uniqid] ) ) {
			?><div id="redirect"><?php echo $referrer; ?></div><?php
		}

	}
	
}

/**
 *
 * Content
 *
 */

?>
<div id="gwa-gopf-admin-wrap" class="gwa-gopf-wrap wrap">
	<?php
	if ( empty( $_POST ) && !isset( $_GET['edit'] ) || ( isset( $_GET['edit'] ) && empty ( $_GET['edit'] ) ) ) : 
	?>
	<form id="gwa-gopf-form" name="gwa-gopf-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <?php
	endif;
	if ( empty( $_POST ) && isset( $_GET['edit'] ) && !empty ( $_GET['edit'] ) ) : 
	?>
	<form id="gwa-gopf-form" name="gwa-gopf-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" data-ajax="<?php echo ( isset( $general_settings['disable-ajax'] ) ? 'false' : 'true' );  ?>" data-ajaxerrormsg="<?php esc_attr_e( 'Oops, AJAX error! If you keep getting this message, please enable "Disable AJAX in admin?" option under General Settings plugin page. ', 'go_portfolio_textdomain' ); ?>">
    <?php	
	endif;
	?>
	<div class="gwa-gopf-ploader"><div class="gwa-gopf-ploader-content" data-content="<?php esc_attr_e( 'Hey, just a sec!', 'go_portfolio_textdomain' ); ?>"><div class="gwa-gopf-spinner"></div></div></div>
	<div class="gwa-gopf-ptopbar">
		<div class="gwa-gopf-logo"></div>
		<div class="gwa-gopf-ptopbar-title"><?php _e( 'Go Portfolio', 'go_portfolio_textdomain' ); ?></div>
		<div class="gwa-gopf-ptopbar-content">
		<?php if ( empty( $_POST ) && !isset( $_GET['edit'] ) || ( isset( $_GET['edit'] ) && empty ( $_GET['edit'] ) ) ) :   
		// Default 
		?>
        <input type="submit" class="gwa-gopf-btn-style5 gwa-gopf-new" value="<?php esc_attr_e( '++ Create', 'go_portfolio_textdomain' ); ?>" />
        <?php
		else : 
		// Portfolio editor
		?>
<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-ml10"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
        <?php
		endif;
		?>
        </div>
    </div>
    <h2 class="gwa-gopf-pheader">
    	<div class="gwa-gopf-logo"></div>
        <div class="gwa-gopf-pheader-title"><?php _e( 'Go Portfolio', 'go_portfolio_textdomain' ); ?></div>
    </h2>
	<?php

	/* Print message */
	$response = !isset( $response ) ? get_option( md5( $screen->id . '-response' ) ) : $response;
	if ( ( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' || isset( $_POST['ajax'] ) )  && $response ) : 
	?>
	<div id="result" class="<?php echo $response['result'] == 'error' ? 'error' : 'updated'; ?>">
	<?php foreach ( $response['message'] as $error_msg ) : ?>
		<p><strong><?php echo $error_msg; ?></strong></p>
	<?php endforeach;  $response = array(); ?>
	</div>
	<?php 
	if ( isset( $_POST['ajax'] ) ) { 
		exit; 
	} else {
		delete_option( md5( $screen->id . '-response' )  );
	}	
	endif;
	/* /Print message */

	?>
	
	<?php
	
	/**
	 *
	 * Default Page content
	 *
	 */
	 
	if ( empty( $_POST ) && !isset( $_GET['edit'] ) || ( isset( $_GET['edit'] ) && empty ( $_GET['edit'] ) ) ) : 
	?>
	<!-- form -->
		<input id="gwa-gopf-action-type" name="action-type" type="hidden" value="edit" />
		<?php wp_nonce_field( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ); ?>

		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
	        <?php 
			$cnt_pf = isset( $portfolios ) && !empty( $portfolios ) ? count( $portfolios ) : 0;
			?>
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-nav"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
  <path fill-rule="evenodd" clip-rule="evenodd" d="M12.7 17c-.5 0-.8 0-1-.3L10 15.2C9 17 8.5 19 8.5 21.2c0 3.2 1 6 3.3 8.2.2 0 .3.2.3.3h16s0-.2.3-.3c2-2.3 3.2-5 3.2-8 0-2.4-.6-4.5-1.7-6.3L24 21c.4.6.5 1.3.5 2 0 1.2-.5 2.2-1.4 3-.7 1-1.8 1.5-3 1.5s-2.3-.5-3-1.4c-1-1-1.5-2-1.5-3.2s.5-2.3 1.4-3.2c.7-1 1.8-1.3 3-1.3.7 0 1.4 0 2 .4l6-6c-2-1.6-4-2.6-6.5-2.8v2.6c0 .4-.2.7-.5 1-.2.3-.6.4-1 .5-.4 0-.8 0-1-.4-.3-.3-.5-.6-.5-1V10C16 10 14 11 12 13l1.7 1.6c.3.2.4.6.5 1 0 .4-.2.8-.5 1-.3.3-.6.5-1 .5zm-3-6c3-2.8 6.3-4.3 10.3-4.3 4 0 7.4 1.5 10.3 4.3 2.8 2.8 4.2 6.2 4.2 10.3 0 4-1.4 7.4-4.2 10.3l-1 .7c0 .3-.5.4-1 .4H11.6c-.4 0-.7-.2-1-.5l-.7-.6c-3-3-4.3-6.4-4.3-10.4S7 13.8 9.8 11z"/>
</svg><?php _e( 'Dashboard', 'go_portfolio_textdomain' ); ?><span><?php printf(" (%d)", $cnt_pf); ?></span></div>
			<div class="gwa-gopf-abox-content">
            <div class="gwa-gopf-abox-content-header">
            </div>
                <!-- list -->
                <div class="gw-gopf-list">
					<?php 
					$cnt = 0;
                    if ( isset( $portfolios ) && !empty( $portfolios ) ) :
                    foreach ( $portfolios as $portfolio_key => $portfolio_value ) :
                    ?>               
	                <div class="gw-gopf-list-item gwa-gopf-clearfix">
                    	<div class="gw-gopf-list-item-details">
                        <div class="gw-gopf-list-item-count"><?php printf("%02d.", $cnt); ?></div>
                        <div class="gw-gopf-list-item-name"><?php echo $portfolio_value['name']; ?></div>
                        <div class="gw-gopf-list-item-meta"><?php printf( __( '#%s', 'go_portfolio_textdomain' ), $portfolio_key ); ?></div>
                        </div>
                    	<div class="gw-gopf-list-item-main">
                        <?php 
                        $shortcode = isset( $portfolio_value['name'] ) ? sprintf( '[go_portfolio id="%s"]', trim( $portfolio_value['id'] ) ) : '';
                        ?>
                        <input type="text" value="<?php echo esc_attr( $shortcode) ?>" class="gwa-gopf-w250" readonly="readonly" data-select-all="true">                    
                        </div>                        
                    	<div class="gw-gopf-list-item-assets">
                        	<input type="submit" class="gwa-gopf-btn-style1 gwa-gopf-edit" data-id="<?php echo esc_attr( $portfolio_key ); ?>"  value="<?php esc_attr_e( 'Edit', 'go_portfolio_textdomain' ); ?>" />
                        	<input type="button" class="gwa-gopf-btn-style2 gwa-gopf-ml10 gwa-gopf-clone" data-id="<?php echo esc_attr( $portfolio_key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure?', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Clone', 'go_portfolio_textdomain' ); ?>" />
                        	<input type="button" class="gwa-gopf-btn-style3 gwa-gopf-ml10 gwa-gopf-delete" data-id="<?php echo esc_attr( $portfolio_key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure?', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Delete', 'go_portfolio_textdomain' ); ?>" />
                        </div>
                    </div>
	                                    
					<?php 
					$cnt++;
                    endforeach;
                    else : 
					?>
						<div class="gw-gopf-dash-welcome">
                        	<div class="gwa-gopf-logo gw-gopf-dash-wlogo"></div>
							<div class="gw-gopf-dash-wtitle"><?php esc_html_e( 'Let\'s get started!', 'go_portfolio_textdomain' ); ?></div>
                        	<div class="gw-gopf-dash-wdesc"><?php esc_html_e( 'Create a new portfolio from Scratch or Import existing ones from our demos', 'go_portfolio_textdomain' ); ?></div>
                        </div>                    
                    <?php
					endif;	
                    ?>  
                    <input type="hidden" name="cpt-item">                 
                </div>
                <!-- /list -->
                
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit">
			<input type="submit" class="gwa-gopf-btn-style5 gwa-gopf-new" value="<?php esc_attr_e( '++ Create', 'go_portfolio_textdomain' ); ?>" />
		</p>

	</form>
	<!-- /form -->
	
	<?php endif; ?>
	
	<?php
	
	/**
	 *
	 * Edit Portfolio Page content
	 *
	 */

	if ( empty( $_POST ) && isset( $_GET['edit'] ) && !empty ( $_GET['edit'] ) ) : 
		 
	/* Get temporary POST data */
	$temp_post_data = get_option( md5( $screen->id . '-data' ) );
	if ( $temp_post_data ) {
		delete_option( md5( $screen->id . '-data' ) );
		$portfolio=$temp_post_data;
	}

	/* Get data */
	$item_id = $_GET['edit'] == 'new' ? uniqid() : sanitize_key( $_GET['edit'] );
	if ($_GET['edit'] != 'new') {
		if ( !isset( $portfolios[$item_id] ) ) {
			?>
			<div id="result" class="error">
			<p><strong><?php _e( 'Portfolio doesn\'t exist!', 'go_portfolio_textdomain' ); ?> <a href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $_GET['page'] ) ) ?>"><?php _e( 'Click here', 'go_portfolio_textdomain' ); ?></a> <?php _e( 'to create new portfolio.', 'go_portfolio_textdomain' ); ?></strong></p>
			</div>
			<?php
			exit;
		} else {
			$portfolio = isset( $portfolios[$item_id] ) ? $portfolios[$item_id] : null;
		}
	}

	?>
	<!-- form -->

		<input type="hidden" name="uniqid" value="<?php echo esc_attr( $item_id ); ?>" />
		<?php wp_nonce_field( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ); ?>

		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>1</div>
                <div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Basic Settings', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Name, ID and Post Type', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content">
				<table class="form-table">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Portfolio Name', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="name" value="<?php echo esc_attr( isset( $portfolio['name'] ) ? $portfolio['name'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Name for the portfolio, used for identification in admin area only.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Portfolio ID', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="id" value="<?php echo esc_attr( isset( $portfolio['id'] ) ? $portfolio['id'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td>
							<p class="description"><?php _e( 'Unique ID, used in shortcodes. <strong>Important:</strong> Only lowercase letters, numbers, hyphens and underscores.', 'go_portfolio_textdomain' ); ?></p>
							<p class="description"><?php _e( 'E.g. if the ID is "my_portfolio" the shortcode will be <strong>[go_portfolio id="my_portfolio"]</strong>.', 'go_portfolio_textdomain' ); ?></p>
						</td>
					</tr>
                    <?php
					
					$shortcode = null;
					if ( !empty( $portfolio ) ) :
					$shortcode = isset( $portfolio['name'] ) ? sprintf( '[go_portfolio id="%s"]', trim( $portfolio['id'] ) ) : '';
					endif;
					?>
					<tr class="gw-gopf-shortcode"<?php echo is_null( $shortcode ) ? ' style="display:none;"' : '' ?>>
						<th class="gwa-gopf-w200"><?php _e( 'Shortcode', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input data-id="sc" type="text" value="<?php echo esc_attr( $shortcode) ?>" class="gwa-gopf-w250 gwa-gopf-highlighted-input" readonly="readonly" data-select-all="true"></td>
						<td><p class="description"><?php _e( 'Shortcode of the current portfolio. Paste it into any post or page.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
                    <?php
					?>                                        													
				</table>			
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1 gwa-gopf-edit" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>
		
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>2</div>            
				<div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Query Settings', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Post Selection Criterias', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content">
				
				<!-- Query builder type -->
				<table class="form-table gwa-gopf-builder-type">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Post Type for Portfolio', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="post-type" class="gwa-gopf-w250" data-parent="post-type">
								<optgroup label="<?php echo esc_attr( 'Plugin Custom Post Types', 'go_portfolio_textdomain' ); ?>"></optgroup>
								<?php 
								if ( isset( $custom_post_types ) && !empty( $custom_post_types ) ) :
								foreach ( $custom_post_types as $cpt_key => $cpt_value ) :
								foreach ( get_object_taxonomies( $cpt_value['slug'] ) as $tax_name ) { $registered_taxonomies[$cpt_value['slug']][]=$tax_name; }
								?>
								<option data-children="<?php echo esc_attr( $cpt_value['slug'] ); ?>" value="<?php echo esc_attr( $cpt_value['slug'] ); ?>"<?php echo ( isset( $portfolio['post-type'] ) && $portfolio['post-type'] == $cpt_value['slug'] ? ' selected="selected"' : '' ); ?>><?php echo $cpt_value['name']; ?></option>	
								<?php 
								endforeach;
								else :	
								?>
								<option value="">-- <?php _e( 'No Portfolios Found', 'go_portfolio_textdomain' ); ?> --</option>
								<?php endif; ?>
								<?php
								$args = array(
								   'public'   => true,
								   '_builtin' => true  
								);
								$output = 'objects';
								$operator = 'and';
								$post_types = get_post_types( $args, $output, $operator ); 
								if ( !empty( $post_types ) ) : 
								?>
								<optgroup label="<?php echo esc_attr( 'Default Post Types', 'go_portfolio_textdomain' ); ?>"></optgroup>
								<?php
								foreach ( $post_types  as $post_type_key => $post_type ) :
								if ( $post_type_key == 'attachment' ) { $post_type->labels->name .= ' (Attachments) for Gallery'; }
								foreach ( get_object_taxonomies( $post_type_key ) as $tax_name ) { $registered_taxonomies[$post_type_key][]=$tax_name; }			
								?>
								<option data-children="<?php echo esc_attr( $post_type_key ); ?>" value="<?php echo esc_attr( $post_type_key ); ?>"<?php echo ( isset( $portfolio['post-type'] ) && $portfolio['post-type'] ==  $post_type_key ? ' selected="selected"' : '' ); ?> data-group="post-type-<?php echo esc_attr( $post_type_key ); ?>"><?php echo $post_type->labels->name; ?></option>
								<?php
								endforeach;
								endif;
								$args = array(
								   'public'   => true,
								   '_builtin' => false,  
								);			
								$output = 'objects';
								$operator = 'and';
								$post_types = get_post_types( $args, $output, $operator );
								if ( !empty( $post_types ) ) {
									foreach ( $post_types  as $post_type_key => $post_type ) {
										if ( !post_type_supports( $post_type_key, 'thumbnail' ) ) { unset( $post_types[$post_type_key] ); }
										if ( isset( $portfolio_cpts ) && in_array( $post_type_key, $portfolio_cpts ) ) { unset( $post_types[$post_type_key] ); }
									}
								}
								if ( !empty( $post_types ) ) : 
								?>
								<optgroup label="<?php esc_attr_e( 'Custom Post Types', 'go_portfolio_textdomain' ); ?>"></optgroup>
								<?php
								foreach ( $post_types  as $post_type_key => $post_type ) :
								foreach ( get_object_taxonomies( $post_type_key ) as $tax_name ) { $registered_taxonomies[$post_type_key][]=$tax_name; }
								?>
								<option data-children="<?php echo esc_attr( $post_type_key ); ?>" value="<?php echo esc_attr( $post_type_key ); ?>"<?php echo ( isset( $portfolio['post-type'] ) && $portfolio['post-type'] ==  $post_type_key ? ' selected="selected"' : '' ); ?>><?php echo $post_type->labels->name; ?></option>
								<?php
								endforeach;
								endif;
								?>							
							</select>
						</td>
						<td>
							<p class="description"><?php _e( 'Select a post type or custom post type for the portfolio.', 'go_portfolio_textdomain' ); ?></p>
							<p class="description"><?php printf ( __( '%1$s to create a new custom post type.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-custom-post-types' ) . '">' . __( 'Click here', 'go_portfolio_textdomain' ). '</a>' ); ?></p>
							<p class="description"><?php printf ( __( '%1$s to enable the portfolio with default post types or other (not plugin defined) custom post types.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-settings' ) . '">' . __( 'Click here', 'go_portfolio_textdomain' ). '</a>' ); ?></p>
						</td>
					</tr>                
					<tr class="gwa-gopf-builder-bt gwa-gopf-bt-cpt">
						<th style="display:none;" class="gwa-gopf-w200"><?php _e( 'Query Builder', 'go_portfolio_textdomain' ); ?></th>
						<td style="display:none;" class="gwa-gopf-w300">
                            <select name="cpt-query-method" class="gwa-gopf-w250">
								<option data-children="cpt-mb" value="manual"<?php echo ( isset( $portfolio['cpt-query-method'] ) && $portfolio['cpt-query-method'] == 'manual' ? ' selected="selected"' : '' ); ?>><?php _e( 'Manual builder', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td style="display:none;" ><p class="description"><?php _e( 'Select query building method of the posts.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-builder-bt gwa-gopf-bt-gallery">
						<th class="gwa-gopf-w200"><?php _e( 'Query Builder', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="gallery-query-method" class="gwa-gopf-w250">
								<option data-children="gallery-vb" value="visual"<?php echo ( isset( $portfolio['gallery-query-method'] ) && $portfolio['gallery-query-method'] == 'visual' ? ' selected="selected"' : '' ); ?>><?php _e( 'Visual Builder', 'go_portfolio_textdomain' ); ?></option>							
								<option data-children="gallery-mb" value="manual"<?php echo ( isset( $portfolio['gallery-query-method'] ) && $portfolio['gallery-query-method'] == 'manual' ? ' selected="selected"' : '' ); ?>><?php _e( 'Manual Builder', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Select query building method of the posts.  <strong>Important: </strong> You can select posts by parameters (manual method) or with a visual builder.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
				</table>
				<!-- /Query builder type -->
				
				<!-- Gallery visual builder -->				
				<table class="form-table gwa-gopf-builder" data-children="gallery-vb">
					<tr>
						<td colspan="3">
							<div class="gwa-gopf-abox">
								<div class="gwa-gopf-abox-header"><?php _e( 'Gallery', 'go_portfolio_textdomain' ); ?><span class="gwa-gopf-abox-toggle"></span></div>
								<div class="gwa-gopf-abox-content gwa-gopf-abox-content-dark gwa-gopf-gallery gwa-gopf-clearfix">
									<?php if ( isset( $portfolio['inquery-items']['attachment'] ) &&  !empty( $portfolio['inquery-items']['attachment'] ) ) : 
									foreach ( $portfolio['inquery-items']['attachment'] as $portfolio_item_key => $portfolio_item ) : 
									$post_exist = get_post_type ( $portfolio_item_key );
									if ( $post_exist ) :
									?>
									<div class="gwa-gopf-thumb">
										<input type="hidden" name="inquery-items[attachment][<?php echo esc_attr( $portfolio_item_key  ); ?>]" value="<?php echo esc_url( $portfolio_item ); ?>">
										<?php 
										$thumb_img = wp_get_attachment_image_src( $portfolio_item_key, array(150, 150) );
										$thumb_img = !empty( $thumb_img[0] ) ? $thumb_img[0] : '';										
										$thumb_img_original = wp_get_attachment_image_src( $portfolio_item_key, '' );
										$thumb_img_original = !empty( $thumb_img_original[0] ) ? $thumb_img_original[0] : '';											
										?>
                                        <div class="gwa-gopf-thumb-inner"><a href="#"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?php echo esc_attr( $thumb_img ); ?>" data-src-full="<?php echo esc_attr( $thumb_img_original ); ?>" width="150" height="150"><?php //echo wp_get_attachment_image( $portfolio_item_key, array(150, 150) ); ?></a></div>
                                        
									</div>
									<?php 
									endif;
									endforeach; ?>
									<?php else: ?>
									<div class="gwa-gopf-thumb-add"><a href="#"><span></span></a></div>
									<?php endif; ?>
								</div>
								<div class="gwa-gopf-abox-content gwa-gopf-gallery-controls">
									<input type="button" class="gwa-gopf-btn-style1 gwa-gopf-thumb-add-new" value="<?php esc_attr_e( 'Add New', 'go_portfolio_textdomain' ); ?>" />
                                    <input type="button" class="gwa-gopf-btn-style3 gwa-gopf-ml10 gwa-gopf-thumb-delete-selected gwa-gopf-hidden" value="<?php esc_attr_e( 'Delete Selected', 'go_portfolio_textdomain' ); ?>" />
									<input type="button" class="gwa-gopf-btn-style3 gwa-gopf-ml10 gwa-gopf-thumb-delete-all<?php echo !isset( $portfolio['inquery-items']['attachment'] ) || empty( $portfolio['inquery-items']['attachment'] ) ? ' gwa-gopf-hidden' : ''; ?>" value="<?php esc_attr_e( 'Delete All', 'go_portfolio_textdomain' ); ?>" />
									<div class="gwa-gopf-gallery-controls-tip"><p class="description"><?php _e( '<strong>Tip:</strong> You can use SHIFT or CRTL to select item range.', 'go_portfolio_textdomain' ); ?></p></div>
								</div>					
							</div>
						</td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Order', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="order-vb" class="gwa-gopf-w250">
								<option value="ASC"<?php echo ( isset( $portfolio['order-vb'] ) && $portfolio['order-vb'] == 'ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Asccending Order', 'go_portfolio_textdomain' ); ?></option>							
								<option value="DESC"<?php echo ( isset( $portfolio['order-vb'] ) && $portfolio['order-vb'] == 'DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Descending Order', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Ascending order from lowest to highest values (1, 2, 3; a, b, c) or descending order from highest to lowest values.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>				
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Order Parameter', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="orderby-vb" class="gwa-gopf-w250">
								<option value="post__in"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'post__in' ? ' selected="selected"' : '' ); ?>><?php _e( 'Default Order', 'go_portfolio_textdomain' ); ?></option>
								<option value="ID"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'ID' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by ID', 'go_portfolio_textdomain' ); ?></option>							
								<option value="date"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'date' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by Date', 'go_portfolio_textdomain' ); ?></option>
								<option value="author"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'author' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by Author', 'go_portfolio_textdomain' ); ?></option>
								<option value="title"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'title' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by Title', 'go_portfolio_textdomain' ); ?></option>
								<option value="modified"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'modified' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by Last Modified Date', 'go_portfolio_textdomain' ); ?></option>
								<option value="rand"<?php echo ( isset( $portfolio['orderby-vb'] ) && $portfolio['orderby-vb'] == 'rand' ? ' selected="selected"' : '' ); ?>><?php _e( 'Random Order', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Parameter to sort the attachments by.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>	
				</table>
				<!-- /Gallery visual builder -->
					
				<!-- Manual builder -->					
				<table class="form-table gwa-gopf-builder" data-children="cpt-mb gallery-mb">
					<!-- get tax -->
					<?php
					ob_start();
					
					/* Loop through post types */
					if ( isset( $registered_taxonomies ) && !empty( $registered_taxonomies ) ) :
					foreach ( $registered_taxonomies as $post_type => $post_type_taxonomies ) :
					
					/* Loop through taxonomies */
					if ( isset( $post_type_taxonomies ) && !empty( $post_type_taxonomies ) ) :
					foreach ( $post_type_taxonomies as $taxonomy ) :
					/* if has one tax */
					$terms = get_terms( $taxonomy );
					if ( isset( $terms ) && !empty( $terms ) ) : 
					$tax_data = get_taxonomy( $taxonomy );
					?>
					<tr class="gwa-gopf-group" data-parent="post-type <?php echo esc_attr( $post_type ); ?>" data-children="<?php echo esc_attr( $post_type ); ?> <?php echo esc_attr( $taxonomy ); ?>">
						<th class="gwa-gopf-w200"><?php echo $tax_data->labels->name; ?> </th>
						<td class="gwa-gopf-w300">
							<ul class="gwa-gopf-checkbox-list">
								<li><label><input type="checkbox" name="post-term<?php echo esc_attr( '['.$post_type.']['.$taxonomy.'][]' ); ?>" value="all" <?php echo isset( $portfolio['post-term'][$post_type][$taxonomy] ) && in_array( 'all', $portfolio['post-term'][$post_type][$taxonomy] ) ? 'checked="checked"' : ''; ?> class="gwa-gopf-checkbox-parent"><span></span> All <?php echo esc_attr( $tax_data->labels->name ); ?> [&nbsp;.&nbsp;]<span class="gwa-gopf-cb-toggle-icon"></span></label>
									<ul class="gwa-gopf-checkbox-list" style="display: block;">
							<?php 
							foreach ( $terms as $term ) :
							if ( $term->taxonomy == $taxonomy ) :
							$used_tax[$post_type][$taxonomy]['name']=$tax_data->labels->name;
							?>
							<li><label><input type="checkbox" name="post-term<?php echo esc_attr( '['.$post_type.']['.$taxonomy.'][]' ); ?>" value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo isset( $portfolio['post-term'][$post_type][$taxonomy] ) && in_array( $term->term_id, $portfolio['post-term'][$post_type][$taxonomy] ) ? 'checked="checked"' : ''; ?> /><span></span> <?php echo $term->name; ?></label></li>
							<?php
							endif;
							endforeach;
							?>
							</ul></li></ul>
						</td>
						<td>
							<p class="description"><?php _e( 'Select the terms that you would like use in post query and for filtering (if the porfolio is filterble). ', 'go_portfolio_textdomain' ); ?></p>
							<p class="description"><?php _e( '<strong>Important: </strong>Don\'t select any terms if you wouldn\'t like to filter the post query by a taxonomy or taxnomy terms.', 'go_portfolio_textdomain' ); ?></p>
						</td>
					</tr>				
					<?php
					endif;
					endforeach;
					endif;
					endforeach;
					endif;
					$content = ob_get_contents();
					ob_end_clean();
					if ( isset( $used_tax ) && !empty( $used_tax ) ) :
					foreach ( $used_tax as $post_type => $post_type_taxonomies ) :
					if ( count( $post_type_taxonomies ) > 1 ) :
					?>
					<tr class="gwa-gopf-group" data-parent="post-type" data-children="<?php echo esc_attr( $post_type ); ?>">
						<th class="gwa-gopf-w200"><?php _e( 'Taxonomy', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="post-tax<?php echo esc_attr( '['.$post_type.']' );?>" class="gwa-gopf-w250" data-parent="<?php echo esc_attr( $post_type ); ?>">
					<?php foreach ( $post_type_taxonomies as $tax_slug => $tax ) : ?>
								<option data-children="<?php echo esc_attr( $tax_slug ); ?>" value="<?php echo esc_attr( $tax_slug ); ?>"<?php echo ( isset( $portfolio['post-tax'][$post_type] ) && $portfolio['post-tax'][$post_type] == $tax_slug ? ' selected="selected"' : '' ); ?>><?php echo $tax['name']; ?></option>
					<?php endforeach; ?>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Select the taxonomy that you would like use in post query and for filtering (if the porfolio is filterble).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>				
					<?php 
					else:
					?>
					<input type="hidden" name="post-tax<?php echo esc_attr( '['.$post_type.']' );?>" value="<?php echo esc_attr( key( $post_type_taxonomies ) ); ?>" />
					<?php 
					endif;
					endforeach;
					endif;
					?>
					
					<!-- Filter taxonomy -->
					<?php ob_start();
					if ( isset( $used_tax ) && !empty( $used_tax ) ) :
					foreach ( $used_tax as $post_type => $post_type_taxonomies ) :
					if ( count( $post_type_taxonomies ) > 1 ) :
					/* For backward compatibility */
					if ( !isset( $portfolio['filter-tax'] ) && isset( $portfolio ) ) { $portfolio['filter-tax']=$portfolio['post-tax']; }
					?>					
					<tr class="gwa-gopf-group" data-parent="post-type" data-children="<?php echo esc_attr( $post_type ); ?> filter">
						<th class="gwa-gopf-w200"><?php _e( 'Taxonomy filter', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="filter-tax<?php echo esc_attr( '['.$post_type.']' );?> filter" class="gwa-gopf-w250" data-parent="<?php echo esc_attr( $post_type ); ?> filter">
					<?php foreach ( $post_type_taxonomies as $tax_slug => $tax ) : ?>
								<option value="<?php echo esc_attr( $tax_slug ); ?>"<?php echo ( isset( $portfolio['filter-tax'][$post_type] ) && $portfolio['filter-tax'][$post_type] == $tax_slug ? ' selected="selected"' : '' ); ?>><?php echo $tax['name']; ?></option>
					<?php endforeach; ?>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Select the taxonomy that you would like use in post query and for filtering (if the porfolio is filterble).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<?php 
					else:
					?>
					<input type="hidden" name="filter-tax<?php echo esc_attr( '['.$post_type.']' );?>" value="<?php echo esc_attr( key( $post_type_taxonomies ) ); ?>" />
					<?php 
					endif;
					endforeach;
					endif;
					$filter_tax_content = ob_get_contents();
					ob_end_clean();					
					?>
					<!-- /Filter taxonomy -->
					
					<?php echo $content; ?>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Post Order', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="order" class="gwa-gopf-w250">
								<option value="DESC"<?php echo ( isset( $portfolio['order'] ) && $portfolio['order'] == 'DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Descending order', 'go_portfolio_textdomain' ); ?></option>
								<option value="ASC"<?php echo ( isset( $portfolio['order'] ) && $portfolio['order'] == 'ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'Asccending order', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Descending order from highest to lowest values (3, 2, 1; c, b, a) or ascending order from lowest to highest values.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>				
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Order Parameter', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="orderby" class="gwa-gopf-w250">
								<option value="date"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'date' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by date', 'go_portfolio_textdomain' ); ?></option>
								<option value="author"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'author' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by author', 'go_portfolio_textdomain' ); ?></option>
								<option value="ID"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'ID' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by post id', 'go_portfolio_textdomain' ); ?></option>
								<option value="title"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'title' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by title', 'go_portfolio_textdomain' ); ?></option>
								<option value="name"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'name' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by post name (post slug)', 'go_portfolio_textdomain' ); ?></option>
								<option value="modified"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'modified' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by last modified date', 'go_portfolio_textdomain' ); ?></option>
								<option value="comment_count"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'comment_count' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by number of comments', 'go_portfolio_textdomain' ); ?></option>
								<option value="menu_order"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'menu_order' ? ' selected="selected"' : '' ); ?>><?php _e( 'Order by page order', 'go_portfolio_textdomain' ); ?></option>
								<option value="rand"<?php echo ( isset( $portfolio['orderby'] ) && $portfolio['orderby'] == 'rand' ? ' selected="selected"' : '' ); ?>><?php _e( 'Random order', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Parameter to sort the posts by.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>																													
				</table>
				<!-- /Manual builder -->
				
				<table class="form-table">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Number of Posts', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="post-count" value="<?php echo esc_attr( isset( $portfolio['post-count'] ) ? $portfolio['post-count'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Number of posts to be shown. <strong>Important: </strong>Leave empty if you wouldn\'t like to limit number of posts.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Exclude Current Item?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="exclude-current" <?php echo isset( $portfolio['exclude-current'] ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
						<td><p class="description"><?php _e( 'Whether to exlude the current item from the query. If you insert the portfolio into a single post page of the selected post type, the current single item will be excluded form the post list (portfolio).<br>This is ideal for "Related Posts" or "Recents Post" teasers.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
				</table>						
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>
		
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>3</div>            
				<div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Layout Settings', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Columns, Spaces and Thumbnail', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content">
				<table class="form-table">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Layout Type', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="layout-type" class="gwa-gopf-w250" data-parent="layout-type">
								<option data-children="grid" value="grid"<?php echo ( isset( $portfolio['layout-type'] ) && $portfolio['layout-type'] == 'grid' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Grid Type (e.g. Regular Porfolio)', 'go_portfolio_textdomain' ); ?></option>
								<option data-children="slider" value="slider"<?php echo ( isset( $portfolio['layout-type'] ) && $portfolio['layout-type'] == 'slider' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Slider Type (e.g. Porfolio Teaser)', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'Select layout type. Grid Type is ideal for (filterable) portfolio, Slider type is for teasers (e. g. recent or related items). <strong>Important: </strong>Slider type portfolio cannot be filterable.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
                    <tr class="gwa-gopf-group" data-parent="layout-type" data-children="grid">
                        <th class="gwa-gopf-w200"><?php _e( 'Layout Mode', 'go_portfolio_textdomain' ); ?></th>
                        <td class="gwa-gopf-w300">
                            <select name="layout-mode" class="gwa-gopf-w250">
                                <option data-children="masonry" value="masonry"<?php echo ( isset( $portfolio['layout-mode'] )
                                && $portfolio['layout-mode'] == 'masonry' ? ' selected="selected"' : '' ); ?>> <?php _e(
                                        'Masonry', 'go_portfolio_textdomain' ); ?></option>
                                <option data-children="fit-rows" value="fitRows"<?php echo ( isset( $portfolio['layout-mode']
                                ) && $portfolio['layout-mode'] == 'fitRows' ? ' selected="selected"' : '' ); ?>> <?php _e
                                    ( 'Fit Rows (Equal height rows)', 'go_portfolio_textdomain' ); ?></option>
                            </select>
                        </td>
                        <td><p class="description"><?php _e( 'Select layout mode. Masonry: Items have their own dimensions depending on the media and content. Fit Rows: Items have same height in each row. ','go_portfolio_textdomain' ); ?></p></td>
                    </tr>

					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="grid">
						<th class="gwa-gopf-w200"><?php _e( 'Enable CSS Transforms?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="trans-enabled" <?php echo isset( $portfolio['trans-enabled'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
						<td><p class="description"><?php _e( 'Whether enable CSS transforms for isotope grid animations (e.g. ordering, filtering post items.) if available. <strong>Important: </strong>Recommended to enable this option except if you use videos or other iframe Flash content in thumbnnails. Safari and Firefox on Mac don\'t render videos (and other Flash content) when transform is applied to parent element.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Infinite?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="slider-infinite" <?php echo isset( $portfolio['slider-infinite'] ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
						<td><p class="description"><?php _e( 'Whether to slide infinitely.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Autoplay?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="slider-autoplay" <?php echo isset( $portfolio['slider-autoplay'] ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
						<td><p class="description"><?php _e( 'Whether to autoplay sider.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>																
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Autoplay direction', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="slider-autoplay-direction" class="gwa-gopf-w250">
								<option value="left"<?php echo ( isset( $portfolio['slider-autoplay-direction'] ) && $portfolio['slider-autoplay-direction'] == 'left' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Left', 'go_portfolio_textdomain' ); ?></option>
								<option value="right"<?php echo ( isset( $portfolio['slider-autoplay-direction'] ) && $portfolio['slider-autoplay-direction'] == 'right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Right', 'go_portfolio_textdomain' ); ?></option>
							</select>						
						<td><p class="description"><?php _e( 'Direction of autoplay.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Autoplay timeout duration?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="slider-autoplay-timeout" value="<?php echo esc_attr( isset( $portfolio['slider-autoplay-timeout'] ) ? $portfolio['slider-autoplay-timeout'] : '3000' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Timeout duration between slides (milliseconds).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Slider arrows alignment', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="slider-arrows-align" class="gwa-gopf-w250">
								<option value=""<?php echo ( isset( $portfolio['slider-arrows-align'] ) && $portfolio['slider-arrows-align'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Left', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-slider-controls-centered"<?php echo ( isset( $portfolio['slider-arrows-align'] ) && $portfolio['slider-arrows-align'] == 'gw-gopf-slider-controls-centered' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Center', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-slider-controls-right"<?php echo ( isset( $portfolio['slider-arrows-align'] ) && $portfolio['slider-arrows-align'] == 'gw-gopf-slider-controls-right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Right', 'go_portfolio_textdomain' ); ?></option>																															
							</select>
						</td>
						<td><p class="description"><?php _e( 'Select alignment for slider arrows.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>									
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Slider arrows space', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="slider-arrows-v-space" value="<?php echo esc_attr( isset( $portfolio['slider-arrows-v-space'] ) ? $portfolio['slider-arrows-v-space'] : '20' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Space between slider arrows and portfolio (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr class="gwa-gopf-group" data-parent="layout-type" data-children="slider">
						<th class="gwa-gopf-w200"><?php _e( 'Slider arrows vertical space', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="slider-arrows-h-space" value="<?php echo esc_attr( isset( $portfolio['slider-arrows-h-space'] ) ? $portfolio['slider-arrows-h-space'] : '6' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Space between the slider arrows (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>																																
				</table>
				<div class="gwa-gopf-separator"></div>
				<table class="form-table">
                    <tr>
                        <th class="gwa-gopf-w200"><?php _e( 'Layout Direction', 'go_portfolio_textdomain' ); ?></th>
                        <td class="gwa-gopf-w300">
                            <select name="layout-direction" class="gwa-gopf-w250">
                                <option value=""<?php echo ( isset( $portfolio['layout-direction'] ) && $portfolio['layout-direction'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Left to Right (LTR)', 'go_portfolio_textdomain' ); ?></option>
                                <option value="gw-gopf-rtl"<?php echo ( isset( $portfolio['layout-direction'] ) && $portfolio['layout-direction'] == 'gw-gopf-rtl' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Right to Left (RTL)', 'go_portfolio_textdomain' ); ?></option>
                            </select>
                        </td>
                        <td><p class="description"><?php _e( 'Select layout direction. <strong>Important: </strong>If you select RTL you should also opt-out the "Enable CSS Transforms?" option.', 'go_portfolio_textdomain' ); ?></p></td>
                    </tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Column Layout per Row', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="column-layout" class="gwa-gopf-w250">
								<option value="gw-gopf-1col"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-1col' ? ' selected="selected"' : '' ); ?>> <?php _e( '1 Column', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-2cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-2cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '2 Columns', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-3cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-3cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '3 Columns', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-4cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-4cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '4 Columns', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-5cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-5cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '5 Columns', 'go_portfolio_textdomain' ); ?></option>
								<option value="gw-gopf-6cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-6cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '6 Columns', 'go_portfolio_textdomain' ); ?></option>	
								<option value="gw-gopf-7cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-7cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '7 Columns', 'go_portfolio_textdomain' ); ?></option>	
								<option value="gw-gopf-8cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-8cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '8 Columns', 'go_portfolio_textdomain' ); ?></option>	
								<option value="gw-gopf-9cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-9cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '9 Columns', 'go_portfolio_textdomain' ); ?></option>	
								<option value="gw-gopf-10cols"<?php echo ( isset( $portfolio['column-layout'] ) && $portfolio['column-layout'] == 'gw-gopf-10cols' ? ' selected="selected"' : '' ); ?>> <?php _e( '10 Columns', 'go_portfolio_textdomain' ); ?></option>	
							</select>
						</td>
						<td><p class="description"><?php _e( 'How many items would you like to be shown in a row.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Column Space', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="h-space" value="<?php echo esc_attr( isset( $portfolio['h-space'] ) ? $portfolio['h-space'] : '20' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Horizontal space between portfolio items (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>							
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Row Space', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="v-space" value="<?php echo esc_attr( isset( $portfolio['v-space'] ) ? $portfolio['v-space'] : '20' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Vertical space between portfolio items (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>																																				
				</table>
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->       

		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>
        		
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>4</div>            
				<div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Thumbnail Settings', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Thumbnail and Lightbox Image Settings', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content">
				<table class="form-table">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Thumbnail Width', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="width" value="<?php echo esc_attr( isset( $portfolio['width'] ) ? $portfolio['width'] : '' ); ?>" class="gwa-gopf-w250" /></label></td>
						<td  rowspan="2">
							<p class="description"><?php _e( 'You can set fixed thumbnail aspect ratio (width and height ratio) overriding thumbnail default one. <br>For example: If you set the width and the height to "1" the image aspect ratio will be 1 (=1/1). This means the width and height is the same.<br><strong>Important: </strong>You can set fixed height (in pixels) if you set only the height field.<br>Leave these fields empty if you would like to use the default dimensions and aspect ratios for thumbnails.', 'go_portfolio_textdomain' ); ?></p>
						</td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Thumbnail Height', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="text" name="height" value="<?php echo esc_attr( isset( $portfolio['height'] ) ? $portfolio['height'] : '' ); ?>" class="gwa-gopf-w250" /></label></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Select Thumbnail Image Size', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							
							<?php
							global $_wp_additional_image_sizes;
							$thumb_sizes  = array();
							foreach( get_intermediate_image_sizes() as $s ){
								$thumb_sizes [$s] = array( 0, 0 );
								if ( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ) {
									$thumb_sizes [$s][0] = get_option( $s . '_size_w' );
									$thumb_sizes [$s][1] = get_option( $s . '_size_h' );
								} else {
									if ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[$s] ) ) { $thumb_sizes[$s] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[$s]['height'] ); }
								}
							}
							?>
							<select name="thumbnail-size" class="gwa-gopf-w250">	
							<option value="full"<?php echo ( isset( $portfolio['thumbnail-size'] ) &&  $portfolio['thumbnail-size'] == 'full' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Original Resolution', 'go_portfolio_textdomain' ); ?></option>
					 		<optgroup label="<?php esc_attr_e( 'Intermediate Resolutions', 'go_portfolio_textdomain' ); ?>"></optgroup>
							<?php
							if ( isset($thumb_sizes) && !empty( $thumb_sizes ) ) :
							foreach ( $thumb_sizes as $thumb_key => $thumb_size ) :
							?>
							<option value="<?php echo esc_attr( $thumb_key ); ?>"<?php echo ( isset( $portfolio['thumbnail-size'] ) &&  $portfolio['thumbnail-size'] == $thumb_key ? ' selected="selected"' : '' ); ?>> <?php echo $thumb_key . ' (' . $thumb_size[0] . 'x' . $thumb_size[1] . ')'; ?></option>
							<?php
							endforeach;
							endif;
							?>							
							</select>
						</td>
						<td><p class="description"><?php _e( 'The preferred resolution for thumbnail image. The original resolution is loaded if a thumbnail size doesn\'t exist for an image.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Thumbnail bg Position X', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="thumb-bg-pos-x" value="<?php echo esc_attr( isset( $portfolio['thumb-bg-pos-x'] ) ? $portfolio['thumb-bg-pos-x'] : '50' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Thumbnail image background X position (percent).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Thumbnail bg Position Y', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="thumb-bg-pos-y" value="<?php echo esc_attr( isset( $portfolio['thumb-bg-pos-y'] ) ? $portfolio['thumb-bg-pos-y'] : '50' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Thumbnail image background Y position (percent).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Image Thumbnail Link', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="media-link" class="gwa-gopf-w250">
								<option value="disable"<?php echo ( isset( $portfolio['media-link'] ) && $portfolio['media-link'] == 'disable' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Disable (Not Link)', 'go_portfolio_textdomain' ); ?></option>
								<option value="lightbox"<?php echo ( isset( $portfolio['media-link'] ) && $portfolio['media-link'] == 'lightbox' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Link to the Lightbox', 'go_portfolio_textdomain' ); ?></option>
								<option value="link"<?php echo ( isset( $portfolio['media-link'] ) && $portfolio['media-link'] == 'link' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Link to an URL', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>
						<td><p class="description"><?php _e( 'You can make the whole image thumbnail linkable if you disable overlay or overlay buttons globally or locally (per post). The link can open lightbox or go to an URL.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'First Image as Thumbnail?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="first-img-thumb" class="gwa-gopf-w250">
								<option value="disable"<?php echo ( isset( $portfolio['first-img-thumb'] ) && $portfolio['first-img-thumb'] == 'disable' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Disable (Not Set)', 'go_portfolio_textdomain' ); ?></option>
								<option value="force"<?php echo ( isset( $portfolio['first-img-thumb'] ) && $portfolio['first-img-thumb'] == 'force' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Force', 'go_portfolio_textdomain' ); ?></option>
								<option value="fallback"<?php echo ( isset( $portfolio['first-img-thumb'] ) && $portfolio['first-img-thumb'] == 'fallback' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Fallback', 'go_portfolio_textdomain' ); ?></option>
							</select>
						</td>						
						<td><p class="description"><?php _e( 'Whether to set the first image in the post content as thumbnail image.<br><strong>Force:</strong> The first image in post content will be set as thumbnail overriding other thumbnail settings. <br><strong>Fallback:</strong> The first image in post content will be set as thumbnail image if no thumbnail image has been set ("Thumbnail Image" under "Go Portfolio Options" metabox or "Featured image").', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Default Image', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<div class="gwa-gopf-media-upload">
                            	<?php
								$image_value = '';
								$image_url = '';
								$default_img = isset( $portfolio['default-img'] ) ? (int)$portfolio['default-img'] : '';
								if ( !empty( $default_img ) ) {
									$_image = wp_get_attachment_image_src( $default_img, array( 150, 150 ) );
									if ( $_image || is_array( $_image ) && !isset( $_image[0] ) ) {
										$image_url = $_image[0];
									}
								}
								?>
	                            <input type="hidden" name="default-img" value="<?php echo esc_attr( $default_img ); ?>">
                                <?php
								if ( !empty( $default_img ) ) :
								?>
								<div class="gwa-gopf-media-upload-item" data-id="<?php echo esc_attr( $default_img ); ?>" data-src="<?php echo esc_attr( $image_url ); ?>" data-file="<?php echo esc_attr( basename( $image_url ) ); ?>" data-type="image"><a href="#" class="gwa-gopf-media-upload-item-close"><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i38"></use></svg></a><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i120"></use></svg><div class="gwa-gopf-media-upload-item-media"><a href="#" title="<?php echo esc_attr( basename( $image_url ) ); ?>"></a><img src="<?php echo esc_attr( $image_url ); ?>"></div></div>
								<div style="display:none;" class="gwa-gopf-media-upload-item gwa-gopf-media-upload-item-new"><a href="#" title="<?php esc_attr_e( esc_html_e( 'Add' ), 'go_portfolio_textdomain' ); ?>"></a><div class="gwa-gopf-media-upload-item-new-inner-front"><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i1c"></use></svg></div><div class="gwa-gopf-media-upload-item-new-inner-back"><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i1c"></use></svg></div></div>                                
								<?php 
								else :
								?>
    	                        <div class="gwa-gopf-media-upload-item gwa-gopf-media-upload-item-new"><a href="#" title="<?php esc_attr_e( esc_html_e( 'Add' ), 'go_portfolio_textdomain' ); ?>"></a><div class="gwa-gopf-media-upload-item-new-inner-front"><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i1c"></use></svg></div><div class="gwa-gopf-media-upload-item-new-inner-back"><svg viewBox="0 0 26 26"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-i1c"></use></svg></div></div>
								<?php
								endif;
								?>                                
                            </div>
						</td>						
						<td><p class="description"><?php _e( 'Default image is used only when image is not specified for portfolio items.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>                    
                    										
				</table>
				<div class="gwa-gopf-separator"></div>
				<table class="form-table">										
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Select Lightbox Image Size', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
							<select name="lightbox-size" class="gwa-gopf-w250">	
					 		<option value="full"<?php echo ( isset( $portfolio['lightbox-size'] ) &&  $portfolio['lightbox-size'] == 'full' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Original Resolution', 'go_portfolio_textdomain' ); ?></option>
							<optgroup label="<?php esc_attr_e( 'Intermediate Resolutions', 'go_portfolio_textdomain' ); ?>"></optgroup>
							<?php
							if ( isset( $thumb_sizes ) && !empty( $thumb_sizes ) ) :
							foreach ( $thumb_sizes as $thumb_key => $thumb_size ) :
							?>
							<option value="<?php echo esc_attr( $thumb_key ); ?>"<?php echo ( isset( $portfolio['lightbox-size'] ) &&  $portfolio['lightbox-size'] == $thumb_key ? ' selected="selected"' : '' ); ?>> <?php echo $thumb_key . ' (' . $thumb_size[0] . 'x' . $thumb_size[1] . ')'; ?></option>
							<?php
							endforeach;
							endif;
							?>							
							</select>
						</td>
						<td><p class="description"><?php _e( 'The preferred resolution for lighbox image. The original resolution is loaded if a thumbnail size doesn\'t exist for an image.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>			
				</table>
			
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     
				
		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>
        
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>5</div>            
				<div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Template & Style Settings', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Select Template and Style', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content gwa-gopf-abox-content-dark">
				
				<!-- gwa-gopf-abox -->
				<div class="gwa-gopf-abox gwa-gopf-abox-tabbed gwa-gopf-style-vario">
					<div class="gwa-gopf-abox-header">
	                    <div class="gwa-gopf-abox-header-tab  gwa-gopf-current"><?php _e( 'Style', 'go_portfolio_textdomain' ); ?></div>                    
	                    <div class="gwa-gopf-abox-header-tab"><?php _e( 'Template', 'go_portfolio_textdomain' ); ?></div>
                    </div>
					<div class="gwa-gopf-abox-content gwa-gopf-current">									
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w400">
									<select name="style" class="gwa-gopf-group-btn-select gwa-gopf-w150" data-parent="style">
									<?php 
									if ( isset( $styles ) && !empty( $styles ) ) :
									foreach( $styles as $skey => $style ) : 
									if ( isset( $style['data'] ) && !empty( $style['data'] ) ) :
									?>
									<option data-children="<?php echo esc_attr( $skey ); ?>" value="<?php echo esc_attr( $skey ); ?>"<?php echo ( isset( $portfolio['style'] ) && $portfolio['style'] == $skey ? ' selected="selected"' : '' ); ?>><?php echo $style['name']; ?></option>
									<?php
									endif;
									endforeach;
									endif;
									?>
									</select>
									<input type="button" class="gwa-gopf-btn-style4 gwa-gopf-ml10 gwa-gopf-group-btn" data-parent="style-btn" data-label-m="<?php esc_attr_e( 'Hide source', 'go_portfolio_textdomain' ); ?>" data-label-o="<?php esc_attr_e( 'Show Source', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Show Source', 'go_portfolio_textdomain' ); ?>" />
									<input type="button" class="gwa-gopf-btn-style4 gwa-gopf-ml10 gwa-gopf-reset-style" value="<?php esc_attr_e( 'Reset', 'go_portfolio_textdomain' ); ?>" data-ajaxerrormsg="<?php _e( 'Oops, AJAX error!', 'go_portfolio_textdomain' ); ?>" />
									<img src="<?php echo admin_url(); ?>/images/wpspin_light.gif" class="ajax-loading" alt="" />										
								</td>
								<td><p class="description"><?php _e( 'Select a style.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<?php 
							if ( isset( $styles ) && !empty( $styles ) ) :
							foreach( $styles as $skey => $style ) : 
							if ( isset( $style['data'] ) && !empty( $style['data'] ) ) :							
							if ( isset( $style['effects'] ) && !empty( $style['effects'] ) ) :
							?>
							<tr class="gwa-gopf-group" data-parent="style" data-children="<?php echo esc_attr( $skey ); ?>">
								<th class="gwa-gopf-w200"><?php _e( 'Effect', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select data="style-effect[<?php echo esc_attr( $skey ); ?>]" class="gwa-gopf-w150">
									<?php
									foreach( $style['effects'] as $fkey => $effect ) : 
									?>
									<option value="<?php echo esc_attr( $fkey ); ?>"<?php echo ( isset( $portfolio['effect-data'] ) && $portfolio['effect-data'] == $fkey ? ' selected="selected"' : '' ); ?>><?php echo $effect; ?></option>
									<?php
									endforeach;
									?>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select an effect for this style.', 'go_portfolio_textdomain' ); ?></p></td>
                           	</tr>									
							<?php endif; ?>
							<tr class="gwa-gopf-group" data-parent="style-btn" data-children="<?php echo esc_attr( $skey ); ?>">
								<td colspan="3"><p class="description"><?php printf( __( 'All changes will affect this portfolio only. If you would like to change the layout generally, navigate to %1$s.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-editor' ) . '">' . __( 'Template & Style Editor', 'go_portfolio_textdomain' ). '</a>' ); ?></p></td>
							</tr>							
							<tr class="gwa-gopf-group" data-parent="style-btn" data-children="<?php echo esc_attr( $skey ); ?>">
                            	<td colspan="3">
									<textarea data="style-code[<?php echo esc_attr( $skey ); ?>]" style="width:100%;" rows="10" class="gwa-gopf-textarea-code"><?php echo stripslashes( isset( $portfolio['style-data'] ) && isset( $portfolio['style'] ) && $portfolio['style'] == $skey ? $portfolio['style-data'] : ( isset( $style['data'] ) ? $style['data'] : '' ) ); ?></textarea>
								</td>
                           	</tr>						   							
							<?php
							endif;
							endforeach;
							?>
							<input type="hidden" name="style-data" />
							<input type="hidden" name="effect-data" />
							<?php
							endif;
							?>																
						</table>				
					</div>                    
					<div class="gwa-gopf-abox-content">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Template', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w400">
									<select name="template" class="gwa-gopf-group-btn-select gwa-gopf-w150">
									<?php 
									if ( isset( $templates ) && !empty( $templates ) ) :
									foreach( $templates as $tkey => $template ) : 
									if ( isset( $template['data'] ) && !empty( $template['data'] ) ) :
									?>
									<option value="<?php echo esc_attr( $tkey ); ?>"<?php echo ( isset( $portfolio['template'] ) && $portfolio['template'] == $tkey ? ' selected="selected"' : '' ); ?>><?php echo $template['name']; ?></option>
									<?php
									endif;
									endforeach;
									endif;
									?>
									</select>
									<input type="button" class="gwa-gopf-btn-style4 gwa-gopf-ml10 gwa-gopf-group-btn" data-parent="template" data-label-m="<?php esc_attr_e( 'Hide source', 'go_portfolio_textdomain' ); ?>" data-label-o="<?php esc_attr_e( 'Show Source', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Show Source', 'go_portfolio_textdomain' ); ?>" />
									<input type="button" class="gwa-gopf-btn-style4 gwa-gopf-ml10 gwa-gopf-reset-template" value="<?php esc_attr_e( 'Reset', 'go_portfolio_textdomain' ); ?>" data-ajaxerrormsg="<?php _e( 'Oops, AJAX error!', 'go_portfolio_textdomain' ); ?>" />
									<img src="<?php echo admin_url(); ?>/images/wpspin_light.gif" class="ajax-loading" alt="" />									
								</td>
								<td><p class="description"><?php _e( 'Select a template.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<?php 
							if ( isset( $templates ) && !empty( $templates ) ) :
							foreach( $templates as $tkey => $template ) : 
							if ( isset( $template['data'] ) && !empty( $template['data'] ) ) :							
							?>
							<tr class="gwa-gopf-group" data-parent="template" data-children="<?php echo esc_attr( $tkey ); ?>">
								<td colspan="3"><p class="description"><?php printf( __( 'All changes will affect this portfolio only. If you would like to change the layout generally, navigate to %1$s.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-editor' ) . '">' . __( 'Template & Style Editor', 'go_portfolio_textdomain' ). '</a>' ); ?></p></td>
							</tr>							
							<tr class="gwa-gopf-group" data-parent="template" data-children="<?php echo esc_attr( $tkey ); ?>">
                            	<td colspan="3">
									<textarea data="template-code[<?php echo esc_attr( $tkey ); ?>]" style="width:100%;" rows="10" class="gwa-gopf-textarea-code"><?php echo stripslashes( isset( $portfolio['template-data'] ) && isset( $portfolio['template'] ) && $portfolio['template'] == $tkey ? $portfolio['template-data'] : ( isset( $template['data'] ) ? $template['data'] : '' ) ); ?></textarea>
								</td>
                           	</tr>						   							
							<?php
							endif;
							endforeach;
							?>
							<input type="hidden" name="template-data" />
							<?php
							endif;
							?>																
						</table>
					</div>
				</div> 
				<!-- /gwa-gopf-abox -->
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>
        
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large-aside">
				<div class="gwa-gopf-abox-header-aside"><span>#</span>6</div>            
				<div class="gwa-gopf-handle-icon-general-options"><?php _e( 'Style Customization', 'go_portfolio_textdomain' ); ?><small><?php _e( 'Fonts, Colors an Other Styling', 'go_portfolio_textdomain' ); ?></small></div>
				<span class="gwa-gopf-abox-toggle"></span>
			</div>
			<div class="gwa-gopf-abox-content gwa-gopf-abox-content-dark">

				<!-- gwa-gopf-abox -->
				<div class="gwa-gopf-abox gwa-gopf-abox-tabbed">
					<div class="gwa-gopf-abox-header">
	                    <div class="gwa-gopf-abox-header-tab gwa-gopf-current"><?php _e( 'General', 'go_portfolio_textdomain' ); ?></div>                    
	                    <div class="gwa-gopf-abox-header-tab"><?php _e( 'Filtering', 'go_portfolio_textdomain' ); ?></div>
	                    <div class="gwa-gopf-abox-header-tab"><?php _e( 'Overlay & Lightbox', 'go_portfolio_textdomain' ); ?></div>
	                    <div class="gwa-gopf-abox-header-tab"><?php _e( 'Content', 'go_portfolio_textdomain' ); ?></div>
	                    <div class="gwa-gopf-abox-header-tab"><?php _e( 'Pagination', 'go_portfolio_textdomain' ); ?></div> 
                        <div class="gwa-gopf-abox-header-tab"><?php _e( 'Custom CSS', 'go_portfolio_textdomain' ); ?></div>                        
                    </div> 
                    
                    <!-- General -->    
					<div class="gwa-gopf-abox-content gwa-gopf-current">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Extra Large Font Size', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[font_size_xl][val]" value="<?php echo esc_attr( isset( $portfolio['css']['font_size_xl']['val'] ) ? $portfolio['css']['font_size_xl']['val'] : '22' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[font_size_xl][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Extra large font used in WooCommerce price text (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_size_xl))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Extra Large Line Height', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[line_height_xl][val]" value="<?php echo esc_attr( isset( $portfolio['css']['line_height_xl']['val'] ) ? $portfolio['css']['line_height_xl']['val'] : '22' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[line_height_xl][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Line Height (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((line_height_xl))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Extra Large Font Family', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="css[font_family_xl][val]" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['css']['font_family_xl']['val'] ) && $portfolio['css']['font_family_xl']['val'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Default (Theme or Other Defined)', 'go_portfolio_textdomain' ); ?></option>
										<option value="1"<?php echo ( isset( $portfolio['css']['font_family_xl']['val'] ) && $portfolio['css']['font_family_xl']['val'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Primary Font Family', 'go_portfolio_textdomain' ); ?></option>
										<option value="2"<?php echo ( isset( $portfolio['css']['font_family_xl']['val'] ) && $portfolio['css']['font_family_xl']['val'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Secondary Font Family', 'go_portfolio_textdomain' ); ?></option>										
									</select>
									<input type="hidden" name="css[font_family_xl][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php printf ( __( 'Font families can be set under "%1$s" plugin submenu.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-settings' ) . '">' . __( 'General Settings', 'go_portfolio_textdomain' ). '</a>' ); ?></a></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_family_xl))</strong></p></td>
							</tr>
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">	
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Large Font Size', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[font_size_l][val]" value="<?php echo esc_attr( isset( $portfolio['css']['font_size_l']['val'] ) ? $portfolio['css']['font_size_l']['val'] : '16' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[font_size_l][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Large font used in post titles (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_size_l))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Large Font Line Height', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[line_height_l][val]" value="<?php echo esc_attr( isset( $portfolio['css']['line_height_l']['val'] ) ? $portfolio['css']['line_height_l']['val'] : '20' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[line_height_l][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Line Height (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((line_height_l))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Large Font Family', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="css[font_family_l][val]" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['css']['font_family_l']['val'] ) && $portfolio['css']['font_family_l']['val'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Default (Theme or Other Defined)', 'go_portfolio_textdomain' ); ?></option>
										<option value="1"<?php echo ( isset( $portfolio['css']['font_family_l']['val'] ) && $portfolio['css']['font_family_l']['val'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Primary Font Family', 'go_portfolio_textdomain' ); ?></option>
										<option value="2"<?php echo ( isset( $portfolio['css']['font_family_l']['val'] ) && $portfolio['css']['font_family_l']['val'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Secondary Font Family', 'go_portfolio_textdomain' ); ?></option>										
									</select>
									<input type="hidden" name="css[font_family_l][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php printf ( __( 'Font families can be set under "%1$s" plugin submenu.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-settings' ) . '">' . __( 'General Settings', 'go_portfolio_textdomain' ). '</a>' ); ?></a></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_family_l))</strong></p></td>
							</tr>
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">															
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Middle Font Size', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[font_size_m][val]" value="<?php echo esc_attr( isset( $portfolio['css']['font_size_m']['val'] ) ? $portfolio['css']['font_size_m']['val'] : '12' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[font_size_m][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Middle font used in portfolio filter and post excerpt (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_size_m))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Middle Font Line Height', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[line_height_m][val]" value="<?php echo esc_attr( isset( $portfolio['css']['line_height_m']['val'] ) ? $portfolio['css']['line_height_m']['val'] : '15' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[line_height_m][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Line Height (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((line_height_m))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Middle Font Family', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="css[font_family_m][val]" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['css']['font_family_m']['val'] ) && $portfolio['css']['font_family_m']['val'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Default (Theme or Other Defined)', 'go_portfolio_textdomain' ); ?></option>
										<option value="1"<?php echo ( isset( $portfolio['css']['font_family_m']['val'] ) && $portfolio['css']['font_family_m']['val'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Primary Font Family', 'go_portfolio_textdomain' ); ?></option>
										<option value="2"<?php echo ( isset( $portfolio['css']['font_family_m']['val'] ) && $portfolio['css']['font_family_m']['val'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Secondary Font Family', 'go_portfolio_textdomain' ); ?></option>										
									</select>
									<input type="hidden" name="css[font_family_m][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php printf ( __( 'Font families can be set under "%1$s" plugin submenu.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-settings' ) . '">' . __( 'General Settings', 'go_portfolio_textdomain' ). '</a>' ); ?></a></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_family_m))</strong></p></td>
							</tr>
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">														
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Small Font Size', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[font_size_s][val]" value="<?php echo esc_attr( isset( $portfolio['css']['font_size_s']['val'] ) ? $portfolio['css']['font_size_s']['val'] : '11' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[font_size_s][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Small font used in post meta (e.g. Post Date) (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_size_s))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Small Font Line Height', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[line_height_s][val]" value="<?php echo esc_attr( isset( $portfolio['css']['line_height_s']['val'] ) ? $portfolio['css']['line_height_s']['val'] : '15' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[line_height_s][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Line Height (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((line_height_s))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Small Font Family', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="css[font_family_s][val]" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['css']['font_family_s']['val'] ) && $portfolio['css']['font_family_s']['val'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Default (Theme or Other Defined)', 'go_portfolio_textdomain' ); ?></option>
										<option value="1"<?php echo ( isset( $portfolio['css']['font_family_s']['val'] ) && $portfolio['css']['font_family_s']['val'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Primary Font Family', 'go_portfolio_textdomain' ); ?></option>
										<option value="2"<?php echo ( isset( $portfolio['css']['font_family_s']['val'] ) && $portfolio['css']['font_family_s']['val'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Secondary Font Family', 'go_portfolio_textdomain' ); ?></option>										
									</select>
									<input type="hidden" name="css[font_family_s][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php printf ( __( 'Font families can be set under "%1$s" plugin submenu.', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-settings' ) . '">' . __( 'General Settings', 'go_portfolio_textdomain' ). '</a>' ); ?></a></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((font_family_s))</strong></p></td>
							</tr>																					
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Main Color 1', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[main_color_1][val]" value="<?php echo esc_attr( isset( $portfolio['css']['main_color_1']['val'] ) ? $portfolio['css']['main_color_1']['val'] : '#333333' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
									<input type="hidden" name="css[main_color_1][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is used for example for post excerpt and post title font color or buttons background color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((main_color_1))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Main Color 2', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[main_color_2][val]" value="<?php echo esc_attr( isset( $portfolio['css']['main_color_2']['val'] ) ? $portfolio['css']['main_color_2']['val'] : '#787878' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
								<input type="hidden" name="css[main_color_2][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is used for example for post meta (date) font color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((main_color_2))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Main Color 3', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[main_color_3][val]" value="<?php echo esc_attr( isset( $portfolio['css']['main_color_3']['val'] ) ? $portfolio['css']['main_color_3']['val'] : '#ffffff' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
								<input type="hidden" name="css[main_color_3][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is used for example for button text color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((main_color_3))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Main Color 4', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[main_color_4][val]" value="<?php echo esc_attr( isset( $portfolio['css']['main_color_4']['val'] ) ? $portfolio['css']['main_color_4']['val'] : '#b8b8b8' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
								<input type="hidden" name="css[main_color_4][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is used in WooCommerce old price color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((main_color_4))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Highlight Color', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[highlight_color][val]" value="<?php echo esc_attr( isset( $portfolio['css']['highlight_color']['val'] ) ? $portfolio['css']['highlight_color']['val'] : '#28ac86' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
									<input type="hidden" name="css[highlight_color][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is used for example for button background, link or overlay button color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((highlight_color))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Content bg Color', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[post_content_color][val]" value="<?php echo esc_attr( isset( $portfolio['css']['post_content_color']['val'] ) ? $portfolio['css']['post_content_color']['val'] : '#ffffff' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" />
									<input type="hidden" name="css[post_content_color][type]" value="string" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This color is for post content background color.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((post_content_color))</strong></p></td>
							</tr>							
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Content Inner Padding', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[post_padding][val]" value="<?php echo esc_attr( isset( $portfolio['css']['post_padding']['val'] ) ? $portfolio['css']['post_padding']['val'] : '20' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[post_padding][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Post content inner space (distance between content and border) (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((post_padding))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Content Opacity', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[post_opacity][val]" value="<?php echo esc_attr( isset( $portfolio['css']['post_opacity']['val'] ) ? $portfolio['css']['post_opacity']['val'] : '100' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[post_opacity][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Post content background opacity (percent, between 0-100).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((post_opacity))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Border Radius 1', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[border_radius_1][val]" value="<?php echo esc_attr( isset( $portfolio['css']['border_radius_1']['val'] ) ? $portfolio['css']['border_radius_1']['val'] : '0' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[border_radius_1][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This property used for the whole post border (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((border_radius_1))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Border Radius 2', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[border_radius_2][val]" value="<?php echo esc_attr( isset( $portfolio['css']['border_radius_2']['val'] ) ? $portfolio['css']['border_radius_2']['val'] : '0' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[border_radius_2][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This property used for buttons and portfolio filter tags border (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((border_radius_2))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Border Radius 3', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[border_radius_3][val]" value="<?php echo esc_attr( isset( $portfolio['css']['border_radius_3']['val'] ) ? $portfolio['css']['border_radius_3']['val'] : '22' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[border_radius_3][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'This property is used for the portfolio overlay circle border (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((border_radius_3))</strong></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Box Shadow Opacity', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[box_shadow_opacity][val]" value="<?php echo esc_attr( isset( $portfolio['css']['box_shadow_opacity']['val'] ) ? $portfolio['css']['box_shadow_opacity']['val'] : '0' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[box_shadow_opacity][type]" value="int" />
								</td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'The shadow opacity around the the post (percent, between 0-100).', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((box_shadow_opacity))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Box Shadow Blur', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[box_shadow_blur][val]" value="<?php echo esc_attr( isset( $portfolio['css']['box_shadow_blur']['val'] ) ? $portfolio['css']['box_shadow_blur']['val'] : '0' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[box_shadow_blur][type]" value="int" />
								</td>									
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'The shadow blur around the the post. (pixels)', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((box_shadow_blur))</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Box Shadow Spread', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<input type="text" name="css[box_shadow_spread][val]" value="<?php echo esc_attr( isset( $portfolio['css']['box_shadow_spread']['val'] ) ? $portfolio['css']['box_shadow_spread']['val'] : '0' ); ?>" class="gwa-gopf-w250" />
									<input type="hidden" name="css[box_shadow_spread][type]" value="int" />
								</td>									
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'The shadow spread around the the post. (pixels)', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'CSS varible:', 'go_portfolio_textdomain' ); ?> <strong>((box_shadow_spread))</strong></p></td>
							</tr>							
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"></th>
								<td colspan="3"><p class="description"><?php printf ( __( 'You can customize every part of the portfolio style including fonts, colors, border radius, opacity, and other properties using the source editor under "%1$s".', 'go_portfolio_textdomain' ), '<a href="' . admin_url( 'admin.php?page=go-portfolio-editor' ) . '">' . __( 'Template & Style Settings', 'go_portfolio_textdomain' ). '</a>' ); ?></p></td>
							</tr>																																									
						</table>		
					
					</div>

					<!-- Filtering -->
					<div class="gwa-gopf-abox-content">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filterable?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="filterable" <?php echo isset( $portfolio['filterable'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( "Enable or disable filtering (show or hide).", 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filtering Type?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="filter-type" class="gwa-gopf-w250" data-parent="filter-type">
										<option value="classic"<?php echo ( isset( $portfolio['filter-type'] ) && $portfolio['filter-type'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Classic (Isotope Filtering)', 'go_portfolio_textdomain' ); ?></option>
										<option data-children="opacity" value="opacity"<?php echo ( isset( $portfolio['filter-type'] ) && $portfolio['filter-type'] == 'opacity' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Opacity Change', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Filtering method. "Classic" isotope filtering with modifying layout or "Opacity Change" without modifying the layout.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="filter-type" data-children="opacity">
								<th class="gwa-gopf-w200"><?php _e( 'Element opacity', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="filter-inactive-opacity" value="<?php echo esc_attr( isset( $portfolio['filter-inactive-opacity'] ) ? $portfolio['filter-inactive-opacity'] : '30' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Opacity of inactive elements (percent, between 0-100).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>																												
							<!-- Filter taxonomy -->
							<?php echo $filter_tax_content; ?>
							<!-- /Filter taxonomy -->
							<tr>
								<th class="gwa-gopf-w200"><?php _e( '"All" Filter Tag Text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="filter-all-text" value="<?php echo esc_attr( isset( $portfolio['filter-all-text'] ) ? $portfolio['filter-all-text'] : 'All' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text of the "All" filter tag.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filter Tag Style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="filter-tag-style" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['filter-tag-style'] ) && $portfolio['filter-tag-style'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Button', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-btn-outlined"<?php echo ( isset( $portfolio['filter-tag-style'] ) && $portfolio['filter-tag-style'] == 'gw-gopf-btn-outlined' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Outlined Button', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select style for filter tag.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Selected Filter Tag Style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="filter-current-tag-style" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['filter-current-tag-style'] ) && $portfolio['filter-current-tag-style'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Button', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-btn-outlined"<?php echo ( isset( $portfolio['filter-current-tag-style'] ) && $portfolio['filter-current-tag-style'] == 'gw-gopf-btn-outlined' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Outlined Button', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select style for "Selected" filter tags.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filter Alignment', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="filter-align" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['filter-align'] ) && $portfolio['filter-align'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Left', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-cats-centered"<?php echo ( isset( $portfolio['filter-align'] ) && $portfolio['filter-align'] == 'gw-gopf-cats-centered' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Center', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-cats-right"<?php echo ( isset( $portfolio['filter-align'] ) && $portfolio['filter-align'] == 'gw-gopf-cats-right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Right', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select alignment for filter tags.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>									
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filter Vertical Space', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="filter-v-space" value="<?php echo esc_attr( isset( $portfolio['filter-v-space'] ) ? $portfolio['filter-v-space'] : '20' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Vertical space between portfolio filter and portfolio items (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Filter Vertical Position', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="filter-v-pos" class="gwa-gopf-w250">
										<option value="top"<?php echo ( isset( $portfolio['filter-v-pos'] ) && $portfolio['filter-v-pos'] == 'top' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Top', 'go_portfolio_textdomain' ); ?></option>
										<option value="bottom"<?php echo ( isset( $portfolio['filter-v-pos'] ) && $portfolio['filter-v-pos'] == 'bottom' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Bottom', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Vertical Position of Filter.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Space Between Filter Tags', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="filter-h-space" value="<?php echo esc_attr( isset( $portfolio['filter-h-space'] ) ? $portfolio['filter-h-space'] : '6' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Space between portfolio filter tags (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>									
						</table>
					</div>

					<!-- Overlay & Lightbox -->
					<div class="gwa-gopf-abox-content">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Overlay?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="overlay" <?php echo isset( $portfolio['overlay'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( "Enable or disable overlay for thumbnail images.", 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Show Overlay On', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="overlay-hover" class="gwa-gopf-w250">
										<option value="1"<?php echo ( isset( $portfolio['overlay-hover'] ) && $portfolio['overlay-hover'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Image Hover', 'go_portfolio_textdomain' ); ?></option>
										<option value="2"<?php echo ( isset( $portfolio['overlay-hover'] ) && $portfolio['overlay-hover'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Post Hover', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Wether to show overlay on image hover or post hover.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>														
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Overlay Color', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-color" value="<?php echo esc_attr( isset( $portfolio['overlay-color'] ) ? $portfolio['overlay-color'] : '#333333' ); ?>" class="gwa-gopf-colorpicker-input gwa-gopf-w50" /></td>
								<td><p class="description"><?php _e( 'Overlay color. Use the colorpicker to choose a color.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Overlay Opacity', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-opacity" value="<?php echo esc_attr( isset( $portfolio['overlay-opacity'] ) ? $portfolio['overlay-opacity'] : '30' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Overlay opacity (percent, between 0-100).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Lighbox Button on Overlay?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="overlay-button-lb" <?php echo isset( $portfolio['overlay-button-lb'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Enable or disable the lightbox button on overlay. You can disable the button per post/attachment under "Go Portfolio Options" metabox if you enable it here.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Read More Button on Overlay?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="overlay-button-link" <?php echo isset( $portfolio['overlay-button-link'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Enable or disable the read more/link button on overlay. You can disable the button per post/attachment under "Go Portfolio Options" metabox if you enable it here.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>														
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Overlay Button Type', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="overlay-style" class="gwa-gopf-w250" data-parent="overlay">
										<option data-children="overlay-circle" value="1"<?php echo ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '1' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Circle Buttons with Icons', 'go_portfolio_textdomain' ); ?></option>
										<option data-children="overlay-button" value="2"<?php echo ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '2' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Buttons with Text', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the overlay button type.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="overlay" data-children="overlay-button">
								<th class="gwa-gopf-w200"><?php _e( 'Overlay button style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="overlay-btn-style" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['overlay-btn-style'] ) && $portfolio['overlay-btn-style'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Button', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-btn-outlined"<?php echo ( isset( $portfolio['overlay-btn-style'] ) && $portfolio['overlay-btn-style'] == 'gw-gopf-btn-outlined' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Outlined Button', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the overlay button style.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>														
							<tr class="gwa-gopf-group" data-parent="overlay" data-children="overlay-button">
								<th class="gwa-gopf-w200"><?php _e( 'Overlay link button text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-btn-link-post" value="<?php echo esc_attr( isset( $portfolio['overlay-btn-link-post'] ) ? $portfolio['overlay-btn-link-post'] : 'Read More' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text for "link to post" button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="overlay" data-children="overlay-button">
								<th class="gwa-gopf-w200"><?php _e( 'Overlay lightbox link button text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-btn-link-image" value="<?php echo esc_attr( isset( $portfolio['overlay-btn-link-image'] ) ? $portfolio['overlay-btn-link-image'] : 'Show More' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text for "link to image" lightbox button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="overlay" data-children="overlay-button">
								<th class="gwa-gopf-w200"><?php _e( 'Overlay video link button text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-btn-link-video" value="<?php echo esc_attr( isset( $portfolio['overlay-btn-link-video'] ) ? $portfolio['overlay-btn-link-video'] : 'Watch This' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text for "link to video" lightbox button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="overlay" data-children="overlay-button">
								<th class="gwa-gopf-w200"><?php _e( 'Overlay audio link button text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="overlay-btn-link-audio" value="<?php echo esc_attr( isset( $portfolio['overlay-btn-link-audio'] ) ? $portfolio['overlay-btn-link-audio'] : 'Listen This' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text for "link to audio" lightbox button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>																												
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Disable Lightbox?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="disable-lightbox" <?php echo isset( $portfolio['disable-lightbox'] ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Disable the default lightbox.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>						
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Show Caption Under Lightbox?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="lightbox-caption" <?php echo isset( $portfolio['lightbox-caption'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Caption source is the media title for attachments post type, otherwise the post title.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Lightbox Gallery?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="lightbox-gallery" <?php echo isset( $portfolio['lightbox-gallery'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Enable gallery option for lightbox.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Deep Linking for the Lightbox?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="lightbox-deep-linking" <?php echo isset( $portfolio['lightbox-deep-linking'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Enable deep linking for the lightbox. <strong>Important:</strong> Deep linking manipulates the hashtag in the site url and the browser history. Enabling may cause conflicts with themes (plugins) which also modify hash or history (e.g. page reloading).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>																			
						</table>
					</div>

					<!-- Content -->
					<div class="gwa-gopf-abox-content">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Content Alignment', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="post-align" class="gwa-gopf-w250">
										<option value="left"<?php echo ( isset( $portfolio['post-align'] ) && $portfolio['post-align'] == 'left' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Left', 'go_portfolio_textdomain' ); ?></option>
										<option value="center"<?php echo ( isset( $portfolio['post-align'] ) && $portfolio['post-align'] == 'center' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Center', 'go_portfolio_textdomain' ); ?></option>
										<option value="right"<?php echo ( isset( $portfolio['post-align'] ) && $portfolio['post-align'] == 'right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Right', 'go_portfolio_textdomain' ); ?></option>										
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the text alignment for the post content.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>											
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Title Maximum Length', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="title-length" value="<?php echo esc_attr( isset( $portfolio['title-length'] ) ? $portfolio['title-length'] : '' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Number of characters to show. <br><strong>Important:</strong> Leave empty if you don\'t like to set this value.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Excerpt Source', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="excerpt-src" class="gwa-gopf-w250">
										<option value="content"<?php echo ( isset( $portfolio['excerpt-src'] ) && $portfolio['excerpt-src'] == 'content' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Post Content / Image Description', 'go_portfolio_textdomain' ); ?></option>
										<option value="excerpt"<?php echo ( isset( $portfolio['excerpt-src'] ) && $portfolio['excerpt-src'] == 'excerpt' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Post Excerpt (If Available)', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'The source of the generated post excerpt.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Excerpt Maximum Words ', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="excerpt-length" value="<?php echo esc_attr( isset( $portfolio['excerpt-length'] ) ? $portfolio['excerpt-length'] : '10' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Number of words to show. The longer text will be cut off.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Post Excerpt More String', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="excerpt-more" value="<?php echo esc_attr( isset( $portfolio['excerpt-more'] ) ? $portfolio['excerpt-more'] : '...' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'The string at the end of the excerpt.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Strip Shortcodes from Excerpt?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="excerpt-strip-sc" <?php echo isset( $portfolio['excerpt-strip-sc'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Whether to remove shortcodes in the excerpt.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Strip HTML Tags from Excerpt?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="excerpt-strip-html" <?php echo isset( $portfolio['excerpt-strip-html'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Whether to remove HTML in the excerpt.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Allowed HTML Tags in Excerpt ', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="excerpt-allowed-tags" value="<?php echo esc_attr( isset( $portfolio['excerpt-allowed-tags'] ) ? $portfolio['excerpt-allowed-tags'] : '' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Allowed HTML tags in excerpt if the "Strip HTML Tags from Excerpt?" option is enabled. Others will be stripped. E.g. to allow links enter "&lt;a&gt;", to allow links and paragraph enter "&lt;a&gt;&lt;p&gt;". <br> <strong>Important:</strong> Don\'t use space or other delimiters if you add more tags.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>														
						</table>
						<div class="gwa-gopf-separator"></div>
						<table class="form-table">																												
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Read More Button Text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="post-button-text" value="<?php echo esc_attr( isset( $portfolio['post-button-text'] ) ? $portfolio['post-button-text'] : 'Read More' ); ?>" class="gwa-gopf-w250" /></td>
								<td class="gwa-gopf-w360"><p class="description"><?php _e( 'Text of the "Road More" button.', 'go_portfolio_textdomain' ); ?></p></td>
								<td><p class="description"><?php _e( 'HTML varible in templates:', 'go_portfolio_textdomain' ); ?> <strong>{{post_button_text}}</strong></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Read More Button Alignment', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="post-button-align" class="gwa-gopf-w250">
										<option value="left"<?php echo ( isset( $portfolio['post-button-align'] ) && $portfolio['post-button-align'] == 'left' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Left', 'go_portfolio_textdomain' ); ?></option>
										<option value="center"<?php echo ( isset( $portfolio['post-button-align'] ) && $portfolio['post-button-align'] == 'center' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Center', 'go_portfolio_textdomain' ); ?></option>
										<option value="right"<?php echo ( isset( $portfolio['post-button-align'] ) && $portfolio['post-button-align'] == 'right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Right', 'go_portfolio_textdomain' ); ?></option>										
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the button alignment for the "Road More" button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Read More Button Style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="post_button_style" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['post_button_style'] ) && $portfolio['post_button_style'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Link', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-btn"<?php echo ( isset( $portfolio['post_button_style'] ) && $portfolio['post_button_style'] == 'gw-gopf-btn' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Button', 'go_portfolio_textdomain' ); ?></option>																				
										<option value="gw-gopf-btn gw-gopf-btn-outlined"<?php echo ( isset( $portfolio['post_button_style'] ) && $portfolio['post_button_style'] == 'gw-gopf-btn gw-gopf-btn-outlined' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Outlined Button', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select style for the "Road More" button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>																																										
						</table>
					</div>                
                
					<!-- Pagination -->                
					<div class="gwa-gopf-abox-content">
						<table class="form-table">
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Enable Pagination?', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><label><input type="checkbox" name="pagination" <?php echo isset( $portfolio['pagination'] ) || !isset( $portfolio ) ? 'value="1" checked="checked"' : ''; ?> /><span></span> </label></td>
								<td><p class="description"><?php _e( 'Enable pagination if your post length is larger than the "Number of Posts" option.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>						
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Pagination Type', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="pagination-type" class="gwa-gopf-w250" data-parent="pagination-type">
										<option data-children="load-more" value="load-more"<?php echo ( isset( $portfolio['pagination-type'] ) && $portfolio['pagination-type'] == 'load-more' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Load More', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the type of the pagination.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Pagination Alignment', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="pagination-align" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['pagination-align'] ) && $portfolio['pagination-align'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Left', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-pagination-centered"<?php echo ( isset( $portfolio['pagination-align'] ) && $portfolio['pagination-align'] == 'gw-gopf-pagination-centered' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Center', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-pagination-right"<?php echo ( isset( $portfolio['pagination-align'] ) && $portfolio['pagination-align'] == 'gw-gopf-pagination-right' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Align Right', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select the alignment for the pagination.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Pagination Vertical Space', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="pagination-v-space" value="<?php echo esc_attr( isset( $portfolio['pagination-v-space'] ) ? $portfolio['pagination-v-space'] : '20' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Vertical space between pagination and portfolio (pixels).', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr>
								<th class="gwa-gopf-w200"><?php _e( 'Button Style', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300">
									<select name="load-more-button-style" class="gwa-gopf-w250">
										<option value=""<?php echo ( isset( $portfolio['load-more-button-style'] ) && $portfolio['load-more-button-style'] == '' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Button', 'go_portfolio_textdomain' ); ?></option>
										<option value="gw-gopf-btn-outlined"<?php echo ( isset( $portfolio['load-more-button-style'] ) && $portfolio['load-more-button-style'] == 'gw-gopf-btn-outlined' ? ' selected="selected"' : '' ); ?>> <?php _e( 'Outlined Button', 'go_portfolio_textdomain' ); ?></option>
									</select>
								</td>
								<td><p class="description"><?php _e( 'Select style for "Load More" button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>							
							<tr class="gwa-gopf-group" data-parent="pagination-type" data-children="load-more">
								<th class="gwa-gopf-w200"><?php _e( 'Load more button text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="load-more-button-text" value="<?php echo esc_attr( isset( $portfolio['load-more-button-text'] ) ? $portfolio['load-more-button-text'] : 'Load More' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text of the "Load more" button.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>
							<tr class="gwa-gopf-group" data-parent="pagination-type" data-children="load-more">
								<th class="gwa-gopf-w200"><?php _e( 'Load more button loading text', 'go_portfolio_textdomain' ); ?></th>
								<td class="gwa-gopf-w300"><input type="text" name="load-more-button-loading-text" value="<?php echo esc_attr( isset( $portfolio['load-more-button-loading-text'] ) ? $portfolio['load-more-button-loading-text'] : 'Loading...' ); ?>" class="gwa-gopf-w250" /></td>
								<td><p class="description"><?php _e( 'Text of the "Load more" button when new posts are loading.', 'go_portfolio_textdomain' ); ?></p></td>
							</tr>																																																															
						</table>
					</div>
                    
					<!-- Custom CSS -->                
					<div class="gwa-gopf-abox-content">
                        <table class="form-table">
                            <tr class="gwa-fw-row">
                                <th class="gwa-gopf-w200"><label><?php _e( 'Code', 'go_portfolio_textdomain' ); ?></label></th>
                            </tr>
                            <tr class="gwa-fw-row">
                                <td colspan="3" style="padding-bottom:10px;">
                                    <textarea style="width:100%;" name="custom-css" rows="10" class="gwa-gopf-textarea-code"><?php echo isset( $portfolio['custom-css'] ) ? esc_html( $portfolio['custom-css'] ) : ''; ?></textarea>
                                </td>
                            </tr>                
                            <tr class="gwa-fw-row">
                                <td colspan="3"><p class="description"><?php esc_html_e( 'CSS code will be automatically prefixed with an id selector (e.g. #gw_go_portfolio_my_portfolio .my-custom-rule { ... }) and will be applied to this portfolio only! For general - not portfolio specific - CSS codes use the "Custom CSS" section under General Settings page.', 'go_portfolio_textdomain' ); ?> </p></td>
                            </tr>                    
                        </table>
					</div>                    
                    
				</div> 
				<!-- /gwa-gopf-abox -->		

			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit gwa-gopf-clearfix">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=go-portfolio' ) ); ?>" title="<?php esc_attr_e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?>" class="gwa-gopf-btn-style5 gwa-gopf-fr"><?php _e( 'Go to Dashboard', 'go_portfolio_textdomain' ); ?></a>
		</p>

	</form>
	<!-- /form -->
	
	<?php
	endif;	
	?>	
	
</div>
<?php
// SVG icon sprite
$svg = new GW_GoPortfolio_Svg;
$svg->load( GW_GO_PORTFOLIO_DIR . 'admin/images/icons.svg' );
$svg->render_sprite( null, true );

// Template
echo '<script type="text/html" id="tmpl-gw-gopf-media-upload-item"><div class="gwa-gopf-media-upload-item" data-id="{{{data.id}}}" data-src="{{{data.src}}}" data-file="{{{data.file}}}" data-type="image"><a href="#" class="gwa-gopf-media-upload-item-close"><svg viewBox="0 0 26 26"><use xlink:href="#icon-i38"></use></svg></a><div class="gwa-gopf-media-upload-item-media"><a href="#" title="{{{data.file}}}"></a><img src="{{{data.src}}}"></div></div></script>';

?>		