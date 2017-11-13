<?php

namespace WPUP\SiteTreeView;

use WP_Post_Type;
use WP_Post;
use WP_Query;
use WPUP\SiteTreeView\Nodes\Node;
use WPUP\SiteTreeView\Nodes\Post_Node;
use WPUP\SiteTreeView\Nodes\Post_Nodes;
use WPUP\SiteTreeView\Nodes\Post_Type_Node;

class Repository {

	/**
	 * Posts instance.
	 *
	 * @var \WPUP\SiteTreeView\Posts
	 */
	protected $posts;

	/**
	 * Repository construct.
	 */
	public function __construct() {
		$this->posts = new Posts;
	}

	/**
	 * Convert data object to node.
	 *
	 * @param  mixed $data
	 *
	 * @return array
	 */
	protected function get_node( $data ) {
		if ( $data instanceof WP_Post ) {
			$node = new Post_Node( $data );
		} else if ( $data instanceof WP_Post_Type ) {
			$node = new Post_Type_Node( $data );
		} else {
			$node = new Node( $data );
		}

		/**
		 * Modify site tree view node.
		 *
		 * @param array $node
		 */
		$node = apply_filters( 'stv_node', $node );

		return $node instanceof Node ? $node : new Node;
	}

	/**
	 * Get nodes.
	 *
	 * @return array
	 */
	public function get_nodes() {
		$nodes = [];

		// Use the frontpage if that exists as first level.
		if ( $post = get_post( get_option( 'page_on_front' ) ) ) {
			$nodes[] = $this->get_node( $post );
		} else {
			$nodes[] = $this->get_node( [
				'text' => '/',
				'tags' => [
					esc_html__( 'Front Page', 'stv' ),
				],
			] );
		}

		// Loop through all post types that should be used.
		foreach ( $this->posts->get_post_types() as $type ) {
			// Post and page are built in post types and should be treated different.
			if ( $type->name === 'post' || $type->name === 'page' ) {
				$nodes[0]->add_nodes( $this->posts( $type->name ) );
			} else {
				$nodes[0]->nodes[] = $this->get_node( $type );
			}
		}

		// Sort by name.
		usort( $nodes[0]->nodes, [$this, 'sort'] );

		// Merge top level nodes.
		$nodes[0]->nodes = $this->merge_nodes( $nodes[0]->nodes );

		return $nodes;
	}

	/**
	 * Merge top level nodes that has the same text.
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
				foreach ( $node as $key => $value ) {
					if ( empty( $new_nodes[$node->text]->$key ) ) {
						$new_nodes[$node->text]->$key = $value;
					} else if ( is_array( $new_nodes[$node->text]->$key ) ) {
						$new_nodes[$node->text]->$key = array_merge( $new_nodes[$node->text]->$key, $node->$key );
					}
				}

				$new_nodes[$node->text]->tags = array_unique( $new_nodes[$node->text]->tags );
			}
		}

		return array_values( $new_nodes );
	}

	/**
	 * Get post nodes.
	 *
	 * @param  string $post_type
	 *
	 * @return array
	 */
	public function posts( $post_type ) {
		return new Post_Nodes( $post_type );
	}

	/**
	 * Sort nodes by text.
	 *
	 * @param  \WPUP\SiteTreeView\Nodes\Node $a
	 * @param  \WPUP\SiteTreeView\Nodes\Node $b
	 *
	 * @return int
	 */
	protected function sort( $a, $b ) {
		return strcmp( $a->text, $b->text );
	}
}
