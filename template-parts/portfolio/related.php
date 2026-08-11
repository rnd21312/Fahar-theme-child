<?php
/**
 * Related Portfolio two-column section.
 *
 * Accepts Task 21 results as a `posts` array through template-part args.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_related_posts = isset( $args['posts'] ) ? (array) $args['posts'] : array();
$fahar_related_posts = array_values(
	array_filter(
		$fahar_related_posts,
		function ( $post ) {
			return $post instanceof WP_Post;
		}
	)
);

if ( ! $fahar_related_posts ) {
	return;
}

$fahar_related_title_id = wp_unique_id( 'fahar-related-portfolios-title-' );
?>
<section class="fahar-related-portfolios" aria-labelledby="<?php echo esc_attr( $fahar_related_title_id ); ?>">
	<header class="fahar-related-portfolios__header">
		<h2 id="<?php echo esc_attr( $fahar_related_title_id ); ?>" class="fahar-related-portfolios__title">
			<?php esc_html_e( 'نمونه‌کارهای مرتبط', 'fahar-theme-child' ); ?>
		</h2>
	</header>

	<div class="fahar-related-portfolios__grid">
		<?php foreach ( $fahar_related_posts as $fahar_related_post ) : ?>
			<?php get_template_part( 'template-parts/portfolio/card', null, array( 'post' => $fahar_related_post ) ); ?>
		<?php endforeach; ?>
	</div>
</section>
<?php
unset(
	$fahar_related_posts,
	$fahar_related_post,
	$fahar_related_title_id
);
