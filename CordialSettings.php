<?php
/**
 * Wordpress plugin for Cordial-Api.com
 * The CordialSettings class is responsible for managing the Cordial admin 
 * settings page under the Wordpress Admin Dashboard Settings -> Cordial option.
 */
namespace Cordial;
class CordialSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    static $instance = false;

    /**
     * Start up
     */
    public function __construct()
    {

        //admin-only hooks
        if( is_admin() == true)
        {
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
            add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
        }
    }

    public static function get_instance() 
    {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
    }

    //admin page scripts
    public function admin_enqueue_scripts( $hook ) {

        //only load scripts for Cordial admin page
        if($hook == "settings_page_cordial_admin_settings") {
            
            //scripts
            wp_enqueue_script( 'fontawesome', plugin_dir_url( __FILE__ ) . 'static/js/21b09dd44d.js', array());
            wp_enqueue_script( 'react.production', plugin_dir_url( __FILE__ ) . 'static/js/react.production.min.js', array());
            wp_enqueue_script( 'react.production.dom', plugin_dir_url( __FILE__ ) . 'static/js/react-dom.production.min.js', array());
            wp_enqueue_script( 'popper', plugin_dir_url( __FILE__ ) . 'static/js/popper.min.js', array());
            wp_enqueue_script( 'bootstrap', plugin_dir_url( __FILE__ ) . 'static/js/bootstrap.min.js', array());
            wp_enqueue_script( 'cordial_registration', plugin_dir_url( __FILE__ ) . 'static/js/2.js', array());
            wp_enqueue_script( 'cordial_registration_1', plugin_dir_url( __FILE__ ) . 'static/js/main.js', array());
            wp_add_inline_script( 'cordial_registration_1', '
            
            function closeRegistrationWindow(){
                jQuery(function($){
                    $("#registerModal").modal("hide");
                });
            }
            
            jQuery(function($){
                $(document).ready(() => {
                    $("[data-toggle=\'tooltip\']").tooltip();
                    !function (a) { function e(e) { for (var r, t, n = e[0], o = e[1], u = e[2], i = 0, l = []; i < n.length; i++)t = n[i], Object.prototype.hasOwnProperty.call(f, t) && f[t] && l.push(f[t][0]), f[t] = 0; for (r in o) Object.prototype.hasOwnProperty.call(o, r) && (a[r] = o[r]); for (s && s(e); l.length;)l.shift()(); return c.push.apply(c, u || []), p() } function p() { for (var e, r = 0; r < c.length; r++) { for (var t = c[r], n = !0, o = 1; o < t.length; o++) { var u = t[o]; 0 !== f[u] && (n = !1) } n && (c.splice(r--, 1), e = i(i.s = t[0])) } return e } var t = {}, f = { 1: 0 }, c = []; function i(e) { if (t[e]) return t[e].exports; var r = t[e] = { i: e, l: !1, exports: {} }; return a[e].call(r.exports, r, r.exports, i), r.l = !0, r.exports } i.m = a, i.c = t, i.d = function (e, r, t) { i.o(e, r) || Object.defineProperty(e, r, { enumerable: !0, get: t }) }, i.r = function (e) { "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(e, "__esModule", { value: !0 }) }, i.t = function (r, e) { if (1 & e && (r = i(r)), 8 & e) return r; if (4 & e && "object" == typeof r && r && r.__esModule) return r; var t = Object.create(null); if (i.r(t), Object.defineProperty(t, "default", { enumerable: !0, value: r }), 2 & e && "string" != typeof r) for (var n in r) i.d(t, n, function (e) { return r[e] }.bind(null, n)); return t }, i.n = function (e) { var r = e && e.__esModule ? function () { return e.default } : function () { return e }; return i.d(r, "a", r), r }, i.o = function (e, r) { return Object.prototype.hasOwnProperty.call(e, r) }, i.p = "/"; var r = window["webpackJsonpcordial-wp-registration"] = window["webpackJsonpcordial-wp-registration"] || [], n = r.push.bind(r); r.push = e, r = r.slice(); for (var o = 0; o < r.length; o++)e(r[o]); var s = n; p() }([])
                });
            });        
            ' );

            
            //styles
            wp_register_style( 'bootstrap_css', plugin_dir_url(__FILE__) . 'static/css/bootstrap.min.css', false);
            wp_enqueue_style( 'bootstrap_css' );
        }
        else if ($hook == "edit-comments.php") {
            
        }
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Cordial', 
            'manage_options', 
            'cordial_admin_settings', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'cordial_options' );

        ?>
        <div class="wrap">
            <h1>Cordial Settings</h1>
            <p>
                <!-- In order to use Cordial, you will need to <a href="https://cordial.lostcoastweb.com/register">register for an API key</a>.
                -->
                <!-- example tooltip if needed for future use -->
                <!-- <i class="fas fa-question-circle tooltip"><span class="tooltiptext">The default behavior for all new comments. </span></i> -->
            </p>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'cordial_settings' );
                do_settings_sections( 'cordial_admin_settings' );
                submit_button();
            ?>
            </form>
        </div>

        <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModal" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModal">Cordial API Key Registration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="CordialRegistration" class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
                </div>
            </div>
        </div>
            
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'cordial_settings', // Option group
            'cordial_options', // Option name
            array( 'sanitize_callback' => array($this, 'sanitize' )) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'cordial_admin_settings' // Page
        );  

        add_settings_field(
            CordialConstants::OPTIONS_API_KEY, // ID
            'API Key', // Title 
            array( $this, 'api_key_callback' ), // Callback
            'cordial_admin_settings', // Page
            'setting_section_id' // Section           
        );    
        
        add_settings_field(
            CordialConstants::OPTIONS_FLAG_THRESHOLD, // ID
            'Flag Threshold 
            <i class="fas fa-question-circle" data-toggle="tooltip" title="The score (0-100) above which a comment will be flagged for moderation. A score of 100 means no comments will be flagged."></i>
            ', // Title 
            array( $this, 'flag_threshold_callback' ), // Callback
            'cordial_admin_settings', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            CordialConstants::OPTIONS_DELETE_THRESHOLD, // ID
            'Delete Threshold 
            <i class="fas fa-question-circle" data-toggle="tooltip" title="The score (0-100) above which a comment will be automatically deleted. A score of 100 means no comments will be flagged."></i>
            ', // Title 
            array( $this, 'delete_threshold_callback' ), // Callback
            'cordial_admin_settings', // Page
            'setting_section_id' // Section           
        );

        /*
        add_settings_field(
            'default_comment_behavior', 
            'New Comment Action', 
            array( $this, 'default_comment_behavior_callback' ), 
            'cordial_admin_settings', 
            'setting_section_id'
        );      
        */
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input[CordialConstants::OPTIONS_API_KEY] ) )
        {
            $new_input[CordialConstants::OPTIONS_API_KEY] = sanitize_text_field( $input[CordialConstants::OPTIONS_API_KEY] );
        }
        if( isset( $input[CordialConstants::OPTIONS_FLAG_THRESHOLD] ) )
        {
            $new_input[CordialConstants::OPTIONS_FLAG_THRESHOLD] = sanitize_text_field( $input[CordialConstants::OPTIONS_FLAG_THRESHOLD] );
        }
        if( isset( $input[CordialConstants::OPTIONS_DELETE_THRESHOLD] ) )
        {
            $new_input[CordialConstants::OPTIONS_DELETE_THRESHOLD] = sanitize_text_field( $input[CordialConstants::OPTIONS_DELETE_THRESHOLD] );
        }
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        $register_button = '<button type="button" class="button" data-toggle="modal" data-target="#registerModal">
        Register for API key
        </button>';

        //disable button if API key already exists
        if(isset( $this->options[CordialConstants::OPTIONS_API_KEY] ))
        {
            //turned off for debugging
            //$register_button = "";
        }
        printf(
            '<input type="text" id="%s" name="%s" value="%s" style="width:25rem;" /> %s',
            CordialConstants::OPTIONS_API_KEY,
            'cordial_options[' . CordialConstants::OPTIONS_API_KEY . ']',
            isset( $this->options[CordialConstants::OPTIONS_API_KEY] ) ? esc_attr( $this->options[CordialConstants::OPTIONS_API_KEY]) : '',
            $register_button
        );
    }

    public function flag_threshold_callback()
    {
        printf(
            '<input type="text" id="%s" name="%s" value="%s"  />',
            CordialConstants::OPTIONS_FLAG_THRESHOLD,
            'cordial_options[' . CordialConstants::OPTIONS_FLAG_THRESHOLD . ']',
            isset( $this->options[CordialConstants::OPTIONS_FLAG_THRESHOLD] ) ? esc_attr( $this->options[CordialConstants::OPTIONS_FLAG_THRESHOLD]) : CordialConstants::OPTIONS_FLAG_THRESHOLD_DEFAULT
        );
    }

    public function delete_threshold_callback()
    {
        printf(
            '<input type="text" id="%s" name="%s" value="%s"  />',
            CordialConstants::OPTIONS_DELETE_THRESHOLD,
            'cordial_options[' . CordialConstants::OPTIONS_DELETE_THRESHOLD . ']',
            isset( $this->options[CordialConstants::OPTIONS_DELETE_THRESHOLD] ) ? esc_attr( $this->options[CordialConstants::OPTIONS_DELETE_THRESHOLD]) : CordialConstants::OPTIONS_DELETE_THRESHOLD_DEFAULT
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function default_comment_behavior_callback()
    {
        $approved_selected = '';
        $hold_selected = '';
        if(isset($this->options['default_comment_behavior']) == true)
        {
            $behavior = $this->options['default_comment_behavior'];
            if($behavior == 'approve')
            {
                $approved_selected = 'checked="checked"';
            }
            else if($behavior == 'hold')
            {
                $hold_selected = 'checked="checked"';
            }
        }
        printf('<input 
                    id="default_comment_behavior_approve" 
                    type="radio" 
                    name="cordial_options[default_comment_behavior]" 
                    value="approve" 
                    %s />
                    <label for="default_comment_behavior_approve">Mark as approved</label>', 
                    $approved_selected);
        printf('<br />');
        printf('<input 
                    id="default_comment_behavior_hold" 
                    type="radio" 
                    name="cordial_options[default_comment_behavior]" 
                    value="hold" 
                    %s />
                    <label for="default_comment_behavior_hold">Mark as pending</label>', 
                    $hold_selected);
    }
}