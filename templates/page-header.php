<?php
/**
 * Page heading template
 */

$ref_class = isset( $_GET['referrer'] ) ? 'ref-' . sanitize_html_class( $_GET['referrer'], 'default' ) : 'default';

?>
<div class="wrap">
	<div class="cdi-kit <?php echo $ref_class; ?>">
