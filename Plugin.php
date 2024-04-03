<?php
namespace AccessibleOverlaySearch;

class Plugin {
    private $plugin_name;
    private $version;

    public function __construct() {
        $this->plugin_name = 'a11y-overlay-search'; //
        $this->version = '1.0';
        load_plugin_textdomain( 'overlay-search', false, dirname (plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public static function activate_plugin() {
        // Activation code..
    }

    public static function deactivate_plugin() {
        // Deactivation code..
    }

    /**
     * Register the stylesheets.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/styles.css', [], $this->version, 'all' );
    }

    /**
     * Register the JavaScript.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        // Enqueue overlay-search-public.js script
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/scripts.js', [], $this->version, true );

        // Localize theme name
        $theme = get_stylesheet(); // Get the directory name of the current theme
        wp_localize_script( $this->plugin_name, 'theme_name', [ 'get_themename' => $theme ] );

        // Localize translations
        $translations = [ 'no_results_found' => esc_html__( 'No results found.', 'overlay-search' ) ];
        wp_localize_script( $this->plugin_name, 'translations', $translations );
    }

    /**
     * Initializes the plugin.
     */
    public function init() {
        $this->enqueue_styles();
        $this->enqueue_scripts();
    }

    /**
     * Generates the search form HTML.
     *
     * @return string HTML output.
     */
    private function generate_search_form() {
        // Detect current home URL if Polylang is used.
        $home_url = ( function_exists( 'pll_home_url' ) ) ? pll_home_url() : home_url( '/' );

        $results = '<ul class="a11y-overlay-search_results" tabindex="-1" aria-live="polite" role="listbox"></ul>';

        // Check if Polylang exists
        if ( function_exists( 'pll_the_languages' ) ) {
            // Disable results if it isn't Polylang Pro
            $results = ( class_exists( 'PLL_REST_Post' ) ) ? $results : false;
        }

        $form = sprintf(
            '<form id="a11y-overlay-search" role="search" method="get" class="a11y-overlay-search__form" action="%1$s">
            <label for="s" class="screen-reader-text">%2$s</label>
            <input placeholder="%2$s" autocomplete="off" role="combobox" type="search" value="%3$s" name="s" class="a11y-overlay-search__input" autofocus />
            <button type="submit" form="a11y-overlay-search" value="%4$s" class="a11y-overlay-search__submit a11y-overlay-search--icon"><span class="screen-reader-text">%4$s</span>%5$s</button>
            </form>%6$s',
            esc_url( $home_url ), // Form action.
            esc_html__( 'Search from site', 'overlay-search' ), // Label & placeholder text.
            get_search_query(), // Input value.
            esc_html__( 'Search', 'overlay-search' ), // Submit value.
            '<svg class="magnifying-glass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>',
            $results ? $results : '' // Append $results if it exists
        );

        return $form;
    }

    /**
     * Generates the overlay dialog HTML.
     *
     * @return string HTML output.
     */
    public function generate_dialog() {
        $overlay_search = sprintf(
            '<div role="dialog" class="a11y-overlay-search__dialog">
            <div class="a11y-overlay-search__container">
            <button class="a11y-overlay-search__close a11y-overlay-search--icon"><span class="screen-reader-text">%s</span>%s</button>
            %s</div>
            </div>',
            esc_html__( 'Close search', 'overlay-search' ), // Close button screen reader text.
            '<svg class="close-search-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
            $this->generate_search_form() // Form.
        );

        return $overlay_search;
    }

    /**
     * Overlay search button to open modal, HTML output.
     *
     * @param bool   $show_label to determine whether the search label should be visible or not.
     * @param string $class set custom classes for search button.
     * @param string $svg set custom icon for search button.
     *
     * @return string Button HTML.
     */
    public static function overlay_search_button( $show_label = true, $class = '', $svg = '' ) {
        // Set defaults.
        $sr_only = ( $show_label ) ? '' : 'screen-reader-text';
        $class   = ( $class ) ? 'a11y-overlay-search__open a11y-overlay-search--icon ' . $class : 'a11y-overlay-search__open a11y-overlay-search--icon';
        $svg     = ( $svg ) ? $svg : '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>';

        $button = sprintf(
            '<button class="%s" aria-haspopup="dialog"><span class="%s">%s</span>%s</button>',
            esc_attr( $class ), // Button class.
            esc_attr( $sr_only ), // Span class.
            esc_html__( 'Search', 'overlay-search' ), // Span content.
            $svg
        );

        // Generate the dialog HTML
        $plugin = new self();
        $dialog = $plugin->generate_dialog();

        return $button . $dialog;
    }

    /**
     * Registers the custom REST route for overlay search.
     */
    public function register_overlay_search_rest_route() {
        $theme = wp_get_theme()->get_stylesheet();
        $route = $theme . '/v2';

        register_rest_route(
            $route,
            '/search',
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'overlay_search_rest_query_callback' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Query callback using REST API.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return array Results.
     */
    public function overlay_search_rest_query_callback( $request ) {
        $search_value = $request->get_param( 's' );
        $lang = $request->get_param( 'lang' );
        $results = [];
    
        $query_args = [
            's'              => sanitize_text_field( $search_value ),
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'posts_per_page' => 25,
            'no_found_rows'  => true,
        ];
    
        $query = new \WP_Query( $query_args );
    
        if ( function_exists( 'relevanssi_do_query' ) ) {
            relevanssi_do_query( $query );
        }
    
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
    
                // Get the translated post type name
                $post_type_object = get_post_type_object( get_post_type() );
                $post_type_name = $post_type_object->labels->singular_name;
    
                $results[] = [
                    'id'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'post_type' => $post_type_name,
                ];
            }
        }
    
        wp_reset_postdata();
    
        return $results;
    }    

    /**
     * Check if the request is JSON and set the JSON locale if needed.
     */
    public function check_json_request() {
        if ( wp_is_json_request() ) {
            add_filter( 'locale', [ $this, 'set_json_locale' ] );
        }
    }

    /**
     * Set locale for JSON response.
     *
     * @param string $locale Current locale.
     * @return string Locale.
     */
    public function set_json_locale($locale) {
        $current_language = isset( $_GET['lang'] ) ? sanitize_text_field( $_GET['lang' ]) : '';
        return $current_language;
    }
}

// Outside the class definition
$plugin_instance = new Plugin();
add_action( 'rest_api_init', [ $plugin_instance, 'register_overlay_search_rest_route' ] );

