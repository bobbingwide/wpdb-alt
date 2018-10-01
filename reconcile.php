<?php // (C) Copyright Bobbing Wide 2017

/**
 * Syntax: oikwp reconcile.php 
 * Reconciles content
 * 
 * Note: This logic depends on wpdb-alt being activated.
 * @TODO This seems unnecessary, we should be able to specify the configuration for the alternative database without having to activate a plugin.
 * But the compare logic is what we're focussing on.
 */
function reconcile( $post_type="post" ) {
	echo PHP_EOL;
	echo "Processing: $post_type";
	echo PHP_EOL;
	
	
	/**
	 * @TODO Should we care about the post's status? 
	 * @TODO For which post types do we need numberposts = -1
	 * And which need post_parent = 'ignore' ? 
	 * What ordering is required? by title, name or otherwise? 
	 *
	 */
	$atts = array( "post_type" => $post_type );
	oik_require( "includes/bw_posts.inc" );
	$posts = bw_get_posts( $atts );
	//echo "Did get posts normal" ;
	//echo count( $posts ); 
	wpdb_alt();
	//echo "Switched to alt";
	$posts_alt = bw_get_posts( $atts );
	//echo "Did get posts alt" ;
	wpdb_alt( false );
	
	oik_require( "class-reconciler.php", "wpdb-alt" );
	$reconciler = new Reconciler( $posts, $posts_alt );
	$reconciler->compare( $posts, $posts_alt );
}


reconcile( "post" );
reconcile( "page" );
reconcile( "oik-plugins" );
reconcile( "oik_pluginversion" );
reconcile( "oik_premiumversion" );
reconcile( "oik-themes" );
reconcile( "oik_themeversion" );
reconcile( "oik_themiumversion" );
reconcile( "oik_shortcodes" );
reconcile( "oik_sc_param" );
reconcile( "shortcode_example" );
reconcile( "attachment" );


