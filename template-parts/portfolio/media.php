<?php
/**
 * Reusable secure Portfolio Media Renderer.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_media_post = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $fahar_media_post instanceof WP_Post || ! fahar_theme_is_portfolio_post( $fahar_media_post ) ) {
	return;
}

$fahar_media_items         = fahar_theme_get_portfolio_media_items( $fahar_media_post );
$fahar_media_count         = count( $fahar_media_items );
$fahar_media_is_slider     = $fahar_media_count > 1;
$fahar_media_viewport_id   = $fahar_media_is_slider ? wp_unique_id( 'fahar-portfolio-slider-viewport-' ) : '';
$fahar_media_counter_label = $fahar_media_is_slider
	? sprintf(
		/* translators: 1: current slide number, 2: total slide count. */
		__( 'اسلاید %1$s از %2$s', 'fahar-theme-child' ),
		number_format_i18n( 1 ),
		number_format_i18n( $fahar_media_count )
	)
	: '';
?>
<div
	class="fahar-portfolio-media<?php echo $fahar_media_is_slider ? ' fahar-portfolio-slider' : ''; ?>"
	<?php if ( $fahar_media_is_slider ) : ?>
		data-fahar-slider
		role="region"
		aria-label="<?php esc_attr_e( 'گالری رسانه نمونه‌کار', 'fahar-theme-child' ); ?>"
	<?php endif; ?>
>
	<?php if ( $fahar_media_items ) : ?>
		<div
			<?php if ( $fahar_media_viewport_id ) : ?>id="<?php echo esc_attr( $fahar_media_viewport_id ); ?>"<?php endif; ?>
			class="fahar-portfolio-slider__viewport"
			<?php if ( $fahar_media_is_slider ) : ?>tabindex="0"<?php endif; ?>
		>
			<div class="fahar-portfolio-slider__track">
				<?php foreach ( $fahar_media_items as $fahar_media_index => $fahar_media_item ) : ?>
					<?php
					$fahar_media_slide_label = sprintf(
						/* translators: 1: current slide number, 2: total slide count. */
						__( 'اسلاید %1$s از %2$s', 'fahar-theme-child' ),
						number_format_i18n( $fahar_media_index + 1 ),
						number_format_i18n( $fahar_media_count )
					);
					?>
					<div
						class="fahar-portfolio-slider__slide"
						<?php if ( $fahar_media_is_slider ) : ?>
							role="group"
							aria-roledescription="<?php esc_attr_e( 'اسلاید', 'fahar-theme-child' ); ?>"
							aria-label="<?php echo esc_attr( $fahar_media_slide_label ); ?>"
							<?php if ( 0 === $fahar_media_index ) : ?>aria-current="true"<?php endif; ?>
						<?php endif; ?>
					>
						<?php
						get_template_part(
							'template-parts/portfolio/media-item',
							null,
							array( 'item' => $fahar_media_item )
						);
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $fahar_media_is_slider ) : ?>
			<div class="fahar-portfolio-slider__controls" hidden>
				<button
					class="fahar-portfolio-slider__button fahar-portfolio-slider__prev"
					type="button"
					aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'fahar-theme-child' ); ?>"
					aria-controls="<?php echo esc_attr( $fahar_media_viewport_id ); ?>"
					disabled
				>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
				</button>
				<span
					class="fahar-portfolio-slider__counter"
					role="status"
					aria-live="polite"
					aria-atomic="true"
					aria-label="<?php echo esc_attr( $fahar_media_counter_label ); ?>"
					data-counter-template="<?php esc_attr_e( '%1$s / %2$s', 'fahar-theme-child' ); ?>"
					data-counter-aria-template="<?php esc_attr_e( 'اسلاید %1$s از %2$s', 'fahar-theme-child' ); ?>"
				>
					<?php
					printf(
						/* translators: 1: current slide number, 2: total slide count. */
						esc_html__( '%1$s / %2$s', 'fahar-theme-child' ),
						esc_html( number_format_i18n( 1 ) ),
						esc_html( number_format_i18n( $fahar_media_count ) )
					);
					?>
				</span>
				<button
					class="fahar-portfolio-slider__button fahar-portfolio-slider__next"
					type="button"
					aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'fahar-theme-child' ); ?>"
					aria-controls="<?php echo esc_attr( $fahar_media_viewport_id ); ?>"
				>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
				</button>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p class="fahar-portfolio-media__empty" role="status">
			<?php esc_html_e( 'رسانه‌ای برای این پروژه ثبت نشده است.', 'fahar-theme-child' ); ?>
		</p>
	<?php endif; ?>
</div>
<?php
unset(
	$fahar_media_post,
	$fahar_media_items,
	$fahar_media_count,
	$fahar_media_is_slider,
	$fahar_media_viewport_id,
	$fahar_media_counter_label,
	$fahar_media_index,
	$fahar_media_slide_label,
	$fahar_media_item
);
