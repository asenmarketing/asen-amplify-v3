<?php
/**
 * Block Name: Asen - Icon Grid
 */

if ( is_admin() ) {
    echo '<h3 class="asen-block-heading">' . esc_html( $block['title'] ) . '</h3>';
}

$container_width = get_field( '__container_width' ) ?: 'xxl';

$classes = array( 'asen-icon-grid', 'block-width-' . $container_width );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) ); ?>>
    <div class="inside">
        <?php if ( have_rows( 'items' ) ) : ?>
        <div class="items">
            <?php while ( have_rows( 'items' ) ) : the_row();
                $text     = get_sub_field( 'text' );
                $has_link = ! is_admin() && 'none' !== get_sub_field( 'link_target' );
                $link     = get_sub_field( 'use_custom_url' ) ? get_sub_field( 'link_custom' ) : get_the_permalink( get_sub_field( 'link_wordpress' ) );
            ?>
            <article class="item">
                <?php if ( $has_link ) : ?>
                <a href="<?php echo esc_url( $link ); ?>" title="<?php echo esc_attr( $text ?: 'Learn More' ); ?>"<?php echo '_blank' === get_sub_field( 'link_target' ) ? ' target="_blank" rel="noopener"' : ''; ?>>
                <?php endif; ?>
                    <?php echo wp_get_attachment_image( get_sub_field( 'icon' ), 'large', false, array( 'sizes' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, (max-width: 1440px) 25vw, 20vw' ) ); ?>
                    <?php if ( $text ) : ?>
                    <p class="text"><?php echo esc_html( $text ); ?></p>
                    <?php endif; ?>
                <?php if ( $has_link ) : ?>
                </a>
                <?php endif; ?>
            </article>
            <?php endwhile; ?>
        </div>
        <?php elseif ( is_admin() ) : ?>
        <p>Add items to the icon grid.</p>
        <?php endif; ?>
    </div>
</section>
