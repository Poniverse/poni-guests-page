<?php

/*
Plugin Name: Guests Page
Plugin URI: https://developer.poniverse.net
Description: Facilitates the easy management of an event guests page.
Version: 1.0
Author: Poniverse
Author URI: https://poniverse.net
License: MIT
*/


register_activation_hook( __FILE__, 'poni_guests_rewrite_flush' );
add_action( 'init', 'poni_guests_create_post_type' );
add_action( 'add_meta_boxes', 'poni_guests_create_metaboxes' );
add_action( 'save_post', 'poni_guests_save' );


function poni_guests_create_post_type() {
	add_image_size( 'poni_guest_thumbnail', 200, 200, true );

	register_taxonomy( 'guest_type', array( 'poni_guest' ), array(
		'labels'            => array(
			'name'              => _x( 'Guest Types', 'taxonomy general name' ),
			'singular_name'     => _x( 'Guest Type', 'taxonomy singular name' ),
			'all_items'         => __( 'All Guest Types' ),
			'edit_item'         => __( 'Edit Guest Type' ),
			'view_item'         => __( 'View Guest Type' ),
			'update_item'       => __( 'Update Guest Type' ),
			'add_new_item'      => __( 'Add New Guest Type' ),
			'new_item_name'     => __( 'New Guest Type Name' ),
			'parent_item'       => __( 'Parent Type' ),
			'parent_item_colon' => __( 'Parent Type:' ),
			'search_items'      => __( 'Search Guest Types' ),
		),
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'hierarchical'      => true,
	) );

	register_post_type( 'poni_guest',
		array(
			'labels'              => array(
				'name'               => _x( 'Guests', 'Post Type General Name' ),
				'singular_name'      => __( 'Guest', 'Post Type Singular Name' ),
				'add_new_item'       => __( 'Add New Guest' ),
				'edit_item'          => __( 'Edit Guest' ),
				'new_item'           => __( 'New Guest' ),
				'view_item'          => __( 'View Guest' ),
				'search_items'       => __( 'Search Guests' ),
				'not_found'          => __( 'No guests found' ),
				'not_found_in_trash' => __( 'No guests found in Trash' ),
			),
			'public'              => true,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-awards',
			'capability_type'     => 'page',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'page-attributes',
			),
			'taxonomies'          => array( 'guest_type' ),
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'guests',
				'with_front' => false,
				'pages'      => false,
			),
			'query_var'           => false,
		)
	);
}


function poni_guests_rewrite_flush() {
	poni_guests_create_post_type();
	flush_rewrite_rules();
}

function poni_guests_create_metaboxes() {
	add_meta_box(
		'poni_guests_contact_metabox',
		__( 'Contact Info' ),
		'poni_guests_contact_metabox_content',
		'poni_guest',
		'side',
		'high'
	);
}

function poni_guests_contact_metabox_content( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'poni_guests_contact_metabox_content_nonce' );

	$title      = get_post_meta( $post->ID, 'title', true );
	$twitter    = get_post_meta( $post->ID, 'twitter', true );
	$site       = get_post_meta( $post->ID, 'website', true );

	echo <<<EOF
	<p>If filled out, this will be shown on this guest&#39;s profile in the listing.</p>
	<p>
		<label for="title">Title</label>
		<input type="text" id="title" name="title" placeholder="That epic narrator" value="{$title}" />
	</p>
	<p>
		<label for="twitter">Twitter</label>
		<input type="text" id="twitter" name="twitter" placeholder="username (no @)" value="{$twitter}" />
	</p>
	<p>
		<label for="twitter">Website</label>
		<input type="text" id="website" name="website" placeholder="http://twilight.poni" value="{$site}" />
	</p>
EOF;
}


function poni_guests_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! wp_verify_nonce( $_POST['poni_guests_contact_metabox_content_nonce'], plugin_basename( __FILE__ ) ) )
		return;

	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
	}

	update_post_meta( $post_id, 'title', $_POST['title'] );
	update_post_meta( $post_id, 'twitter', $_POST['twitter'] );
	update_post_meta( $post_id, 'website', $_POST['website'] );
}