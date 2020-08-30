<?php
/*
* Include css and js files
*/
class wont_gyrix_order_note_manager_load
{
    protected $note_template;
     
	public function wont_gyrixenqueue_styles() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            wp_enqueue_style(
                'wont_templatecss',
                WONT_GYRIXTEMPLATEURL . 'admin/css/templatecss.min.css',
                array(), 
                '1.0.0'
            );
        }
    }

    public function wont_gyrixenqueue_jscript() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            $userId = get_current_user_id();
            $ajaxSend = array(
                'ajaxSave'=> wp_create_nonce('saveGyrixTemplates'. $userId ),
                'ajaxAdd'=> wp_create_nonce('addGyrinotes'. $userId ),
                'ajaxGet'=> wp_create_nonce('getCustomerName'. $userId ),
                );
            wp_register_script(
                    'wont_templatejs',
                    WONT_GYRIXTEMPLATEURL . 'admin/js/template-script.min.js',
                    array(), 
                    '1.0.0' 
                );

            wp_localize_script( 'wont_templatejs', 'gyrixnonce', $ajaxSend );
            wp_enqueue_script('wont_templatejs');
        }
    }

    public function wont_gyrix_admin_notices() {
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            echo "<div class='updated'><p>Please activate woocommerce plugin to use Woo Order Note Templates plugin.</p></div>";
            return;
        }
    }

    // Add submenu to the woocommerce
	public function wont_gyrixcallhooks()
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            add_submenu_page(
                                            'woocommerce', 
                                            'Order Note Template', 
                                            'Order Note Template', 
                                            'edit_shop_orders', 
                                            'wont_gyrix_note_settings',
                                            array($this, 'wont_gyrix_load_template_page')
                                            );
        }
    }
    // Add "Add note" icon to the action column in order note
    function wont_gyrix_order_actions( $add_globalpay_requery_button) 
    {
        if(current_user_can('edit_shop_orders') ) {
            global $woocommerce;

            $add_globalpay_requery_button['note'] = array('url' => '',
                                                          'name'      => "Add Notes",
                                                          'action'    => "shop_order-note"
                                                      );
            return $add_globalpay_requery_button;
        }
    }

    public function wont_gyrix_admin_order_actions_end( $instance ) 
    {
        $note = new wont_gyrix_order_note_view;
        $templates = $note->wont_gyrix_get_note_template();      
        $note->wont_gyrix_order_add_note_on_view();
        $hook = new wont_gyrix_display_popup;
        $hook->wont_gyrix_display_popup($templates);
    }

    public function wont_gyrix_load_template_page() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            $note = new wont_gyrix_order_note_view;
            $templates = $note->wont_gyrix_get_note_template();
            $note->wont_gyrix_order_add_note_on_view();
            $html = new Wont_gyrix_get_template_html;
            $html->wont_gyrix_header_template();
            if($templates)
                $html->wont_gyrix_show_template($templates);
            else
                $html->wont_gyrix_add_new_template();
            $html->wont_gyrix_footer_template();
        }
    }

    public function modify_args_of_CPT() {
        if ( $post_type == "wont_gyrix_templates" ) {
            $args['public'] = true;
        }

        return $args;
    }
    
    // Remove extra metaboxes like revolutionary slider from edit wont_gyrix_templates of this post type
    public function wont_gyrix_remove_meta_boxes(){
        remove_meta_box( 'wpseo_meta', 'wont_gyrix_templates', 'normal' );
        remove_meta_box('mymetabox_revslider_0', 'wont_gyrix_templates', 'normal');
    }

    // Remove media button from edit post of order note
    public function remove_media_buttons_from_edit_post() {
        global $current_screen;
        if('wont_gyrix_templates' == $current_screen->post_type) {
            remove_action('media_buttons', 'media_buttons');
        }    
    }

    // Remove wordpress default edito
    public function wont_gyrix_remove_default_editor() {
        remove_post_type_support( 'wont_gyrix_templates', 'editor' );
    }

    public function wont_gyrix_add_textarea_after_title($post) {
        if($post->post_type === 'wont_gyrix_templates') {
            ?>  <div
                    style="
                        padding: 18px 0px 10px 2px;
                        font-size: 22px;
                        color: #32373c;"
                >
                    Content
                </div>
                <textarea
                    rows="10"
                    style="width:100%;padding:8px;border-radius: 6px;"
                    name="post_content"
                ><?php echo $post->post_content; ?></textarea>
            <?php
        }
    }
    
    public function wont_gyrix_add_note_type_metabox() {
        add_meta_box(
           'custom_meta_box-2',       // $id
           'Order Note Type',         // $title
           'wont_gyrix_order_note_manager_load::show_custom_meta_box_2',  // $callback
           'wont_gyrix_templates',    // $page
           'side',                  // $context
           'high'                     // $priority
        );
    }

    public static function show_custom_meta_box_2() {
        global $post;
        // Nonce field to validate form request came from current site
        wp_nonce_field( basename( __FILE__ ), 'nonce_keyword' );
        // Get the wont_gyrix_note_type data if it's already been entered
        $order_note_type = get_post_meta( $post->ID, 'wont_gyrix_note_type', true );
        // Output the field
        echo '
            <select name="wont_gyrix_note_type" style="width:150px">
                <option
                    value="customer"
                    ';if($order_note_type == "customer") echo "selected"; echo '>
                    Customer Note
                </option>
                <option
                    value="private"
                    ';if($order_note_type == "private") echo "selected"; echo '>
                    Private Note
                </option>
            </select>
        ';
    }

    public function save_order_note_type_meta( $post_id, $post ) {
        // Return if the user doesn't have edit permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
        // Verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times.
        if ( ! isset( $_POST['wont_gyrix_note_type'] ) || ! wp_verify_nonce( $_POST['nonce_keyword'], basename(__FILE__) ) ) {
            return $post_id;
        }
        // Now that we're authenticated, time to save the data.
        // This sanitizes the data from the field and saves it into an array $events_meta.
        $events_meta['wont_gyrix_note_type'] = esc_textarea( $_POST['wont_gyrix_note_type'] );
        // Cycle through the $events_meta array.
        // Note, in this example we just have one item, but this is helpful if you have multiple.
        foreach ( $events_meta as $key => $value ) :
            // Don't store custom data twice
            if ( 'revision' === $post->post_type ) {
                return;
            }
            if ( get_post_meta( $post_id, $key, false ) ) {
                // If the custom field already has a value, update it.
                update_post_meta( $post_id, $key, $value );
            } else {
                // If the custom field doesn't have a value, add it.
                add_post_meta( $post_id, $key, $value);
            }
            if ( ! $value ) {
                // Delete the meta key if there's no value
                delete_post_meta( $post_id, $key );
            }
        endforeach;
    }

    public function change_text_of_publish ( $translation, $text ) {
        global $current_screen;
        if('wont_gyrix_templates' == $current_screen->post_type) {
            if ($text == 'Publish') {
                return 'Save';
            }
        }    
        return $translation;        
    }

    public function wont_gyrix_add_column($columns) {
        
        $date = $columns['date'];
        unset( $columns['date'] );
        $columns['note_type'] = 'Note Type';
        $columns['date'] = $date;
        return $columns;
    }

    public function manage_custom_column($column, $post_id) {
        echo ucfirst(get_post_meta( $post_id , 'wont_gyrix_note_type' , true ));
    }

    public function wont_gyrix_add_filter() {
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
         //only add filter to post type you want
        if ('wont_gyrix_templates' == $type){
            //change this to the list of values you want to show
            //in 'label' => 'value' format
            $values = array(
                'Customer Notes' => 'customer', 
                'Private Notes'  => 'private'
            );
            ?>
            <select name="NOTE_TYPE">
                <option value=""><?php _e('All Notes', 'wose45436'); ?></option>
            <?php
                $current_v = isset($_GET['NOTE_TYPE'])? $_GET['NOTE_TYPE']:'';
                foreach ($values as $label => $value) {
                    printf
                        (
                            '<option value="%s"%s>%s</option>',
                            $value,
                            $value == $current_v? ' selected="selected"':'',
                            $label
                        );
                    }
            ?>
            </select>
            <?php
        }
    }

    public function wont_gyrix_filter($query) {
        global $pagenow;
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ( 'wont_gyrix_templates' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['NOTE_TYPE']) && $_GET['NOTE_TYPE'] != '') {
            $query->query_vars['meta_key'] = 'wont_gyrix_note_type';
            $query->query_vars['meta_value'] = $_GET['NOTE_TYPE'];
        }
    }

    public function wont_gyrix_remove_date_filter() {
        $screen = get_current_screen();
        if('wont_gyrix_templates' == $screen->post_type) {
            add_filter('months_dropdown_results', '__return_empty_array');
        }
    }

    public function wont_gyrix_add_default_term( $post_id, $post, $update ) {
        if ( 'wont_gyrix_templates' == $post->post_type ) {
            if ( empty( wp_get_post_terms( $post_id, 'folder' ) ) ) {
                $term = term_exists( 'general', 'folder' );
                // If general term does not exist create it
                if ( $term === 0 || $term === null ) {
                    wp_insert_term(
                        'General', // the term 
                        'folder', // the taxonomy
                        array(
                        'description'=> 'This is description of general category',
                        'slug' => 'general'
                        )
                    );
                }
                wp_set_object_terms( $post_id, 'general', 'folder' );
            }
        }
    }

    public function wont_gyrix_add_category_filter() {
        global $typenow;
        $post_type = 'wont_gyrix_templates'; // change to your post type
        $taxonomy  = 'folder'; // change to your taxonomy
        if ($typenow == $post_type) {
            $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => __("Show All {$info_taxonomy->label}"),
                'taxonomy'        => $taxonomy,
                'name'            => $taxonomy,
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => true,
            ));
        }
    }

    public function wont_gyrix_convert_id_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'wont_gyrix_templates'; // change to your post type
        $taxonomy  = 'folder'; // change to your taxonomy
        $q_vars    = &$query->query_vars;
        if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
}