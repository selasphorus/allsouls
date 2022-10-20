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

// Include sub-files
// TODO: make them required? Otherwise dependencies may be an issue.
// TODO: maybe: convert to classes/methods approach??

$includes = array( 'posttypes', 'taxonomies', 'events', 'people', 'sermons' );

foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'inc/'.$inc.'.php'; 
    if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "no $filepath found"; }
}

$common_functions_filepath = $plugin_path . 'common_functions.php';
$admin_functions_filepath = $plugin_path . 'admin_functions.php';
if ( file_exists($admin_functions_filepath) ) { include_once( $admin_functions_filepath ); } else { echo "no $admin_functions_filepath found"; }
if ( file_exists($common_functions_filepath) ) { include_once( $common_functions_filepath ); } else { echo "no $common_functions_filepath found"; }

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

function birdhive_log($log_msg) {
    
	// Create directory for storage of log files, if it doesn't exist already
	$log_filename = $_SERVER['DOCUMENT_ROOT']."/_allsouls-devlog";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
	
	$timestamp = current_time('mysql'); // use WordPress function instead of straight PHP so that timezone is correct -- see https://codex.wordpress.org/Function_Reference/current_time
	$datestamp = current_time('Ymd'); // date('d-M-Y')
	
	//birdhive_log("loop_item_divider");
	if ($log_msg == "divline1") {
		$log_msg = "\n=================================================================================\n";
	} else if ($log_msg == "divline2") {
		$log_msg = "-----------";
	} else {
		$log_msg = "[birdhive_log ".$timestamp."] ".$log_msg;
		//$log_msg = "[birdhive_log ".$timestamp."] ".$log_msg."\n";
	}
	
	// Generate a new log file name based on the date
	// (If filename does not exist, the file is created. Otherwise, the existing file is overwritten, unless the FILE_APPEND flag is set.)
    $log_file = $log_filename.'/' . $datestamp . '-birdhive_dev.log';
	// Syntax: file_put_contents(filename, data, mode, context)
    file_put_contents($log_file, $log_msg . "\n", FILE_APPEND);
}

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


// Add post_type query var to edit_post_link so as to be able to selectively load plugins via plugins-corral MU plugin
add_filter( 'get_edit_post_link', 'add_post_type_query_var', 10, 3 );
function add_post_type_query_var( $url, $post_id, $context ) {

    $post_type = get_post_type( $post_id );
    
    // TODO: consider whether to add query_arg only for certain CPTS?
    if ( $post_type && !empty($post_type) ) { $url = add_query_arg( 'post_type', $post_type, $url ); }
    
    return $url;
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


// Enable shortcodes in sidebar widgets
add_filter( 'widget_text', 'shortcode_unautop' );
add_filter( 'widget_text', 'do_shortcode' );


// ACF
//add_filter('acf/settings/row_index_offset', '__return_zero');
// TODO: update other calls to ACF functions in case this screws them up?


/*** Add Custom Post Status: Archived ***/

add_action( 'init', 'birdhive_custom_post_status_creation' );
function birdhive_custom_post_status_creation(){
	register_post_status( 'archived', array(
		'label'                     => _x( 'Archived', 'post' ), 
		'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>'),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'post_type'                 => array( 'post', 'nf_sub' ),
	));
}

add_filter( 'display_post_states', 'birdhive_display_status_label' );
function birdhive_display_status_label( $statuses ) {
	global $post; // we need it to check current post status
	if( get_query_var( 'post_status' ) != 'archived' ){ // not for pages with all posts of this status
		if ( $post && $post->post_status == 'archived' ){ // если статус поста - Архив
			return array('Archived'); // returning our status label
		}
	}
	return $statuses; // returning the array with default statuses
}

// TODO: move script to JS file and enqueue it properly(?)
add_action('admin_footer-edit.php','birdhive_status_into_inline_edit');
function birdhive_status_into_inline_edit() { // ultra-simple example
	echo "<script>
	jQuery(document).ready( function() {
		jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"archived\">Archived</option>' );
	});
	</script>";
}

add_action( 'post_submitbox_misc_actions', 'birdhive_post_submitbox_misc_actions' );
function birdhive_post_submitbox_misc_actions(){

    global $post;

    //only when editing a post
    if ( $post->post_type == 'post' || $post->post_type == 'event' ){

        // custom post status: approved
        $complete = '';
        $label = '';   

        if( $post->post_status == 'archived' ){
            $complete = 'selected=\"selected\"';
            $label = '<span id=\"post-status-display\"> Archived</span>';
        }

        echo '<script>'.
                 'jQuery(document).ready(function($){'.
                     '$("select#post_status").append('.
                         '"<option value=\"archived\" '.$complete.'>'.
                             'Archived'.
                         '</option>"'.
                     ');'.
                     '$(".misc-pub-section label").append("'.$label.'");'.
                 '});'.
             '</script>';
    }
}



/*** MISC ***/

add_shortcode('top','anchor_link_top');
function anchor_link_top() {
    return '<a href="#top">top</a>';
}

// Function to determine default taxonomy for a given post_type, for use with display_posts shortcode, &c.
function atc_get_default_taxonomy ( $post_type = null ) {
    switch ($post_type) {
        case "post":
            return "category";
        case "page":
            return "page_tag"; // ??
        case "event":
            return "event-categories";
        case "product":
            return "product_cat";
        case "repertoire":
            return "repertoire_category";
        case "person":
            return "people_category";
        case "sermon":
            return "sermon_topic";
        default:
            return "category"; // default -- applies to type 'post'
    }
}

// Function to determine default category for given page, for purposes of Recent Posts &c.
function atc_get_default_category () {
	
	$default_cat = "";
	
    if ( is_category() ) {
        $category = get_queried_object();
        $default_cat = $category->name;
    } else if ( is_single() ) {
        $categories = get_the_category();
        $post_id = get_the_ID();
        $parent_id = wp_get_post_parent_id( $post_id );
        //$parent = $post->post_parent;
    }
	
	if ( ! empty( $categories ) ) {
		//echo esc_html( $categories[0]->name );		 
	} else if ( empty($default_cat) ) {
        
		// TODO: make this more efficient by simply checking to see if name of Page is same as name of any Category
		//echo "No categories.<br />";
		if ( is_page('Families') ) {
			$default_cat = "Families";
		} else if (is_page('Giving')) {
			$default_cat = "Giving";
		} else if (is_page('Music')) {
			$default_cat = "Music";
		} else if (is_page('Outreach')) {
			$default_cat = "Outreach";
		} else if (is_page('Parish Life')) {
			$default_cat = "Parish Life";
		} else if (is_page('Rector')) {
			$default_cat = "Rector";
		} else if (is_page('Theology')) {
			$default_cat = "Theology";
		} else if (is_page('Worship')) {
			$default_cat = "Worship";
		} else if (is_page('Youth')) {
			$default_cat = "Youth";
		} else {
			$default_cat = "Latest News";
		}
	}
	//$info .= "default_cat: $default_cat<br />";
	//echo "default_cat: $default_cat<br />";
	
	return $default_cat;
}

function digit_to_word($number){
    switch($number){
        case 0:$word = "zero";break;
        case 1:$word = "one";break;
        case 2:$word = "two";break;
        case 3:$word = "three";break;
        case 4:$word = "four";break;
        case 5:$word = "five";break;
        case 6:$word = "six";break;
        case 7:$word = "seven";break;
        case 8:$word = "eight";break;
        case 9:$word = "nine";break;
    }
    return $word;
}


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

// Get a linked list of Terms
add_shortcode('list_terms', 'atc_list_terms');
function atc_list_terms ($atts = [], $content = null, $tag = '') {

	$info = "";
	
	$a = shortcode_atts( array(
      	'child_of'		=> 0,
		'cat'			=> 0,
		//'depth'			=> 0,
		'exclude'       => array(),
      	'hierarchical'	=> true,
		'include'       => array(),
		//'meta_key'	=> 'key_name',
     	'orderby'		=> 'name', // 'id', 'meta_value'
      	'show_count'	=> 0,
		'tax'			=> 'category',
		'title'        	=> '',
    ), $atts );
	
	$all_items_url = ""; // tft
	$all_items_link = ""; // tft
	$exclusions_per_taxonomy = array(); // init
	
	if ( $a['tax'] == "category" ) {
		$exclusions_per_taxonomy = array(1389, 1674, 1731);
		$all_items_url = "/news/";
	} else if ( $a['tax'] == "event-categories" ) {
		$exclusions_per_taxonomy = array(1675, 1690);
		$all_items_url = "/events/";
	}
	// Turn exclusion/inclusion attribute from comma-sep list into array as prep for merge/ for use w/ atc_get_terms_orderby
	if ( !empty($a['exclude']) ) { $a['exclude'] = array_map('intval', explode(',', $a['exclude']) ); } //$integerIDs = array_map('intval', explode(',', $string));
	if ( !empty($a['include']) ) { $a['include'] = array_map('intval', explode(',', $a['include']) ); }
	$exclusions = array_merge($a['exclude'], $exclusions_per_taxonomy);
	$inclusions = $a['include'];
	$term_names_to_skip = array('Featured Posts', 'Featured Posts (2)', 'Featured Events', 'Featured Events (2)');
	
	// List terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)
    $args = array(
        'child_of' => $a['child_of'],
		//'depth' => $a['depth'],
		'exclude' => $exclusions,
		'include' => $inclusions,
        //'current_category'    => $a['cat'],
        'taxonomy'     => $a['tax'],
        'orderby'      => $a['orderby'],
        //'show_count'   => $a['show_count'],
        //'hierarchical' => $a['hierarchical'],
        //'title_li'     => $a['title']
    );
	$info .= "<!-- ".print_r($args, true)." -->"; // tft
	
	$terms = get_terms($args);
	
	/*
	'meta_query' => array(
        [
            'key' => 'meta_key_slug_1',
            'value' => 'desired value to look for'
        ]
    ),
    'meta_key' => 'meta_key_slug_2',
    'orderby' => 'meta_key_slug_2'
	*/
	
    if ($all_items_url) { 
        $all_items_link = '<a href="'.$all_items_url.'"';
        if ( $a['tax'] === "event-categories" ) {
            $all_items_link .= ' title="All Events">All Events';
        } else {
            $all_items_link .= ' title="All Articles">All Articles';
        }
        $all_items_link .= '</a>';
    }
	
	
	if ( !empty( $terms ) && !is_wp_error( $terms ) ){
		$info .= "<ul>";
		$info .= '<li>'.$all_items_link.'</li>';
		foreach ( $terms as $term ) {
			if ( !in_array($term->name, $term_names_to_skip) ) {
			//if ($term->name != 'Featured Events' AND $term->name != 'Featured Events (2)') {
				if ( $a['tax'] === "event-categories" ) {
                    $term_link = "/events/?category=".$term->slug;
                } else {
                    $term_link = get_term_link( $term );
                }
                $term_name = $term->name;
				//if ($term_name === "Worship Services") { $term_name = "All Worship Services"; }
				$info .= '<li>';
				$info .= '<a href="'.$term_link.'" rel="bookmark">'.$term_name.'</a>';
				$info .= '</li>';
			}		
		}
		$info .= "</ul>";
	} else {
		$info .= "No terms.";
	}
	return $info;
}

// Function to facilitate custom order when calling get_terms
/**
 * Modifies the get_terms_orderby argument if orderby == include
 *
 * @param  string $orderby Default orderby SQL string.
 * @param  array  $args    get_terms( $taxonomy, $args ) arg.
 * @return string $orderby Modified orderby SQL string.
 */
add_filter( 'get_terms_orderby', 'atc_get_terms_orderby', 10, 2 );
function atc_get_terms_orderby( $orderby, $args ) {
  	//if ( isset( $args['orderby'] ) && 'include' == $args['orderby'] ) {
	if ( isset( $args['orderby'] ) ) {
		if ($args['orderby'] === 'include') {
          $ids = implode(',', array_map( 'absint', $args['include'] ));
          $orderby = "FIELD( t.term_id, $ids )";
		} /*else if ($args['orderby'] === 'post_types') {
          $ids = implode(',', array_map( 'absint', $args['post_types'] ));
          $orderby = "FIELD( t.term_id, $ids )";
		}*/
	} 
	return $orderby;
}

function birdhive_add_post_term( $post_id = null, $arr_term_slugs = array(), $taxonomy = "", $return_info = false ) {
//function birdhive_add_post_terms( $post_id = null, $arr_term_slugs = array(), $taxonomy = "", $return_info = false ) {
    
    $term_ids = array();
    $info = "";
    $result = null;
    
    // If a string was passed instead of an array, then explode it.
    if ( !is_array($arr_term_slugs) ) { $arr_term_slugs = explode(',', $arr_term_slugs); }
    
    // Add 'programmatically-updated' to all posts updated via this function
    if ( is_dev_site() == true ) { $term_ids[] = 1963; } else { $term_ids[] = 2204; }
    //$arr_term_slugs[] = 'programmatically-updated';
    
    // NB: Hierarchical taxonomies must always pass IDs rather than names -- so, get the IDs
    foreach ( $arr_term_slugs as $term_slug ) {
        
        if ( $term_slug == 'cleanup-required' ) {
            if ( is_dev_site() ) { $term_ids[] = 1668; } else { $term_ids[] = 1668; }
        } else if ( $term_slug == 'program-personnel-placeholders' ) {
            if ( is_dev_site() ) { $term_ids[] = 2177; } else { $term_ids[] = 2548; }
        } else if ( $term_slug == 'program-item-placeholders' ) {
            if ( is_dev_site() ) { $term_ids[] = 2178; } else { $term_ids[] = 2549; }
        } else if ( $term_slug == 'program-placeholders' ) {
            if ( is_dev_site() ) { $term_ids[] = 2176; } else { $term_ids[] = 2547; }
        } else if ( $term_slug == 'slug-updated' ) {
            if ( is_dev_site() ) { $term_ids[] = 1960; } else { $term_ids[] = 2203; }
        } else if ( $term_slug == 't4m-updated' ) {
            if ( is_dev_site() ) { $term_ids[] = 2174; } else { $term_ids[] = 2532; }
        } else if ( $term_slug == 'field-conversion-ok' ) {
            if ( is_dev_site() ) { $term_ids[] = 3091; } else { $term_ids[] = null; }
        }/*else if ( $term_slug == 'programmatically-updated' ) {
            if ( is_dev_site() ) { $term_ids[] = 1963; } else { $term_ids[] = 2204; }
        }*/
        
        if ( has_term( $term_slug, $taxonomy ) ) {
            return "<!-- [birdhive_add_post_term] post $post_id already has $taxonomy: $term_slug. No changes made. -->";
        } else {
            $result = wp_set_post_terms( $post_id, $term_ids, $taxonomy, true ); // wp_set_post_terms( int $post_id, string|array $tags = '', string $taxonomy = 'post_tag', bool $append = false )
        }
        
    }
    
    if ( $return_info == true ) {
        
        $info .= "<!-- [birdhive_add_post_term] -- ";
        //$info .= "<!-- wp_set_post_terms -- ";
        $info .= "$taxonomy: $term_slug";
        //$info .= implode(", ",$arr_term_slugs)." -- ";
        if ( $result ) { 
			$info .= " success!"; 
		} else { 
			$info .= " FAILED!";
			$info .= print_r($term_ids, true);
		}
        $info .= " -->";
        return $info;
        
    } else {
        
        return $result;
    }
    
}

function birdhive_remove_post_term( $post_id = null, $term_slug = null, $taxonomy = "", $return_info = false ) {
    
    $term_ids = array();
    $info = "";
    
    // TODO -- Cleanup: remove t4m-updated from all events -- it doesn't apply because events don't have a title_for_matching field -- they have title_uid instead
    
    $result = wp_remove_object_terms( $post_id, $term_slug, $taxonomy ); // wp_remove_object_terms( int $object_id, string|int|array $terms, array|string $taxonomy )
    
    if ( $return_info == true ) {
        $info .= "<!-- wp_remove_object_terms -- ";
        $info .= $term_slug;
        if ( $result ) { $info .= "success!"; } else { $info .= "FAILED!"; }
        $info .= " -->";
        return $info;
    } else {
        return $result;
    }
}


/*** Custom Post Types Content ***/

// Umbrella function to get CPT content
// TODO: phase this out? It makes fine-tuning content ordering a bit tricky...
function atc_custom_post_content() {
	
	$info = "";
	$post_type = get_post_type( get_the_ID() );
	
	if ($post_type === "ensemble") {
		$info .= get_cpt_ensemble_content();
	} else if ($post_type === "liturgical_date") {
		$info .= get_cpt_liturgical_date_content();
	} else if ($post_type === "person") {
		$info .= get_cpt_person_content();
	} else if ($post_type === "repertoire") {
		$info .= get_cpt_repertoire_content();
	} else if ($post_type === "edition") {
		$info .= get_cpt_edition_content();
	} else if ($post_type === "reading") {
		$info .= get_cpt_reading_content();
	} else if ($post_type === "sermon") {
		//$info .= get_cpt_sermon_content(); // Disabled because the function doesn't currently add any actual custom content.
	} else {
		//$info .= "<p>[post] content (default)-- coming soon</p>";
		//return false;
		//return;
	}
	
	return $info;
}

// Modify the display order of CPT archives
add_action('pre_get_posts', 'atc_pre_get_posts'); //mind_pre_get_posts
//add_filter( 'posts_orderby' , 'custom_cpt_order' );
function atc_pre_get_posts( $query ) {
  
    if ( is_admin() ) {
        return $query; 
    }
	
	if ( is_archive() && $query->is_main_query() ) {
		
		// In paginated posts loop, show ONLY "Website Archives" category posts 
		// (current/recent posts will be shown separately at the top of the page -- see archive.php)
		//if ( ! is_category( 'website-archives' ) && !is_post_type_archive('repertoire') ) {
		/*if ( is_category() ) { // is_post_type_archive('post')
			if ( is_dev_site() ) {
				$archives_cat_id = '2183'; // dev
			} else {
				$archives_cat_id = '2971'; // live
			}
			//$query->set( 'cat', $archives_cat_id ); // Problem with this approach is that it sets the category for the page as a whole. Need a more fine-tuned approach. 
            // For now, alternate approach is to exclude non-archives posts from display in main loop via archive.php
		}*/
		
		// Custom CPT ORDER
        if ( isset($query->query_vars['post_type']) ) {
            $post_type = $query->query_vars['post_type'];
            if ($post_type === 'bible_book') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sort_num');
                $query->set('order', 'ASC');
            } else if ($post_type === 'sermon') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sermon_date');
                $query->set('order', 'DESC');
            } else if ($post_type === 'person') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'last_name');
                $query->set('order', 'ASC');
            } /*else if ($post_type === 'liturgical_date') { // atcwip
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'date_time');
                $query->set('order', 'DESC');
            }*/
        }
        
	}
                                                               
  	return $query;
}


/*** SEARCH ***/

// WIP

// This function is built in to PHP starting in v. 8.0
if ( !function_exists('str_starts_with') ) {
    
    function str_starts_with ( $haystack, $needle ) {
        return strpos( $haystack , $needle ) === 0;
    }
    
}

// Get name of post_type
function get_post_type_str( $type = "" ) {
	if ($type === "") { $type = get_post_type(); }
	if ($type === 'post') {
		return "Article";
	} else if ($type === 'event') {
		return "Event";
	} else if ($type === 'event_series') {
		return "Event Series";
	} else if ($type === 'repertoire') {
		return "Musical Work";
	} else if ($type === 'liturgical_date') {
		return "Liturgical Date";
	} else {
		return ucfirst($type);
	}
}

/*function atc_add_query_vars_filter( $vars ) {
  $vars[] = "type";
  return $vars;
}
add_filter( 'query_vars', 'atc_add_query_vars_filter' );*/


// Filter search results
// Add shortcode for display of search filter links
//add_shortcode('display_search_filters', 'atc_search_filter_links');
// Get a linked list of Terms
function atc_search_filter_links ($atts = [], $content = null, $tag = '') {

	//global $wp;
	//esc_url( remove_query_arg( 'type' ) ); // this doesn't seem to work
	//$current_url = home_url( add_query_arg( array(), $wp->request ) );
  	$current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
	//$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) ); // nope
	$current_url = remove_query_arg( 'type', $current_url );
	//$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	$info = "";
	
	$a = shortcode_atts( array(
		'exclude'       => '',
		'include'       => '',
     	'orderby'		=> 'name', // 'id', 'meta_value'
      	'show_count'	=> 1,
		'title'        	=> '',
    ), $atts );
	
	$terms = array();
	$terms[] = array('name'=>'People', 'post_type' => 'person');
	$terms[] = array('name'=>'Pages', 'post_type' => 'page');
	$terms[] = array('name'=>'Articles', 'post_type' => 'post');
	$terms[] = array('name'=>'Musical Works', 'post_type' => 'repertoire');
	$terms[] = array('name'=>'Events', 'post_type' => 'event');
	// TODO: add "Other" option to exclude all explicitly filterable post types
	
	if ( !empty( $terms ) && !is_wp_error( $terms ) ){
		$info .= "<ul>";
		$info .= '<li>'.$all_items_link.'</li>';
		foreach ( $terms as $term ) {
			//if ( !in_array($term->name, $term_names_to_skip) ) {
				//$term_link = get_term_link( $term );
				$term_link = $current_url."&type=".$term['post_type'];
				//$term_name = $term->name;
				$term_name = $term['name'];
				//if ($term_name === "Worship Services") { $term_name = "All Worship Services"; }
				$info .= '<li>';
				$info .= '<a href="'.$term_link.'" rel="bookmark">'.$term_name.'</a>';
				$info .= '</li>';
			//}		
		}
		$info .= "</ul>";
	} else {
		$info .= "No terms.";
	}
	return $info;
}


/************** CUSTOM POST TYPES CONTENT ***************/


/*** MISC UTILITY/HELPER FUNCTIONS ***/

// Convert the time string HH:MM:SS to number of seconds (for flowplayer cuepoints &c.)
function xtime_to_seconds($str_time){
	
	/*
	// Method #1
	//$str_time = "23:12:95";
	$str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);
	sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
	$num_seconds = $hours * 3600 + $minutes * 60 + $seconds;
	
	// Method #2
	//$str_time = "2:50";
	sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
	$num_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
	
	// Method #3
	//$str_time = '21:30:10';
	$num_seconds = strtotime("1970-01-01 $str_time UTC");
	*/
	// Method #4
	//$str_time = '21:30:10';
	$parsed = date_parse($str_time);
	$num_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
	
	return $num_seconds;
	
}

//
function make_link( $url, $linktext, $class = null, $target = null) {
	
	// TODO: sanitize URL?
	$link = '<a href="'.$url.'"';
	if ($target !== null ) { $link .= ' target="'.$target.'"'; }
    if ($class !== null ) { $link .= ' class="'.$class.'"'; }
	$link .= '>'.$linktext.'</a>';
	//return '<a href="'.$url.'">'.$linktext.'</a>';
	
	return $link;
}


/*** Archive Pages ***/

function birdhive_theme_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    }
  
    return $title;
}
 
add_filter( 'get_the_archive_title', 'atc_theme_archive_title' );


// Remove menus selectively for plugins which rely on manage_options capability
//add_action( 'admin_menu', 'remove_admin_menu_items', 999 );
function remove_admin_menu_items() {

    $user = wp_get_current_user();
    if ( !in_array( 'administrator', (array) $user->roles ) ) {
        remove_menu_page('members'); // Tadlock Members plugin
        remove_menu_page('mfmmf'); // ManFisher footnotes plugin
        remove_menu_page('google-captcha-pro.php');
        remove_menu_page('wppusher');
        
        if ( !in_array( 'birdhive_administrator', (array) $user->roles ) ) {
            remove_menu_page('tools.php'); // Tools
            remove_menu_page('options-general.php'); // Settings
            remove_menu_page('pmxi-admin-home'); // WP All Import
            remove_menu_page('wpdesk-helper');
            remove_menu_page('metaslider'); // no dice -- ??
            remove_submenu_page('index.php','metaslider-settings' ); // no dice -- ??
            //remove_submenu_page('admin.php','metaslider' ); //admin.php?page=metaslider -- no dice -- why?
            //remove_menu_page('admin.php?page=metaslider'); // :-(
            remove_menu_page('WP-Optimize');
            remove_menu_page('seed_csp4'); // admin.php?page=seed_csp4
        }
    }
    
 }

// Function to delete capabilities
// Based on http://chrisburbridge.com/delete-unwanted-wordpress-custom-capabilities/
//add_action( 'admin_init', 'clean_unwanted_caps' ); // tmp disabled -- this function need only ever run once per site per set of caps
function clean_unwanted_caps() {
	global $wp_roles;
	$delete_caps = array(
        
        //'edit_music', 'edit_others_music', 'delete_music', 'publish_music', 'read_music', 'read_private_music',
        //'edit_musics', 'edit_others_musics', 'delete_musics', 'publish_musics', 'read_musics', 'read_private_musics',
        
        //'edit_saurs', 'edit_others_saurs', 'delete_saurs', 'publish_saurs', 'read_private_saurs',
        //'edit_dinosaurs', 'edit_others_dinosaurs', 'delete_dinosaurs', 'publish_dinosaurs', 'read_private_dinosaurs',
        
        //'read_dev_wip', 'read_private_dev_wips', 'edit_dev_wip', 'edit_dev_wips', 'edit_others_dev_wips', 'edit_private_dev_wips', 'edit_published_dev_wips', 'delete_dev_wip', 'delete_dev_wips', 'delete_others_dev_wips', 'delete_private_dev_wips', 'delete_published_dev_wips', 'publish_dev_wips',
        
        //'assign_allsoulsdev_term', 'assign_allsoulsdev_terms', 'edit_allsoulsdev_term', 'edit_allsoulsdev_terms', 'delete_allsoulsdev_term', 'delete_allsoulsdev_terms', 'manage_allsoulsdev_terms',
        
        //'read_wipdev', 'edit_wipdev', 'read_private_wipdevs', 'edit_wipdevs', 'edit_others_wipdevs', 'edit_private_wipdevs', 'edit_published_wipdevs', 'publish_wipdevs', 'delete_wipdev', 'delete_wipdevs', 'delete_others_wipdevs', 'delete_private_wipdevs', 'delete_published_wipdevs',  
        
        //'smartslider', 'smartslider_config', 'smartslider_delete', 'smartslider_edit',
		
        //'nextend', 'nextend_config', 'nextend_visual_delete', 'nextend_visual_edit'
        
	 );
	foreach ($delete_caps as $cap) {
		foreach (array_keys($wp_roles->roles) as $role) {
			$wp_roles->remove_cap($role, $cap);
		}
	}
}


?>