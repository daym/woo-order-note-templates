<?php
/*
get template data	
*/
class wont_gyrix_order_note_view
{
	public function wont_gyrix_get_note_template()
	{
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die(0);
		}
		$post_data = array();
		$template_array = array();
		$return = false;
		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'post_title',
			'order'            => 'ASC',
			'post_type'        => 'wont_gyrix_templates'
		);
		$the_query = new WP_Query( $args );
		$posts = $the_query->get_posts();
		$post_data = array();
		if(count($posts))
		{
			foreach($posts as $post) :
				// get category of post/ template
				$term_obj_list = get_the_terms( $post->ID, 'folder' );
				$folder = wp_list_pluck($term_obj_list, 'slug');
				// folder is the custom texonmy
				$post_data[] = array('Id'=>intval($post->ID),
									'title' => $post->post_title, 
									'content' => $post->post_content, 
									'type' => get_post_meta($post->ID,"wont_gyrix_note_type",true),
									'folder' => $folder
								);
			endforeach;
			return $post_data;
		}		
		return(false);
	}

	public function wont_gyrix_order_add_note_on_view()
	{

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die(0);
		}
            $newOptions=''; 
            $postID = $_GET['post'];
            if(isset($postID)&&!empty($postID)) 
            {
                $args = array(
                    'posts_per_page'   => -1,
                    'orderby'          => 'post_title',
                    'order'            => 'ASC',
                    'post_type'        => 'wont_gyrix_templates'
                );
                $the_query = new WP_Query( $args );
                
                $posts = $the_query->get_posts();
                $post_data = array();
                if(count($posts))
                {
                    foreach($posts as $post) :
                        $post_data[] = array('Id'=>intval($post->ID),
                                            'title' => $post->post_title, 
                                            'content' => $post->post_content, 
                                            'type' => get_post_meta($post->ID,"wont_gyrix_note_type",true)
                                        );
                    endforeach;

                    foreach ($post_data as $post_data_value) :
                        if(esc_html($post_data_value['type']=='customer'))
                        {
                            $value = 'value ="'.esc_html($post_data_value['type']).'"';
                        }
                        else
                        {
                            $value = 'value';
                        }
                        $newOptions .='<option id="'.$post_data_value['Id'].'" title="'.esc_html($post_data_value['title']).'"  content="'.esc_textarea(str_replace(array("\n","\r\n","\r"), '<br>', $post_data_value['content'])).'" '.$value.'>'.esc_html($post_data_value['title']).'</option>';
                    endforeach;  
                }   
            }              
	?>
	<script>
	jQuery(document).ready(function() {    
	    var newOption = '<?php echo $newOptions; ?>';
	    jQuery("#order_note_type").append(newOption);
	    jQuery("#order_note_type").change(function() {
	       var orderNote =  jQuery(this).find("option:selected").attr('content');
	       jQuery("textarea#add_order_note").val(orderNote.replace(/<br\s*\/?>/mg,"\n"));
	    });
	});
	</script>
	<?php
	}
}	