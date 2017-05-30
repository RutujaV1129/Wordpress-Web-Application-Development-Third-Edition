<?php

class WPWAF_Dashboard {
    /*
     * Include neccessary actions and filters to initialize the plugin.
     *
     * @param  -
     * @return -
     */

    public function __construct() {
        // $this->set_frontend_toolbar(FALSE);

        add_action( 'wp_before_admin_bar_render', array( $this, 'customize_admin_toolbar' ) );
        add_action( 'admin_menu', array( $this, 'customize_main_navigation' ) );
    }

    /*
     * Enable or disable front end admin toolbar
     *
     * @param  boolean $status Display status of admin toolbar
     * @return -
     */
    public function set_frontend_toolbar( $status ) {
        show_admin_bar( $status );
    }

    /*
     * Customize exisitng menu items and adding new menu items
     *
     * @param  - 
     * @return -
     */
    public function customize_admin_toolbar() {
        global $wp_admin_bar;

        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('customize');
        
        if ( current_user_can('edit_wpwaf_topics') ) {    
            $wp_admin_bar->add_menu( array(
                'id' => 'wpwaf-forums',
                'title' => __('Forum Components','wpwaf'),
                'href' => admin_url()
            ));            

            $wp_admin_bar->add_menu( array(
                'id' => 'wpwaf-new-topics',
                'title' => __('Topics','wpwaf'),
                'href' => admin_url() . "post-new.php?post_type=wpwaf_topic",
                'parent' => 'wpwaf-forums'
            ));
        }

        if ( current_user_can('edit_posts') ) {
            $wp_admin_bar->add_menu( array(
                    'id' => 'wpwaf-new-forums',
                    'title' => __('Forums','wpwaf'),
                    'href' => admin_url() . "post-new.php?post_type=wpwaf_forum",
                    'parent' => 'wpwaf-forums'
                ));
        }
    }

    /*
     * Removes the dashboard menu item
     *
     * @param  - 
     * @return -
     */
    public function customize_main_navigation() {
        global $menu, $submenu;
        //unset($menu[2]);
    }   

}



?>
