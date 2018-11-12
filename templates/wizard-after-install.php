<?php
/**
 * Template part for displaying advanced popup
 */

if ( function_exists( 'jet_plugins_wizard_interface' ) ) {
	$skin     = jet_plugins_wizard_interface()->get_skin_data( 'slug' );
	$referrer = 'jet-plugins-wizard';
} elseif ( function_exists( 'tm_wizard_interface' ) ) {
	$skin     = tm_wizard_interface()->get_skin_data( 'slug' );
	$referrer = 'tm-wizard';
} elseif ( function_exists( 'cherry_plugin_wizard_interface' ) ) {
	$skin     = cherry_plugin_wizard_interface()->get_skin_data( 'slug' );
	$referrer = 'cherry-plugin-wizard';
}

$type = ! empty( $_GET['type'] ) ? esc_attr( $_GET['type'] ) : 'lite';

$file = jdi()->get_setting( array( 'advanced_import', $skin, $type ) );
$file = jdi_tools()->secure_path( $file );


?>
<h2><?php esc_html_e( 'We\'re almost there!', 'jet-data-importer' ); ?></h2>

<?php esc_html_e( 'We are ready to install demo data. Do you want to append demo content to your existing content or completely rewrite it?', 'jet-data-importer' ); ?>
<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
	<div class="tm-wizard-type__select">
		<label class="tm-wizard-type__item">
			<input type="radio" name="type" value="append" checked>
			<span class="tm-wizard-type__item-mask"></span>
			<span class="tm-wizard-type__item-label">
				<span class="tm-wizard-type__item-label-title"><?php
					esc_html_e( 'Append demo content to my existing content', 'jet-data-importer' );
				?></span>
				<span class="tm-wizard-type__item-label-desc"><?php
					esc_html_e( 'If you have chosen this option, the sample data will be added to the current content of your theme.', 'jet-data-importer' );
				?></span>
			</span>
		</label>
		<label class="tm-wizard-type__item">
			<input type="radio" name="type" value="replace">
			<span class="tm-wizard-type__item-mask"></span>
			<span class="tm-wizard-type__item-label">
				<span class="tm-wizard-type__item-label-title"><?php
					esc_html_e( 'Replace my existing content with demo content', 'jet-data-importer' );
				?></span>
				<span class="tm-wizard-type__item-label-desc"><?php
					esc_html_e( 'NB! If you want to install theme demo content, you agree that your current data will be replaced by the new demo content (sample data). If you want to save the current content of your theme, please choose Skip Data Installation.', 'jet-data-importer' );
				?></span>
			</span>
		</span>

		</label>
		<label class="tm-wizard-type__item">
			<input type="radio" name="type" value="skip">
			<span class="tm-wizard-type__item-mask"></span>
			<span class="tm-wizard-type__item-label">
				<span class="tm-wizard-type__item-label-title"><?php
					esc_html_e( 'Skip demo content installation', 'jet-data-importer' );
				?></span>
				<span class="tm-wizard-type__item-label-desc"><?php
					esc_html_e( 'If you have chosen this option, the sample data will not be installed on your theme and your current content will stay as it is.', 'jet-data-importer' );
				?></span>
			</span>
		</label>
	</div>
	<input type="hidden" name="tab" value="import">
	<input type="hidden" name="step" value="2">
	<input type="hidden" name="file" value="<?php echo $file; ?>">
	<input type="hidden" name="page" value="<?php echo jdi()->slug; ?>">
	<input type="hidden" name="referrer" value="<?php echo $referrer; ?>">
	<input type="hidden" name="skin" value="<?php echo $skin; ?>">
	<input type="hidden" name="xml_type" value="<?php echo $type; ?>">
	<button class="btn btn-primary" data-wizard="confirm-install" data-loader="true" data-href=""><span class="text"><?php
		esc_html_e( 'Next', 'jet-data-importer' );
	?></span><span class="tm-wizard-loader"><span class="tm-wizard-loader__spinner"></span></span></button>
</form>