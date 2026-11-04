<?php
$site_name      = get_bloginfo( 'name', 'display' );
$logo_id        = get_field( 'site_header_logo_image', 'option' );
$text_transform = get_field( 'header_menu_text_transform', 'option' );

$header_classes = array(
    'site-header',
    ( get_field( 'header_bottom_border', 'option' ) && ! get_field( 'overlapping_header', 'option' ) ) ? 'has-border' : '',
);
?>
<header class="<?php echo esc_attr( implode( ' ', array_filter( $header_classes ) ) ); ?>" role="banner">
    <div class="inside<?php echo get_field( 'header_menu_align_left', 'option' ) ? ' align-left' : ''; ?>">
        <div class="logo">
            <a href="<?php echo esc_url( site_url( '/', 'https' ) ); ?>" title="<?php echo esc_attr( $site_name ); ?>">
                <?php if ( $logo_id ) : ?>
                <?php echo asen_image_without_priority( $logo_id, 'large' ); ?>
                <?php else : ?>
                Add header logo!
                <?php endif; ?>
            </a>
        </div>
        <div class="navigation<?php echo $text_transform ? ' ' . esc_attr( $text_transform ) : ''; ?>">
            <?php if ( has_nav_menu( 'primary-menu' ) ) : ?>
            <nav class="header-menu">
                <?php wp_nav_menu( array(
                    'container'      => '',
                    'menu_class'     => 'primary-menu',
                    'theme_location' => 'primary-menu',
                    'depth'          => 1,
                ) ); ?>
            </nav>
            <?php else : ?>
            <p>Create a menu called "Primary Menu"!</p>
            <?php endif; ?>
            <?php get_template_part( 'templates/custom-cta-options' ); ?>
            <?php get_template_part( 'templates/menu/hamburger' ); ?>
        </div>
    </div>
</header>
