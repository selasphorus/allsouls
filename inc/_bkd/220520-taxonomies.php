<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/*** Taxonomies for GENERAL USE ***/

// Custom Taxonomy: Admin Tag
function allsouls_register_taxonomy_admin_tag() {
    //$cap = 'event_program';
    $labels = array(
        'name'              => _x( 'Admin Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Admin Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Admin Tags' ),
        'all_items'         => __( 'All Admin Tags' ),
        'parent_item'       => __( 'Parent Admin Tag' ),
        'parent_item_colon' => __( 'Parent Admin Tag:' ),
        'edit_item'         => __( 'Edit Admin Tag' ),
        'update_item'       => __( 'Update Admin Tag' ),
        'add_new_item'      => __( 'Add New Admin Tag' ),
        'new_item_name'     => __( 'New Admin Tag Name' ),
        'menu_name'         => __( 'Admin Tags NEW' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'admin_tag' ],
    );
    register_taxonomy( 'admin_tag', [ 'admin_note', 'bible_book', 'collect', 'data_table', 'edition', 'ensemble', 'event', 'event-recurring', 'event_series', 'lectionary', 'liturgical_date', 'liturgical_date_calc', 'location', 'music_list', 'page', 'person', 'post', 'product', 'psalms_of_the_day', 'publication', 'publisher', 'reading', 'repertoire', 'sermon', 'sermon_series' ], $args );
}
add_action( 'init', 'allsouls_register_taxonomy_admin_tag' );

/*** Taxonomies for PEOPLE ***/

// Custom Taxonomy: People Category
function allsouls_register_taxonomy_people_category() {
    //$cap = 'person';
    $labels = array(
        'name'              => _x( 'People Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'People Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search People Categories' ),
        'all_items'         => __( 'All People Categories' ),
        'parent_item'       => __( 'Parent People Category' ),
        'parent_item_colon' => __( 'Parent People Category:' ),
        'edit_item'         => __( 'Edit People Category' ),
        'update_item'       => __( 'Update People Category' ),
        'add_new_item'      => __( 'Add New People Category' ),
        'new_item_name'     => __( 'New People Category Name' ),
        'menu_name'         => __( 'People Categories NEW' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'people_category' ],
    );
    register_taxonomy( 'people_category', [ 'person' ], $args );
}
add_action( 'init', 'allsouls_register_taxonomy_people_category' );

/*** Taxonomies for SERMONS ***/


/*** Taxonomies for EVENT PROGRAMS ***/

// Custom Taxonomy: Person Role
function allsouls_register_taxonomy_person_role() {
    $cap = 'event_program';
    $labels = array(
        'name'              => _x( 'Personnel Roles', 'taxonomy general name' ),
        'singular_name'     => _x( 'Personnel Role', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Personnel Roles' ),
        'all_items'         => __( 'All Personnel Roles' ),
        'parent_item'       => __( 'Parent Personnel Role' ),
        'parent_item_colon' => __( 'Parent Personnel Role:' ),
        'edit_item'         => __( 'Edit Personnel Role' ),
        'update_item'       => __( 'Update Personnel Role' ),
        'add_new_item'      => __( 'Add New Personnel Role' ),
        'new_item_name'     => __( 'New Personnel Role Name' ),
        'menu_name'         => __( 'Personnel Roles NEW' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'person_role' ],
    );
    register_taxonomy( 'person_role', [ 'event_program' ], $args );
}
add_action( 'init', 'allsouls_register_taxonomy_person_role' );



?>