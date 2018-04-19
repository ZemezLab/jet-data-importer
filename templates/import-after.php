<?php
/**
 * Starter import template
 */
?>
<div>
	<div class="cdi-success-mesage"><?php
		_e( 'Now you can visit your website, start customization… or install another skin ;)', 'jet-data-importer' );
	?></div>
	<div class="cdi-success-links"><?php

		$buttons  = jdi()->get_setting( array( 'success-links' ) );
		$format   = '<a href="%4$s" class="cdi-btn %2$s" target="%3$s">%1$s</a>';
		$defaults = array(
			'label'  => __( 'View your site', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_self',
			'url'    => home_url( '/' ),
		);

		if ( ! empty( $buttons ) ) {
			foreach ( $buttons as $button ) {
				$button = wp_parse_args( $button, $defaults );
				printf( $format, $button['label'], $button['type'], $button['target'], $button['url'] );
			}
		}

	?></div>
</div>