<?php
/*
Hook list
*/
class wont_gyrix_order_note_manager
{
	private $my_plugin_screen_name;
    
    private static $instance;

    static function wont_get_instance()
	{	
		if(current_user_can('edit_shop_orders') ) {      
		    if (!isset(self::$instance))
		    {
		        self::$instance = new self();
		    }
		    return self::$instance;
		}	
	}

	public function __construct() {
		if(current_user_can('edit_shop_orders') )
		{
			$this->wont_init_plugin();
		}

	}

	public function wont_load_files()
	{
		if(current_user_can('edit_shop_orders') ) {
		   	include_once (WONT_GYRIXTEMPLATEPATH.'includes/order-note-loader.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH.'admin/inc/order-note-save.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH.'admin/inc/order-note-view.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH."templates/order-note-popup.php");
		   	include_once(WONT_GYRIXTEMPLATEPATH."templates/order-note-templates.php");
		}
	} 
	public function wont_init_plugin()
	{
		if(current_user_can('edit_shop_orders') )
		{
			$this->wont_load_files();
			$gyrixhook = new wont_gyrix_order_note_manager_load;
			add_action('admin_notices', array($gyrixhook,'wont_gyrix_admin_notices'));
	    	add_action( 'admin_enqueue_scripts', array($gyrixhook, 'wont_gyrixenqueue_styles' ));
	    	add_action( 'admin_enqueue_scripts', array($gyrixhook, 'wont_gyrixenqueue_jscript' ));
	    	add_filter( 'woocommerce_admin_order_actions',array($gyrixhook, 'wont_gyrix_order_actions'), 10, 3 );
	    	add_action( 'admin_head',array($gyrixhook, 'wont_gyrix_admin_order_actions_end'), 10, 1 );
	    	add_filter ('the_content',  'wpautop');				
	    	$gyrixnote = new wont_GyrixSaveNoteTemplate;
			add_action('wp_ajax_wont_save_templates',array($gyrixnote , 'wont_gyrix_save_templates' ));
			add_action('wp_ajax_wont_gyrix_customer_name',array($gyrixnote , 'wont_gyrix_customer_name' ));
			add_action('wp_ajax_wont_gyrix_add_note',array($gyrixnote , 'wont_gyrix_add_note' ));
			// Hide extra metaboxes like revolutionary slider
			add_filter('add_meta_boxes', array($gyrixhook, 'wont_gyrix_remove_meta_boxes' ));

			// Remove default editor
			add_action( 'init', array($gyrixhook, 'wont_gyrix_remove_default_editor'), 99);

			// Add textarea after title of post to save post content
			add_action( 'edit_form_after_title', array($gyrixhook, 'wont_gyrix_add_textarea_after_title') );

			// Add metabox for order note tlype            
			add_action('add_meta_boxes', array($gyrixhook , 'wont_gyrix_add_note_type_metabox'));

			// Save metabox fields
			add_action( 'save_post', array($gyrixhook , 'save_order_note_type_meta'), 1, 2 );

			// Change text of the publish button to save
			add_filter( 'gettext', array($gyrixhook , 'change_text_of_publish'), 10, 2 );

			// Add note type column
			add_filter( 'manage_wont_gyrix_templates_posts_columns', array($gyrixhook , 'wont_gyrix_add_column') );

			// Add data to note type column
			add_action( 'manage_wont_gyrix_templates_posts_custom_column' , array($gyrixhook , 'manage_custom_column'), 10, 2 );

			// Add filter to the table
			add_action( 'restrict_manage_posts', array($gyrixhook , 'wont_gyrix_add_filter') );

			// Make filter work
			add_filter( 'parse_query', array($gyrixhook , 'wont_gyrix_filter') );

			// Remove date filter
			add_action('admin_head', array($gyrixhook , 'wont_gyrix_remove_date_filter'));

			// Add default term ( general )
			add_action( 'wp_insert_post',  array($gyrixhook , 'wont_gyrix_add_default_term'), 10, 3 );

			// Add category filter
			add_action( 'restrict_manage_posts',  array($gyrixhook , 'wont_gyrix_add_category_filter') );

			// Add category filter
			add_filter('parse_query', array($gyrixhook , 'wont_gyrix_convert_id_to_term_in_query'));
		}
	}
	public function wont_gyrix_register_cpt()
	{	

		if(current_user_can('edit_shop_orders') ) {
			$labels = array(
		        'name'          => 'Order Note Template',
		        'singular_name' => 'Order Note Template',
		        'add_new_item'  => 'Add New Template',
		        'search_items'  => 'Search Templates',
		        'edit_item'     => 'Edit Template',
		        'all_items'     => 'All Templates',
		        'new_item'      => 'New Template',
		        'view_item'     => 'View Template',
		        'add_new'       => 'Add Template'
		    );

	        $args = array(
		        'labels'        => $labels,
		        'public'        => false,
		        'show_ui'       => true,
		        'show_in_menu'  => true,
		        'menu_position' => 5,
		        'rewrite' 		=> false,
		        'taxonomies'    => array( 'folder' ),
		    );  
		    register_post_type( "wont_gyrix_templates", $args );
		    register_taxonomy_for_object_type( 'folder', 'wont_gyrix_templates' );
		}
	}

	// Register custom taxonomy
	public function wont_gyrix_register_taxonomy() {
		$labels = array(
            'name'              => 'Category',
            'singular_name'     => 'Category',
            'search_items'      => 'Search Category',
            'all_items'         => 'All Categorys',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category',
            'menu_name'         => 'Folders'
        );
        // register taxonomy
        register_taxonomy(
            'folder',
            'wont_gyrix_templates',
            array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => true
            )
        );
	}
}
