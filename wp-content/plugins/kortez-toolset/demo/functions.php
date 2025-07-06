<?php

/**
 * The Kortez Toolset hooks callback functionality of the plugin.
 *
 */
class Kortez_Toolset_Hooks {

    private $hook_suffix;

    public static function instance() {

        static $instance = null;

        if ( null === $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'switch_theme', array( $this, 'flush_transient' ) );

    }

    /**
     * Check to see if advanced import plugin is not installed or activated.
     * Adds the Demo Import menu under Apperance.
     *
     * @since    1.0.0
     */
    public function import_menu() {
        if( !class_exists( 'Advanced_Import' ) ){
            $this->hook_suffix[] = add_theme_page( esc_html__( 'Demo Import ','kortez-toolset' ), esc_html__( 'Demo Import','kortez-toolset'  ), 'manage_options', 'advanced-import', array( $this, 'demo_import_screen' ) );
        } 
    }

    /**
     * Enqueue styles.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook_suffix ) {
        if ( !is_array( $this->hook_suffix ) || !in_array( $hook_suffix, $this->hook_suffix ) ){
            return;
        }
        wp_enqueue_style( 'kortez-toolset', KORTEZ_TEMPLATE_URL . 'assets/kortez-toolset.css',array( 'wp-admin', 'dashicons' ), '1.0.0', 'all' );
    }

    /**
     * Enqueue scripts.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook_suffix ) {
        if ( !is_array($this->hook_suffix) || !in_array( $hook_suffix, $this->hook_suffix )){
            return;
        }

        wp_enqueue_script( 'kortez-toolset', KORTEZ_TEMPLATE_URL . 'assets/kortez-toolset.js', array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'kortez-toolset', 'kortez_toolset', array(
            'btn_text' => esc_html__( 'Processing...', 'kortez-toolset' ),
            'nonce'    => wp_create_nonce( 'kortez_toolset_nonce' )
        ) );
    }

    /**
     * The demo import menu page comtent.
     *
     * @since    1.0.0
     */
    public function demo_import_screen() {
        ?>
        <div id="ads-notice">
            <div class="ads-container">
                <img class="ads-screenshot" src="<?php echo esc_url( kortez_toolset_get_theme_screenshot() ) ?>" >
                <div class="ads-notice">
                    <h2>
                        <?php
                        printf(
                            esc_html__( 'Thank you for choosing %1$s! It is detected that an essential plugin, Advanced Import, is not activated. Importing demos for %1$s can begin after pressing the button below.', 'kortez-toolset' ), '<strong>'. wp_get_theme()->get('Name'). '</strong>');
                        ?>
                    </h2>

                    <p class="plugin-install-notice"><?php esc_html_e( 'Clicking the button below will install and activate the Advanced Import plugin.', 'kortez-toolset' ); ?></p>

                    <a class="ads-gsm-btn button" href="#" data-name="" data-slug="" aria-label="<?php esc_html_e( 'Get started with the Theme', 'kortez-toolset' ); ?>">
                        <?php esc_html_e( 'Install Now', 'kortez-toolset' );?>
                    </a>
                </div>
            </div>
        </div>
        <?php

    }

    /**
     * Installs or activates advanced import plugin if not detected as such.
     *
     * @since    1.0.0
     */
    public function install_advanced_import() {

        check_ajax_referer( 'kortez_toolset_nonce', 'security' );

        $slug   = 'advanced-import';
        $plugin = 'advanced-import/advanced-import.php';
        $status = array(
            'install' => 'plugin',
            'slug'    => sanitize_key( wp_unslash( $slug ) ),
        );
        $status['redirect'] = admin_url( '/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome' );

        if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
            // Plugin is activated
            wp_send_json_success( $status );
        }

        if ( ! current_user_can( 'install_plugins' ) ) {
            $status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'kortez-toolset' );
            wp_send_json_error( $status );
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        // Looks like a plugin is installed, but not active.
        if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
            $plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $status['plugin']     = $plugin;
            $status['pluginName'] = $plugin_data['Name'];

            if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
                $result = activate_plugin( $plugin );

                if ( is_wp_error( $result ) ) {
                    $status['errorCode']    = $result->get_error_code();
                    $status['errorMessage'] = $result->get_error_message();
                    wp_send_json_error( $status );
                }

                wp_send_json_success( $status );
            }
        }

        $api = plugins_api(
            'plugin_information',
            array(
                'slug'   => sanitize_key( wp_unslash( $slug ) ),
                'fields' => array(
                    'sections' => false,
                ),
            )
        );

        if ( is_wp_error( $api ) ) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error( $status );
        }

        $status['pluginName'] = $api->name;

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if ( is_wp_error( $result ) ) {
            $status['errorCode']    = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( is_wp_error( $skin->result ) ) {
            $status['errorCode']    = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( $skin->get_errors()->get_error_code() ) {
            $status['errorMessage'] = $skin->get_error_messages();
            wp_send_json_error( $status );
        } elseif ( is_null( $result ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            WP_Filesystem();
            global $wp_filesystem;

            $status['errorCode']    = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'kortez-toolset' );

            // Pass through the error from WP_Filesystem if one was raised.
            if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
                $status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
            }

            wp_send_json_error( $status );
        }

        $install_status = install_plugin_install_status( $api );

        if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
            $result = activate_plugin( $install_status['file'] );

            if ( is_wp_error( $result ) ) {
                $status['errorCode']    = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error( $status );
            }
        }

        wp_send_json_success( $status );

    }
    /**
     * Demo list of the Kortez Themes with their recommended plugins.
     *
     * @since    1.0.0
     */
    public function kortez_toolset_demo_import_lists(){

        $theme_slug = kortez_toolset_get_theme_slug();
        switch( $theme_slug ):
            case 'kortez':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-pro':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-pro-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-education':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-education-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-finance':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-finance-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-construction':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-construction-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-blog':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-blog-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-business':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-business-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-travel':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-travel-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-music':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-music-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-insurance':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-insurance-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-medical':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-medical-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-charity':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-charity-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-restaurant':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-restaurant-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-photography':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-photography-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-marketing':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-marketing-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-lawyer':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-lawyer-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-yoga':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-yoga-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-event':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-event-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-portfolio':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-portfolio-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-auto':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-auto-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-corporate':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-corporate-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            case 'kortez-kindergarten':
                $list_url = "https://gitlab.com/api/v4/projects/42840007/repository/files/kortez-kindergarten-demolist%2Ejson?ref=main";
                while( empty( get_transient( 'kortez_toolset_demo_lists' ) ) ){
                    $request_demo_list_body = wp_remote_retrieve_body( wp_remote_get( $list_url ) );
                    if( is_wp_error( $request_demo_list_body ) ) {
                        return false; // Bail early
                    }
                    $demo_list_std     = json_decode( $request_demo_list_body, true );
                    $demo_list_array   = (array) $demo_list_std;
                    $demo_list_content = $demo_list_array['content'];
                    $demo_lists_json   = base64_decode( $demo_list_content );
                    $demo_lists        = json_decode( $demo_lists_json, true );
                    set_transient( 'kortez_toolset_demo_lists', $demo_lists, DAY_IN_SECONDS );
                }
                $demo_lists = get_transient( 'kortez_toolset_demo_lists' );
                break;
            default:
                $demo_lists = array();
                break;
        endswitch;
        return $demo_lists;
    }

    /**
     * Deletes the demo and template lists upon theme switch.
     *
     * @since    1.0.0
     */
    public function flush_transient(){
        delete_transient( 'kortez_toolset_demo_lists' );
    }

    /**
     * Replaces categories id during demo import.
     *
     * @since    1.0.0
     */
    public function replace_term_ids( $replace_term_ids ){

        /*terms IDS*/
        $term_ids = array(
            'slider_category',
            'highlight_posts_category',
            'feature_posts_category',
            'latest_posts_category',
            'feature_posts_two_category',
        );

        return array_merge( $replace_term_ids, $term_ids );
    }

    /**
     * Replaces attachment id during demo import.
     *
     * @since    1.0.0
     */
    public function replace_attachment_ids( $replace_attachment_ids ){
        $theme_slug = kortez_toolset_get_theme_slug();
        switch( $theme_slug ):
            case 'kortez':
            case 'kortez-pro':
            case 'kortez-education':
            case 'kortez-finance':
            case 'kortez-construction':
            case 'kortez-blog':
            case 'kortez-business':
            case 'kortez-travel':
            case 'kortez-music':
            case 'kortez-insurance':
            case 'kortez-medical':
            case 'kortez-charity':
            case 'kortez-restaurant':
            case 'kortez-photography':
            case 'kortez-marketing':
            case 'kortez-lawyer':
            case 'kortez-yoga':
            case 'kortez-event':
            case 'kortez-portfolio':
            case 'kortez-auto':
            case 'kortez-corporate':
            case 'kortez-kindergarten':
                /*attachments IDS*/
                $attachment_ids = array(
                    'banner_image',
                    'error404_image',
                    'footer_image',
                    'bottom_footer_image',
                    'box_frame_background_image',
                    'fixed_header_separate_logo',
                    'header_separate_logo',
                    'header_advertisement_banner',
                    'preloader_custom_image',
                    'notification_bar_image',
                    'slider_item',
                    'blog_advertisement_banner',
                    'featured_pages_one',
                    'featured_pages_two',
                    'featured_pages_three',
                    'featured_pages_four',
                );
                break;
            default:
                $attachment_ids = array();
                break;
        endswitch;
        return array_merge( $replace_attachment_ids, $attachment_ids );
    }
}

/**
 * Begins execution of the hooks.
 *
 * @since    1.0.0
 */
function kortez_toolset_hooks( ) {
    return Kortez_Toolset_Hooks::instance();
}