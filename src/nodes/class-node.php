<?php

namespace WPUP\SiteTreeView\Nodes;

use JsonSerializable;
use WP_Post;
use WP_Post_Type;

class Node implements JsonSerializable {

	/**
	 * Node id.
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * Node text.
	 *
	 * @var string
	 */
	public $text = '';

	/**
	 * Node link.
	 *
	 * @var string
	 */
	public $href = '';

	/**
	 * Node icon.
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * Node tags.
	 *
	 * @var array
	 */
	public $tags = [];

	/**
	 * Node nodes.
	 *
	 * @var array
	 */
	public $nodes = [];

	/**
	 * Node public url.
	 *
	 * @param string $url
	 */
	public $url;

	/**
	 * Node constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = [] ) {
		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Add nodes.
	 *
	 * @param array $nodes
	 */
	public function add_nodes( $nodes ) {
		if ( is_object( $nodes ) && method_exists( $nodes, 'to_array' ) ) {
			$nodes = $nodes->to_array();
		}

		$this->nodes = array_merge( $this->nodes, $nodes );
	}

	/**
	 * Specify data which should be serialized to JSON.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'text'  => $this->text,
			'href'  => $this->href,
			'icon'  => $this->icon,
			'nodes' => $this->nodes,
			'tags'  => $this->tags(),
		];
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

	/**
	 * Get node tags.
	 *
	 * @return array
	 */
	public function tags() {
		$tags = $this->tags;

		if ( empty( $this->href ) ) {
			array_unshift( $tags, json_encode( [
				'text'  => '<a href="#" class="badge-icon-link disabled" title="You can\'t edit this page"><span class="dashicons dashicons-edit"></span>',
				'color' => 'transparent',
			] ) );
		} else {
			array_unshift( $tags, json_encode( [
				'text'  => sprintf( '<a href="%s" class="badge-icon-link" title="Edit"><span class="dashicons dashicons-edit"></span>', $this->href ),
				'color' => 'transparent',
			] ) );
		}

		array_unshift( $tags, json_encode( [
			'text'  => sprintf( '<a href="%s" class="badge-icon-link" target="_blank" title="Visit public url"><span class="dashicons dashicons-external"></span></a>', $this->url ),
			'color' => 'transparent',
		] ) );

		return $tags;
	}

	/**
	 * Get node array.
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->jsonSerialize();
	}
}
