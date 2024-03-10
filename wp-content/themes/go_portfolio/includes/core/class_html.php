<?php
/*
* @todo	escape atts
*
*
* 
 */

class GW_GoPortfolio_Html {

	protected $name = null;
	protected $atts = null;
	protected $content = null;

	protected $is_self_closing = false;	
	protected $self_closing_tags = array( 'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' );
	
	public function __construct( $name = null, $self_closing = 'auto', $atts = array(), $content = '' ) { 

		$this->name( $name, $self_closing );
		$this->parse_atts( $atts );
		$this->content( $content );		
		
	}
	
	public function name( $name, $self_closing = 'auto' ) {
		
		if ( !is_null( $this->name ) ) return false;
		
		if ( !empty( $name ) ) {
			$name = (string)$name;
			//$name = preg_replace( '/[^a-zA-Z]+/', '', $name );
			$name = strtolower( $name );
		}
		
		if ( empty( $name ) ) return false;
		
		if ( $self_closing == 'auto' && in_array( $name, array( 'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ) ) || filter_var( $self_closing, FILTER_VALIDATE_BOOLEAN ) ) {
			$this->is_self_closing = true;
		}
		return $this->name = $name;
		
	}


	public function content( $content ) {

		if ( $this->is_self_closing || is_null( $this->name ) ) return false;
		
		if ( is_array( $content ) ) {
			foreach( $content as $part ) {
				$this->content( $part )	;
			}
		} elseif ( is_object( $content ) && $content instanceof GW_GoPortfolio_Html ) {
			return $this->content[] = $content;
		} else {
			$content = (string)$content;
			if ( strlen( $content ) ) {
				return $this->content[] = $content;
			}
		}
		return false;
	}


	public function get_content( $index = null, $type = null ) {
		
		if ( empty( $this->content ) ) return false;
		
		if ( !is_null( $index ) ) $index = (int)$index;
		if ( !is_null( $type ) ) {
			$type = trim( (string) $type );
			$type = in_array( $type, array( 'element', 'text' ) ) ? $type : null;
		}

		$result = $this->content;
				
		if ( $type == 'text' ) {
			$result = array_filter( $result, 'is_string' );
		} elseif ( $type == 'element' ) {
			$result = array_filter( $result, 'is_object' );
		}		
		
		if ( !is_null( $index ) ) {
			$result = array_slice( $result, $index, 1, true );
			$key = key( $result );
			return isset( $result[$key] ) ? $result[$key] : false;
		} 

		return !empty( $result ) ? $result : false;
		
	}

	
	public function remove_content( $index = null, $type = null ) {
		
		$result = $this->get_content( $index, $type );

		if ( $result !== false ) {
			if ( is_array( $result ) ) {
				foreach( $result as $key => $value ) {					
					unset( $this->content[$key] );
				}
			} else {
				unset( $this->content[$index] );
			}
			
			if ( count( $this->content ) == 0 ) $this->content = null;
		}
				
		return $result;
	}

	
	public function set_attr( $name, $value = null, $unfiltered = false ) {
		
		//$name = strtolower( (string)$name );
		$name = (string)$name;
		$name = preg_replace( '/[^a-zA-Z0-9_\-:]+/', '', $name );
			
		if ( empty( $name ) || is_null( $value ) ) return false;			
		$value = (string)$value;
		if ( !filter_var( $unfiltered, FILTER_VALIDATE_BOOLEAN ) ) {
			$values = explode( ' ', $value );
			$value = array_values( $values );
		} else {
			$value = (array)$value;
		}

		if ( empty( $this->atts ) ) $this->atts = new stdClass;

		if ( !empty( $this->atts->$name ) ) {
			$this->atts->$name = array_unique( array_merge( $this->atts->$name , $value ) );
		} else {
			$this->atts->$name = $value;
		}
		return true;
		
	}

	
	public function get_attr( $name = null ) {

		if ( empty( $this->atts ) ) return false;
		
		if ( !is_null( $name ) ) {	
			$name = strtolower( (string)$name );
			$name = preg_replace( '/[^a-zA-Z0-9_\-:]+/', '', $name );
			if ( isset( $this->atts->$name ) ) {
				return $this->atts->$name;
			} else {
				return false;	
			}
		}  else {		
			return $this->atts;			
		}
	}

	
	public function remove_attr( $name = null, $value = null, $unfiltered = false ) {

		if ( empty( $this->atts ) ) return false;
		
		if ( is_null( $name ) ) {
			$this->atts = null;
			return true;						
		} 

		$name = strtolower( (string)$name );
		$name = preg_replace( '/[^a-zA-Z0-9_\-:]+/', '', $name );			

		if ( is_null( $value ) ) {
			if( !empty($this->atts->$name ) ) {
				unset( $this->atts->$name );
				return true;
			} else {
				return false;
			}
		} else {
			if ( count( $this->atts->$name )  == 1 ) {
				unset( $this->atts->$name );
			} else {
				$this->atts->$name = array_diff( $this->atts->$name, (array)$value );
			}
		}
		
			
	}		
	

	public function parse_atts( $atts ) {

		if ( empty( $atts ) ) return false;

		$atts = (array)$atts;
		
		foreach( $atts as $att_name => $att_value ) {
			$att_name = trim( $att_name );
			if ( $att_name != '' && !is_array( $att_value ) ) {
				$this->set_attr( $att_name, $att_value  );	
			} else {
				$filtered = true;
				if ( isset( $att_value[1] ) ) $filtered = filter_var( $att_value[1], FILTER_VALIDATE_BOOLEAN );
				if ( isset( $att_value[0] ) ) {
					$this->set_attr( $att_name, $att_value[0], $filtered );	
				}

			}
		}
		return true;

	}	

	

	public function render_atts( $do_shortcode = false ) {
	
		if ( empty( $this->atts ) ) return false;
	
		$do_shortcode = filter_var( $do_shortcode, FILTER_VALIDATE_BOOLEAN );

		foreach( (array)$this->atts as $name => $value ) {
			if ( $do_shortcode ) $value = do_shortcode( $value );
			$value = array_values( array_filter( $value ,'strlen' ) );
			$html[] = sprintf( '%1$s="%2$s"', $name, esc_attr( implode( ' ', $value ) ) );
		}
		return !empty( $html ) ? ' ' . implode( ' ', $html ) : '';
		
	}	


	public function render( $print = false ) {
		
		$html = array();
		if ( is_array(  $this->content ) ) {		
			foreach ( $this->content as $content ) {
				if ( is_object( $content ) ) { 
					$html[] = $content->render();
				} else {
					$html[] = $content;
				}
			}
		}

		$content = sprintf( '<%1$s%2$s>%3$s%4$s', 
			$this->name,
			$this->render_atts(),
			implode( '', $html ), 
			$this->is_self_closing ? '' : '</' . $this->name . '>'
		 );

		/*return sprintf( '<%1$s%2$s>%3$s%4$s', 
			$this->name,
			$this->render_atts(),
			implode( '', $html ), 
			$this->is_self_closing ? '' : '</' . $this->name . '>'
		 );*/

		if ( !filter_var( $print, FILTER_VALIDATE_BOOLEAN ) ) {
			return $content;
		} else {
			echo $content;
		}
				

		 
	}

}
?>