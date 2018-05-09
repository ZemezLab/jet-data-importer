<?php
/**
 * Starter import template
 */
?>
<div>
	<div class="cdi-success-mesage"><?php
		_e( 'Don’t know from where to start? Let us help you!', 'jet-data-importer' );
	?></div>
	<div class="cdi-success-links"><?php

		$buttons  = jdi()->get_setting( array( 'success-links' ) );
		$buttons  = apply_filters( 'jet-data-importer/success-buttons', $buttons );
		$format   = '<a href="%4$s" class="cdi-btn %2$s" target="%3$s">%1$s</a>';
		$defaults = array(
			'label'  => __( 'View your site', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_self',
			'icon'   => 'dashicons-admin-home',
			'desc'   => __( 'See what you get', 'jet-data-importer' ),
			'url'    => home_url( '/' ),
		);

		if ( ! empty( $buttons ) ) {
			echo '<div class="cdi-after-actions">';
			foreach ( $buttons as $button ) {
				$button = wp_parse_args( $button, $defaults );
				?>
				<div class="cdi-after-actions__row">
					<div class="cdi-after-actions__desc">
						<span class="dashicons <?php echo $button['icon']; ?>"></span>
						<span><?php echo $button['desc']; ?></span>
					</div>
					<?php printf( $format, $button['label'], $button['type'], $button['target'], $button['url'] ); ?>
				</div>
				<?php
			}
			echo '</div>';
		}

	?></div>
</div>