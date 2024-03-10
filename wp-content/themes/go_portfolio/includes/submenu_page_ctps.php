<?php
/**
 * Submenu page for in admin area
 * Custom Post Types Page
 *
 * @package   Go Portfolio - WordPress Responsive Portfolio 
 * @author    Granth <granthweb@gmail.com>
 * @link      https://granthweb.com
 * @copyright 2019 Granth
 */
 
$screen = get_current_screen();

/* Get cpts db data */
$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );


/* Handle post */
if ( !empty( $_POST ) && check_admin_referer( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ) ) {

	$reponse = array();
	$referrer=$_POST['_wp_http_referer'];

	/* Clean post fields */
	$_POST = go_portfolio_clean_input( $_POST, array(),
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
			$new_custom_post_types = $custom_post_types;
			$new_custom_post_types[$new_uniqid] = $new_custom_post_types[$uniqid];
			
			$new_custom_post_types[$new_uniqid]['name'] = $new_custom_post_types[$new_uniqid]['name'] . ' copy ' . $uniqid;
			$new_custom_post_types[$new_uniqid]['singular_name'] = $new_custom_post_types[$new_uniqid]['singular_name'] . ' copy ' . $uniqid;
			$new_custom_post_types[$new_uniqid]['slug'] = substr( $new_custom_post_types[$new_uniqid]['slug'], 0 ,6 ) . '_' . $new_uniqid;
						
			/* Save data to db */
			if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
				if ( $new_custom_post_types != $custom_post_types ) { 
					update_option( self::$plugin_prefix . '_cpts', $new_custom_post_types );
				}
				$response['result'] = 'success';
				$response['message'][] = __( 'The custom post type has been successfully cloned.', 'go_portfolio_textdomain' );
				update_option( md5( $screen->id . '-response' ), $response, false );
			}

			/* Redirect */
			wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&updated=true' ) );
			exit;	
			
		/* Delete action */
		} elseif ( $_POST['action-type'] == 'delete' && !empty( $uniqid ) ) {
				
			/* Do stuff */
			$new_custom_post_types = $custom_post_types;
			unset( $new_custom_post_types[$uniqid] );
			
			/* Save data to db */
			if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
				if ( $new_custom_post_types != $custom_post_types ) { 
					update_option( self::$plugin_prefix . '_cpts', $new_custom_post_types );
				}
				$response['result'] = 'success';
				$response['message'][] = __( 'The custom post type has been successfully deleted.', 'go_portfolio_textdomain' );
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
		$new_custom_post_types = $custom_post_types;
		$post_types = get_post_types( '', 'objects' );
		$new_custom_post_type = $_POST;

		/* Do stuff - verify post data */
		if ( !empty( $new_custom_post_type ) ) {
			
			/* Check if post type name (built-in & CPT) does exist */
			if ( !isset( $new_custom_post_type['name'] ) || empty( $new_custom_post_type['name'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'Custom post type name is empty!', 'go_portfolio_textdomain' );						
			} elseif ( isset( $post_types ) || !empty( $post_types ) ) {		
				foreach ( $post_types as $post_type ) {
					if ( $new_custom_post_type['name'] == $post_type->label && !isset( $custom_post_types[$uniqid] ) ) {
						$response['result'] = 'error';
						$response['message'][] = __( 'Custom post type name already exists!', 'go_portfolio_textdomain' );
						break;
					}
				}
			}
			
			/* Check if post type singular name (built-in & CPT) does exist */
			if ( !isset( $new_custom_post_type['singular_name'] ) || empty( $new_custom_post_type['singular_name'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'Custom post type singular name is empty!', 'go_portfolio_textdomain' );
			} elseif ( isset( $post_types ) || !empty( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					if ( $new_custom_post_type['singular_name'] == $post_type->labels->singular_name && !isset( $custom_post_types[$uniqid] ) ) {
						$response['result'] = 'error';
						$response['message'][] = __( 'Post type singular name already exists!', 'go_portfolio_textdomain' );
						break;
					}
				}
			}
			
			/* Check if post type slug (built-in & CPT) does exist */
			if ( !isset( $new_custom_post_type['slug'] ) || empty( $new_custom_post_type['slug'] ) ) {
				$response['result'] = 'error';
				$response['message'][] = __( 'Custom post type slug is empty!', 'go_portfolio_textdomain' );
			} elseif( preg_match( '/([A-Z\s])/', $new_custom_post_type['slug'] ) ){
				/* Check if custom post type slug contains invalid characters 1.7.3 */
				$response['result'] = 'error';
				$response['message'][] = __( 'Custom post type slug contains invalid characters! Only lowercase letters, numbers, hyphens and underscores are allowed.', 'go_portfolio_textdomain' );				
			} elseif ( isset( $post_types ) || !empty( $post_types ) ) {
				// Remove accents
				$new_custom_post_type['slug'] = remove_accents( $new_custom_post_type['slug'] );
				foreach ( $post_types as $post_type ) {
					if ( $new_custom_post_type['slug'] == $post_type->name && !isset( $custom_post_types[$uniqid] ) ) {
						$response['result'] = 'error';
						$response['message'][] = __( 'Post type slug already exists!', 'go_portfolio_textdomain' );
						break;
					}
				}
			} 
			
			
			
			
		}
		
		/* Save data to db */
		if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
			$new_custom_post_types[$uniqid]= $new_custom_post_type;			
			if ( $new_custom_post_types != $custom_post_types ) { 
				update_option( self::$plugin_prefix . '_cpts', $new_custom_post_types );
			}
			$response['result'] = 'success';
			$response['message'][] = __( 'Custom post types has been successfully updated.', 'go_portfolio_textdomain' );
		}
		
		/* Set the reponse message */
		update_option( md5( $screen->id . '-response' ), $response, false );		
		
		/* Set temporary POST data */
		update_option( md5( $screen->id . '-data' ), $new_custom_post_type, false );
		
		/* Redirect */
		if ( !isset( $custom_post_types[$uniqid] ) && isset( $response['result'] ) && $response['result'] == 'success' ) { $referrer = preg_replace( '/&edit=new/', '&edit='. $uniqid, $referrer ); }		
		$referrer = preg_match( '/&updated=true$/', $referrer ) ? $referrer : $referrer . '&updated=true';
		wp_redirect( $referrer );
		exit;		

	}
	
}

/**
 *
 * Content
 *
 */
 
?>
<div id="gwa-gopf-admin-wrap" class="gwa-gopf-wrap wrap">
	<form id="gwa-gopf-cpt-form" name="gwa-gopf-cpt-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<div class="gwa-gopf-ploader"><div class="gwa-gopf-ploader-content" data-content="<?php esc_attr_e( 'Hey, just a sec!', 'go_portfolio_textdomain' ); ?>"><div class="gwa-gopf-spinner"></div></div></div>
	<div class="gwa-gopf-ptopbar">
		<div class="gwa-gopf-logo"></div>
		<div class="gwa-gopf-ptopbar-title"><?php _e( 'Go Portfolio', 'go_portfolio_textdomain' ); ?></div>
		<div class="gwa-gopf-ptopbar-content">
		<?php 
		if ( empty( $_POST ) && !isset( $_GET['edit'] )  || ( isset( $_GET['edit'] ) && empty ( $_GET['edit'] ) ) ) : 
		?>
        <input type="submit" class="gwa-gopf-btn-style5 gwa-gopf-new" value="<?php esc_attr_e( '++ Create', 'go_portfolio_textdomain' ); ?>" />
        <?php
		else : 
		?>
		<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
        <?php
		endif;
		?>
        </div>
    </div>
    <h2 class="gwa-gopf-pheader">
    	<div class="gwa-gopf-logo"></div>
        <div class="gwa-gopf-pheader-title"><?php _e( 'Go Portfolio - Custom Post Type Manager', 'go_portfolio_textdomain' ); ?></div>
    </h2>
	<?php

	/* Print message */
	if ( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' && $response = get_option( md5( $screen->id . '-response' ) ) ) : 
	?>
	<div id="result" class="<?php echo $response['result'] == 'error' ? 'error' : 'updated'; ?>">
	<?php foreach ( $response['message'] as $error_msg ) : ?>
		<p><strong><?php echo $error_msg; ?></strong></p>
	<?php endforeach; $response = array(); ?>
	</div>
	<?php 	
	delete_option( md5( $screen->id . '-response' ) );
	endif;
	/* /Print message */

	?>
	
	<?php
		
	/**
	 *
	 * Default Page content
	 *
	 */
	 
	if ( empty( $_POST ) && !isset( $_GET['edit'] )  || ( isset( $_GET['edit'] ) && empty ( $_GET['edit'] ) ) ) : 
	?>
	<!-- form -->
		<input id="gwa-gopf-action-type" name="action-type" type="hidden" value="edit" />
		<?php wp_nonce_field( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ); ?>

		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header gwa-gopf-abox-header-large"><?php _e( 'Custom Post Type Manager', 'go_portfolio_textdomain' ); ?></div>
			<div class="gwa-gopf-abox-content">
                
                <!-- list -->
                <div class="gw-gopf-list">
					<?php 
					$cnt = 0;
                    if ( isset( $custom_post_types ) && !empty( $custom_post_types ) ) :
                    foreach ( $custom_post_types as $cpt_key => $cpt_value ) :
                    ?>               
	                <div class="gw-gopf-list-item gwa-gopf-clearfix">
                    	<div class="gw-gopf-list-item-details">
                        <div class="gw-gopf-list-item-count"><?php printf( "%02d.", $cnt ); ?></div>
                        <div class="gw-gopf-list-item-name"><?php echo $cpt_value['name'];  ?></div>
                        <div class="gw-gopf-list-item-meta"><?php printf( __( '#%s', 'go_portfolio_textdomain' ), $cpt_key ); ?>, <?php printf( __( '<strong>Slug</strong>: %s', 'go_portfolio_textdomain' ), $cpt_value['slug'] ); ?></div>
                        </div>
                    	<div class="gw-gopf-list-item-main">
                        </div>                        
                    	<div class="gw-gopf-list-item-assets">
                        	<input type="submit" class="gwa-gopf-btn-style1 gwa-gopf-edit" data-id="<?php echo esc_attr( $cpt_key ); ?>"  value="<?php esc_attr_e( 'Edit', 'go_portfolio_textdomain' ); ?>" />
                        	<input type="button" class="gwa-gopf-btn-style2 gwa-gopf-ml10 gwa-gopf-clone" data-id="<?php echo esc_attr( $cpt_key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure?', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Clone', 'go_portfolio_textdomain' ); ?>" />
                        	<input type="button" class="gwa-gopf-btn-style3 gwa-gopf-ml10 gwa-gopf-delete" data-id="<?php echo esc_attr( $cpt_key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure?', 'go_portfolio_textdomain' ); ?>" value="<?php esc_attr_e( 'Delete', 'go_portfolio_textdomain' ); ?>" />
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
                        	<div class="gw-gopf-dash-wdesc"><?php esc_html_e( 'Create a new custom post type from Scratch or Import existing ones', 'go_portfolio_textdomain' ); ?></div>
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
	 * Edit Custom Post Type Page content
	 *
	 */

	if ( empty( $_POST ) && isset( $_GET['edit'] ) && !empty ( $_GET['edit'] ) ) : 
	
	/* Get temporary POST data */
	$temp_post_data = get_option( md5( $screen->id . '-data' ) );
	if ( $temp_post_data ) {
		delete_option( md5( $screen->id . '-data' ) );
		$custom_post_type=$temp_post_data;
	}
	
	/* Get data */
	$item_id = $_GET['edit'] == 'new' ? uniqid() : sanitize_key( $_GET['edit'] );
	if ($_GET['edit'] != 'new') {
		if ( !isset( $custom_post_types[$item_id] ) ) {
			?>
			<div id="result" class="error">
			<p><strong><?php _e( 'Custom post type doesn\'t exist!', 'go_portfolio_textdomain' ); ?> <a href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $_GET['page'] ) ) ?>"><?php _e( 'Click here', 'go_portfolio_textdomain' ); ?></a> <?php _e( 'to create new custom post type.', 'go_portfolio_textdomain' ); ?></strong></p>
			</div>
			<?php
			exit;
		} else {
			$custom_post_type = isset( $custom_post_types[$item_id] ) ? $custom_post_types[$item_id] : null;
		}
	}	

	?>
	<!-- form -->
		<input type="hidden" name="uniqid" value="<?php echo esc_attr( $item_id ); ?>" />
		<?php wp_nonce_field( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ); ?>
		
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header"><?php _e( 'Custom Post Type Manager', 'go_portfolio_textdomain' ); ?><span class="gwa-gopf-abox-toggle"></span></div>
			<div class="gwa-gopf-abox-content">
				<table class="form-table">
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Plural name', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="name" value="<?php echo esc_attr( isset( $custom_post_type['name'] ) ? $custom_post_type['name'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'General name for the post type, usually plural (e.g. Portfolio).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>							
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Singular name', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="singular_name" value="<?php echo esc_attr( isset( $custom_post_type['singular_name'] ) ? $custom_post_type['singular_name'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'Name for one object of this post type (e.g. Portfolio post).', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Slug', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><input type="text" name="slug" value="<?php echo esc_attr( isset( $custom_post_type['slug'] ) ? $custom_post_type['slug'] : '' ); ?>" class="gwa-gopf-w250" /></td>
						<td><p class="description"><?php _e( 'URL friendly version of the custom post type (e.g. portfolio). <strong>Important: </strong>Only lowercase letters, numbers, hyphens and underscores. Max. 20 characters.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Has archive?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="has-archive" <?php echo isset( $custom_post_type['has-archive'] ) ? 'value="1" checked="checked"' : ''; ?> /> <span></span></label></td>
						<td><p class="description"><?php _e( 'Enables post type archives. Will use the post type as archive slug by default.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Exclude from search?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="search-exclude" <?php echo isset( $custom_post_type['search-exclude'] ) ? 'value="1" checked="checked"' : ''; ?> /> <span></span></label></td>
						<td><p class="description"><?php _e( 'Whether to exclude posts with this post type from front end search results.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
				</table>
				<div class="gwa-gopf-separator"></div>
				<table class="form-table">	
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Enable post type?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="enabled" <?php echo isset( $custom_post_type['enabled'] )  || ! isset( $custom_post_type ) ? 'value="1" checked="checked"' : ''; ?> /> <span></span></label></td>
						<td><p class="description"><?php _e( 'Enable or disable custom post type.  <strong>Important: </strong>You should enable the custom post type to be registered.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>								
				</table>	
			</div>
		</div> 
		<!-- /gwa-gopf-abox --> 
		
		<!-- gwa-gopf-abox -->
		<div class="gwa-gopf-abox">
			<div class="gwa-gopf-abox-header"><?php _e( 'Custom Post Type Taxonomy Manager', 'go_portfolio_textdomain' ); ?><span class="gwa-gopf-abox-toggle"></span></div>
			<div class="gwa-gopf-abox-content">
				<table class="form-table">	
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Create custom category taxonomy?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="custom-tax-cat" <?php echo isset( $custom_post_type['custom-tax-cat'] ) || ! isset( $custom_post_type ) ? 'value="1" checked="checked"' : ''; ?> /> <span></span></label></td>
						<td><p class="description"><?php _e( 'Whether to register custom category taxonomy.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Create custom tag taxonomy?', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300"><label><input type="checkbox" name="custom-tax-tag" <?php echo isset( $custom_post_type['custom-tax-tag'] )  || ! isset( $custom_post_type ) ? 'value="1" checked="checked"' : ''; ?> /> <span></span></label></td>
						<td><p class="description"><?php _e( 'Whether to register custom tag taxonomy.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>													
				</table>
				<div class="gwa-gopf-separator"></div>
				<table class="form-table">	
					<tr>
						<th class="gwa-gopf-w200"><?php _e( 'Select additional taxonomies', 'go_portfolio_textdomain' ); ?></th>
						<td class="gwa-gopf-w300">
						<?php
						$args = array(
						  'public'   => true,
						  'show_ui' => true
  						); 
						$output = 'objects';
						$operator = 'and';
						$taxonomies = get_taxonomies( $args, $output, $operator );
						if ( isset( $taxonomies ) && !empty( $taxonomies ) ) {
							foreach ( $taxonomies as $tax_key => $taxonomy ) {
								if ( !isset( $custom_post_type['slug'] ) || ( $custom_post_type['slug'] . '-cat' != $tax_key && $custom_post_type['slug'] . '-tag' != $tax_key ) ) :
								?>
								<label><input type="checkbox" name="tax[]" value="<?php echo esc_attr( $tax_key ); ?>" <?php echo isset( $custom_post_type['tax'] ) && in_array( $tax_key, $custom_post_type['tax'] ) ? 'checked="checked"' : ''; ?> /><span></span> <?php echo esc_attr( $taxonomy->labels->name ) . ' (' . $tax_key .')'; ?> </label><br>
								<?php
								endif;
							}
						}
						?>
						</td>
						<td><p class="description"><?php _e( 'Select the existing taxonomies to be registerered for the post type.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>								
				</table>							
			</div>
		</div> 
		<!-- /gwa-gopf-abox -->     

		<p class="submit">
			<input type="submit" class="gwa-gopf-btn-style1" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
		</p>

	</form>
	<!-- /form -->
	
	<?php
	endif;	
	?>	
	
</div>		