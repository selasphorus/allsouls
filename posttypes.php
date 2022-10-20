<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/*** GENERAL/ADMIN ***/

// Admin Note
function birdhive_register_post_type_admin_note() {

	$labels = array(
		'name' => __( 'Admin Notes', 'birdhive' ),
		'singular_name' => __( 'Admin Note', 'birdhive' ),
		'add_new' => __( 'New Admin Note', 'birdhive' ),
		'add_new_item' => __( 'Add New Admin Note', 'birdhive' ),
		'edit_item' => __( 'Edit Admin Note', 'birdhive' ),
		'new_item' => __( 'New Admin Note', 'birdhive' ),
		'view_item' => __( 'View Admin Notes', 'birdhive' ),
		'search_items' => __( 'Search Admin Notes', 'birdhive' ),
		'not_found' =>  __( 'No Admin Notes Found', 'birdhive' ),
		'not_found_in_trash' => __( 'No Admin Notes found in Trash', 'birdhive' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'admin_note' ),
        'capability_type' => array('admin_note', 'admin_notes'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-info-outline',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'adminnote_category', 'admin_tag' ),
		'show_in_rest' => true,    
	);

	register_post_type( 'admin_note', $args );
	
}
add_action( 'init', 'birdhive_register_post_type_admin_note' );


/*** PEOPLE ***/

function allsouls_register_post_type_person() {

	$labels = array(
		'name' => __( 'People', 'allsouls' ),
		'singular_name' => __( 'Person', 'allsouls' ),
		'add_new' => __( 'New Person', 'allsouls' ),
		'add_new_item' => __( 'Add New Person', 'allsouls' ),
		'edit_item' => __( 'Edit Person', 'allsouls' ),
		'new_item' => __( 'New Person', 'allsouls' ),
		'view_item' => __( 'View Person', 'allsouls' ),
		'search_items' => __( 'Search People', 'allsouls' ),
		'not_found' =>  __( 'No People Found', 'allsouls' ),
		'not_found_in_trash' => __( 'No People found in Trash', 'allsouls' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'person' ),
        'capability_type' => array('person', 'people'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-groups',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
		'taxonomies' => array( 'people_category', 'admin_tag' ),
		'show_in_rest' => true,    
	);

	register_post_type( 'person', $args );
	
}
add_action( 'init', 'allsouls_register_post_type_person' );


/*** SERMONS ***/

function allsouls_register_post_type_sermon() {

	$labels = array(
		'name' => __( 'Sermons', 'allsouls' ),
		'singular_name' => __( 'Sermon', 'allsouls' ),
		'add_new' => __( 'New Sermon', 'allsouls' ),
		'add_new_item' => __( 'Add New Sermon', 'allsouls' ),
		'edit_item' => __( 'Edit Sermon', 'allsouls' ),
		'new_item' => __( 'New Sermon', 'allsouls' ),
		'view_item' => __( 'View Sermon', 'allsouls' ),
		'search_items' => __( 'Search Sermons', 'allsouls' ),
		'not_found' =>  __( 'No Sermons Found', 'allsouls' ),
		'not_found_in_trash' => __( 'No Sermons found in Trash', 'allsouls' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'sermon' ),
        'capability_type' => array('sermon', 'sermons'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-welcome-write-blog',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
		'taxonomies' => array( 'admin_tag' ), //'people_category', 'people_tag', 
		'show_in_rest' => true,    
	);

	register_post_type( 'sermon', $args );
	
}
add_action( 'init', 'allsouls_register_post_type_sermon' );

/*** SERIES ***/

// Event Series
function birdhive_register_post_type_event_series() {

	$labels = array(
		'name' => __( 'Event Series', 'birdhive' ),
		'singular_name' => __( 'Event Series', 'birdhive' ),
		'add_new' => __( 'New Event Series', 'birdhive' ),
		'add_new_item' => __( 'Add New Event Series', 'birdhive' ),
		'edit_item' => __( 'Edit Event Series', 'birdhive' ),
		'new_item' => __( 'New Event Series', 'birdhive' ),
		'view_item' => __( 'View Event Series', 'birdhive' ),
		'search_items' => __( 'Search Event Series', 'birdhive' ),
		'not_found' =>  __( 'No Event Series Found', 'birdhive' ),
		'not_found_in_trash' => __( 'No Event Series found in Trash', 'birdhive' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=event',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'event_series' ),
        //'capability_type' => array('lectionary', 'lectionary'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	//'menu_icon'          => 'dashicons-book',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag' ),
		'show_in_rest' => true,    
	);

	register_post_type( 'event_series', $args );
	
}
add_action( 'init', 'birdhive_register_post_type_event_series' );

// Sermon Series
function birdhive_register_post_type_sermon_series() {

	$labels = array(
		'name' => __( 'Sermon Series', 'birdhive' ),
		'singular_name' => __( 'Sermon Series', 'birdhive' ),
		'add_new' => __( 'New Sermon Series', 'birdhive' ),
		'add_new_item' => __( 'Add New Sermon Series', 'birdhive' ),
		'edit_item' => __( 'Edit Sermon Series', 'birdhive' ),
		'new_item' => __( 'New Sermon Series', 'birdhive' ),
		'view_item' => __( 'View Sermon Series', 'birdhive' ),
		'search_items' => __( 'Search Sermon Series', 'birdhive' ),
		'not_found' =>  __( 'No Sermon Series Found', 'birdhive' ),
		'not_found_in_trash' => __( 'No Sermon Series found in Trash', 'birdhive' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=sermon',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'sermon_series' ),
        //'capability_type' => array('lectionary', 'lectionary'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	//'menu_icon'          => 'dashicons-book',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
		'taxonomies' => array( 'admin_tag' ),
		'show_in_rest' => true,    
	);

	register_post_type( 'sermon_series', $args );
	
}
//add_action( 'init', 'birdhive_register_post_type_sermon_series' );

?>