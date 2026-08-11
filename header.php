<?php
/**
 * Theme header entry point.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( ! fahar_theme_is_elementor_canvas_template() ) : ?>
	<a class="fahar-screen-reader-text" href="#content">
		<?php esc_html_e( 'رفتن به محتوای اصلی', 'fahar-theme-child' ); ?>
	</a>
<?php endif; ?>

<?php if ( ! fahar_theme_render_elementor_location( 'header' ) && ! fahar_theme_is_elementor_canvas_template() ) : ?>
	<?php get_template_part( 'template-parts/header/site-header' ); ?>
<?php endif; ?>
