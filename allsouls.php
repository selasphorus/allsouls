<?php
/**
 * @package AllSouls
 */

/*
Plugin Name: AllSouls Custom Functionality
Plugin URI: 
Description: 
Version: 0.1
Author: atc
Author URI: 
License: 
Text Domain: allsouls
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$plugin_path = plugin_dir_path( __FILE__ );

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */


/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// Enqueue scripts and styles -- WIP
add_action( 'wp_enqueue_scripts', 'birdhive_scripts_method' );
function birdhive_scripts_method() {
    
    global $current_user;
    $current_user = wp_get_current_user();
    
    //$birdhive_fpath = WP_PLUGIN_DIR . '/allsouls';
    //if (file_exists($birdhive_fpath)) { $ver = filemtime($fpath); } else { $ver = "201209"; }    
    //wp_enqueue_script( 'stc', plugins_url( 'js/stc.js', __FILE__ ), array( 'jquery-ui-dialog' ), $ver  );
    
    // CSS
    //$ver = filemtime( plugin_dir_url( __FILE__ ) . 'birdhive.css');
    $ver = "0.1";
    wp_enqueue_style( 'birdhive-style', plugin_dir_url( __FILE__ ) . 'birdhive.css', NULL, $ver );
    
    // Events Manager (EM) style overrides and additions
    //$ver = filemtime( get_stylesheet_directory() . '/css/stc-events-manager.css');
    //wp_enqueue_style( 'atc-em-style', get_stylesheet_directory_uri() . '/css/stc-events-manager.css', NULL, $ver );

}

// WIP
//add_action('wp_head', 'birdhive_meta_tags');
function birdhive_meta_tags() { 
    
    // Set defaults
    $og_url = "https://www.allsouls-nyc.org/";
    $og_type = "website";
    $og_title = "All Souls NYC";
    #$og_image = "https://www.allsouls-nyc.org/wp-content/uploads/2021/01/allsouls-logo-with-grey-background.png";
    $og_description = "All SoulS NYC: A Unitarian Universalist Congregation";
    
    if ( is_page() || is_single() || is_singular() ) {
        
        $og_type = "article";
        $post_id = get_queried_object_id();
        $og_url = get_the_permalink( $post_id );
        $og_title = get_the_title( $post_id );
        
        // Get the featured image URL, if there is one
        if ( get_the_post_thumbnail_url( $post_id ) ) { $og_image = get_the_post_thumbnail_url( $post_id ); }
        
        // Get and clean up the excerpt for use in the description meta tag
        $excerpt = get_the_excerpt( $post_id );
        $excerpt = str_replace('&nbsp;Read more...','...',$excerpt); // Remove the "read more" tag from auto-excerpts
        $og_description = wp_strip_all_tags( $excerpt, true );
        
    }

    echo '<meta property="og:url" content="'.$og_url.'" />';
    echo '<meta property="og:type" content="'.$og_type.'" />';
    echo '<meta property="og:title" content="'.$og_title.'" />';
    echo '<meta property="og:image" content="'.$og_image.'" />';
    echo '<meta property="og:description" content="'.$og_description.'" />';
    //fb:app_id
    
}

/*** MISC ***/

// External Resources (ACF field group)
add_shortcode('display_external_resources', 'get_external_resources');
function get_external_resources ( $post_id = null ) {
    
    if ($post_id == null) { $post_id = get_the_ID(); }
    $info = "<!-- External Resources for post_id: $post_id -->";
    
    $post = get_post( $post_id );
    $post_type = $post->post_type;
    
    $resources = get_field('urls', $post_id);
    //$resources = get_post_meta( $post_id, 'urls' ); //get_post_meta( $post_id, 'urls', true );
    $info .= "<!-- ACF resources for post_id $post_id: ".print_r($resources,true)." -->";

    if ( !empty($resources) ) {

        $info .= '<hr class="clear" />';
        $resources_header = get_post_meta( $post_id, 'resources_header', true );
        if ( empty($resources_header) ) { $resources_header = "External Resources"; }
		$info .= '<h2 id="resources">'.$resources_header.'</h2>';

        foreach ( $resources as $resource ) {        

			$resource_url = $resource['url'];
			$resource_title = $resource['link_text'];

            $info .= make_link($resource_url, $resource_title, null, "_blank")."<br />"; // make_link( $url, $linktext, $class = null, $target = null)

        }
    } else {
        $info .= "<!-- No External Resources found. -->";
    }
    
    return $info;
    
}


?>