<?php

class WPWAF_Model_Manager {

    public function __construct() {
    }

    public function create_post_type($params) {        
        extract($params);

        $capabilities = isset($capabilities) ? $capabilities : array();
        $labels = array(
            'name'                  => sprintf( __( '%s', 'wpwaf' ), $plural_post_name),
            'singular_name'         => sprintf( __( '%s', 'wpwaf' ), $singular_post_name),
            'add_new'               => __( 'Add New', 'wpwaf' ),
            'add_new_item'          => sprintf( __( 'Add New %s ', 'wpwaf' ), $singular_post_name),
            'edit_item'             => sprintf( __( 'Edit %s ', 'wpwaf' ), $singular_post_name),
            'new_item'              => sprintf( __( 'New  %s ', 'wpwaf' ), $singular_post_name),
            'all_items'             => sprintf( __( 'All  %s ', 'wpwaf' ), $plural_post_name),
            'view_item'             => sprintf( __( 'View  %s ', 'wpwaf' ), $singular_post_name),
            'search_items'          => sprintf( __( 'Search  %s ', 'wpwaf' ), $plural_post_name),
            'not_found'             => sprintf( __( 'No  %s found', 'wpwaf' ), $plural_post_name),
            'not_found_in_trash'    => sprintf( __( 'No  %s  found in the Trash', 'wpwaf' ), $plural_post_name),
            'parent_item_colon'     => '',
            'menu_name'             => sprintf( __('%s', 'wpwaf' ), $plural_post_name),
        );

        $args = array(
            'labels'                => $labels,
            'hierarchical'          => true,
            'description'           => $description,
            'supports'              => $supported_fields,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'has_archive'           => true,
            'query_var'             => true,
            'can_export'            => true,
            'rewrite'               => true
        );

        if(count($capabilities) != 0){
            $args['capability_type'] = $post_type;
            $args['capabilities'] = $capabilities;
            $args['map_meta_cap'] = true;
        }else{
            $args['capability_type'] = 'post';
        }

        register_post_type( $post_type, $args );
    }

    public function create_custom_taxonomies($params) {
        
        extract($params);

        $capabilities = isset($capabilities) ? $capabilities : array();
        
        register_taxonomy(
                $category_taxonomy,
                $post_type,
                array(
                  'labels' => array(
                    'name'              => sprintf( __( '%s ', 'wpwaf' ) , $singular_name),
                    'singular_name'     => sprintf( __( '%s ', 'wpwaf' ) , $singular_name),
                    'search_items'      => sprintf( __( 'Search %s ', 'wpwaf' ) , $singular_name),
                    'all_items'         => sprintf( __( 'All %s ', 'wpwaf' ) , $singular_name),
                    'parent_item'       => sprintf( __( 'Parent %s ', 'wpwaf' ) , $singular_name),
                    'parent_item_colon' => sprintf( __( 'Parent %s :', 'wpwaf' ) , $singular_name),
                    'edit_item'         => sprintf( __( 'Edit %s ', 'wpwaf' ) , $singular_name),
                    'update_item'       => sprintf( __( 'Update %s ', 'wpwaf' ) , $singular_name),
                    'add_new_item'      => sprintf( __( 'Add New %s ', 'wpwaf' ) , $singular_name),
                    'new_item_name'     => sprintf( __( 'New %s  Name', 'wpwaf' ) ,$singular_name),
                    'menu_name'         => sprintf( __( '%s ', 'wpwaf' ) , $singular_name),
                   ),
                   'hierarchical' => $hierarchical,
                   'capabilities' => $capabilities ,
                )
        );

        
    }

    public function generate_messages( $messages, $params ) {
        global $post, $post_ID;
        
        extract($params);
        $this->error_message = get_transient( $post_type."_error_message_$post->ID" );

        if ( !empty( $this->error_message ) ) {
            $messages[$post_type] = array();
        } else {

            // Customize the messages list 
            $messages[$post_type] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__('%1$s updated. <a href="%2$s">View %3$s</a>', 'wpwaf' ),$singular_name, esc_url(get_permalink($post_ID)),$singular_name),
                
                2 => __('Custom field updated.', 'wpwaf' ),
                
                3 => __('Custom field deleted.', 'wpwaf' ),
                
                4 => sprintf( __('%1$s updated.', 'wpwaf' ), $singular_name),
                
                5 => isset($_GET['revision']) ? sprintf(__('%1$s restored to revision from %2$s', 'wpwaf' ),$singular_name, wp_post_revision_title((int) $_GET['revision'], false)) : false,
                
                6 => sprintf(__('%1$s published. <a href="%2$s">View %3$s</a>', 'wpwaf' ),$singular_name, esc_url(get_permalink($post_ID)),$singular_name),
                
                7 => sprintf(__('%1$s saved.', 'wpwaf' ),$singular_name),
                
                8 => sprintf(__('%1$s submitted. <a target="_blank" href="%2$s">Preview %3$s</a>', 'wpwaf' ), $singular_name, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))), $singular_name),
                
                9 => sprintf(__('%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %4$s</a>', 'wpwaf' ),
                        $singular_name,
                        date_i18n(__('M j, Y @ G:i'),strtotime($post->post_date)), 
                        esc_url(get_permalink($post_ID)),
                        $singular_name),
                
                10 => sprintf(__('%1$s draft updated. <a target="_blank" href="%2$s">Preview %3$s</a>', 'wpwaf' ), $singular_name,  esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))), $singular_name),
            );
        }


        return $messages;
    }
}
?>