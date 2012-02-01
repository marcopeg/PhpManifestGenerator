<?php
/**
 * Manifest Generator
 * by Marco Pegoraro
 * http://movableapp.com
 *
 * This is an utility script to generate a cache manifest file for HTML5 offline web applications.
 * Put this file into the main folder of the app and launch it form the browser.
 * 
 * A cache file named "cache.manifest" will be generated.
 * You need PHP to be able to read and write from the file system!
 *
 * NOTICE!!!
 * To make your webapp works offline you need an .htaccess to set mimetype of manifest file:
 * ---------- .htaccess --------------------
 * AddType text/cache-manifest manifest
 * -----------------------------------------
 * 
 * Then you need to setup the manifest in you HTML5 declaration:
 * ---------------- index.html -------------
 * <!DOCTYPE html> 
 * <html manifest="cache.manifest"> 
 * -----------------------------------------
 */



/**
 * Utility Debugging Method
 * (Thanks to CakePHP)
 */
if ( !function_exists('debug') ) {
	function debug($var = false, $showHtml = false) {
		
		if (true) {
			print "\n<pre class=\"cake_debug\">\n";
			ob_start();
			print_r($var);
			$var = ob_get_clean();

			if ($showHtml) {
				$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
			}
			print "{$var}\n</pre>\n";
		}
		
	} function ddebug($var = false, $showHtml = false) { debug($var,$showHtml); exit; }
}





/**
 * Manifest File Generator Utility
 */
class ManifestGenerator {
	
	
	var $source_path 	= '';
	var $manifest_path	= '';
	var $files 			= array();
	
	
	
	function __construct( $source = '', $dest = '' ) {
		
		$this->__init();
		
		if ( !empty($source) ) 	$this->setSource($source);
		if ( !empty($dest) )	$this->setManifestPath($dest);
		
		$this->generate();
		
	} // EndOf: "__construct()" ###
	
	
	private function __init() {
		
		$this->source_path 		= '';
		$this->manifest_path	= '';
		$this->files			= array();
		
	} // EndOf: "__init()" ##
	
	
	private function __ready() {
		
		// Value test
		if ( empty($this->source_path) ) 			return false;
		if ( empty($this->manifest_path) ) 			return false;
		
		// Existance test
		if ( !file_exists($this->source_path) ) 	return false;
		if ( !file_exists($this->manifest_path) ) 	return false;
		
		return true;
		
	} // EndOf: "__ready()" ###
	
	
	private function __buildResources( $path = '' ) {
		
		$dir = opendir($path);
		
		if ( !$dir ) return;
		
		while ( false !== ( $item = readdir($dir) ) ) {
			
			if ( in_array($item,array('.','..')) ) 	continue;
			if ( substr($item,0,1) == '.' ) 		continue;
			
			$item_path = $path . $item;
			
			// File -> add to files list
			if ( is_file($item_path) ) {
				
				if ( substr($item_path,0,2) == './' ) {
					$item_path = substr($item_path,2);
					
				} else {
					$item_path = substr( $item_path, strlen($this->source_path) );
					
				}
				
				// Exclude manifest file.
				if ( strpos($this->manifest_path,$item_path) !== false ) continue;
				
				$this->files[] = $item_path;
				
			// Directory -> recursion
			} else {
				
				if ( substr(strrev($item_path),0,1) != '/' ) $item_path.= '/';
				
				$this->__buildResources($item_path);
				
			}
			
		}
		
	} // EndOf: "__buildResources()" ###
	
	
	function setSource( $path = '' ) {
		
		if ( !file_exists($path) ) 	return false;
		if ( is_file($path) ) 		return false;
		
		$this->source_path = $path;
		return true;
		
	} // EndOf: "setSource()" ###
	
	
	function setManifestPath( $path = '', $removeOldFile = true ) {
		
		// Path is a folder, setup pre-defined manifest name.
		if ( file_exists($path) && !is_file($path) ) {
			if ( substr(strrev($path),0,1) != '/' ) $path.= '/';
			$path.= 'cache.manifest';
		}
		
		// Remove existing manifest file.
		if ( file_exists($path) && $removeOldFile ) unlink($path);
		
		// Try to touch the destination file.
		@touch($path);
		if ( !file_exists($path) ) return false;
		
		$this->manifest_path = $path;
		return true;
		
	} // EndOf: "setManifestPath()" ###
	
	
	function generate() {
		
		if ( !$this->__ready() ) return false;
		
		$this->files = array();
		
		$this->__buildResources( $this->source_path );
		
		// Create the manifest content.
		$cnt = "CACHE MANIFEST\r\n\r\n";
		$cnt.= "CACHE:\r\n";
		foreach ( $this->files as $file )  $cnt.= $file . "\r\n";
		
		// Save the manifest to the output file.
		file_put_contents( $this->manifest_path, $cnt );
	
	} // EndOf: "generate()" ###

}




/**
 * Functional script.
 * Does the job for this folder!
 */
$gen = new ManifestGenerator( './', 'cache.manifest' );


?>