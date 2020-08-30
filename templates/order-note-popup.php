<?php
/**
* 
*/
class wont_gyrix_display_popup
{	
	function __construct()
	{
		$this->wont_gyrix_loadScript();
	}
	function wont_gyrix_loadScript()
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-dialog');
	}

	function wont_gyrix_get_template_categories() {
		$terms = get_terms( array( 
		    'taxonomy' => 'folder',
	        'parent'   => 0
		));
		return $terms;
	}

	function wont_gyrix_display_popup($templates)
	{	
		$term_obj_list = $this->wont_gyrix_get_template_categories();
		$template_categories = wp_list_pluck($term_obj_list, 'name', 'slug');
		$templates_json = json_encode($templates);
		?>
		<script type="text/javascript">
			var templates = <?php echo $templates_json; ?>;
			$( document ).ready(function() {
			   	$('#template_category').on('change', function(e) {
					// clear data of Order note content
					$('textarea#add_order_note_text').html("");
					$("textarea#add_order_note_text").val("");

					var selectedCategory = this.value;
					
					var filteredTemplates = templates.filter(function(template){
						if(selectedCategory == 'all') return true;
						if(template.folder.indexOf(selectedCategory) > -1 ) {
							return true;
						} else {
							return false;
						}
					});
					
					var templateHtml = '<option value="">Please select the order note type</option>';
					filteredTemplates.forEach(function(item, index){
						
						var itemType;
						if(item.type === 'customer') {
							itemType = 'customer';
						} else {
							itemType = 'private';
						}
						templateHtml += '<option value="'+ itemType +'" id="' + item.Id + '">' + item.title +'</option>';
					});

					$('#add_order_note_type').html(templateHtml);

				});
			});
		</script>
		<div id="dialog" title="<?php _e('Add Order Note', 'woocommerce'); ?>" style="display:none;">
		
	    <div class="add_note" style="border:none;">  
	    <form method="POST" name="add_notes">  
	        <p>
	            <label>Customer Name:</label>
	            <input type="text" name="customer_name" id="customer_name" style="width:250px;" readonly="readonly" required>
	            <input type="hidden" id = "added_note_type" value="">
	        </p>
	        <p>
	            <label>Order Id:</label>
	            <input type="text" name="order_id" id="order_post_id" style="width:250px;" readonly="readonly" required>
	        </p>
	        <p>
	            <label>Filter templates by category</label>
	            <?php if($templates) { ?>
	            <select name="template_category" id="template_category" style="width:250px;">
	            	<option value="all">All</option>
	                <?php if($template_categories)
	                    {
	                      foreach($template_categories as $slug => $template_category) : ?>
	                      <option value="<?php echo $slug ?>"><?php echo esc_html($template_category); ?></option>
	                      <?php endforeach; 
	                    }
	                ?> 
	            </select>
	            <?php } ?>
	        </p>
	        <p>
	            <label>Order Note Type:</label>
	            <select name="order_note_type" id="add_order_note_type" style="width:250px;" required>
	            	<option value="">Please select the order note type</option>
	                <?php if($templates) 
	                    {
	                      foreach($templates as $index => $template) : ?>
	                      <option value="<?php if($template['type'] == 'customer') echo 'customer'; else echo'private'; ?>" id="<?php echo $template['Id'];?>"><?php echo esc_html($template["title"]); ?></option>
	                      <?php endforeach; 
	                    }
	                    else
	                    { ?>
	                        <option value="customer" id="0">Customer Note</option>
	                        <option value="private" id="1">Private Note</option>
	                    <?php } ?> 
	            </select>	
	            <span class="error_note" style="display: none;">* Required field.</span>           
	        </p>
	        <p>
	            <label>Order Note:</label>		            
	            <textarea type="text" name="order_note" id="add_order_note_text" class="input-text" cols="20" rows="6" required></textarea>
	        	<span class="error_note_txt" style="display: none;">* Required field.</span>
	        </p>
	        <p id="gyrix_default_content">
	            <?php if($templates)
	            {
	                foreach($templates as $index => $template) : ?>
	                <textarea type="text" id="<?php echo 'gyrix_content-'.$template[Id]; ?>" required><?php echo esc_textarea($template["content"]); ?></textarea>
	                <?php endforeach;
	            }?> 
	        </p>		        
        </form>
	    </div>	
	    <div class="wont_overlay_note" style="display:none;"></div>
	    <span ><img style="display:none;" class ="wont_img_loader" src="<?php echo WONT_GYRIXTEMPLATEURL; ?>admin/image/loading_icon.gif" ></span>    
	</div>
	<?php
	}
}