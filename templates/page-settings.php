<?php

$cache_handler = get_option( 'jdi_cache_handler', 'session' );

?>
<div class="cdi-wrap">
	<form method="post">
		<label><?php _e( 'Cache handler:', 'jet-data-importer' ); ?></label><br>
		<select name="jdi_cache_handler" style="min-height: 34px; margin: 5px 0 0 0; width: 300px;"><?php
			foreach ( array( 'session', 'file' ) as $val ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					$val,
					selected( $cache_handler, $val, false ),
					ucfirst( $val )
				);
			}
		?></select>
		<br><br>
		<button type="submit" class="cdi-btn primary"><?php _e( 'Save', 'jet-data-importer' ); ?></button>
	</form>
</div>