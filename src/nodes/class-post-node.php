<?php

namespace WPUP\SiteTreeView\Nodes;

use WP_Post;
use WPUP\SiteTreeView\Posts;

class Post_Node extends Node {

	/**
	 * Posts instance.
	 *
	 * @var \WPUP\SiteTreeView\Posts
	 */
	protected $posts;

	/**
	 * Post node constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		$this->posts = new Posts;

		if ( $data instanceof WP_Post ) {
			$this->setup_post( $data );
		}
	}

	/**
	 * Setup post.
	 *
	 * @param  \WP_Post $post
	 */
	protected function setup_post( $post ) {
		$data = [
			'id'   => $post->ID,
			'text' => $post->post_title,
			'href' => get_edit_post_link( $post->ID ),
			'url'  => get_permalink( $post->ID ),
			'icon' => '',
			'tags' => [
				ucfirst( get_post_type_object( $post->post_type )->labels->singular_name ),
				json_encode( [
					'text'  => ucfirst( $post->post_status ),
					'color' => '#0085ba',
				] ),
			],
		];

		// Find child nodes.
		if ( $posts = $this->walk( $this->posts->find( $post->post_type, $post->ID ) ) ) {
			$data['nodes'] = $posts[0]->nodes;
		}

		// Add front page tag.
		if ( intval( get_option( 'page_on_front' ) ) === $post->ID ) {
			array_unshift( $data['tags'], esc_html__( 'Front Page', 'stv' ) );
		}

		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Walk the posts and children.
	 *
	 * @param  array $posts
	 *
	 * @return array
	 */
	protected function walk( $posts ) {
		$nodes = [];

		foreach ( $posts as $post ) {
			if ( empty( $post->post_parent ) ) {
				if ( ! empty( $post->post_title ) ) {
					$nodes[$post->ID] = new Post_Node( $post );
				}
			} else {
				if ( ! isset( $nodes[$post->post_parent] ) ) {
					$nodes[$post->post_parent] = new Node;
				}

				$nodes[$post->post_parent]->nodes[] = new Post_Node( $post );

				// Sort by name.
				usort( $nodes[$post->post_parent]->nodes, [$this, 'sort'] );
			}
		}

		// Sort by name.
		usort( $nodes, [$this, 'sort'] );

		return $nodes;
	}
}
