<?php
/**
 * Dedicated Single Portfolio template.
 *
 * Selected through the adapter-gated theme template router.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="fahar-app-shell">
	<main id="content" class="fahar-main fahar-portfolio-single">
		<?php while ( have_posts() ) : ?>
			<?php
			the_post();
			get_template_part( 'template-parts/portfolio/single-shell', null, array( 'post' => get_post() ) );
			?>
		<?php endwhile; ?>
	</main>
</div>
<?php
get_footer();
