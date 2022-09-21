<?php
    /**
     * Display single product reviews (comments)
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see     https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 4.3.0
     */

    defined( 'ABSPATH' ) || exit;

    global $product;

    if ( ! comments_open() ) return;

    // Get current user info
    $user = wp_get_current_user();

    // Post to the correct function
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) :
	    $post_review = new Yotpo_Reviews_Public();
	    $post_review->post_yotpo_review();
	endif;

?>

<div id="reviews" class="woocommerce-Reviews text-block">
    <div class="container">

        <div id="comments">
			<h2 class="woocommerce-Reviews-title">
				<?php
				$count = $product->get_review_count();
				if ( $count && wc_review_ratings_enabled() ) {
					/* translators: 1: reviews count 2: product name */
					$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
					echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ); // WPCS: XSS ok.
				} else {
					esc_html_e( 'Reviews', 'woocommerce' );
				}
				?>
			</h2>

			<?php if ( have_comments() ) : ?>
				<ol class="commentlist">
					<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
				</ol>

				<?php
				if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
					echo '<nav class="woocommerce-pagination">';
					paginate_comments_links(
						apply_filters(
							'woocommerce_comment_pagination_args',
							array(
								'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
								'next_text' => is_rtl() ? '&larr;' : '&rarr;',
								'type'      => 'list',
							)
						)
					);
					echo '</nav>';
				endif;
				?>
			<?php else : ?>
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			<?php endif; ?>
		</div>

        <div class="product-reviews__form-wrapper">
            <div class="text-center">
                <?php if ( !is_user_logged_in() ) : // Not logged in ?>
                    <p><a href="<?php bloginfo('url'); ?>/account" class="product-reviews__write-review"><?php esc_html_e( 'You must be logged in to leave a review.', 'woocommerce' ); ?></a></p>
                <?php elseif ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', $user->ID, $product->get_id() ) ) : // Logged in and has purchased ?>
                    <p><a href="" class="product-reviews__write-review" data-link="review-form"><?php esc_html_e( 'Write a review', 'woocommerce' ); ?></a></p>
                <?php else : // Logged in but has not purchased ?>
                    <p><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
                <?php endif; ?>
            </div>

            <?php
                // Only show form if user is logged in and has purchased
                if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', $user->ID, $product->get_id() ) ) :
            ?>

            <div class="product-reviews__form">

                <h3 class="product-reviews__form-heading"><?php echo esc_html( 'Write A Review' ); ?></h3>
                <p class="text-center">All fields are required.</p>

                <form action="" enctype="multipart/form-data" method="post">
                    <?php //wp_nonce_field( 'product-review' . $product->post_id() ); ?>

                    <input type="hidden" name="product_id" value="<?php echo $product->get_sku(); ?>" />
                    <input type="hidden" name="product_title" value="<?php the_title(); ?>" />
                    <input type="hidden" name="product_url" value="<?php the_permalink(); ?>" />

                    <?php
                    	$main_image = wp_get_attachment_image_src( $product->get_image_id() );
                    	$main_image = $main_image[0] ?? wc_placeholder_img_src();
                    ?>
                    <input type="hidden" name="product_image" value="<?php echo $main_image; ?>" />

                    <div class="row">
                        <div class="col-md-6 col">
                            <label for="review-name"><?php echo esc_html( 'Name' ); ?></label>
                            <input type="text" name="review[name]" id="review-name" value="<?php echo isset( $user->display_name ) ? esc_attr( $user->display_name ) : ''; ?>" required aria-required="true"/>
                        </div>
                        <div class="col-md-6 col">
                            <label for="review-email"><?php echo esc_html( 'Email Address' ); ?></label>
                            <input type="text" name="review[email]" id="review-email" value="<?php echo isset( $user->user_email ) ? esc_attr( $user->user_email ) : ''; ?>" required aria-required="true"/>

                        </div>
                        <div class="col-md-6 col">
                            <label for="review-rating"><?php echo esc_html( 'Rating' ); ?></label>
                            <select name="review[rating]" id="review-rating" required aria-required="true">
                                <option value="0"><?php echo esc_html( 'Select Rating' ); ?></option>
                                <option value="1"><?php echo esc_html( '1 star (worst)' );?></option>
                                <option value="2"><?php echo esc_html( '2 stars' );?></option>
                                <option value="3"><?php echo esc_html( '3 stars (average)' );?></option>
                                <option value="4"><?php echo esc_html( '4 stars' );?></option>
                                <option value="5"><?php echo esc_html( '5 stars (best)' );?></option>
                            </select>
                        </div>
                        <div class="col-md-6 col">
                            <label for="review-subject"><?php echo esc_html( 'Subject' ); ?></label>
                            <input type="text" name="review[subject]" id="review-subject" value="" required aria-required="true"/>
                        </div>
                        <div class="col-md-12 col">
                            <label for="content"><?php echo esc_html( 'Review' ); ?></label>
                            <textarea id="content" name="review[content]" required aria-required="true"></textarea>
                        </div>


                        <div class="col-md-12 col" style="display: none;">
                            <label for="fav-color"><?php echo esc_html( 'Favorite Color' ); ?></label>
                            <input type="text" name="review[color]" id="fav-color" value=""/>
                        </div>
                    </div>

                    <p class="text-center">
                        <button class="button" aria-label="<?php esc_html_e( 'Submit' ); ?>" type="submit"><?php esc_html_e( 'Submit' ); ?></button>
                    </p>
                </form>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

