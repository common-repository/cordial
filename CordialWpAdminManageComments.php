<?php
/**
 * Wordpress plugin for Cordial-Api.com
 * The CordialWpAdminManageComments class is responsible for adding additional content
 * to the Wordpress Admin Dashboard "Comments" page.  
 */
namespace cordial;


class CordialWpAdminManageComments
{
    static $instance = false;
    
    private $api_key = "";
    private $flag_threshold = CordialConstants::OPTIONS_FLAG_THRESHOLD_DEFAULT;
    private $delete_threshold = CordialConstants::OPTIONS_DELETE_THRESHOLD_DEFAULT;
    private $options = array();

    public function __construct()
    {

        //set API key based on options page
        if(function_exists('get_option') == true)
        {
            $this->options = get_option(CordialConstants::OPTIONS_KEY, array());
            if(array_key_exists(CordialConstants::OPTIONS_API_KEY, $this->options))
            {
                $this->api_key = $this->options[CordialConstants::OPTIONS_API_KEY];
            }
            if(array_key_exists(CordialConstants::OPTIONS_FLAG_THRESHOLD, $this->options))
            {
                $this->flag_threshold = $this->options[CordialConstants::OPTIONS_FLAG_THRESHOLD];
            }
            if(array_key_exists(CordialConstants::OPTIONS_API_KEY, $this->options))
            {
                $this->delete_threshold = $this->options[CordialConstants::OPTIONS_DELETE_THRESHOLD];
            }
            
        }

        //hook into WP
        add_filter('manage_edit-comments_columns', array($this, 'filter_manage_comments_custom_columns'));
        add_action('manage_comments_custom_column', array($this, 'action_manage_comments_custom_column'), 10, 2);

        //styles
        wp_register_style( 'CordialWpAdminManageComments_css', plugin_dir_url(__FILE__) . 'static/css/CordialWpAdminManageComments.css', false);
        wp_enqueue_style( 'CordialWpAdminManageComments_css' );

        //admin-only hooks
        if(is_admin() == true)
        {
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
        }
    }

    public static function get_instance() 
    {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
    }

    /**
     * Will add a custom column header to the comments management admin page.
     * 
     * Documentation: https://make.wordpress.org/docs/plugin-developer-handbook/10-plugin-components/custom-list-table-columns/#hook-summary
     *                https://make.wordpress.org/docs/plugin-developer-handbook/10-plugin-components/custom-list-table-columns/list-table-column-data/#comments
     *                https://premiumcoding.com/wordpress-tutorial-how-to-extend-wp-list-table/
     *                https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
     */
    public function filter_manage_comments_custom_columns($columns)
    {
        $columns['cordial'] = 'Cordial Score';
        return $columns;
    }

    /**
     * Will retrieve the cordial score for the supplied comment ID
     */
    public function action_manage_comments_custom_column($column_name, $comment_id)
    {
        if($column_name == 'cordial')
        {
            $data = get_comment_meta($comment_id, CordialConstants::META_CORDIAL_SCORE_KEY, true);
            $overridden = get_comment_meta($comment_id, CordialConstants::META_CORDIAL_OVERRIDE_KEY, true);
            $moderated = get_comment_meta($comment_id, CordialConstants::META_CORDIAL_MODERATED_KEY, true);
            $css_class = 'cordial-unknown';
            $content = "N/A";
            if(strlen($data) > 0)
            {
                $content = $data;
            }
            if($moderated == 'true')
            {
                if($overridden == 'true')
                {
                    $css_class = 'cordial-overridden';
                    $content .= ' (admin override)';
                }
                else
                {
                    if($data > $this->delete_threshold)
                    {
                        $css_class = 'cordial-deleted';
                    }
                    else if($data > $this->flag_threshold)
                    {
                        $css_class = 'cordial-flagged';
                    }
                    else
                    {
                        $css_class = 'cordial-pass';
                    }
                }
            }

            printf('<div class="%s">%s</div>', $css_class, $content);
            
        }
    }    
}


