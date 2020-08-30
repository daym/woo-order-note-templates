<?php
/*
update data to database 
*/
class wont_GyrixSaveNoteTemplate
{
	public function wont_gyrix_save_templates()
	{
		check_ajax_referer( 'saveGyrixTemplates'.get_current_user_id(), 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die(0);
		}
		if(isset($_POST['template_title']) )
		{
			$id = $_POST['post_id'];
			$title = $_POST['template_title'];
			$text = '';
			if(isset($_POST['template_text']))
				$text = $_POST['template_text'];
			$type = '';
			if(isset($_POST['note_type']))
				$type = $_POST['note_type'];
			$result = '';
			foreach ($title as $key => $value) 
			{
				if(intval($id[$key]) === 0)
				{
					$my_post = array(
						  'post_title'    => sanitize_text_field($title[$key]),
						  'post_name'     => sanitize_text_field($title[$key]),
						  'post_content'  => trim(stripslashes($text[$key])),
						  'post_type'     => sanitize_text_field('wont_gyrix_templates')
						);
						 
						// Insert the post into the database
					$post_id = wp_insert_post( $my_post );
					$typeNote = sanitize_text_field($type[$key]);
					add_post_meta($post_id, 'wont_gyrix_note_type', $typeNote);
				}
				else
				{
					$my_post = array(
					    'ID'           => intval($id[$key]),
					    'post_title'   => $title[$key],
					    'post_content' => $text[$key],
					  );
					// Update the post into the database
					wp_update_post( sanitize_post($my_post));	
					$typeNote = sanitize_text_field($type[$key]);
					update_post_meta( sanitize_text_field($id[$key]), 'wont_gyrix_note_type', $typeNote);
				}				
			}
		}
		// check if any note to delete
		if(isset($_POST['note_delete']))
		{
			foreach ($_POST['note_delete'] as $delete_note) 
			{
				delete_post_meta ( intval($delete_note), sanitize_text_field('wont_gyrix_note_type'));
				wp_delete_post(intval($delete_note));
			}
		}
		die("updated");
	}
	function wont_gyrix_add_note() 
	{
		check_ajax_referer( 'addGyrinotes'.get_current_user_id(), 'security' );
	    global $woocommerce;
	    if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die(0);
		}
	    $post_id  = 0;
	    if(isset($_POST['post_id']))
	    	$post_id  = intval($_POST['post_id']);
	    $note = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );
	    $note_type = '';
	    if(isset($_POST['note_type']))
	    	$note_type  = $_POST['note_type'];

	    //email is based on this flag if this flag value is 1 then
	    //customer will notify with the note via email
	    $is_customer_note = 0;
	    if($note_type == 'customer')
	    {
	        $is_customer_note = 1;
	    }
	    
	    if ( $post_id > 0 ) 
	    {
	      
	        $order = new WC_Order( $post_id );
	        $comment_id = $order->add_order_note( $note, $is_customer_note );
	    }	    
	    // Quit out
	    die(json_encode($post_id));
		
	}
	public function wont_gyrix_customer_name()
	{		
		check_ajax_referer( 'getCustomerName'.get_current_user_id(), 'security' );
	    $order_id  = intval($_POST['post_id']);	    
	    $customer_name = get_post_meta( $order_id, "_billing_first_name", true )." ".get_post_meta( $order_id, "_billing_last_name", true );
	    die(json_encode($customer_name));
	} 
}


