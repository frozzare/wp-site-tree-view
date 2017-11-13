<?php

namespace WPUP\SiteTreeView\Nodes;

use WPUP\SiteTreeView\Posts;

class Post_Nodes {

	/**
	 * Array of post nodes.
	 *
	 * @var array
	 */
	protected $nodes = [];

	/**
	 * Posts instance.
	 *
	 * @var \WPUP\SiteTreeView\Posts
	 */
	protected $posts;

	/**
	 * Post nodes constructor.
	 *
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {
		$this->posts = new Posts;

		// Convert all post to node.
		$this->nodes = array_map( function( $post ) {
			return new Post_Node( $post );
		}, $this->posts->find( $post_type ) );

		// If it's a post post type they should be grouped by permalink structure.
		if ( $post_type === 'post' ) {
			$this->nodes = $this->group_nodes_by_permalink( $this->nodes );
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
	 * Group post nodes with permalink structure.
	 *
	 * @param  array $nodes
	 *
	 * @return array
	 */
	protected function group_nodes_by_permalink( $nodes ) {
		foreach ( $nodes as $i => $node ) {
			$parent    = $this->get_post_permalink_nodes( $node->id );
			$nodes[$i] = array_shift( $this->append_node( $parent, $node ) );
		}

		return $this->merge_nodes( $nodes );
	}

	/**
	 * Get post permalink nodes.
	 *
	 * @return array
	 */
	protected function get_post_permalink_nodes( $post_id ) {
		// Parse post url to get the real permalink and not just the structure.
		$url   = get_permalink( $post_id );
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

		// Keep a array of paths that are looped
		// to build public url.
		$paths2 = [];

		foreach ( $paths as $path ) {
			$paths2[] = $path;

			// Append path node at the bottom of the nodes array.
			$nodes = $this->append_node( $nodes, new Node( [
				'url'   => home_url( implode( '/', $paths2 ) . '/' ),
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
	 * Merge child nodes with the same parent node that has the same text.
	 *
	 * @param  array $nodes
	 *
	 * @return array
	 */
	protected function merge_nodes( $nodes ) {
		$new_nodes = [];

		// Loop through nodes to group them by text.
		foreach ( array_values( $nodes ) as $i => $node ) {
			if ( ! isset( $new_nodes[$node->text] ) ) {
				$new_nodes[$node->text] = $node;
			} else if ( is_array( $node->nodes ) ) {
				$new_nodes[$node->text]->nodes = array_merge( $new_nodes[$node->text]->nodes, $node->nodes );
			}
		}

		// Remove text indexes.
		$new_nodes = array_values( $new_nodes );

		// Loop trough all child nodes and merge and sort them.
		foreach ( $new_nodes as $i => $node ) {
			if ( ! is_array( $node->nodes ) ) {
				continue;
			}

			// Merge child nodes.
			$new_nodes[$i]->nodes = $this->merge_nodes( $node->nodes );

			// Sort by name.
			usort( $new_nodes[$i]->nodes, [$this, 'sort'] );
		}

		return $new_nodes;
	}

	/**
	 * Sort nodes by text.
	 *
	 * @param  array $a
	 * @param  array $b
	 *
	 * @return int
	 */
	protected function sort( $a, $b ) {
		return strcmp( $a->text, $b->text );
	}

	/**
	 * Get nodes as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->nodes;
	}
}
