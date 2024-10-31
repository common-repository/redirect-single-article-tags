<?php
/*
Plugin Name: Redirect Single Article Tags
Description: When tags only have one article associated with it, this plugin will redirect links to the associated article instead of the tag page.
Version: 1.0.3
Author: Merten Peetz
License: GPLv2
*/

// Modify tag links
function rsat_tag_link( $termlink, $term_term_id ) {
	// Return default link if more than 1 article
	$tag = get_tag( $term_term_id );
	if ( $tag->count > 1 ) {
		return $termlink;
	}

	// Get the single article with the tag
	$article_link = rsat_get_article_link_for_tag( $term_term_id );
	if ( false !== $article_link ) {
		return $article_link;
	}
	return $termlink;
};
add_filter( 'tag_link', 'rsat_tag_link', 10, 2 );

// Redirect from tag page
function rsat_template_redirect() {
	if ( is_tag() ) {
		$term_id = get_queried_object()->term_id;
		$article_link = rsat_get_article_link_for_tag( $term_id );
		if ( false !== $article_link ) {
			wp_redirect( $article_link, 307 ); // Temporary redirect
			exit;
		}
	}
}
add_action( 'template_redirect', 'rsat_template_redirect' );

// Get article link for given tag. Returns false if there are no posts or more than 1 post with that tag.
// TODO: Use transients for caching get_posts()-requests
function rsat_get_article_link_for_tag( $term_id ) {
	// Get posts for tag
	$args = array(
		'tag_id' => $term_id,
	);
	$posts = get_posts( $args );
	if ( 1 != count( $posts ) ) {
		return false;
	}

	// Get link
	return get_permalink( $posts[0]->ID );
}
