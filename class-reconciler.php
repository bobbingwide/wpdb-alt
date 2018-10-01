<?php // (C) Copyright Bobbing Wide 2017
/**
 * Class: Reconciler
 * Reconciles content in the primary site with content in the alternative database.
 * 
 * The theory is that the alternative database may have something that isn't in the live site but should be.
 * 
 * Scenario
 * 
 * - local site is used to create the content
 * - Content gets cloned to the target slave site
 * - Some changes may be made directly on the slave site
 * - The target site is exported
 * - The local site is exported
 * - The target slave site is imported into the local site
 * - The local site is imported into an alternative database
 * - We now want to reconcile the local site, made from the slave, with uncloned changes now saved in the alternative database
 * 
 * 
 */
class Reconciler {

 
	public $posts;
	public $posts_alt; 
	
	/**
	 * From the point of view of the alt database
	 * Each entry is a copy of the post - a reference by ID
	 */
	public $added;    /* In posts but not in posts_alt       */ 
	public $deleted;  /* Not in posts_alt but still in posts */
	public $same;			/* Matching content in both databases  */
	public $changed;	/* Contents differ                     */


	function __construct() {
		$this->reset();
	}
	
	function reset() {
		$this->posts = array();
		$this->posts_alt = array();
		$this->added = array();
		$this->deleted = array();
		$this->same = array();
		$this->changed = array();
	}
	
	function compare( $posts, $posts_alt ) {
		echo "Local: ";
		echo count( $posts ); 
		echo PHP_EOL;
		echo "Alt: ";
		echo count( $posts_alt );
		echo PHP_EOL;
		$this->posts = $posts;
		$this->posts_alt = $posts_alt; 
		
		$this->process_all_posts();
	}
	
	function process_all_posts() {
		foreach ( $this->posts as $post ) {
			$this->match_to_posts_alt( $post );
		}
		
		// Any post in $this->post_all that is not in $this->same or $this->changed has been deleted
		// 
		$this->find_deleted(); 
	}
	
	function match_to_posts_alt( $post ) {
		$matched = false;
		foreach ( $this->posts_alt as $post_alt ) {
			$matched = $this->match( $post, $post_alt );
			if ( $matched ) {
				break;
			}
		}
		if ( !$matched ) {
			$this->added( $post );
		} 
	}
	
	/**
	 * Determines if posts are unchanged
	 */
	function exact_match( $post, $post_alt ) {
		bw_trace2();
		$match = $post->post_name === $post_alt->post_name;
		$match &= $post->post_content === $post_alt->post_content;
		$match &= $post->post_title === $post_alt->post_title;
		$match &= $post->post_parent === $post_alt->post_parent;
		return $match;
	}
	
	/**
	 * Determines if posts appear to have changed
	 * 
   */
	function changed_match( $post, $post_alt ) {
		$match = ( $post->post_content == $post_alt->post_content );
		$match |= ( $post->post_name == $post_alt->post_name );
		$match |= ( $post->guid == $post_alt->guid );
		$match |= ( $post->ID == $post_alt->ID );  
		return $match;
	}
	
	/**
	 * Determines if posts can be matched
	 */
	function match( $post, $post_alt ) {
		$same = $this->exact_match( $post, $post_alt ); 
		if ( $same ) {
			$this->same( $post_alt );
		} else {
			$changed = $this->changed_match( $post, $post_alt );
			if ( $changed ) {
				$this->changed( $post_alt );
			} else {
				// We have to complete the loop before knowing if it's been deleted
			}
		}
		$matched = $same || $changed ;
		return $matched;
	}
	
	function find_deleted() {
		foreach ( $this->posts_alt as $post_alt ) {
			if ( isset( $this->same[ $post_alt->ID ] ) || isset( $this->changed[ $post_alt->ID ] ) ) {
				//
			} else {
				$this->deleted( $post_alt );
			}
		}
	}
	
	
	
	function report() {
		echo count( $this->added );
		echo count( $this->deleted );
		echo count( $this->same );
		echo count( $this->changed );
		echo count( $this->posts );
		echo count( $this->posts_alt );
		
	}
	
	function same( $post ) {
		$this->same[ $post->ID ] = $post->ID;
	}
	
	function changed( $post ) {
		$this->changed[ $post->ID ] = $post->ID;
	}
	
	function added( $post ) {
		$this->added[ $post->ID ] = $post->ID;
	}
	
	function deleted( $post ) {
		$this->deleted[ $post->ID ] = $post->ID;
	}
	


} 
