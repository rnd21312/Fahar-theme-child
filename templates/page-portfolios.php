<?php
/**
 * Template Name: Fahar Portfolio Explore
 * Description: Server-rendered portfolio discovery with contextual filters.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_explore_page_id = get_queried_object_id();
$fahar_explore_title   = $fahar_explore_page_id ? get_the_title( $fahar_explore_page_id ) : '';
$fahar_explore_url     = $fahar_explore_page_id ? get_permalink( $fahar_explore_page_id ) : '';
$fahar_explore_url     = $fahar_explore_url ? $fahar_explore_url : fahar_theme_get_portfolio_url();

if ( '' === trim( $fahar_explore_title ) ) {
	$fahar_explore_title = __( 'نمونه‌کارها', 'fahar-theme-child' );
}

$fahar_search_input   = isset( $_GET['portfolio_search'] ) ? wp_unslash( $_GET['portfolio_search'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filtering.
$fahar_category_input = isset( $_GET['portfolio_category'] ) ? wp_unslash( $_GET['portfolio_category'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filtering.
$fahar_tag_input      = isset( $_GET['portfolio_tag'] ) ? wp_unslash( $_GET['portfolio_tag'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filtering.
$fahar_search_term    = is_scalar( $fahar_search_input ) ? trim( sanitize_text_field( (string) $fahar_search_input ) ) : '';
$fahar_category_slug  = is_scalar( $fahar_category_input ) ? sanitize_title( (string) $fahar_category_input ) : '';
$fahar_tag_slug       = is_scalar( $fahar_tag_input ) ? sanitize_title( (string) $fahar_tag_input ) : '';

$fahar_explore_post_type = fahar_theme_get_portfolio_post_type();
$fahar_category_taxonomy  = fahar_theme_get_explore_filter_taxonomy( 'category' );
$fahar_tag_taxonomy       = fahar_theme_get_explore_filter_taxonomy( 'tag' );
$fahar_category_terms     = array();
$fahar_selected_category = null;
$fahar_contextual_tags    = array();
$fahar_selected_tag      = null;

if (
	$fahar_explore_post_type
	&& taxonomy_exists( $fahar_category_taxonomy )
	&& is_object_in_taxonomy( $fahar_explore_post_type, $fahar_category_taxonomy )
) {
	$fahar_category_terms = get_terms(
		array(
			'taxonomy'   => $fahar_category_taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	$fahar_category_terms = is_wp_error( $fahar_category_terms ) ? array() : $fahar_category_terms;
}

if ( '' !== $fahar_category_slug && taxonomy_exists( $fahar_category_taxonomy ) ) {
	$fahar_category_candidate = get_term_by( 'slug', $fahar_category_slug, $fahar_category_taxonomy );

	if ( $fahar_category_candidate instanceof WP_Term ) {
		$fahar_selected_category = $fahar_category_candidate;
		$fahar_contextual_tags    = fahar_theme_get_contextual_portfolio_tags( $fahar_selected_category );
	} else {
		$fahar_category_slug = '';
	}
}

if ( $fahar_selected_category instanceof WP_Term && '' !== $fahar_tag_slug ) {
	foreach ( $fahar_contextual_tags as $fahar_contextual_tag ) {
		if ( $fahar_tag_slug === $fahar_contextual_tag->slug ) {
			$fahar_selected_tag = $fahar_contextual_tag;
			break;
		}
	}

	if ( ! $fahar_selected_tag instanceof WP_Term ) {
		$fahar_tag_slug = '';
	}
} else {
	$fahar_tag_slug = '';
}

$fahar_filter_url_args = array();

if ( $fahar_selected_category instanceof WP_Term ) {
	$fahar_filter_url_args['portfolio_category'] = $fahar_selected_category->slug;
}

if ( $fahar_selected_tag instanceof WP_Term ) {
	$fahar_filter_url_args['portfolio_tag'] = $fahar_selected_tag->slug;
}

$fahar_search_clear_url = $fahar_filter_url_args ? add_query_arg( $fahar_filter_url_args, $fahar_explore_url ) : $fahar_explore_url;
$fahar_filter_reset_url = '' !== $fahar_search_term ? add_query_arg( 'portfolio_search', $fahar_search_term, $fahar_explore_url ) : $fahar_explore_url;
$fahar_has_filters      = $fahar_selected_category instanceof WP_Term || $fahar_selected_tag instanceof WP_Term;
$fahar_active_count     = ( $fahar_selected_category instanceof WP_Term ? 1 : 0 ) + ( $fahar_selected_tag instanceof WP_Term ? 1 : 0 );
$fahar_state_base_args  = '' !== $fahar_search_term ? array( 'portfolio_search' => $fahar_search_term ) : array();
$fahar_category_links       = array();
$fahar_all_categories_url = $fahar_state_base_args ? add_query_arg( $fahar_state_base_args, $fahar_explore_url ) : $fahar_explore_url;

foreach ( $fahar_category_terms as $fahar_category_term ) {
	$fahar_category_args                       = $fahar_state_base_args;
	$fahar_category_args['portfolio_category'] = $fahar_category_term->slug;
	$fahar_category_links[]                    = array(
		'label'    => $fahar_category_term->name,
		'slug'     => $fahar_category_term->slug,
		'url'      => add_query_arg( $fahar_category_args, $fahar_explore_url ),
		'selected' => $fahar_selected_category instanceof WP_Term && $fahar_selected_category->term_id === $fahar_category_term->term_id,
	);
}

$fahar_tag_links    = array();
$fahar_all_tags_url = '';

if ( $fahar_selected_category instanceof WP_Term ) {
	$fahar_tag_base_args                       = $fahar_state_base_args;
	$fahar_tag_base_args['portfolio_category'] = $fahar_selected_category->slug;
	$fahar_all_tags_url                        = add_query_arg( $fahar_tag_base_args, $fahar_explore_url );

	foreach ( $fahar_contextual_tags as $fahar_contextual_tag ) {
		$fahar_tag_args                  = $fahar_tag_base_args;
		$fahar_tag_args['portfolio_tag'] = $fahar_contextual_tag->slug;
		$fahar_tag_links[]               = array(
			'label'    => $fahar_contextual_tag->name,
			'slug'     => $fahar_contextual_tag->slug,
			'url'      => add_query_arg( $fahar_tag_args, $fahar_explore_url ),
			'selected' => $fahar_selected_tag instanceof WP_Term && $fahar_selected_tag->term_id === $fahar_contextual_tag->term_id,
		);
	}
}

$fahar_page_input             = isset( $_GET['portfolio_page'] ) ? wp_unslash( $_GET['portfolio_page'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public pagination.
$fahar_explore_page           = is_scalar( $fahar_page_input ) ? absint( $fahar_page_input ) : 1;
$fahar_explore_page           = max( 1, min( 10000, $fahar_explore_page ) );
$fahar_partial_input          = isset( $_GET['fahar_explore_partial'] ) ? wp_unslash( $_GET['fahar_explore_partial'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only same-route partial response.
$fahar_is_partial_request     = is_scalar( $fahar_partial_input ) && '1' === sanitize_text_field( (string) $fahar_partial_input );
$fahar_posts_per_page         = absint( apply_filters( 'fahar_theme_explore_posts_per_page', 24 ) );
$fahar_posts_per_page         = $fahar_posts_per_page ? min( 100, $fahar_posts_per_page ) : 24;
$fahar_explore_query          = null;
$fahar_explore_next_url       = '';
$fahar_explore_query_args     = array(
	'post_type'           => $fahar_explore_post_type,
	'post_status'         => 'publish',
	'posts_per_page'      => $fahar_posts_per_page,
	'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
	'ignore_sticky_posts' => true,
	'paged'               => $fahar_explore_page,
	'no_found_rows'       => false,
);

if ( '' !== $fahar_search_term ) {
	$fahar_explore_query_args['s'] = $fahar_search_term;
}

if ( $fahar_selected_category instanceof WP_Term ) {
	$fahar_explore_query_args['tax_query'] = array(
		array(
			'taxonomy' => $fahar_category_taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $fahar_selected_category->term_id ),
		),
	);

	if ( $fahar_selected_tag instanceof WP_Term ) {
		$fahar_explore_query_args['tax_query'][] = array(
			'taxonomy' => $fahar_tag_taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $fahar_selected_tag->term_id ),
		);
	}
}

if ( $fahar_explore_post_type && post_type_exists( $fahar_explore_post_type ) ) {
	$fahar_explore_query = fahar_theme_query_portfolios( $fahar_explore_query_args );
}

if ( $fahar_explore_query instanceof WP_Query && $fahar_explore_page < (int) $fahar_explore_query->max_num_pages ) {
	$fahar_next_url_args = $fahar_filter_url_args;

	if ( '' !== $fahar_search_term ) {
		$fahar_next_url_args['portfolio_search'] = $fahar_search_term;
	}

	$fahar_next_url_args['portfolio_page'] = $fahar_explore_page + 1;
	$fahar_explore_next_url                = add_query_arg( $fahar_next_url_args, $fahar_explore_url );
}

if ( $fahar_is_partial_request ) {
	ob_start();

	if ( $fahar_explore_query instanceof WP_Query && $fahar_explore_query->have_posts() ) {
		get_template_part( 'template-parts/portfolio/feed-cards', null, array( 'query' => $fahar_explore_query ) );
	}

	$fahar_cards_html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json(
		array(
			'html'     => (string) $fahar_cards_html,
			'page'     => $fahar_explore_page,
			'next_url' => $fahar_explore_next_url,
			'has_more' => '' !== $fahar_explore_next_url,
		)
	);
}

$fahar_search_listbox_id = wp_unique_id( 'fahar-search-suggestions-' );

get_header();
?>
<div class="fahar-app-shell fahar-explore">
	<main id="content" class="fahar-main fahar-page fahar-container fahar-container--wide" data-fahar-explore>
		<header class="fahar-explore__header">
			<h1 class="fahar-explore__title"><?php echo esc_html( $fahar_explore_title ); ?></h1>
		</header>

		<div class="fahar-explore__layout">
			<aside class="fahar-explore__sidebar" aria-label="<?php esc_attr_e( 'جستجو و فیلتر نمونه‌کارها', 'fahar-theme-child' ); ?>">
				<div class="fahar-explore__sidebar-inner">
					<form
						class="fahar-explore-search"
						role="search"
						method="get"
						action="<?php echo esc_url( $fahar_explore_url ); ?>"
						data-fahar-search-suggest
						data-endpoint="<?php echo esc_url( rest_url( 'fahar/v1/portfolio-suggestions' ) ); ?>"
						data-loading-message="<?php esc_attr_e( 'در حال جستجو…', 'fahar-theme-child' ); ?>"
						data-empty-message="<?php esc_attr_e( 'نتیجه‌ای پیدا نشد', 'fahar-theme-child' ); ?>"
						data-error-message="<?php esc_attr_e( 'پیشنهادها در دسترس نیستند؛ جستجوی معمولی همچنان کار می‌کند.', 'fahar-theme-child' ); ?>"
					>
						<label class="screen-reader-text" for="fahar-explore-search-input">
							<?php esc_html_e( 'جستجو', 'fahar-theme-child' ); ?>
						</label>
						<div class="fahar-explore-search__field">
							<span class="fahar-explore-search__icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg>
							</span>
							<input
								id="fahar-explore-search-input"
								class="fahar-explore-search__input"
								type="search"
								name="portfolio_search"
								value="<?php echo esc_attr( $fahar_search_term ); ?>"
								placeholder="<?php esc_attr_e( 'جستجوی نمونه‌کارها…', 'fahar-theme-child' ); ?>"
								autocomplete="off"
								role="combobox"
								aria-autocomplete="list"
								aria-haspopup="listbox"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $fahar_search_listbox_id ); ?>"
								data-fahar-search-input
							>
							<?php if ( '' !== $fahar_search_term ) : ?>
								<a class="fahar-explore-search__clear" href="<?php echo esc_url( $fahar_search_clear_url ); ?>" aria-label="<?php esc_attr_e( 'پاک‌کردن جستجو', 'fahar-theme-child' ); ?>">
									<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="m7 7 10 10M17 7 7 17" /></svg>
								</a>
							<?php endif; ?>
						</div>
						<?php if ( $fahar_selected_category instanceof WP_Term ) : ?>
							<input type="hidden" name="portfolio_category" value="<?php echo esc_attr( $fahar_selected_category->slug ); ?>">
						<?php endif; ?>
						<?php if ( $fahar_selected_tag instanceof WP_Term ) : ?>
							<input type="hidden" name="portfolio_tag" value="<?php echo esc_attr( $fahar_selected_tag->slug ); ?>">
						<?php endif; ?>
						<p class="fahar-search-status" aria-live="polite" aria-atomic="true" data-fahar-search-status></p>
						<ul id="<?php echo esc_attr( $fahar_search_listbox_id ); ?>" class="fahar-search-suggestions" role="listbox" data-fahar-search-listbox hidden></ul>
					</form>

					<?php
					get_template_part(
						'template-parts/portfolio/filters',
						null,
						array(
							'categories'         => $fahar_category_links,
							'all_categories_url' => $fahar_all_categories_url,
							'tags'               => $fahar_tag_links,
							'all_tags_url'       => $fahar_all_tags_url,
							'has_category'       => $fahar_selected_category instanceof WP_Term,
							'has_tag'            => $fahar_selected_tag instanceof WP_Term,
							'active_count'       => $fahar_active_count,
							'reset_url'          => $fahar_filter_reset_url,
						)
					);
					?>
				</div>
			</aside>

			<section class="fahar-explore__results" aria-labelledby="fahar-explore-results-title">
				<h2 id="fahar-explore-results-title" class="screen-reader-text"><?php esc_html_e( 'نتایج نمونه‌کارها', 'fahar-theme-child' ); ?></h2>

				<?php if ( $fahar_explore_query instanceof WP_Query && $fahar_explore_query->have_posts() ) : ?>
					<div class="fahar-explore__feed" data-fahar-infinite-feed data-current-page="<?php echo esc_attr( $fahar_explore_page ); ?>" data-loading-message="<?php esc_attr_e( 'در حال بارگذاری نمونه‌کارهای بیشتر', 'fahar-theme-child' ); ?>" data-loaded-message="<?php esc_attr_e( '%d نمونه‌کار دیگر بارگذاری شد.', 'fahar-theme-child' ); ?>" data-error-message="<?php esc_attr_e( 'نمونه‌کارهای بیشتر بارگذاری نشد. دوباره تلاش کنید.', 'fahar-theme-child' ); ?>" data-end-message="<?php esc_attr_e( 'به پایان نمونه‌کارها رسیدید.', 'fahar-theme-child' ); ?>" data-retry-label="<?php esc_attr_e( 'تلاش دوباره', 'fahar-theme-child' ); ?>">
						<div class="fahar-explore__grid" data-fahar-masonry>
							<?php get_template_part( 'template-parts/portfolio/feed-cards', null, array( 'query' => $fahar_explore_query ) ); ?>
						</div>

						<?php if ( $fahar_explore_next_url ) : ?>
							<div class="fahar-explore-load" data-fahar-load-controls>
								<span class="fahar-explore-load__spinner" data-fahar-load-spinner aria-hidden="true"></span>
								<p class="fahar-explore-load__status" data-fahar-load-status aria-live="polite" aria-atomic="true"></p>
								<a class="fahar-button fahar-button--secondary fahar-explore-load__link" href="<?php echo esc_url( $fahar_explore_next_url ); ?>" data-fahar-load-more><?php esc_html_e( 'نمایش بیشتر', 'fahar-theme-child' ); ?></a>
							</div>
						<?php endif; ?>
					</div>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/portfolio/empty-state', null, array( 'search_term' => $fahar_search_term, 'has_filters' => $fahar_has_filters, 'reset_url' => $fahar_explore_url ) ); ?>
				<?php endif; ?>
			</section>
		</div>
	</main>
</div>
<?php
get_footer();

unset(
	$fahar_explore_page_id,
	$fahar_explore_title,
	$fahar_explore_url,
	$fahar_search_input,
	$fahar_category_input,
	$fahar_tag_input,
	$fahar_search_term,
	$fahar_category_slug,
	$fahar_tag_slug,
	$fahar_explore_post_type,
	$fahar_category_taxonomy,
	$fahar_tag_taxonomy,
	$fahar_category_terms,
	$fahar_selected_category,
	$fahar_contextual_tags,
	$fahar_selected_tag,
	$fahar_filter_url_args,
	$fahar_search_clear_url,
	$fahar_filter_reset_url,
	$fahar_has_filters,
	$fahar_active_count,
	$fahar_state_base_args,
	$fahar_category_links,
	$fahar_all_categories_url,
	$fahar_tag_links,
	$fahar_all_tags_url,
	$fahar_page_input,
	$fahar_explore_page,
	$fahar_partial_input,
	$fahar_is_partial_request,
	$fahar_posts_per_page,
	$fahar_explore_query,
	$fahar_explore_next_url,
	$fahar_explore_query_args,
	$fahar_next_url_args,
	$fahar_cards_html,
	$fahar_search_listbox_id,
	$fahar_category_candidate,
	$fahar_contextual_tag,
	$fahar_category_term,
	$fahar_category_args,
	$fahar_tag_base_args,
	$fahar_tag_args
);
