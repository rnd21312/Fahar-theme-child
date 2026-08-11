<?php
/**
 * Portfolio results empty state.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_empty_search_term = isset( $args['search_term'] ) && is_string( $args['search_term'] ) ? trim( $args['search_term'] ) : '';
$fahar_empty_has_filters = ! empty( $args['has_filters'] );
?>
<div class="fahar-empty-state fahar-explore__empty" role="status">
	<?php if ( '' !== $fahar_empty_search_term && $fahar_empty_has_filters ) : ?>
		<h2>
			<?php
			printf(
				/* translators: %s: portfolio search term. */
				esc_html__( 'برای «%s» نمونه‌کار فیلترشده‌ای پیدا نشد', 'fahar-theme-child' ),
				esc_html( $fahar_empty_search_term )
			);
			?>
		</h2>
		<p><?php esc_html_e( 'هیچ نمونه‌کاری با این جستجو و فیلترهای انتخاب‌شده مطابقت ندارد.', 'fahar-theme-child' ); ?></p>
	<?php elseif ( '' !== $fahar_empty_search_term ) : ?>
		<h2>
			<?php
			printf(
				/* translators: %s: portfolio search term. */
				esc_html__( 'برای «%s» نمونه‌کاری پیدا نشد', 'fahar-theme-child' ),
				esc_html( $fahar_empty_search_term )
			);
			?>
		</h2>
		<p><?php esc_html_e( 'هیچ نمونه‌کاری با این جستجو مطابقت ندارد.', 'fahar-theme-child' ); ?></p>
	<?php elseif ( $fahar_empty_has_filters ) : ?>
		<h2><?php esc_html_e( 'نتیجه‌ای پیدا نشد', 'fahar-theme-child' ); ?></h2>
		<p><?php esc_html_e( 'هیچ نمونه‌کاری با فیلترهای انتخاب‌شده مطابقت ندارد.', 'fahar-theme-child' ); ?></p>
	<?php else : ?>
		<h2><?php esc_html_e( 'نمونه‌کاری پیدا نشد', 'fahar-theme-child' ); ?></h2>
		<p><?php esc_html_e( 'در حال حاضر نمونه‌کاری در دسترس نیست.', 'fahar-theme-child' ); ?></p>
	<?php endif; ?>
</div>
<?php unset( $fahar_empty_search_term, $fahar_empty_has_filters ); ?>
