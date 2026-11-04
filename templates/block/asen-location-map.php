<?php
/**
 * Block Name: Asen - Location Map
 */

if ( is_admin() ) {
    echo '<h3 class="asen-block-heading">' . esc_html( $block['title'] ) . '</h3>';
}

$heading         = get_field( 'heading' );
$address         = get_field( 'address' );
$name            = get_field( 'popup_content' );
$directions      = get_field( 'maps_link' );
$marker          = get_field( 'marker_icon' );
$full_width      = get_field( 'full_width' );
$container_width = get_field( '__container_width' ) ?: 'xxl';

$coords = $address ? asen_geocode_address( $address ) : null;

// Tile provider. OpenStreetMap's standard tiles need no key; a client site can swap in a keyed provider via this filter.
$tiles = apply_filters( 'asen/map_tiles', array(
    'url'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    'max_zoom'    => 19,
) );

// Marker icon: URL plus its pixel size so Leaflet can anchor it.
$marker_url = is_array( $marker ) ? ( $marker['url'] ?? '' ) : ( is_numeric( $marker ) ? wp_get_attachment_image_url( $marker, 'full' ) : (string) $marker );
$marker_size = array( 40, 40 );
if ( $marker_url ) {
    $uploads = wp_upload_dir();
    $path    = str_replace( $uploads['baseurl'], $uploads['basedir'], $marker_url );
    $size    = file_exists( $path ) ? wp_getimagesize( $path ) : false;
    if ( $size ) {
        $marker_size = array( $size[0], $size[1] );
    }
}

// view.js loads Leaflet itself when the map nears the viewport; the registered handle's URL keeps a child's override.
$leaflet     = wp_scripts()->query( 'leaflet' );
$leaflet_src = $leaflet ? add_query_arg( 'ver', $leaflet->ver, $leaflet->src ) : '';

$classes = array_filter( array( 'asen-location-map', $full_width ? 'is-full-width' : '', 'block-width-' . $container_width ) );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) ); ?>>
    <div class="inside">
        <?php if ( $heading ) : ?>
        <div class="heading-container">
            <h2><?php echo esc_html( $heading ); ?></h2>
        </div>
        <?php endif; ?>

        <?php if ( $coords ) : ?>
        <div class="map" data-lat="<?php echo esc_attr( $coords['lat'] ); ?>" data-lon="<?php echo esc_attr( $coords['lon'] ); ?>"
            data-address="<?php echo esc_attr( $address ); ?>" data-name="<?php echo esc_attr( $name ); ?>" data-directions="<?php echo esc_url( $directions ); ?>"
            data-leaflet="<?php echo esc_url( $leaflet_src ); ?>" data-tiles="<?php echo esc_attr( $tiles['url'] ); ?>" data-attribution="<?php echo esc_attr( $tiles['attribution'] ); ?>" data-max-zoom="<?php echo (int) $tiles['max_zoom']; ?>"
            <?php if ( $marker_url ) : ?>data-marker="<?php echo esc_url( $marker_url ); ?>" data-marker-width="<?php echo (int) $marker_size[0]; ?>" data-marker-height="<?php echo (int) $marker_size[1]; ?>"<?php endif; ?>>
            <?php if ( is_admin() ) : ?><p class="map-placeholder">Map renders on the front end.</p><?php endif; ?>
        </div>
        <?php elseif ( is_admin() ) : ?>
        <p><?php echo $address ? 'That address could not be geocoded.' : 'Add an address.'; ?></p>
        <?php endif; ?>
    </div>
</section>
