<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/*** PEOPLE ***/

function allsouls_register_post_type_person() {

	$labels = array(
		'name' => __( 'People', 'allsouls' ),
		'singular_name' => __( 'Person', 'allsouls' ),
		'add_new' => __( 'New Person', 'allsouls' ),
		'add_new_item' => __( 'Add New Person', 'allsouls' ),
		'edit_item' => __( 'Edit Person', 'allsouls' ),
		'new_item' => __( 'New Person', 'allsouls' ),
		'view_item' => __( 'View People', 'allsouls' ),
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
		'view_item' => __( 'View Sermons', 'allsouls' ),
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
	 	'menu_icon'          => 'dashicons-groups',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
		'taxonomies' => array( 'admin_tag' ), //'people_category', 'people_tag', 
		'show_in_rest' => true,    
	);

	register_post_type( 'sermon', $args );
	
}
add_action( 'init', 'allsouls_register_post_type_sermon' );



?>