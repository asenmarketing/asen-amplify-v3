<?php
/**
 * Banner markup for the Asen - Banner block and for templates without the block editor (Contact page, CPT archives).
 * Build $args with asen_banner_defaults(); 'attributes' is the escaped attribute string for the wrapper, else 'classes' and
 * 'style' (the overlay custom property) are printed here.
 */

$attributes = $args['attributes'] ?? 'class="' . esc_attr( implode( ' ', $args['classes'] ) ) . '"' . ( empty( $args['style'] ) ? '' : ' style="' . esc_attr( $args['style'] ) . '"' );
$slides     = array_filter( (array) $args['slides'] );
?>
<header <?php echo $attributes; ?>>
    <?php if ( 'image' === $args['background'] && $args['image'] ) : ?>
    <div class="banner-media"><?php echo wp_get_attachment_image( $args['image'], 'asen-banner-general', false, array( 'sizes' => '100vw' ) ); ?></div>
    <?php elseif ( 'slideshow' === $args['background'] && $slides ) :
        wp_enqueue_script( 'asen-carousel' );
        wp_enqueue_style( 'splide' );
        $options = array( 'type' => 'fade', 'rewind' => true, 'autoplay' => true, 'interval' => 5000, 'speed' => 1000, 'arrows' => false, 'pagination' => count( $slides ) > 1, 'pauseOnHover' => false, 'drag' => false );
    ?>
    <div class="banner-media banner-slideshow splide" aria-label="Banner images" data-splide="<?php echo esc_attr( wp_json_encode( $options ) ); ?>">
        <div class="splide__track">
            <ul class="splide__list">
                <?php foreach ( $slides as $slide ) : ?>
                <li class="splide__slide"><?php echo wp_get_attachment_image( $slide, 'asen-banner-general', false, array( 'sizes' => '100vw' ) ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php elseif ( 'video' === $args['background'] && ( $args['video_desktop'] || $args['video_mobile'] ) ) : ?>
    <div class="banner-media">
        <video autoplay muted loop playsinline>
            <?php if ( $args['video_mobile'] ) : ?><source src="<?php echo esc_url( $args['video_mobile'] ); ?>" media="(max-width: 800px)"><?php endif; ?>
            <?php if ( $args['video_desktop'] ) : ?><source src="<?php echo esc_url( $args['video_desktop'] ); ?>"><?php endif; ?>
        </video>
    </div>
    <?php endif; ?>

    <?php if ( 'none' !== $args['overlay'] && 'none' !== $args['background'] ) : ?>
    <div class="banner-overlay" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="inside">
        <?php if ( $args['eyebrow'] ) : ?>
        <p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
        <?php endif; ?>
        <h1 class="title"><?php echo esc_html( $args['title'] ); ?></h1>
        <?php if ( $args['subtitle'] ) : ?>
        <p class="subtitle"><?php echo wp_kses_post( $args['subtitle'] ); ?></p>
        <?php endif; ?>
        <?php if ( $args['links'] ) : ?>
        <div class="banner-buttons">
            <?php foreach ( $args['links'] as $link ) : if ( empty( $link['url'] ) ) { continue; } ?>
            <a class="button" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</header>
