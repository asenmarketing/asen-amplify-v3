<?php
/**
 * Team member card for the Team Hub (templates/block/partials/collection.php).
 */

$item     = $args['post'];
$image    = get_field( 'team_member_image', $item->ID );
$position = get_field( 'team_member_position', $item->ID );
?>
<article class="team-item">
    <?php if ( $image ) : ?>
    <div class="item-media"><?php echo wp_get_attachment_image( $image, 'large', false, array( 'sizes' => $args['image_sizes'] ?? '' ) ); ?></div>
    <?php endif; ?>
    <div class="item-copy">
        <h3 class="item-title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
        <?php if ( $position ) : ?>
        <p class="item-meta"><?php echo esc_html( $position ); ?></p>
        <?php endif; ?>
    </div>
</article>
