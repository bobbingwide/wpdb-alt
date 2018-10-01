<?php // (C) Copyright Bobbing Wide 2017

/**
 * Reconciles content
 */
function reconcile() {
	$atts = array( "post_type" => "post" );
	oik_require( "includes/bw_posts.inc" );
	$posts = bw_get_posts( $atts );
	echo "Did get posts normal" ;
	echo count( $posts ); 
	wpdb_alt();
	echo "Switched to alt";
	$posts_alt = bw_get_posts( $atts );
	echo "Did get posts alt" ;
	wpdb_alt( false );
	
	compare( $posts, $posts_alt );
}

function compare( $posts, $posts_alt ) {
	echo count( $posts );
	echo count( $posts_alt );
}

reconcile();
