<?php
/**
 * Native WordPress comments presentation.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

$fahar_comment_count = get_comments_number();
?>
<section id="comments" class="fahar-portfolio-comments">
	<header class="fahar-portfolio-comments__header">
		<h2 class="fahar-portfolio-comments__title">
			<?php
			if ( $fahar_comment_count ) {
				printf(
					/* translators: %s: Number of comments. */
					esc_html( _n( '%s دیدگاه', '%s دیدگاه', $fahar_comment_count, 'fahar-theme-child' ) ),
					esc_html( number_format_i18n( $fahar_comment_count ) )
				);
			} else {
				esc_html_e( 'دیدگاه‌ها', 'fahar-theme-child' );
			}
			?>
		</h2>
	</header>

	<?php if ( have_comments() ) : ?>
		<ol class="fahar-comment-list">
			<?php
			wp_list_comments(
				array(
					'avatar_size' => 44,
					'short_ping'  => true,
					'style'       => 'ol',
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'دیدگاه‌های قبلی', 'fahar-theme-child' ),
				'next_text' => esc_html__( 'دیدگاه‌های بعدی', 'fahar-theme-child' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<?php
		comment_form(
			array(
				'class_container' => 'comment-respond fahar-comment-respond',
				'class_form'      => 'comment-form fahar-comment-form',
				'class_submit'    => 'submit fahar-comment-form__submit',
				'label_submit'    => esc_html__( 'ارسال دیدگاه', 'fahar-theme-child' ),
				'title_reply'     => esc_html__( 'دیدگاه خود را بنویسید', 'fahar-theme-child' ),
			)
		);
		?>
	<?php elseif ( $fahar_comment_count ) : ?>
		<p class="fahar-portfolio-comments__closed"><?php esc_html_e( 'ارسال دیدگاه بسته است.', 'fahar-theme-child' ); ?></p>
	<?php endif; ?>
</section>
<?php
unset( $fahar_comment_count );
