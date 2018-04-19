<?php
/**
 * Template part for displaying advanced popup
 */
?>
<div class="cdi-advanced-popup popup-hidden">
	<div class="cdi-advanced-popup__content">
		<h3><?php esc_html_e( 'Attention!', 'jet-data-importer' ); ?></h3>
		<?php esc_html_e( 'We are ready to install demo data. Do you want to append demo content to your existing content or completely rewrite it?', 'jet-data-importer' ); ?>
		<div class="cdi-advanced-popup__select">
			<label class="cdi-advanced-popup__item">
				<input type="radio" name="install-type" value="append" checked>
				<span class="cdi-advanced-popup__item-mask"></span>
				<span class="cdi-advanced-popup__item-label"><?php
					esc_html_e( 'Append demo content to my existing content', 'jet-data-importer' );
				?></span>
			</label>
			<label class="cdi-advanced-popup__item">
				<input type="radio" name="install-type" value="replace">
				<span class="cdi-advanced-popup__item-mask"></span>
				<span class="cdi-advanced-popup__item-label"><?php
					esc_html_e( 'Replace my existing content with demo content', 'jet-data-importer' );
				?></span>

			</label>
		</div>
		<div class="cdi-advanced-popup__warning cdi-hide">
			<b><?php esc_html_e( 'NOTE:', 'jet-data-importer' ); ?></b>
			<?php esc_html_e( 'This option will remove all your existing content - posts, pages, attachments, terms and comments', 'jet-data-importer' ); ?>
		</div>
		<div class="cdi-advanced-popup__action">
			<button class="cdi-btn primary" data-action="confirm-install"><span class="text"><?php
				esc_html_e( 'Start Install', 'jet-data-importer' );
			?></span><span class="cdi-loader-wrapper-alt"><span class="cdi-loader-alt"></span></span></button>
		</div>
		<button class="cdi-advanced-popup__close"><span class="dashicons dashicons-no"></span></button>
	</div>
</div>