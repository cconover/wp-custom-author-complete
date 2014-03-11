<?php
/**
 * Plugin Name: Author Customization
 * Plugin URI: https://christiaanconover.com/code/wp-author-customization
 * Description: Author Customization adds additional author management capabilities beyond the native user account structure. Save author data to each post, enable WYSIWYG editing of biographical info, and more.
 * Version: 0.3.0-alpha
 * Author: Christiaan Conover
 * Author URI: https://christiaanconover.com
 * License: GPLv2
 * @package cc-author-customization
 **/

/**
 * Main plugin class
 **/
class cc_author {
	/* Plugin-wide settings and data */
	// Plugin identifier
	const ID = 'cc-author';
	
	// Plugin name
	const NAME = 'Author Customization';
	
	// Plugin version
	const VERSION = '0.3.0-alpha';
	
	// Minimum version of WordPress required for this plugin
	const WPVER = '3.5.2';
	
	// This plugin's database prefix
	protected $prefix = 'cc_author_';
	
	/* Plugin path & file location */
	protected $pluginpath;
	protected $pluginfile;
	
	/* Plugin's class constructor */
	function __construct() {
		// Set plugin variables
		$this->pluginpath = dirname( __FILE__ );
		$this->pluginfile = __FILE__;
		
		/* Plugin hooks and filters */
		// Display name and description
		if ( !is_admin() ) { // Only add filters if not in admin
			add_filter( 'the_author', array( &$this, 'displayname' ) ); // Hook display name function into 'the_author' filter
			add_filter( 'get_the_author_display_name', array( &$this, 'displayname' ) ); // Hook display name function into 'get_the_author_display_name' filter
			add_filter( 'get_the_author_description', array( &$this, 'description' ) ); // Hook description into 'get_the_author_description' filter
		}		
		
		// Load admin class if in admin
		if ( is_admin() ) {
			require_once( $this->pluginpath . '/admin/author-customization-admin.php' );
			$admin = new cc_author_admin;
			
			// Register plugin activation and deactivation hooks
			register_activation_hook( $this->pluginfile, array( &$admin, 'activate' ) );
			register_deactivation_hook( $this->pluginfile, array( &$admin, 'deactivate' ) );
		}
	} // End __construct()
	
	// Display the author name
	public function displayname( $post ) {
		global $post;
		
		$postpage = get_option( 'cc_author_postpage' ); // Retrive plugin's post/page options
	
		if ( isset( $postpage['multiple-authors'] ) ) { // If multiple authors support is enabled in plugin options, run this code
			$name = 'Multiple Authors';
		}
		else { // If multiple authors support is not enabled in plugin options, run this code
			$postmeta = get_post_meta( $post->ID, '_cc_author_meta', true ); // Get the post-specific author metadata, if available
			
			/* If the plugin setting is enabled and there's post-specific metadata stored and a post, page, or attachment is being displayed, show the post-specific display name. Otherwise use the profile display name. */
			if ( $postmeta && !is_author() && isset( $postpage['perpost'] ) ) {
				foreach ( $postmeta as $authormeta ) {
					foreach ( $authormeta as $key => $meta ) {
						$name = $authormeta['display_name']; // Set the name to the display name stored for the post
					}
				}
			}
			else {
				$author = get_userdata( $post->post_author ); // Get the profile data for the post author
				$name = $author->display_name; // Set the display name to the value stored in the author's profile
			}
		}
	
		return $name; // Send the name back to WordPress for displaying on the post
	} // End displayname()
	
	public function description( $post ) {
		global $post;
	
		$postpage = get_option( 'cc_author_postpage' ); // Get plugin options for posts/pages
	
		if ( isset( $postpage['multiple-authors'] ) ) { // If multiple authors support is enabled in plugin options, run this code
			
		}
		else { // If multiple authors support is not enabled in plugin options, run this code
			$author = get_post_meta( $post->ID, '_cc_author_meta', true ); // Get the post-specific author metadata
			
			/* If the plugin setting is enabled and there's post-specific metadata stored and a post, page, or attachment is being displayed, show the post-specific bio. Otherwise use the profile bio. */
			if ( $author && isset( $postpage['perpost'] ) ) {
				foreach ( $author as $authormeta ) {
					foreach ( $authormeta as $key => $meta ) {
						$description = $authormeta['description']; // Set the description to the one saved in the post metadata
					}
				}
			}
			else {
				$author = get_userdata( $post->post_author ); // Get the profile data for the post author
				$description = $author->description; // Set the description to the value stored in the author's profile
			}
		
			/* If 'relnofollow' is set, add rel="nofollow" to links in bio */
			if ( isset( $postpage['relnofollow'] ) ) {
				$description = str_replace( 'href', 'rel="nofollow" href', $description );
			}
			
			$description = apply_filters( 'the_content', $description ); // Enable formatting for bio
		}
		
		return $description; // Send back the description for WordPress to display
	} // End description()
}
/**
 * End cc_author
 **/

/* Create an instance of the plugin in the global space */
global $cc_author;
$cc_author = new cc_author;
?>