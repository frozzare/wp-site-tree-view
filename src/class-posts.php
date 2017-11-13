<?php

namespace WPUP\SiteTreeView;

use WP_Query;

class Posts {

	/**
	 * Find posts by post type and post parent.
	 *
	 * @param  string $post_type
	 * @param  int    $post_parent
	 *
	 * @return array
	 */
	public function find( $post_type, $post_parent = 0 ) {
		$args = [
			'public'         => true,
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => ['publish', 'pending', 'draft', 'private', 'future'],
		];

		if ( ! empty( $post_parent ) ) {
			$args['post_parent'] = $post_parent;
		} else {
			$args['post_parent'] = 0;
		}

		/**
		 * Filter query arguments.
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'stv_query_args', $args );

		return ( new WP_Query( $args ) )->posts;
	}

	/**
	 * Get all post types that should be presents.
	 *
	 * @return array
	 */
	public function get_post_types() {
		return array_map( function ( $name ) {
			return get_post_type_object( $name );
		}, array_diff( get_post_types(), ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset'] ) );
	}
}
