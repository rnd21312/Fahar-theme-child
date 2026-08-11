<?php
/**
 * Minimal Fahar site footer boundary.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="fahar-site-footer" role="contentinfo">
	<div class="fahar-container">
		<p>
			<?php
			printf(
				/* translators: 1: current year, 2: site name. */
				esc_html__( '© %1$s %2$s', 'fahar-theme-child' ),
				esc_html( wp_date( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
</footer>
