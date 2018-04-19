<?php
/**
 * Remove content form template
 */
?>
<div class="cdi-remove-form">
	<div class="cdi-remove-form__message">
		<?php
			esc_html_e( 'Please, enter your WordPress user password to confirm and start content replacing.', 'jet-data-importer' );
		?>
		<span class="cdi-remove-form__note"><?php
			esc_html_e( 'NOTE: All your content will be replaced (posts, pages, comments, attachments and terms)', 'jet-data-importer' );
		?></span>
	</div>
	<div class="cdi-remove-form__controls">
		<input type="password" class="cdi-remove-form__input" placeholder="<?php esc_html_e( 'Please, enter your password', 'jet-data-importer' ); ?>">
		<button class="cdi-btn primary" data-action="remove-content"><span class="text"><?php
				esc_html_e( 'Import Content', 'jet-data-importer' );
			?></span><span class="cdi-loader-wrapper-alt"><span class="cdi-loader-alt"></span></span></button>
	</div>
	<div class="cdi-remove-form__notices cdi-hide">
	</div>
</div>