<?php
/**
 * Single-category and contextual-tag Explore navigation.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_filter_categories         = isset( $args['categories'] ) && is_array( $args['categories'] ) ? $args['categories'] : array();
$fahar_filter_tags               = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();
$fahar_filter_all_categories_url = isset( $args['all_categories_url'] ) && is_string( $args['all_categories_url'] ) ? $args['all_categories_url'] : '';
$fahar_filter_all_tags_url       = isset( $args['all_tags_url'] ) && is_string( $args['all_tags_url'] ) ? $args['all_tags_url'] : '';
$fahar_filter_reset_url          = isset( $args['reset_url'] ) && is_string( $args['reset_url'] ) ? $args['reset_url'] : '';
$fahar_filter_has_category       = ! empty( $args['has_category'] );
$fahar_filter_has_tag            = ! empty( $args['has_tag'] );
$fahar_filter_active_count       = isset( $args['active_count'] ) ? absint( $args['active_count'] ) : 0;
$fahar_filter_instance           = wp_unique_id( 'fahar-portfolio-filters-' );
$fahar_filter_panel_id           = $fahar_filter_instance . '-panel';
$fahar_filter_title_id           = $fahar_filter_instance . '-title';
$fahar_filter_body_id            = $fahar_filter_instance . '-body';
$fahar_filter_label              = $fahar_filter_active_count
	? sprintf(
		/* translators: %s: number of active filters. */
		__( 'فیلترها · %s', 'fahar-theme-child' ),
		number_format_i18n( $fahar_filter_active_count )
	)
	: __( 'فیلترها', 'fahar-theme-child' );

if ( ! $fahar_filter_categories ) {
	return;
}

$fahar_render_category_options = static function ( $categories, $instance, $depth = 0, $list_id = '' ) use ( &$fahar_render_category_options ) {
	if ( ! $categories ) {
		return;
	}
	?>
	<ul<?php echo $list_id ? ' id="' . esc_attr( $list_id ) . '"' : ''; ?> class="fahar-filter-options<?php echo $depth ? ' fahar-filter-options--nested' : ''; ?>" data-fahar-category-list>
		<?php foreach ( $categories as $category ) : ?>
			<?php
			$children    = isset( $category['children'] ) && is_array( $category['children'] ) ? $category['children'] : array();
			$children_id = $instance . '-children-' . absint( $category['id'] );
			$expanded    = ! empty( $category['expanded'] );
			?>
			<li class="fahar-filter-category<?php echo $children ? ' has-children' : ''; ?>">
				<div class="fahar-filter-category__row">
					<a class="fahar-filter-option" href="<?php echo esc_url( $category['url'] ); ?>" <?php if ( ! empty( $category['selected'] ) ) : ?>aria-current="page"<?php endif; ?>>
						<span class="fahar-filter-option__label"><?php echo esc_html( $category['label'] ); ?></span>
					</a>
					<?php if ( $children ) : ?>
						<button class="fahar-filter-category__toggle" type="button" aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $children_id ); ?>" aria-label="<?php echo esc_attr( sprintf( $expanded ? __( 'پنهان‌کردن زیرمجموعه‌های %s', 'fahar-theme-child' ) : __( 'نمایش زیرمجموعه‌های %s', 'fahar-theme-child' ), $category['label'] ) ); ?>" data-expand-label="<?php echo esc_attr( sprintf( __( 'نمایش زیرمجموعه‌های %s', 'fahar-theme-child' ), $category['label'] ) ); ?>" data-collapse-label="<?php echo esc_attr( sprintf( __( 'پنهان‌کردن زیرمجموعه‌های %s', 'fahar-theme-child' ), $category['label'] ) ); ?>" data-fahar-category-toggle>
							<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
						</button>
					<?php endif; ?>
				</div>
				<?php $fahar_render_category_options( $children, $instance, $depth + 1, $children_id ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
};
?>
<div class="fahar-explore-discovery" data-fahar-filter-root>
	<button class="fahar-button fahar-button--secondary fahar-filter-trigger" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $fahar_filter_panel_id ); ?>" data-fahar-filter-trigger hidden>
		<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
		<span><?php echo esc_html( $fahar_filter_label ); ?></span>
	</button>

	<details class="fahar-filter-disclosure" data-fahar-filter-disclosure>
		<summary class="fahar-filter-fallback-trigger">
			<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
			<span><?php echo esc_html( $fahar_filter_label ); ?></span>
		</summary>

		<div class="fahar-filter-backdrop" aria-hidden="true"></div>
		<section id="<?php echo esc_attr( $fahar_filter_panel_id ); ?>" class="fahar-filter-panel" aria-labelledby="<?php echo esc_attr( $fahar_filter_title_id ); ?>" data-fahar-filter-panel>
			<header class="fahar-filter-panel__header">
				<h2 id="<?php echo esc_attr( $fahar_filter_title_id ); ?>"><?php esc_html_e( 'فیلتر نمونه‌کارها', 'fahar-theme-child' ); ?></h2>
				<button class="fahar-button fahar-button--ghost fahar-button--icon fahar-filter-close" type="button" aria-label="<?php esc_attr_e( 'بستن فیلترها', 'fahar-theme-child' ); ?>" data-fahar-filter-close hidden>
					<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
				</button>
			</header>

			<div id="<?php echo esc_attr( $fahar_filter_body_id ); ?>" class="fahar-filter-panel__body" data-fahar-filter-panel-body>
				<nav class="fahar-filter-group" aria-labelledby="<?php echo esc_attr( $fahar_filter_instance ); ?>-categories">
					<h3 id="<?php echo esc_attr( $fahar_filter_instance ); ?>-categories" class="fahar-filter-group__legend"><?php esc_html_e( 'دسته‌بندی‌ها', 'fahar-theme-child' ); ?></h3>
					<ul class="fahar-filter-options">
						<li>
							<a class="fahar-filter-option" href="<?php echo esc_url( $fahar_filter_all_categories_url ); ?>" <?php if ( ! $fahar_filter_has_category ) : ?>aria-current="page"<?php endif; ?>>
								<span><?php esc_html_e( 'همه دسته‌بندی‌ها', 'fahar-theme-child' ); ?></span>
							</a>
						</li>
					</ul>
					<?php $fahar_render_category_options( $fahar_filter_categories, $fahar_filter_instance ); ?>
				</nav>

				<?php if ( $fahar_filter_has_category && $fahar_filter_tags ) : ?>
					<nav class="fahar-filter-group" aria-labelledby="<?php echo esc_attr( $fahar_filter_instance ); ?>-tags">
						<h3 id="<?php echo esc_attr( $fahar_filter_instance ); ?>-tags" class="fahar-filter-group__legend"><?php esc_html_e( 'برچسب‌ها', 'fahar-theme-child' ); ?></h3>
						<ul class="fahar-filter-options fahar-filter-options--tags">
							<li>
								<a class="fahar-filter-option" href="<?php echo esc_url( $fahar_filter_all_tags_url ); ?>" <?php if ( ! $fahar_filter_has_tag ) : ?>aria-current="page"<?php endif; ?>>
									<span><?php esc_html_e( 'همه برچسب‌ها', 'fahar-theme-child' ); ?></span>
								</a>
							</li>
							<?php foreach ( $fahar_filter_tags as $fahar_filter_tag ) : ?>
								<li>
									<a class="fahar-filter-option" href="<?php echo esc_url( $fahar_filter_tag['url'] ); ?>" <?php if ( ! empty( $fahar_filter_tag['selected'] ) ) : ?>aria-current="page"<?php endif; ?>>
										<span><?php echo esc_html( $fahar_filter_tag['label'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>

				<?php if ( $fahar_filter_active_count && $fahar_filter_reset_url ) : ?>
					<a class="fahar-filter-reset" href="<?php echo esc_url( $fahar_filter_reset_url ); ?>"><?php esc_html_e( 'پاک‌کردن فیلترها', 'fahar-theme-child' ); ?></a>
				<?php endif; ?>
			</div>
		</section>
	</details>
</div>
<?php
unset(
	$fahar_filter_categories,
	$fahar_filter_tags,
	$fahar_filter_all_categories_url,
	$fahar_filter_all_tags_url,
	$fahar_filter_reset_url,
	$fahar_filter_has_category,
	$fahar_filter_has_tag,
	$fahar_filter_active_count,
	$fahar_filter_instance,
	$fahar_filter_panel_id,
	$fahar_filter_title_id,
	$fahar_filter_body_id,
	$fahar_filter_label,
	$fahar_render_category_options,
	$fahar_filter_category,
	$fahar_filter_tag
);
