<?php
/**
 * Prevent direct call
 */

if ( !defined( 'WPINC' ) ) die;


/**
 * SVG Class
 * 
 * Create and render SVG maps from files, strings and render svg tags.
 * 
 */

class GW_GoPortfolio_Svg {


	/**
	 * Array of svg symbols (map)
	 * 
	 * @access protected
	 * @var array
	 */
	
	protected $symbols = array();

	
	/**
	 * Constructor of the class
	 * 
	 * @return void
	 */

	public function __construct() {	
	
	}


	/**
	 * Add svg symbols to svg map from file.
	 *
	 * @param string 		$file 		Svg file.
	 * @param bool 			$overwrite 	Items with existing ids will be skipped or overwritten.
	 * @return null|false 	Null on success, false on failure.
	 */
	
	public function load( $file, $overwrite = false ) {
		
		$file = trim( $file );
		if ( !file_exists( $file ) || !is_file( $file ) ) return false;
		$content = @file_get_contents( $file );
		if ( $content === false ) return false;		
		return $this->map_svg( $content, $overwrite );
	
	}
	

	/**
	 * Add svg symbols to svg map from string.
	 *
	 * @param string 		$content 		Svg HTML content.
	 * @param bool 			$overwrite 	Items with existing ids will be skipped or overwritten.
	 * @return null|false 	Null on success, false on failure.
	 */
	
	public function add( $content, $overwrite = false ) {
		
		$content = trim( $content );
		if ( !strlen( $content ) ) return false;
		return $this->map_svg( $content, $overwrite );
	}
	

	/**
	 * Remove item(s) from svg map.
	 *
	 * @param null|string 	$id 		ID of the item, removes all items if null.
	 * @return bool			True on success, false on failure.
	 */

	public function remove( $id = null ) {
		
		if ( empty( $this->symbols ) ) return false;
		
		if ( is_null( $id ) ) {
			$this->symbols = array();
		} else {	
			$id = trim( (string)$id );
			if ( !isset( $this->symbols[$id] ) ) return false;
			unset( $this->symbols[$id] );
		}
		return true;
				
	}
	

	/**
	 * Return item(s) from svg map.
	 *
	 * @param null|string 	$id 		ID of the item, retuns all items if null.
	 * @return array|false	Array of items on success and $id is set item array when $id is null, false on failure.
	 */

	public function get( $id = null ) {
		
		if ( empty( $this->symbols ) ) return false;
		
		if ( is_null( $id ) ) {
			return $this->symbols;
		} else {	
			$id = trim( (string)$id );
			if ( !isset( $this->symbols[$id] ) ) return false;
			return $this->symbols[$id];		
		}
			
	}				
	
	
	/**
	 * SVG mapping function. 
	 * Walks SVG find symbols, remove unnecessary tags (nodes) and attributes.
	 * 
	 * @access private
	 * 
	 * @param string 		$data		Data to fetch the svg from.
	 * @param bool 			$overwrite 	Items with existing ids will be skipped or overwritten.
	 * @return int			Number of symbols found.
	 */

	private function map_svg( $data, $overwrite = false )	{
		
		$count = 0;
		$svg = new DOMDocument();
		@$svg->loadXML( $data );
		
		foreach( $svg->getElementsByTagName( 'symbol' ) as $symbol ) {
		
			if ( strlen( $symbol->getAttribute( 'id' ) ) ) {
				$innerHTML = '';
				$children = $symbol->childNodes; 
				foreach ( $children as $child ) { 
	
					// Skip unused nodes
					switch( $child->nodeName ) {
	
						case '#text' :
						case 'title' :
						case 'desc' :
							break;
							
						default: 
							$innerHTML .= $svg->saveXML( $child );						
					}
	
				}
				
				if ( !filter_var( $overwrite, FILTER_VALIDATE_BOOLEAN ) && isset( $this->symbols[$symbol->getAttribute( 'id' )] ) ) continue;
				
				// Apply only important attributes (id & viewBox)
				$this->symbols[$symbol->getAttribute( 'id' )] = array(
					'id' => $symbol->getAttribute( 'id' ),
					'viewbox' => $symbol->getAttribute( 'viewBox' ),
					'content' => $innerHTML
				);
				$count++;
				
			}
		}
		
		return $count;
		
	}
	
	
	/**
	 * Renders SVG sprite from the map database. 
	 * 
	 * @param string|array 	$id		ID or array of ID to render in the sprite.
	 * @param bool 			$print 	Whether to print or only return the generated HTML.
	 * @return mixed		String when $print is true, null when false.
	 */

	public function render_sprite( $ids = null, $print = false ) {
		
		if ( !is_null( $ids ) ) $ids = array_unique( array_filter( array_map( 'trim', (array)$ids ) ) );

		$svg = new GW_GoPortfolio_Html( 'svg' );
		$svg->set_attr( 'xmlns', 'http://www.w3.org/2000/svg' );
		$svg->set_attr( 'style', 'display:none;' );		
		$defs = new GW_GoPortfolio_Html( 'defs' );

		foreach( $this->symbols as $symbol ) {
			if ( !is_null( $ids ) && !in_array( $symbol['id'], $ids ) ) continue;
			$elem = new GW_GoPortfolio_Html( 'symbol');
			$elem->set_attr( 'id', $symbol['id'] );
			$elem->set_attr( 'viewBox', $symbol['viewbox'] );			
			$elem->content( $symbol['content'] );
			$defs->content( $elem );
		}
		$svg->content( $defs );
		return $svg->render( $print );
		
	}	
	

	/**
	 * Renders SVG use element. 
	 * 
	 * @param string|array 	$id		ID of the item.
	 * @param bool 			$print 	Whether to print or only return the generated HTML.
	 * @return mixed		String when $print is true, null when false.
	 */	
	
	public function show( $id, $print = false ) {
		
		$id = trim( (string)$id );
		if ( !isset( $this->symbols[$id] ) ) return false;
		
		$svg = new GW_GoPortfolio_Html( 'svg' );
		$svg->set_attr( 'viewBox', $this->symbols[$id]['viewbox'] );
		$use = new GW_GoPortfolio_Html( 'use' );
		$use->set_attr( 'xlink:href', sprintf( '#%s' ,$this->symbols[$id]['id'] ) );		

		$svg->content( $use );
		return $svg->render( $print );
		
	}	
		
}