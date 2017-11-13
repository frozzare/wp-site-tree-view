<?php

namespace WPUP\SiteTreeView\Nodes;

use WP_Post_Type;
use WPUP\SiteTreeView\Posts;

class Post_Type_Node extends Post_Node {

	/**
	 * Post type node constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		$this->posts = new Posts;

		if ( $data instanceof WP_Post_Type ) {
			$this->setup_post_type( $data );
		}
	}

	/**
	 * Append node to the bottom array.
	 *
	 * @param  array $nodes
	 * @param  array $new_node
	 *
	 * @return array
	 */
	protected function append_node( $nodes, $new_node ) {
		if ( isset( $nodes[0] ) ) {
			$nodes[0]->nodes = $this->append_node( $nodes[0]->nodes, $new_node );
		}

		if ( empty( $nodes ) ) {
			return is_array( $new_node ) && isset( $new_node[0] ) ? $new_node : [$new_node];
		}

		return $nodes;
	}

	/**
	 * Get post type permalink nodes.
	 *
	 * @return array
	 */
	protected function get_post_type_permalink_nodes( $post_type ) {
		// Parse post type url to get the real permalink and not just the structure.
		$url   = get_post_type_archive_link( $post_type );
		$parts = parse_url( $url );

		// Bail if no parts.
		if ( ! isset( $parts['path'] ) ) {
			return [];
		}

		// Get the path parts.
		$paths = array_filter( explode( '/', $parts['path'] ) );
		$nodes = [];

		// Remove last value from array.
		array_pop( $paths );

		foreach ( $paths as $path ) {
			// Append path node at the bottom of the nodes array.
			$nodes = $this->append_node( $nodes, new Node( [
				'text'  => ucfirst( $path ),
				'nodes' => [],
				'tags'  => [
					esc_html__( 'Archive Page', 'stv' ),
				],
			] ) );
		}

		return $nodes;
	}

	/**
	 * Setup post type.
	 *
	 * @param  \WP_Post_Type $post_type
	 */
	protected function setup_post_type( $post_type ) {
		$data = [
			'href'  => admin_url( 'edit.php?post_type=' . $post_type->name ),
			'text'  => $post_type->label,
			'nodes' => $this->walk( $this->posts->find( $post_type->name ) ),
			'tags'  => [
				esc_html__( 'Archive Page', 'stv' ),
			],
			'url'   => get_post_type_archive_link( $post_type->name ),
		];

		// Get post type permalink parents and append post type to the tree.
		if ( $parent = $this->get_post_type_permalink_nodes( $post_type->name ) ) {
			$this->append_node( $parent, new Node( $data ) );
			$data = $parent[0];
		}

		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}
}
