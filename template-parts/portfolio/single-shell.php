<?php
/**
 * Single Portfolio structural shell.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_single_post = isset( $args['post'] ) ? get_post( $args['post'] ) : get_post();

if ( ! $fahar_single_post instanceof WP_Post || ! fahar_theme_portfolio_has_single_permalink( $fahar_single_post ) ) {
	return;
}

?>
<article <?php post_class( 'fahar-portfolio-single__article fahar-container fahar-container--wide', $fahar_single_post->ID ); ?>>
	<nav class="fahar-portfolio-single__back" aria-label="<?php esc_attr_e( 'ناوبری نمونه‌کار', 'fahar-theme-child' ); ?>">
		<a href="<?php echo esc_url( fahar_theme_get_portfolio_url() ); ?>" data-fahar-back-to-explore>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
			<?php esc_html_e( 'بازگشت به کاوش', 'fahar-theme-child' ); ?>
		</a>
	</nav>

	<div class="fahar-portfolio-single__layout">
		<section class="fahar-portfolio-single__media" aria-label="<?php esc_attr_e( 'رسانه نمونه‌کار', 'fahar-theme-child' ); ?>">
			<?php get_template_part( 'template-parts/portfolio/media', null, array( 'post' => $fahar_single_post ) ); ?>
		</section>

		<div class="fahar-portfolio-single__content">
			<?php get_template_part( 'template-parts/portfolio/header', null, array( 'post' => $fahar_single_post ) ); ?>

			<?php get_template_part( 'template-parts/portfolio/description', null, array( 'post' => $fahar_single_post ) ); ?>
		</div>
	</div>

	<?php
	$fahar_related_posts = fahar_theme_get_related_portfolios( $fahar_single_post );
	get_template_part( 'template-parts/portfolio/related', null, array( 'posts' => $fahar_related_posts ) );
	?>

	<?php get_template_part( 'template-parts/portfolio/comments', null, array( 'post' => $fahar_single_post ) ); ?>
</article>
<?php
unset(
	$fahar_single_post,
	$fahar_related_posts
);
