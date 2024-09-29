<?php
/*
 * Plugin Name:       SA WP REST API
 * Description:       This is a WP REST API demo
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Razel Ahmed
 * Author URI:        https://razelahmed.com
 */
class Rest_Demo {
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {

        // GET HAndle
        register_rest_route( 'rest-demo/v1', '/razel', [
            'methods' => 'GET',
            'callback' => [ $this, 'say_hello' ],
        ] );

        register_rest_route( 'rest-demo/v1', '/raposts', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_posts' ]
        ] );

        register_rest_route( 'rest-demo/v1', '/rapost/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_post' ]
        ] );

        // send data with query string /parameter accept
        register_rest_route( 'rest-demo/v1', '/qs', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_query_string' ]
        ] );

        // multiple parameter accept
        register_rest_route( 'rest-demo/v1', '/invoice/(?P<id>\d+)/item/(?P<item_id>\d+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'invoice_item' ]
        ] );

        // access string data
        register_rest_route( 'rest-demo/v1', '/greet/(?P<name>[a-zA-Z-9-]+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'greet_string' ],
        ] );

        // POST Handle
        register_rest_route( 'rest-demo/v1', '/person', [
            'methods' => 'POST',
            'callback' => [ $this, 'process_person' ],
        ] );

        // create a contact form endpoint with GET and POST support
        register_rest_route( 'rest-demo/v1', '/contact', [
            'methods' => [ 'GET', 'POST' ],
            'callback' => [ $this, 'process_contact' ],
        ] );

        register_rest_route( 'rest-demo/v1', '/me', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_me' ],
        ] );

        register_rest_route( 'rest-demo/v1', '/check_permission', [
            'methods' => 'GET',
            'callback' => [ $this, 'check_permission' ],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ] );

        //post endpoints to create post
        register_rest_route( 'rest-demo/v1', '/posts', [
            'methods' => 'POST',
            'callback' => [ $this, 'create_post' ],
            'permission_callback' => function() {
                return current_user_can('publish_posts');
            }
        ] );

    }

    public function say_hello() {
        // return new WP_REST_Response( 'Hello RAZEL', 200 );
        $response = [
            'message' => 'Hello Razel Ahmed',
        ];
        return new WP_REST_Response( $response, 200 );
    }

    public function get_posts() {
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        return new WP_REST_Response( $posts, 200 );
    }

    public function get_post($data) {
        // return "POST ID: ".$data['id'];
        $post_id = $data['id'];
        $post = get_post( $post_id );
        if ( !$post ) {
            return new WP_Error( 'error', 'POST NOT FOUND', [ 'status' => 404 ] );
        }
        return new WP_REST_Response( $post, 200 );
    }

    public function get_query_string($request) {
        $query_string = $request->get_params();
        // $page_number = $request->get_param( 'page');
        // return new WP_REST_Response( $query_string, 200 );

        // if ( !$page_number ) {
        //     $page_number = 1;
        // }
        return new WP_REST_Response( $query_string, 200 );
    }


    public function invoice_item( $data ) {
        $invoice_id = $data['id'];
        $item_id = $data['item_id'];
        $response = [
            'invoice_id' => $invoice_id,
            'item_id' => $item_id,
        ];
        return new WP_REST_Response( $response, 200 );
    }

    public function greet_string( $request ) {
        $name = $request['name'];
        $response = [
            'message' => 'Hello '.$name,
        ];
        return new WP_REST_Response( $response, 200 );
    }

    public function process_person($request) {
        $name = $request['name'];
        $email = $request['email'];
        $response = [
            'name' => $name,
            'email' => $email,
        ];
        return new WP_REST_Response( $response, 200 );
    }

    public function process_contact( $request ) {
        $method = $request->get_method();
        //if GET return a form
        if( $method == 'GET' ) {
            $form = '<form method="post">';
            $form = '<input type="text" name="name" id="" placeholder="Your Name">';
            $form = '<input type="email" name="email" id="" placeholder="Your email">';
            $form = '<input type="submit" value="submit">';
            $form = '</form>';
            return new WP_REST_Response( $form, 200 );
        } else {
            // if POST process the form
            $name = $request['name'];
            $email = $request['email'];
            $response = [
                'name' => $name,
                'email' => $email,
            ];
            return new WP_REST_Response( $response, 200 );
        }
        // return new WP_REST_Response( ['method' => $method], 200 );
    }

    public function get_me() {
        $user_id = get_current_user_id();
        $user_name = get_user_meta( $user_id, 'nickname', true );
        if ( $user_id == 0 ) {
            return new WP_Error( 'error', 'Unauthorized', [ 'status' => 401 ] );
        }
        $user = [
            'id' => $user_id,
            'name' => $user_name
        ];
        return new WP_REST_Response( $user, 200 );

    }

    public function check_permission() {
        // if ( !current_user_can( 'manage_options' ) ) {
        //     return new WP_Error( 'error', 'Unauthorized', [ 'status' => 401 ]  );
        // }
        return new WP_REST_Response( 'You Can Manage Options', 200 );
    }

    public function create_post( $request ) {
        $title = $request['title'];
        $content = $request['content'];
        $post = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ];
        $post_id = wp_insert_post($post);
        if ($post_id) {
            return new WP_REST_Response( 'Post Created', 200 );
        } else {
            return new WP_Error( 'error', 'Post not created', [ 'status' => 500 ] );
        }
    }



}

new Rest_Demo();