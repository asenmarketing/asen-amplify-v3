<?php
/**
 * "See All Posts" tile that leads the Featured Blogs grid.
 */
?>
<div class="post-item is-cta">
    <?php if ( ! empty( $args['image_id'] ) ) : ?>
    <div class="item-media"><?php echo wp_get_attachment_image( $args['image_id'], 'large', false, array( 'sizes' => $args['image_sizes'] ?? '' ) ); ?></div>
    <?php endif; ?>
    <div class="item-copy">
        <?php if ( ! empty( $args['heading'] ) ) : ?>
        <h3 class="item-title"><?php echo esc_html( $args['heading'] ); ?></h3>
        <?php endif; ?>
        <a class="item-link" href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>">See All Posts<i class="fa-regular fa-circle-plus" aria-hidden="true"></i></a>
    </div>
</div>
