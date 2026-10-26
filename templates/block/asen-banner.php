<?php
/**
 * Block Name: Asen - Banner
 *
 * Markup lives in templates/block/partials/banner.php, which page templates without the block editor also use.
 */

$background = get_field( 'background_type' ) ?: 'none';
$links      = array_filter( array( get_field( 'link_1' ), get_field( 'link_2' ) ) );
$args       = asen_banner_defaults( array(
    'eyebrow'         => get_field( 'eyebrow' ),
    'title'           => get_field( 'title' ) ?: get_the_title( $post_id ?? get_the_ID() ),
    'subtitle'        => get_field( 'subtitle' ),
    'links'           => $links,
    'background'      => $background,
    'image'           => 'image' === $background ? get_field( 'background_image' ) : null,
    'slides'          => 'slideshow' === $background ? get_field( 'slides' ) : array(),
    'video_desktop'   => 'video' === $background ? get_field( 'video_desktop' ) : '',
    'video_mobile'    => 'video' === $background ? get_field( 'video_mobile' ) : '',
    'video_poster'    => 'video' === $background ? get_field( 'video_poster' ) : null,
    'overlay'         => 'none' !== $background ? asen_banner_overlay( get_field( 'overlay' ) ) : 'none',
    'container_width' => get_field( '__container_width' ),
    'title_size'      => get_field( '__heading_size' ),
    'has_text_align'  => ! empty( $block['style']['typography']['textAlign'] ),
) );
$args['attributes'] = get_block_wrapper_attributes( array( 'class' => implode( ' ', $args['classes'] ), 'style' => $args['style'] ) );

get_template_part( 'templates/block/partials/banner', null, $args );
