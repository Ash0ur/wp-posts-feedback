<?php

$post_types = self::get_all_posts_types();

do_action( WPF_PREFIX . '_feedback_settings_submit' );

$feedback_settings           = self::get_settings();
$feedback_title              = $feedback_settings['feedback_title'];
$feedback_form_title         = $feedback_settings['feedback_form_title'];
$after_feedback_submit_title = $feedback_settings['after_feedback_submit_title'];
$recaptcha_key               = $feedback_settings['recaptcha_key'];
$recaptcha_secret            = $feedback_settings['recaptcha_secret'];
$recaptcha_status            = $feedback_settings['recaptcha_status'];
$feedback_form_option        = $feedback_settings['feedback_form_option'];
$feedback_thumb_up_color     = $feedback_settings['feedback_thumb_up_color'];
$feedback_thumb_down_color   = $feedback_settings['feedback_thumb_down_color'];
$feedback_posts_types        = (array) $feedback_settings['feedback_posts_types'];
$feedback_custom_css         = json_decode( $feedback_settings['feedback_custom_css'] );
?>
<style>
	.feedback-title { display: inline-grid;}
	.feedback-main-title { display: none; }
	.feedback-main-title.active { display: inline-block; }
	.thumb-wrapper { position: relative; }
	.thumb-wrapper .dashicons-thumb { position: absolute; left: 250px; top: 0px; font-size: 2.5em;}
</style>
<div class="wrap">
	<form method="post" action="" >
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( WPF_PREFIX . '-feedback-settings-nonce' ); ?>" />
		<input type="hidden" name="submit-type" value="feedback-settings" />

		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="feedback-main-title">Feedback Title</label>
					</th>
					<td>
						<span class="feedback-title">
							<select name="feedback-post-type-title" id="feedback-post-type-title">
							<?php foreach ( $post_types as $post_type_name => $post_type ) : ?>
								<option value="<?php echo esc_html( $post_type_name ); ?>"><?php echo esc_html( $post_type ); ?></option>
							<?php endforeach; ?>
							</select>
						</span>
						<span class="feedback-title">
						<?php foreach ( $post_types as $post_type_name => $post_type ) : ?>
							<input type="text" class="regular-text feedback-main-title <?php echo ( $post_type_name === reset( $post_types ) ) ? 'active' : ''; ?>" id="feedback-title-<?php echo esc_html( $post_type_name ); ?>"  name="feedback-title[<?php echo esc_html( $post_type_name ); ?>]" value="<?php echo esc_html( $feedback_title[ $post_type_name ] ); ?>" />
						<?php endforeach; ?>
						</span>

						<span class="description">Select Post type and add Feedback title for each one</span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="feedback-form-title">Feedback Form Title</label>
					</th>
					<td>
						<p>
							<input type="text" id="feedback-form-title" name="feedback-form-title" class="regular-text"  value="<?php echo esc_html( $feedback_form_title ); ?>" />
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="feedback-form-title">After Feedback Form Submit Title</label>
					</th>
					<td>
						<p>
							<input type="text" id="after-feedback-submit-title" name="after-feedback-submit-title" class="regular-text"  value="<?php echo esc_html( $after_feedback_submit_title ); ?>" />
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="feedback-form-option">Include name and email fields in the Feedback Form</label>
					</th>
					<td>
						<p>
							<input type="checkbox" id="feedback-form-option" name="feedback-form-option" <?php echo ( (int) $feedback_form_option ? 'checked' : '' ); ?> />
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label>Add Recaptcha to Feedback Form</label>
					</th>
					<td>
						<fieldset>
							<ul>
								<li>
									<label>
										<input type="checkbox" name="recaptcha-status" <?php echo ( (int) $recaptcha_status ? 'checked' : '' ); ?> />
										Enable/Disable Recaptcha
									</label>
								</li>
								<li>
									<label>
										<span style="margin-right:16px;" >Recaptcha Key</span>
										<input type="text" name="recaptcha-key"  class="regular-text" value="<?php echo esc_html( $recaptcha_key ); ?>" />
									</label>
								</li>
								<li>
									<label>
										<span>Recaptcha Secret</span>
										<input type="text" name="recaptcha-secret" class="regular-text"  value="<?php echo esc_html( $recaptcha_secret ); ?>" />
									</label>
								</li>
							</ul>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th>
						<label for="feedback-thumbs">Thumbs Color</label>
					</th>
					<td>
						<fieldset>
							<p class="thumb-wrapper" >
								<input id="feedback-thumbs-up" class="color-field" name="feedback-thumb-up-color" type="text" value="" data-default-color="<?php echo esc_attr( $feedback_thumb_up_color ); ?>" />
								<span class="dashicons wpf-up dashicons-thumb dashicons-thumbs-up" <?php echo ! empty( $feedback_thumb_up_color ) ? "style=color:" . esc_attr( $feedback_thumb_up_color ) : ''; ?> ></span>

							</p>
							<p class="thumb-wrapper" >
								<input id="feedback-thumbs-down" class="color-field" name="feedback-thumb-down-color" type="text" value="" data-default-color="<?php echo esc_attr( $feedback_thumb_down_color ); ?>" />
								<span class="dashicons wpf-down  dashicons-thumb dashicons-thumbs-down" <?php echo ! empty( $feedback_thumb_down_color ) ? "style=color:" . esc_attr( $feedback_thumb_down_color ) : ''; ?> ></span>

							</p>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th>
						<label for="feedback-pages">Which post type to show the Feedback for?</label>
					</th>
					<td>
						<fieldset>
							<p>
							<?php foreach ( $post_types as $post_type ) : ?>
								<label>
									<input type="checkbox" name="feedback-post-type[]" value="<?php echo $post_type;  ?>"  <?php echo( in_array( $post_type, array_keys( $feedback_posts_types ) ) ? 'checked' : ''); ?> />
									<?php echo $post_type; ?>
								</label>
								<br/>
							<?php endforeach; ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<label for="feedback-form-option">Custom CSS</label>
					</th>
					<td>
						<p>
						<textarea id="feedback-custom-css" name="feedback-custom-css" style="width: 100%; min-height: 150px;" ><?php
							$custom_css = wp_kses( $feedback_custom_css, array( '\'', '\"' ) );
							$custom_css = str_replace ( '&gt;', '>', $custom_css );
							echo $custom_css;
						?></textarea>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit" >
			<input type="submit" id="submit" class="button button-primary" name="submit" value="Save Changes" />
		</p>
	</form>
</div>
