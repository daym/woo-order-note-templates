<?php

class Wont_gyrix_get_template_html
{
	public function wont_gyrix_header_template()
	{
		?>
			<div id="main-template-page">
			<h2>Order Note Settings</h2>
			<button class="add_order_note" >Add new order note template</button>
			<span class="note">*click on - icon on the left of note template to delete template.</span>
			<form method="post" name="update_template" id="update_template">
				<div class="main_template_block">
		<?php
	}
	public function wont_gyrix_footer_template()
	{
		?>
			</div>
			</form>
			<button class="save_template" style="margin-top:10px;">Save</button>
			</div>
		<?php
	}
	public function wont_gyrix_add_new_template()
	{
		?>
		<div class="template_notes div_block">
			<span class="dashicons dashicons-minus delete_current_template" style = 'content: "\f460";'></span>
			<div class="template_div">
					<input name ="post_id" value="0" type="hidden" class = "post_id" required/>
					<input class="template_title" required/>
					<select class="note_type" required>
						<option value = "customer">Customer Note</option>
						<option value = "private">Private Note</option>
					</select>
					<textarea class="template_block_area" required></textarea>
				
			</div>
		</div>
		<?php
	}
	public function wont_gyrix_show_template($templates)
	{
		foreach ($templates as $template ) : ?>
		<div class="template_notes div_block">
			<span class="dashicons dashicons-minus delete_current_template" style = 'content: "\f460";'></span>
			<div class="template_div">
					<input name ="post_id" value="<?php echo $template['Id']; ?>" type="hidden" class = "post_id" required/>
					<input class="template_title" value = "<?php echo esc_html($template['title']); ?>" required/>
					<select class="note_type" required>
					
					<?php if( esc_html($template["type"]) == "customer" ) 
					{	
						echo '<option value = "customer" selected>Customer Note</option>'; 
						echo '<option value = "private" >Private Note</option>';
					}
					else
					{
						echo '<option value = "customer" >Customer Note</option>';
						echo '<option value = "private" selected>Private Note</option>';
					}
					?>
					</select>
				
					<textarea class="template_block_area" required><?php echo esc_textarea( $template['content'] ); ?></textarea>
						
			</div>
		</div>

		<?php endforeach;
	}
}