<?php
/**
 * Blog post card for Featured Blogs (templates/block/partials/collection.php).
 */

$item     = $args['post'];
$category = asen_get_primary_category_meta( $item->ID );
?>
<a class="post-item" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
    <?php if ( has_post_thumbnail( $item ) ) : ?>
    <div class="item-media"><?php echo get_the_post_thumbnail( $item, 'large', array( 'sizes' => $args['image_sizes'] ?? '' ) ); ?></div>
    <?php endif; ?>
    <div class="item-copy">
        <p class="item-meta">
            <?php if ( ! empty( $category['title'] ) ) : ?><span class="category"><?php echo esc_html( $category['title'] ); ?></span> <span class="separator" aria-hidden="true">|</span> <?php endif; ?>
            <time datetime="<?php echo esc_attr( get_the_date( 'c', $item ) ); ?>"><?php echo esc_html( get_the_date( '', $item ) ); ?></time>
        </p>
        <h3 class="item-title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
    </div>
</a>
