<?php
/**
 * Service card for the Services Hub (templates/block/partials/collection.php).
 */

$item       = $args['post'];
$display    = $args['display'] ?? 'image';
$link_style = $args['link_style'] ?? 'none';
$image      = get_field( 'service_image', $item->ID );
$icon       = get_field( 'icon_code', $item->ID );
$blurb      = get_field( 'service_blurb', $item->ID );
?>
<article class="service-item">
    <?php if ( 'image' === $display && $image ) : ?>
    <div class="item-media"><?php echo wp_get_attachment_image( $image, 'large', false, array( 'sizes' => $args['image_sizes'] ?? '' ) ); ?></div>
    <?php elseif ( 'icon' === $display && $icon ) : ?>
    <div class="item-media is-icon"><?php echo $icon; ?></div>
    <?php endif; ?>
    <div class="item-copy">
        <h3 class="item-title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
        <?php if ( $blurb ) : ?>
        <p><?php echo wp_kses_post( $blurb ); ?></p>
        <?php endif; ?>
    </div>
    <?php if ( 'none' !== $link_style ) : ?>
    <a class="item-link<?php echo 'button' === $link_style ? ' button button-small' : ''; ?>" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
        <?php echo esc_html( $args['link_text'] ?? 'View Service' ); ?><?php if ( 'icon' === $link_style && ! empty( $args['link_icon'] ) ) { echo $args['link_icon']; } ?>
    </a>
    <?php endif; ?>
</article>
