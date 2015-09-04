<?php
/**
 * Concursos TAP.
 *
 * @package   Concursos_TAP
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-concursos-tap-admin.php`
 *
 *
 * @package Concursos_TAP
 * @author  Alain Sanchez <luka.ghost@gmail.com>
 */
class Concursos_TAP {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '2.1.2';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'concursos-tap';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        /**
         * Define the default options
         *
         * @since     2.0.0.1
         */
        $this->default_options = array(
            'url_sync_link_concursos' => 'http://www.todoapuestas.org/tdapuestas/web/api/concursos/listado.json/?access_token=%s&_=%s',
        );

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
        add_action( 'concursos_tap_remote_sync', array( $this, 'remote_sync' ) );
        add_action( 'sync_weekly_event', array( $this, 'remote_sync' ) );
        add_action( 'wp' , array( $this, 'active_remote_sync'));
        add_filter( 'cron_schedules', array($this, 'intervals'), 10, 1);
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
        add_option('concursos_tap_remote_info', self::get_instance()->default_options);
        add_option('concursos_tap_concursos', array());

        // execute initial synchronization
        self::get_instance()->remote_sync();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
        wp_clear_scheduled_hook( 'sync_weekly_event' );

		delete_option('concursos_tap_remote_info');
		delete_option('concursos_tap_concursos');
        remove_action('concursos_tap_remote_sync', array( self::get_instance(), 'remote_sync' ));
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

    /**
     * Activate remote synchronization hourly
     *
     * @since   1.0
     */
    public function active_remote_sync() {
        if ( !wp_next_scheduled( 'sync_weekly_event' ) ) {
            wp_schedule_event(time(), 'concursos_tap_weekly_update', 'sync_weekly_event');
        }
    }

    /**
     * Execute synchronizations from todoapuestas.org server
     *
     * @since   1.0
	 * @updated 2.1.0.0
     * @return void
	 * @throws Exception
     */
    public function remote_sync() {
        $option = get_option('concursos_tap_remote_info', $this->default_options);

        $oauthAccessToken = $this->get_oauth_access_token();

	    $timestamp = new DateTime("now");
        $apiUrl = esc_url(sprintf($option['url_sync_link_concursos'], $oauthAccessToken, $timestamp->getTimestamp()));
        $apiResponse = wp_remote_get($apiUrl);
        $list_concursos = json_decode($apiResponse['body'], true);
        if(empty($list_concursos) || !isset($list_concursos['concursos'])){
            throw new Exception('Invalid API response');
        }

        if(!empty($list_concursos['concursos'])){
            update_option('concursos_tap_concursos', $list_concursos['concursos']);
        }
    }

    public function intervals($schedules) {
        // add a 'weekly' interval
        $schedules['concursos_tap_weekly_update'] = array(
            'interval' => 604800,
            'display' => __('Actualizacion semanal', $this->plugin_slug)
        );
        return $schedules;
    }

	/**
     * @since 2.1.1.0
	 * @return string
	 * @throws \Exception
	 */
	private function get_oauth_access_token()
	{
        session_start();
		if(isset($_SESSION['TAP_OAUTH_CLIENT'])){
			$now = new DateTime('now');
			if($now->getTimestamp() <= intval($_SESSION['TAP_OAUTH_CLIENT']['expires_in'])){
				$oauthAccessToken = $_SESSION['TAP_OAUTH_CLIENT']['access_token'];
				return $oauthAccessToken;
			}
			unset($_SESSION['TAP_OAUTH_CLIENT']);
		}

		$oauthUrl = get_option('TAP_OAUTH_CLIENT_CREDENTIALS_URL');
		$publicId = get_option('TAP_PUBLIC_ID');
		$secretKey = get_option('TAP_SECRET_KEY');
		if(empty($publicId) || empty($secretKey)){
			throw new Exception('No public or secret key given');
		}

		$oauthUrl = sprintf($oauthUrl, $publicId, $secretKey);
		$oauthResponse = wp_remote_get($oauthUrl);
		if($oauthResponse instanceof WP_Error || strcmp($oauthResponse['response']['code'], '200') !== 0){
			throw new \Exception('Invalid OAuth response');
		}

		$oauthResponseBody = json_decode($oauthResponse['body']);
		$oauthAccessToken = null;
		if($oauthResponseBody instanceof WP_Error || !is_object($oauthResponseBody)){
			throw new \Exception('Invalid OAuth access token');
		}
		$oauthAccessToken = $oauthResponseBody->access_token;

		if(!isset($_SESSION['TAP_OAUTH_CLIENT'])){
			$now = new DateTime('now');
			$_SESSION['TAP_OAUTH_CLIENT'] = array(
				'access_token' => $oauthAccessToken,
				'expires_in' => $now->getTimestamp() + intval($oauthResponseBody->expires_in)
			);
		}

		return $oauthAccessToken;
	}
}
