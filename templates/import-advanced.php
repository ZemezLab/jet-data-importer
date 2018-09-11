<?php
/**
 * Advanced import template
 */
$item = ! empty( jdi_interface()->data['advanced-item'] ) ? jdi_interface()->data['advanced-item'] : false;
$skin = ! empty( jdi_interface()->data['advanced-slug'] ) ? jdi_interface()->data['advanced-slug'] : false;

if ( ! $item || ! $skin ) {
	return;
}

$thumb    = ! empty( $item['thumb'] )    ? esc_url( $item['thumb'] )    : false;
$label    = ! empty( $item['label'] )    ? $item['label']               : false;
$demo_url = ! empty( $item['demo_url'] ) ? esc_url( $item['demo_url'] ) : false;
$plugins  = ! empty( $item['plugins'] )  ? $item['plugins']             : false;
$xml_full = ! empty( $item['full'] )     ? $item['full']                : false;
$xml_min  = ! empty( $item['lite'] )     ? $item['lite']                : false;

$full_path   = jdi_tools()->secure_path( $xml_full );
$min_path    = jdi_tools()->secure_path( $xml_min );
$allow_types = jdi()->get_setting( array( 'import', 'allow_types' ) );

?>
<div class="advanced-item"
	data-full="<?php echo $full_path; ?>"
	data-lite="<?php echo $min_path; ?>"
	data-skin="<?php echo $skin; ?>"
>
	<div class="advanced-item__thumb">
		<?php
			if ( $thumb ) {
				printf( '<a href="%3$s"><img src="%1$s" alt="%2$s"></a>', $thumb, $label, $demo_url );
			}
		?>
	</div>
	<div class="advanced-item__content">
		<h3 class="advanced-item__title"><?php echo $label; ?></h3>
		<?php if ( ! empty( $plugins ) ) : ?>
		<div class="advanced-item__recommended-plugins"><?php
			esc_html_e( 'Recommended Plugins:', 'jet-data-importer' );
		?></div>
		<div class="advanced-item__plugins-list"><?php
			foreach ( $plugins as $slug => $name ) {
				$plugin = sprintf( '%1$s/%1$s.php', $slug );
				printf(
					'<span class="advanced-item__plugin %2$s">%1$s</span>',
					$name,
					is_plugin_active( $plugin ) ? 'is-active' : 'is-inactive'
				);
			}
		?></div>
		<?php endif; ?>
		<?php if ( $allow_types ) : ?>
		<div class="advanced-item__install-type">
			<label class="advanced-item__type-checkbox">
				<input type="checkbox"><?php esc_html_e( 'Optimize Demo Content', 'jet-data-importer' ); ?>
			</label>
			<?php esc_html_e( 'Please select this option to install light version of demo content. Recommended for slow severs and shared web hosts', 'jet-data-importer' ); ?>
		</div>
		<?php endif; ?>
		<div class="advanced-item__install">
			<button class="cdi-btn primary" data-action="start-install"><span class="text"><?php
				esc_html_e( 'Install Demo', 'jet-data-importer' );
			?></span><span class="cdi-loader-wrapper-alt"><span class="cdi-loader-alt"></span></span></button>
			<a href="<?php echo $demo_url; ?>" target="_blank" class="cdi-btn"><?php
				esc_html_e( 'View Demo', 'jet-data-importer' );
			?></a>
		</div>
	</div>
</div>
