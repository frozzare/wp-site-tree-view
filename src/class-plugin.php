<?php

namespace WPUP\SiteTreeView;

class Plugin {

	/**
	 * The menu index.
	 *
	 * @var int
	 */
	protected $index;

	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	protected $plugin_url;

	/**
	 * Plugin instance.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Get plugin instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
		$this->setup_properties();
	}

	/**
	 * Add admin menu.
	 */
	public function admin_menu() {
		add_menu_page(
			esc_html__( 'Site Tree View', 'stv' ),
			esc_html__( 'Site Tree View', 'stv' ),
			'edit_pages',
			'site-tree-view',
			[$this, 'render'],
			'dashicons-category',
			$this->get_index()
		);
	}

	/**
	 * Get menu index.
	 *
	 * @return int
	 */
	protected function get_index() {
		global $menu;

		if ( isset( $this->index ) ) {
			return $this->index;
		}

		$index = 2;

		while ( isset( $menu[$index] ) ) {
			$index++;
		}

		$this->index = $index;

		return $index;
	}

	/**
	 * Enqueue CSS.
	 */
	public function enqueue_css() {
		wp_enqueue_style( 'stv-main', $this->plugin_url . 'assets/main.css', false, null );
	}

	/**
	 * Enqueue JavaScript.
	 */
	public function enqueue_js() {
		wp_enqueue_script( 'stv-bootstrap-treeview', $this->plugin_url . 'assets/bootstrap-treeview.js', [
			'jquery',
		], '', true );
		wp_enqueue_script( 'stv-main', $this->plugin_url . 'assets/main.js', [
			'jquery',
		], '', true );
	}

	/**
	 * Setup properties.
	 */
	private function setup_properties() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_url = is_ssl() ? str_replace( 'http://', 'https://', $this->plugin_url ) : $this->plugin_url;
	}

	/**
	 * Setup WordPress hooks.
	 */
	private function setup_hooks() {
		add_action( 'admin_menu', [$this, 'admin_menu'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_css'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_js'] );
	}

	/**
	 * Render admin page.
	 */
	public function render() {
		$nodes = ( new Repository )->get_nodes();
		?>
		<div class="wrap">
			<h1>Site Tree View</h1>
			<div id="treeview-top">
				<p class="search-box">
					<label class="screen-reader-text" for="treeview-search-input">Search Page:</label>
					<input type="search" id="treeview-search-input" name="s" value="">
					<input type="submit" id="treeview-search-submit" class="button" value="Search Page">
				</p>
			</div>
			<div id="treeview" data-nodes="<?php echo esc_attr( str_replace( '&amp;', '&', json_encode( $nodes ) ) ); ?>">
				<p><?php echo esc_html__( 'Loading...', 'stv' ); ?></p>
			</div>
		</div>
		<?php
	}
}
