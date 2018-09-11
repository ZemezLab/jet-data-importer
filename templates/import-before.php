<?php
/**
 * Starter import template
 */
?>
<div>
	<?php
		/**
		 * Hook before importer messages output.
		 *
		 * @hooked Jet_Data_Importer_Interface::check_server_params - 10;
		 */
		do_action( 'cherry-data-importer/before-messages' );

	?>
	<?php echo jdi_interface()->get_welcome_message(); ?>
	<?php if ( jdi_interface()->is_advanced_import() ) : ?>
		<?php jdi_interface()->advanced_import(); ?>
	<?php else : ?>
	<div class="cdi-actions">
		<?php echo jdi_interface()->get_import_files_select( '<div class="cdi-file-select">', '</div>' ); ?>
		<?php if ( 1 <= jdi_interface()->get_xml_count() && jdi()->get_setting( array( 'xml', 'use_upload' ) ) ) {
			echo '<span class="cdi-delimiter">' . __( 'or', 'jet-data-importer' ) . '</span>';
		} ?>
		<?php echo jdi_interface()->get_import_file_input( '<div class="cdi-file-upload">', '</div>' ); ?>
	</div>
	<input type="hidden" name="referrer" value="<?php echo jdi_tools()->get_page_url(); ?>">
	<button id="jet-import-start" class="cdi-btn primary">
		<span class="dashicons dashicons-download"></span>
		<?php esc_html_e( 'Start import', 'jet-data-importer' ); ?>
	</button>
	<?php endif; ?>
</div>
