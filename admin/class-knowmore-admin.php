<?php
/**
 * Know_More
 *
 * @package   Know_More_Admin
 * @author    Yuri Victor <yurivictor@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.washingtonpost.com
 * @copyright 2013 Yuri Victor
 */

class Know_More_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = Know_More::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$this->add_actions();

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	private function add_actions() {
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Create url meta box functionality
		add_action( 'admin_menu', array( $this, 'create_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta_box' ), 10, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		// Load admin post page scripts
		global $pagenow;
		if ( $pagenow=='post-new.php' || $pagenow=='post.php' ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Know_More::VERSION );
		}		

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Know_More::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		// Load admin post page scripts
		global $pagenow;
		if ( $pagenow=='post-new.php' || $pagenow=='post.php' ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Know_More::VERSION );
		}

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Know_More::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Know More Settings', $this->plugin_slug ),
			__( 'Know More', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Adds the meta box container
	 * @since 1.0.0
	 */
	public function create_post_meta_box() {
		add_meta_box( 
			$this->plugin_slug, 
			'Suggest a link for further reading', 
			array( $this, 'display_post_meta_box' ), 
			'post', 
			'normal', 
			'high' 
		);
	}

	/**
	 * Render Meta Box content.
	 * @param WP_Post $post The post object.
	 * @since 1.0.0	 
	 */
	public function display_post_meta_box( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'know_more_meta_box', 'know_more_meta_box_nonce' );

		// Use get_post_meta to retrieve an existing values from the database.
		$url      = get_post_meta( $post->ID, '_know_more_post_meta_box_url', true );
		$headline = get_post_meta( $post->ID, '_know_more_post_meta_box_headline', true );
		$image    = get_post_meta( $post->ID, '_know_more_post_meta_box_image', true );	
		$site     = get_post_meta( $post->ID, '_know_more_post_meta_box_site', true );		

		if ( ! $url ) {
			echo '<div id="know_more_meta_box">';
			echo '<label for="know_more_meta_box_url">';
			_e( 'Paste a link to content related to this post', 'myplugin_textdomain' );
			echo '</label>';
			echo '<input type="text" id="know_more_meta_box_url" name="know_more_meta_box_url">';
			echo '</div>';
		} else {
			echo '<div id="know_more_meta_box">';
			echo '<label for="know_more_meta_box_url">';
			_e( 'Paste a link to content related to this post', 'myplugin_textdomain' );
			echo '</label>';
			echo '<input type="text" id="know_more_meta_box_url" name="know_more_meta_box_url" ';
			echo 'value="' . $url . '">';
			echo '<label for="know_more_meta_box_headline">';
			_e( 'Headline', 'myplugin_textdomain' );
			echo '</label>';
			echo '<input type="text" id="know_more_meta_box_headline" name="know_more_meta_box_headline" ';
			echo 'value="' . $headline . '">';
			echo '<label for="know_more_meta_box_image">';
			_e( 'Image', 'myplugin_textdomain' );
			echo '</label>';
			echo '<input type="text" id="know_more_meta_box_image" name="know_more_meta_box_image" ';
			echo 'value="' . $image . '">';
			echo '<input type="hidden" id="know_more_meta_box_site" name="know_more_meta_box_site" ';
			echo 'value="' . $site . '">';
			echo '</div>';		
		}

		echo '<div id="know_more_error" class="know_more_hidden">Something went wrong. Blame Yuri.</div>';


	}

	/**
	 * Save the meta when the post is saved.
	 * @param int $post_id The ID of the post being saved.
	 * @since 1.0.0
	 */
	public function save_post_meta_box( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['know_more_meta_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['know_more_meta_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'know_more_meta_box' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$url      = sanitize_text_field( $_POST['know_more_meta_box_url'] );
		$headline = sanitize_text_field( $_POST['know_more_meta_box_headline'] );
		$image    = sanitize_text_field( $_POST['know_more_meta_box_image'] );
		$site     = sanitize_text_field( $_POST['know_more_meta_box_site'] );

		// Update the meta field.
		update_post_meta( $post_id, '_know_more_post_meta_box_url', $url );
		update_post_meta( $post_id, '_know_more_post_meta_box_headline', $headline );
		update_post_meta( $post_id, '_know_more_post_meta_box_image', $image );
		update_post_meta( $post_id, '_know_more_post_meta_box_site', $site );		
	}

}