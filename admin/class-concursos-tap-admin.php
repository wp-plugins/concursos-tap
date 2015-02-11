<?php
/**
 * Plugin Name.
 *
 * @package   Concursos_TAP_Admin
 * @author    Alain Sanchez <asanchezg@inetzwerk.com>
 * @license   GPL-2.0+
 * @link      http://www.inetzwerk.com
 * @copyright 2014 Alain Sanchez
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-concursos-tap.php`
 *
 *
 * @package Concursos_TAP_Admin
 * @author  Your Name <email@example.com>
 */
class Concursos_TAP_Admin {

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
	protected $plugin_screen_hook_suffix = array();

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Concursos_TAP::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
//		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
//		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/**
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 *
		 * add_action ( 'hook_name', 'your_function_name', [priority], [accepted_args] );
         *
         * add_filter ( 'hook_name', 'your_filter', [priority], [accepted_args] );
		 */


	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

//		$screen = get_current_screen();
//		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
//			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Concursos_TAP::VERSION );
//		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

//		$screen = get_current_screen();
//		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
//			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Concursos_TAP::VERSION );
//		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix['root'] = add_menu_page(
            __( 'Concursos TAP', $this->plugin_slug ),
            __( 'Concursos TAP', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            '',
            'dashicons-admin-generic'
        );

        $this->plugin_screen_hook_suffix['info'] = add_submenu_page(
            $this->plugin_slug,
            __('Concursos TAP &raquo; Informacion', $this->plugin_slug),
            __('Informacion', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_information_page' )
        );

        $this->plugin_screen_hook_suffix['update'] = add_submenu_page(
            $this->plugin_slug,
            __('Concursos TAP &raquo; Actualizar', $this->plugin_slug),
            __('Actualizacion', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug.'/update-information',
            array( $this, 'display_update_page' )
        );

	}

    /**
	 * Render the information page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_information_page() {
		include_once( 'views/information.php' );
	}

    /**
     * Render the update information page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_update_page(){
        include_once('views/update.php');
    }

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Informacion', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

}
