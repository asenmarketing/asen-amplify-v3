<?php
/**
 * Product card for the Products Showcase (templates/block/partials/collection.php).
 */

$item        = $args['post'];
$image       = get_field( 'product_image', $item->ID );
$description = get_field( 'description', $item->ID );
$price       = get_field( 'price', $item->ID );
?>
<article class="product-item">
    <?php if ( $image ) : ?>
    <div class="item-media"><?php echo wp_get_attachment_image( $image, 'large', false, array( 'sizes' => $args['image_sizes'] ?? '' ) ); ?></div>
    <?php endif; ?>
    <div class="item-copy">
        <h3 class="item-title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
        <?php if ( $description ) : ?>
        <p><?php echo wp_kses_post( $description ); ?></p>
        <?php endif; ?>
        <?php if ( '' !== (string) $price ) : ?>
        <p class="item-meta price"><?php echo esc_html( '$' . $price ); ?></p>
        <?php endif; ?>
    </div>
</article>
