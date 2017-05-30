<?php

class WPWAF_Theme {

    private $templates;

    public function __construct() {
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ) );
        add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ), 10, 4 );
        add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item' ), 10, 3 ); 
        if ( ! is_admin() ) {
            add_filter( 'wp_get_nav_menu_items', array( &$this, 'restrict_nav_menu_items' ), 10, 3 );           
        }

        add_action('template_redirect', array($this, 'application_controller'));
        add_action('widgets_init', array($this, 'register_widgets'));

        add_action('wp_enqueue_scripts', array($this, 'include_styles'));
        $this->register_widget_areas();

        add_action('wpwaf_home_widgets_controls', array($this, 'home_widgets_controls'), 10, 2);
        
        add_action( 'customize_register', array($this, 'customize_panel' ));
        add_action( 'wp_head', array($this,'apply_custom_settings'));
    }

    public function edit_nav_menu_walker( $walker ) {
        require_once( WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-walker-nav-menu-edit.php' );
        return 'WPWAF_Walker_Nav_Menu_Edit';
    }

    public function menu_item_custom_fields($item_id, $item, $depth, $args ) {
        global $wp_roles;

        $user_roles = apply_filters( 'nav_menu_roles', $wp_roles->role_names, $item );

        $roles = (array) get_post_meta( $item->ID, 'wpwaf_nav_menu_roles', true );
        $visibility_level = get_post_meta( $item->ID, 'wpwaf_nav_menu_visibility_level', true );
        if($visibility_level == ''){
            $visibility_level = '0';
        }
        
        ?>

        <input type="hidden" name="nav-menu-role-nonce" value="<?php echo wp_create_nonce( 'nav-menu-nonce-name' ); ?>" />

        <div class="description-wide">
            <span class="description"><?php _e( "Visibility", 'wpwaf' ); ?></span>
            <br />

            <input type="hidden" class="nav-menu-id" value="<?php echo $item->ID ;?>" />

            <div class="logged-input-holder" style="float: left; width: 35%;">
                <select class="wpwaf_menu_visibility" name='wpwaf_menu_visibility_<?php echo $item->ID ;?>' id='wpwaf_menu_visibility_<?php echo $item->ID ;?>' >
                    <option value='0' <?php selected('0',$visibility_level); ?> ><?php _e('Everyone','wpwaf'); ?></option>
                    <option value='1' <?php selected('1',$visibility_level); ?> ><?php _e('Members','wpwaf'); ?></option>
                    <option value='2' <?php selected('2',$visibility_level); ?> ><?php _e('Guests','wpwaf'); ?></option>
                    <option value='3' <?php selected('3',$visibility_level); ?> ><?php _e('By User Role','wpwaf'); ?></option>
                    
                </select>
            </div>

            

        </div>

        <?php
            $role_display_panel = "display:none";
            if($visibility_level == '3'){
                $role_display_panel = "display:block";
            }
        ?>
        <div class="wpwaf-menu-role-display-panel description-wide" style="margin: 5px 0;<?php echo $role_display_panel; ?>">
            <span class="description"><?php _e( "Permitted user roles", 'wpwaf' ); ?></span>
            <br />

            <?php
            foreach ( $user_roles as $role => $name ) {

                $checked = checked( true, in_array( $role, $roles ) , false );
                
                ?>

                <div class="" style="">
                    <input type="checkbox" name="wpwaf_menu_roles[<?php echo $item->ID ;?>][]" id="wpwaf_menu_roles<?php echo $item->ID ;?>" <?php echo $checked; ?> value="<?php echo $role; ?>" />
                    <label for="nav_menu_role-<?php echo $role; ?>-for-<?php echo $item->ID ;?>">
                    <?php echo esc_html( $name ); ?>
                </label>
                </div>

        <?php } ?>

        </div>

        <?php
    
    }

    public function update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
      $new_visibility_level = isset( $_POST['wpwaf_menu_visibility_'.$menu_item_db_id] ) ? $_POST['wpwaf_menu_visibility_'.$menu_item_db_id] : '0';
        
      $visibility_roles = isset($_POST['wpwaf_menu_roles'][$menu_item_db_id]) ? $_POST['wpwaf_menu_roles'][$menu_item_db_id] : array();
      update_post_meta( $menu_item_db_id, 'wpwaf_nav_menu_visibility_level', $new_visibility_level );
      update_post_meta( $menu_item_db_id, 'wpwaf_nav_menu_roles', $visibility_roles );
    }

    public function restrict_nav_menu_items( $items, $menu, $args ) {

        $hide_children_of = array();

        // Iterate over the items to search and destroy
        foreach ( $items as $key => $item ) {

            $visible = true;

            $visibility_level = get_post_meta( $item->ID, 'wpwaf_nav_menu_visibility_level', true );
    
            if( in_array( $item->menu_item_parent, $hide_children_of ) ){
                $visible = false;
                $hide_children_of[] = $item->ID;
            }

            if( $visible && isset( $visibility_level ) ) {

                // check all logged in, all logged out, or role
                switch( $visibility_level ) {
                    case '0' :
                        $visible = true;
                        break;
                    case '1' :
                        $visible = is_user_logged_in() ? true : false;
                        break;
                    case '2' :
                        $visible = ! is_user_logged_in() ? true : false;
                        break;
                    case '3' :
                        $visibility_roles = (array) get_post_meta( $item->ID, 'wpwaf_nav_menu_roles', true );
                        $visible = false;
                        foreach ( $visibility_roles as $role ) {
                            if ( current_user_can( $role ) ) 
                                $visible = true;
                        }
                        break;

                }

            }

            // add filter to work with plugins that don't use traditional roles
            $visible = apply_filters( 'nav_menu_roles_item_visibility', $visible, $item );

            // unset non-visible item
            if ( ! $visible ) {
                $hide_children_of[] = $item->ID; // store ID of item 
                unset( $items[$key] ) ;
            }

        }

        return $items;
    }

    /*
     * Register widgetized areas
     *
     * @param  -
     * @return -
     */

    public function register_widget_areas() {
        register_sidebar(array(
            'name' => __('Home Widgets','wpwaf'),
            'id' => 'home-widgets',
            'description' => __('Home Widget Area', 'wpwaf'),
            'before_widget' => '<div id="one" class="home_list">',
            'after_widget' => '</div>',
            'before_title' => '<h2>',
            'after_title' => '</h2>'
        ));
    }

    /*
     * Include the widget classes and register the widgets
     *
     * @param  -
     * @return -
     */

    public function register_widgets() {
        $base_path = WPWAF_PLUGIN_DIR;
        include $base_path . 'widgets/class-wpwaf-home-list-widget.php';
        register_widget('WPWAF_Home_List_Widget');
    }
    
    /*
     * Control the default template loading process with custom templates
     *
     * @param  -
     * @return -
     */

    public function application_controller() {
        global $wp_query,$wpwa_template_loader;
        $control_action = isset ( $wp_query->query_vars['control_action'] ) ? $wp_query->query_vars['control_action'] : '';

        if (is_home () && empty($control_action) ) {
            ob_start();
            $wpwa_template_loader->get_template_part("home");
            echo ob_get_clean();
            exit;
        }
    }

    /*
     * Include styles and scripts for the plugin
     *
     * @param  -
     * @return -
     */

    public function include_styles() {
        wp_register_style('wpwaf_theme', WPWAF_PLUGIN_URL .'css/wpwaf-theme.css');
        wp_enqueue_style('wpwaf_theme');
    }

    /*
     * Adding dynamic controls into extendable areas
     *
     * @param  string   $type   Type of widget to add controls
     * @param  int      $id     Database table records Id
     * @return -
     */
    public function home_widgets_controls($type, $id) {

        if ($type == 'favorite') {
            echo "<input type='button' class='$type' id='" . $type . "_" . $id . "' data-id='$id' value='" . __('Mark Favorite','wpwaf') . "' />";
        }
    }
    
    /*
     * Adding dynamic settings controls into customizer
     *
     * @param  object   $wp_manager   customizer settings and controls
     * @return -
     */
    public function customize_panel( $wp_manager ){
        $wp_manager->add_section( 'wpwaf_settings_section', array(
            'title'          => __('WPWAF Settings','wpwaf'),
            'priority'       => 35,
        ) );

        // Color control
        $wp_manager->add_setting( 'color_setting', array(
            'default'        => '#000000',
        ) );

        $wp_manager->add_control( new WP_Customize_Color_Control( $wp_manager, 'color_setting', array(
            'label'   => __('Text Color','wpwaf'),
            'section' => 'wpwaf_settings_section',
            'settings'   => 'color_setting',
            'priority' => 6
        ) ) );

        
    }
    
    /*
     * Applying custom settings into the page
     *
     * @param  -
     * @param  -
     * @return -
     */
    function apply_custom_settings(){
        ?>
             <style>
                 body {
                      color: <?php echo get_theme_mod( 'color_setting' ); ?>;
                 }
             </style>

        <?php
    }

}


?>
