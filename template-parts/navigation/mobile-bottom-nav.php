<?php
/**
 * Accessible mobile bottom navigation.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_has_mobile_menu = has_nav_menu( 'fahar-mobile-bottom' );
$fahar_navigation_items = $fahar_has_mobile_menu ? array() : fahar_theme_get_mobile_navigation_items();

if ( ! $fahar_has_mobile_menu && empty( $fahar_navigation_items ) ) {
	return;
}
?>
<nav class="fahar-mobile-nav" aria-label="<?php esc_attr_e( 'ناوبری موبایل', 'fahar-theme-child' ); ?>">
	<div class="fahar-mobile-nav__inner">
		<?php if ( $fahar_has_mobile_menu ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'fahar-mobile-bottom',
					'container'      => false,
					'menu_class'     => 'fahar-mobile-nav__list',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_before'    => '<span class="fahar-mobile-nav__label">',
					'link_after'     => '</span>',
				)
			);
			?>
		<?php else : ?>
			<ul class="fahar-mobile-nav__list">
				<?php foreach ( $fahar_navigation_items as $fahar_navigation_key => $fahar_navigation_item ) : ?>
					<?php
					$label  = isset( $fahar_navigation_item['label'] ) ? (string) $fahar_navigation_item['label'] : '';
					$url    = isset( $fahar_navigation_item['url'] ) ? (string) $fahar_navigation_item['url'] : '';
					$active = ! empty( $fahar_navigation_item['active'] );
					$icon   = isset( $fahar_navigation_item['icon'] ) ? sanitize_key( $fahar_navigation_item['icon'] ) : '';

					if ( '' === $label || '' === $url ) {
						continue;
					}
					?>
					<li class="fahar-mobile-nav__item">
						<a class="fahar-mobile-nav__link" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"<?php if ( $active ) : ?> aria-current="page"<?php endif; ?>>
							<?php echo fahar_theme_get_mobile_navigation_icon_markup( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup comes from a fixed internal allowlist. ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</nav>
<?php unset( $fahar_has_mobile_menu, $fahar_navigation_items, $fahar_navigation_key, $fahar_navigation_item, $label, $url, $active, $icon ); ?>
