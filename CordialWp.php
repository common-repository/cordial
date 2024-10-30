<?php
/**
 * Wordpress plugin for Cordial-Api.com
 * The CordialWp class is for functionality not belonging to a specific page 
 * (e.g. REST API, comment processing, etc.)
 */
namespace cordial;

use GuzzleHttp\Client;
use WP_REST_Response;

class CordialWp
{
    static $instance = false;
    const CORDIAL_API_ROUTE = 'https://cordial-api.com';
    const CORDIAL_POST_COMMENT = '/api/v1/comment';
    const LOCAL_API_ROUTE_BASE = 'cordial/v1';
    const LOCAL_API_COMMENT = '/comment';
    
    private $cordial_api = null;
    private $api_key = "";
    private $flag_threshold = CordialConstants::OPTIONS_FLAG_THRESHOLD_DEFAULT;
    private $delete_threshold = CordialConstants::OPTIONS_DELETE_THRESHOLD_DEFAULT;
    private $cipher_method = "aes-256-cbc";
    private $hash_method = "sha256";
    private $options = array();

    public function __construct()
    {

        //set up instance state
        $this->cordial_api = new Client([
            'base_uri' => self::CORDIAL_API_ROUTE,
            'timeout'  => 8.0,
        ]);

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
        add_action('comment_post', array($this, 'action_comment_post'), 10, 3);
        add_action('transition_comment_status', array($this, 'action_comment_status_changed'), 10, 3);
        add_action('pre_get_comments', array($this, 'action_pre_get_comments'), 10, 1);
        //add_action('comments_clauses', array($this, 'action_comments_clause'), 10, 2);

        //admin-only hooks
        if(is_admin() == true)
        {
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
        }

        //register WP REST endpoints
        add_action('rest_api_init', function () {

            //handles feedback from cordial api service
            register_rest_route( self::LOCAL_API_ROUTE_BASE, self::LOCAL_API_COMMENT . '/(?P<id>\d+)', array(
              'methods' => 'POST',
              'callback' => array($this, 'api_comment_post'),
            ) );
        });

    }

    public static function get_instance() 
    {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
    }

    //adds settings link on main WP plugin page
    public function add_action_links ( $links ) {
        $mylinks = array(
        '<a href="' . admin_url( 'options-general.php?page=cordial_admin_settings' ) . '">Settings</a>',
        );
        return array_merge( $links, $mylinks );
    }

    /**
     * Assume all params are base64 encoded
     */
    private function decrypt_message($message, $key, $iv)
    {
        $message = base64_decode($message);
        $key = base64_decode($key);
        $iv = base64_decode($iv);
        $decrypted = openssl_decrypt($message, $this->cipher_method, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    private function handle_comment_score($comment_id, $score)
    {
        if($score > $this->delete_threshold)
            {
                //auto delete?
                wp_set_comment_status($comment_id, 'trash', true);

                //mark as moderated by cordial
                update_comment_meta($comment_id, CordialConstants::META_CORDIAL_MODERATED_KEY, "true");
            }
            else if($score > $this->flag_threshold)
            {
                //flag?
                wp_set_comment_status($comment_id, 'hold', true);

                //mark as moderated by cordial
                update_comment_meta($comment_id, CordialConstants::META_CORDIAL_MODERATED_KEY, "true");
            }
            else
            {
                //all below threshold get approved
                wp_set_comment_status($comment_id, 'approve', true);
            }

            //save score in our local metadata
            update_comment_meta($comment_id, CordialConstants::META_CORDIAL_SCORE_KEY, $score);
    }

    public function api_comment_post($request)
    {
        $params = $request->get_params();

        $response = new WP_REST_Response($params);
        $response->set_status( 200 );

        //validate input
        if(array_key_exists('Message', $params) == false || array_key_exists('IV', $params) == false)
        {
            $response->set_status(500);
            return $response;
        }

        //decode, make sure all bits are there
        $data = json_decode($this->decrypt_message($params['Message'], $this->api_key, $params['IV']), true);
        if(array_key_exists('CommentId', $data) && array_key_exists('Score', $data) && $params['id'] == $data['CommentId'])
        {
            //score comes back as a float 0-1, translate into %
            $score = floatval($data['Score']);
            $score = intval($score * 100);
            $comment_id = $data['CommentId'];
            
            //handle comment score (delete, hold, approve)
            $this->handle_comment_score($comment_id, $score);
            return $response;
        }
        
        $response->set_status(500);
        return $response;
    }

    public function action_comment_post($comment_id, $comment_approved, $comment_data)
    {
        //save placeholder for comment in metadata
        add_comment_meta($comment_id, CordialConstants::META_CORDIAL_SCORE_KEY, -1, true);
        add_comment_meta($comment_id, CordialConstants::META_CORDIAL_MODERATED_KEY, "false");
        add_comment_meta($comment_id, CordialConstants::META_CORDIAL_OVERRIDE_KEY, "false");

        //create REST post request with comment content and API key
        try
        {
            $response = $this->cordial_api->request(
                'POST', 
                self::CORDIAL_POST_COMMENT, [
                'json' => [
                    'ApiKey' => $this->api_key,
                    'CommentId' => $comment_id,
                    'CommentText' => $comment_data['comment_content']
                ]]);
            
            $code = $response->getStatusCode(); // 200
            $reason = $response->getReasonPhrase(); // OK
            $body = $response->getBody();
        }
        catch(Exception $ex)
        {
            //TODO: log exception
        }
    }

    /**
     * Fires whenever an admin triggers comment status change.  We use this to determine when
     * an admin overrides a cordial score.  Used to track which comments need to be flagged in comments admin panel
     * as well as for improving cordial service.
     */
    public function action_comment_status_changed($new_status, $old_status, $comment)
    {
        $comment_id = $comment->comment_ID;
        $is_cordial_moderated = get_comment_meta($comment_id, CordialConstants::META_CORDIAL_MODERATED_KEY, true);
        $needs_update = false;
        $client_override = false;

        //if the status is marked by approved by user but originally marked as moderated by cordial, remember
        //override
        if($new_status != $old_status && $new_status == 'approved' && $is_cordial_moderated == 'true')
        {
            update_comment_meta($comment_id, CordialConstants::META_CORDIAL_OVERRIDE_KEY, 'true');
            $needs_update = true;
            $client_override = true;
        }
        else if($new_status != $old_status && $new_status == 'unapproved' && $is_cordial_moderated == 'true')
        {
            //did they change their mind?
            update_comment_meta($comment_id, CordialConstants::META_CORDIAL_OVERRIDE_KEY, 'false');
            $needs_update = true;
        }
        print "update needed: " . $needs_update;

        //update Cordial server?
        if($needs_update == true)
        {
            try
            {
                $route = self::CORDIAL_POST_COMMENT . '/' . $comment_id;
                $response = $this->cordial_api->request(
                    'PUT', 
                    $route, [
                    'json' => [
                        'ApiKey' => $this->api_key,
                        'CommentId' => $comment_id,
                        'IsClientOverridden' => $client_override
                    ]]);
                
                $code = $response->getStatusCode(); // 200
                $reason = $response->getReasonPhrase(); // OK
                $body = $response->getBody();
            }
            catch(Exception $ex)
            {
                //TODO: log exception
            }
        }
    }

    public function action_pre_get_comments(&$query)
    {
        /*
        $comment_ids = &$query->query_vars['parent__in'];
        if(in_array(7, $comment_ids) == true)
        {
            print("inside");
            unset($comment_ids[7]);
        }
        print_r($query);
        return $query;
        */
    }

    public function action_comments_clause(array $pieces, WP_Comment_Query $query) {
        return $pieces;
        global $wpdb;
     
        $meta_query = new WP_Meta_Query();
        $meta_query->parse_query_vars([
            'meta_key' => self::CORDIAL_SCORE_KEY,
            'meta_value' => 5,
            'meta_compare' => '<='
        ]);
     
        if ( !empty($meta_query->queries) )
        {
            $meta_query_clauses = $meta_query->get_sql( 'comment', $wpdb->comments, 'comment_ID', $query );
     
            if ( !empty($meta_query_clauses) )
            {
                $pieces['join'] .= $meta_query_clauses['join'];
     
                if ( $pieces['where'] )
                    $pieces['where'] .= ' AND ';
                // Strip leading 'AND'.
                $pieces['where'] .= preg_replace( '/^\s*AND\s*/', '', $meta_query_clauses['where'] );
     
                if ( !$query->query_vars['count'] )
                {
                    $pieces['groupby'] = "{$wpdb->comments}.comment_ID";
                }
            }
        }
     
        return $pieces;
    }
    
    
    /**
     * Filter get_comments_number() correctly by our meta query.
     *
     * @param int $count
     * @param int $post_id
     * @return int
     */
    /*
    add_filter('get_comments_number', function($count, $post_id) {
        $query = new WP_Comment_Query(['post_id' => $post_id]);
    
        // Frontend users only see approved comments
        if ( !is_admin() )
            $comment_query['status'] = 'approve';
    
        return sizeof($query->get_comments());
    }, 10, 2);
    */
}


