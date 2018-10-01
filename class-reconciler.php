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
	public $count_posts_alt;
	
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
		$this->count_posts_alt = count( $posts_alt );
		echo $this->count_posts_alt;
		echo PHP_EOL;
		$this->posts = $posts;
		$this->posts_alt = $posts_alt; 
		
		$this->process_all_posts();
		$this->report();
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
		echo "Process: {$post->ID} {$post->post_name} {$post->post_status} {$post->post_modified_gmt} " . PHP_EOL;
		foreach ( $this->posts_alt as $key => $post_alt ) {
			$matched = $this->match( $post, $post_alt );
			if ( $matched ) {
				break;
			}
		}
		if ( !$matched ) {
			$this->added( $post );
		} else {
			unset( $this->posts_alt[ $key ] );
		} 
	}
	
	/**
	 * Determines if posts are unchanged
	 * 
	 * Since we're expected to work with clones then we don't expect post IDs to match
	 * and therefore don't expect post_parents to match either.
	 * So basically we're looking for the same content. 
	 */
	function exact_match( $post, $post_alt ) {
		//bw_trace2();
		$match = $post->post_name === $post_alt->post_name;
		$match &= $post->post_content === $post_alt->post_content;
		$match &= $post->post_title === $post_alt->post_title;
		// $match &= $post->post_parent === $post_alt->post_parent;
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
	 * Determines if posts can be matched.
	 * 
	 * 
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
	
	/**
	 * Provides a summary report of the differences
	 */
	function report() {
		echo "Added: " . count( $this->added ) . PHP_EOL;
		echo "Deleted: " . count( $this->deleted ) . PHP_EOL;
		echo "Same: " . count( $this->same ) . PHP_EOL;
		echo "Changed: " .count( $this->changed ) . PHP_EOL;
		echo "Now: " . count( $this->posts ) . PHP_EOL;
		echo "Alt: " .  $this->count_posts_alt . PHP_EOL;
		
	}
	
	/**
	 * Adds a post to the same array
	 * 
	 * When it comes to reconciliation same is good
	 */
	function same( $post ) {
		$this->same[ $post->ID ] = $post->ID;
	}
	
	/**
	 * Adds a post to the changed array
	 * 
	 * If there aren't many of these then this is OK.
	 */
	function changed( $post ) {
		$this->changed[ $post->ID ] = $post->ID;
		$this->verbose( "Changed:", $post );
	}
	
	/**
	 * Adds a post to the added array
	 * 
	 * This is a post that was added to the slave but wasn't in the original local
	 * So we don't need to do anything except clone it again?
	 * 
	 */
	function added( $post ) {
		$this->added[ $post->ID ] = $post->ID;
		$this->verbose( "Added:  ", $post );
	}
	
	/**
	 * Adds a post to the deleted array
	 * 
	 * This is a post that was either added but not cloned... this is what we're looking for
	 * OR a post that was deleted from the slave but not the master
	 */
	function deleted( $post ) {
		$this->deleted[ $post->ID ] = $post->ID;
		$this->verbose( "Deleted:", $post );
	}
	
	/** 
	 * Produces a slightly verbose report about a change.
	 * 
	 * @TODO Identify where the post_title or post_content strings differ.
	 */
	function verbose( $string, $post ) {
		echo $string;
		echo " ";
		echo $post->ID;
		echo " ";
		echo $post->post_name;
		echo " ";
		echo $post->post_status;
		echo " ";
		echo $post->post_modified_gmt;
		echo PHP_EOL;
		
	}
	


} 
