<div class="wrap dummy-images">
	<h1 class="wp-heading-inline"><?php _e( 'Dummy Images', DUMMY_IMAGES_SLUG ); ?></h1>
	<a href="#" class="page-title-action open-create-area"><?php _e( 'Create New', DUMMY_IMAGES_SLUG ) ?></a>

	<div class="notice-area"></div>

	<div class="create-area">
		<form method="POST" id="create-dummy-form" action="#">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php _e( 'Image Size', DUMMY_IMAGES_SLUG ); ?> <span class="required">*</span></th>
						<td>
							<input type="number" name="image-size-x" placeholder="<?php _e( 'Width', DUMMY_IMAGES_SLUG ); ?>" min="1" required />
							<span>x</span>
							<input type="number"  name="image-size-y" placeholder="<?php _e( 'Height', DUMMY_IMAGES_SLUG ); ?>" min="1" required />
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Background Color', DUMMY_IMAGES_SLUG ); ?></th>
						<td>
							<input name="image-bg-color" value="#000000" class="regular-text color-picker-field" />
							<p class="description"><?php _e( 'Default value: Black', DUMMY_IMAGES_SLUG ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Text', DUMMY_IMAGES_SLUG ); ?></th>
						<td>
							<input name="image-text" placeholder="<?php _e( 'Example: Dummy Image 300x250', DUMMY_IMAGES_SLUG ); ?>" class="regular-text" />
							<p class="description"><?php _e( 'Default value: "Width x Height" (The entered values above)', DUMMY_IMAGES_SLUG ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Text Color', DUMMY_IMAGES_SLUG ); ?></th>
						<td>
							<input name="image-text-color" value="#FFFFFF" class="regular-text color-picker-field" />
							<p class="description"><?php _e( 'Default value: White', DUMMY_IMAGES_SLUG ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="submit" class="button button-primary" value="<?php _e( 'Generate Dummy Image', DUMMY_IMAGES_SLUG ); ?>" />
			</p>
		</form>
	</div>

	<p><?php _e( 'If you want to generate new dummy image, use the <strong>"Create New"</strong> button above.', DUMMY_IMAGES_SLUG ); ?></p>
	<p><?php _e( 'All images, created by the Dummy Images plugin and uploaded in the media library, are listed below:', DUMMY_IMAGES_SLUG ); ?></p>
	<hr class="wp-header-end">

	<div class="dummy-images-listing"></div>
	
	<div class="more-container">
		<button class="button button-primary button-large more-button" data-page="1">Load More</button>
	</div>
</div>