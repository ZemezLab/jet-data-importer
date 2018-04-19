<?php wp_enqueue_script( 'jet-data-export' ); ?>
<div class="cdi-wrap">
	<div class="cdi-message"><?php
		echo jdi()->get_setting( array( 'export', 'message' ) );
	?></div>
	<a href="<?php echo jdi_export_interface()->get_export_url(); ?>" class="cdi-btn primary" id="jet-export">
		<span class="dashicons dashicons-upload"></span>
		<?php _e( 'Export', 'jet-data-importer' ); ?>
	</a>
	<div class="cdi-loader cdi-hidden"></div>
</div>