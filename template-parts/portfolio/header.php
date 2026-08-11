<?php
/**
 * Reusable Single Portfolio title and actions row.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_header_post = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $fahar_header_post instanceof WP_Post || ! fahar_theme_is_portfolio_post( $fahar_header_post ) ) {
	return;
}

$fahar_header_title = trim( get_the_title( $fahar_header_post ) );

if ( '' === $fahar_header_title ) {
	$fahar_header_title = __( 'نمونه‌کار بدون عنوان', 'fahar-theme-child' );
}

$fahar_header_type        = fahar_theme_get_portfolio_type( $fahar_header_post );
$fahar_header_type_labels = array(
	'website'     => __( 'وب‌سایت', 'fahar-theme-child' ),
	'image'       => __( 'تصویر', 'fahar-theme-child' ),
	'video'       => __( 'ویدیو', 'fahar-theme-child' ),
	'single-page' => __( 'پروژه', 'fahar-theme-child' ),
);
$fahar_header_type_label  = isset( $fahar_header_type_labels[ $fahar_header_type ] ) ? $fahar_header_type_labels[ $fahar_header_type ] : '';
$fahar_header_terms       = array();
$fahar_header_taxonomy    = '';
$fahar_header_tax_label   = '';

foreach ( fahar_theme_get_portfolio_taxonomies() as $fahar_header_taxonomy_slug ) {
	$fahar_taxonomy_terms = fahar_theme_get_portfolio_terms( $fahar_header_post, $fahar_header_taxonomy_slug );

	if ( ! $fahar_taxonomy_terms ) {
		continue;
	}

	$fahar_header_taxonomy = $fahar_header_taxonomy_slug;
	$fahar_header_terms    = array_slice( $fahar_taxonomy_terms, 0, 3 );
	break;
}

if ( $fahar_header_taxonomy ) {
	$fahar_taxonomy_object = get_taxonomy( $fahar_header_taxonomy );

	if ( $fahar_taxonomy_object instanceof WP_Taxonomy ) {
		$fahar_header_tax_label = $fahar_taxonomy_object->labels->singular_name;
	}
}

$fahar_header_external_url = fahar_theme_get_portfolio_external_url( $fahar_header_post );
$fahar_header_has_meta     = (bool) ( $fahar_header_type_label || $fahar_header_terms );
?>
<header class="fahar-portfolio-header">
	<div class="fahar-portfolio-header__main">
		<h1 class="fahar-portfolio-header__title"><?php echo esc_html( $fahar_header_title ); ?></h1>

		<?php if ( $fahar_header_has_meta ) : ?>
			<div class="fahar-portfolio-header__meta">
				<?php if ( $fahar_header_type_label ) : ?>
					<span class="fahar-portfolio-header__type"><?php echo esc_html( $fahar_header_type_label ); ?></span>
				<?php endif; ?>

				<?php if ( $fahar_header_terms ) : ?>
					<div class="fahar-portfolio-header__terms">
						<?php if ( $fahar_header_tax_label ) : ?>
							<span class="fahar-portfolio-header__taxonomy-label"><?php echo esc_html( $fahar_header_tax_label ); ?></span>
						<?php endif; ?>

						<?php foreach ( $fahar_header_terms as $fahar_header_term ) : ?>
							<span class="fahar-portfolio-header__term"><?php echo esc_html( $fahar_header_term->name ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $fahar_header_external_url ) : ?>
		<div class="fahar-portfolio-actions">
			<div class="fahar-portfolio-actions__primary">
				<a
					class="fahar-portfolio-action fahar-portfolio-action--external"
					href="<?php echo esc_url( $fahar_header_external_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'مشاهده پروژه', 'fahar-theme-child' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
</header>
<?php
unset(
	$fahar_header_post,
	$fahar_header_title,
	$fahar_header_type,
	$fahar_header_type_labels,
	$fahar_header_type_label,
	$fahar_header_terms,
	$fahar_header_taxonomy,
	$fahar_header_tax_label,
	$fahar_header_taxonomy_slug,
	$fahar_taxonomy_terms,
	$fahar_taxonomy_object,
	$fahar_header_external_url,
	$fahar_header_has_meta,
	$fahar_header_term
);
