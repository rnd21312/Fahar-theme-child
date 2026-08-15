<?php
/**
 * Expandable portfolio description.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_description_post = isset( $args['post'] ) ? get_post( $args['post'] ) : get_post();

if ( ! $fahar_description_post instanceof WP_Post || ! fahar_theme_is_portfolio_post( $fahar_description_post ) ) {
	return;
}

$fahar_description = fahar_theme_get_portfolio_description_content( $fahar_description_post );

if ( '' === $fahar_description ) {
	return;
}

$fahar_description_content_id = wp_unique_id( 'fahar-portfolio-description-content-' );
?>
<section
	class="fahar-portfolio-description"
	data-fahar-description
	aria-label="<?php esc_attr_e( 'توضیحات پروژه', 'fahar-theme-child' ); ?>"
>
	<div
		id="<?php echo esc_attr( $fahar_description_content_id ); ?>"
		class="fahar-portfolio-description__content"
		data-fahar-description-content
	>
		<?php echo $fahar_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered through the_content, stripped of media, and passed through a post HTML allowlist. ?>
	</div>

	<button
		type="button"
		class="fahar-portfolio-description__toggle"
		data-fahar-description-toggle
		data-label-collapsed="<?php esc_attr_e( 'خواندن بیشتر', 'fahar-theme-child' ); ?>"
		data-label-expanded="<?php esc_attr_e( 'نمایش کمتر', 'fahar-theme-child' ); ?>"
		aria-expanded="true"
		aria-controls="<?php echo esc_attr( $fahar_description_content_id ); ?>"
		hidden
	>
		<?php esc_html_e( 'خواندن بیشتر', 'fahar-theme-child' ); ?>
	</button>
</section>
<?php
unset(
	$fahar_description_post,
	$fahar_description,
	$fahar_description_content_id
);
