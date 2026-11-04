<?php
/**
 * Shared list of posts for the CPT hub blocks, as a grid, alternating rows, or a Splide carousel.
 *
 * Args: items (posts or IDs), item_template (template part rendered per post with args + 'post'), layout (grid|alternating|carousel),
 * columns (1-6; slides per view in the carousel), label (carousel aria-label), splide (extra Splide options), item_args,
 * lead (optional [ 'template' => ..., 'args' => [...] ] rendered as the first item, e.g. a CTA tile).
 */

// Filter before get_post(): an empty ACF selection is false, and get_post( false ) returns the current page.
$items     = array_filter( array_map( 'get_post', array_filter( (array) ( $args['items'] ?? array() ) ) ) );
$layout    = $args['layout'] ?? 'grid';
$columns   = max( 1, min( 6, (int) ( $args['columns'] ?? 3 ) ) );
$template  = $args['item_template'];
$item_args = $args['item_args'] ?? array();
$lead      = $args['lead'] ?? null;

// Card image `sizes` from the layout's slot width (breakpoints match _collection.scss and the Splide options below, capped
// at the xxl container), so the browser picks a fitting srcset candidate instead of the 1024px+ default. Phones: the content
// area is 86vw (7% gutters, _measurements.scss), less the carousel's 3rem arrow padding per side.
$per_row = fn( $n, $vw ) => 1 === $n ? $vw . 'vw' : 'calc(' . $vw . 'vw / ' . $n . ')';
$phone   = 'carousel' === $layout ? 'calc(86vw - 96px)' : '86vw';
$sizes   = 'alternating' === $layout
    ? '(max-width: 768px) 86vw, (max-width: 1440px) 50vw, 720px'
    : '(max-width: 640px) ' . $phone . ', (max-width: 1024px) ' . $per_row( (int) ceil( $columns / 2 ), 100 ) . ', (max-width: 1440px) ' . $per_row( $columns, 100 ) . ', ' . (int) ceil( 1440 / $columns ) . 'px';
$item_args += array( 'image_sizes' => $sizes );
if ( $lead ) {
    $lead['args'] = ( $lead['args'] ?? array() ) + array( 'image_sizes' => $sizes );
}

if ( ! $items ) {
    if ( is_admin() ) {
        echo '<p>Select items to display.</p>';
    }
    return;
}

if ( 'carousel' === $layout ) :
    wp_enqueue_script( 'asen-carousel' );
    wp_enqueue_style( 'splide' );
    $options = array_replace_recursive( array(
        'type'         => 'loop',
        'perPage'      => $columns,
        'gap'          => 'var(--wp--custom--gap--lg)',
        'arrows'       => true,
        'pagination'   => false,
        'autoplay'     => true,
        'interval'     => 5000,
        'pauseOnHover' => true,
        'breakpoints'  => array(
            1024 => array( 'perPage' => (int) ceil( $columns / 2 ) ),
            640  => array( 'perPage' => 1 ),
        ),
    ), $args['splide'] ?? array() );

    // Breakpoints where the carousel will have more than one page, i.e. where Splide will show the dots.
    $count     = count( $items ) + ( $lead ? 1 : 0 );
    $per_page  = array( 'lg' => $options['perPage'], 'md' => $options['breakpoints'][1024]['perPage'] ?? $options['perPage'] );
    $per_page += array( 'sm' => $options['breakpoints'][640]['perPage'] ?? $per_page['md'] );
    $pages_at  = $options['pagination'] ? array_keys( array_filter( $per_page, fn( $n ) => $count > (int) $n ) ) : array();
    $classes   = array_merge( array( 'collection', 'collection-carousel', 'splide', 'has-columns-' . $columns ), array_map( fn( $bp ) => 'has-pages-' . $bp, $pages_at ) );
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-label="<?php echo esc_attr( $args['label'] ?? '' ); ?>" data-splide="<?php echo esc_attr( wp_json_encode( $options ) ); ?>">
    <div class="splide__track">
        <ul class="splide__list">
            <?php if ( $lead ) : ?>
            <li class="splide__slide collection-item is-lead"><?php get_template_part( $lead['template'], null, $lead['args'] ?? array() ); ?></li>
            <?php endif; ?>
            <?php foreach ( $items as $item ) : ?>
            <li class="splide__slide collection-item"><?php get_template_part( $template, null, array_merge( $item_args, array( 'post' => $item ) ) ); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php if ( $pages_at ) : // Splide fills this placeholder; until it mounts, one stand-in dot holds the row's height ?>
    <ul class="splide__pagination"><li aria-hidden="true"><span class="splide__pagination__page"></span></li></ul>
    <?php endif; ?>
</div>
<?php else : ?>
<ul class="collection collection-<?php echo esc_attr( $layout ); ?> has-columns-<?php echo $columns; ?>">
    <?php if ( $lead ) : ?>
    <li class="collection-item is-lead"><?php get_template_part( $lead['template'], null, $lead['args'] ?? array() ); ?></li>
    <?php endif; ?>
    <?php foreach ( $items as $item ) : ?>
    <li class="collection-item"><?php get_template_part( $template, null, array_merge( $item_args, array( 'post' => $item ) ) ); ?></li>
    <?php endforeach; ?>
</ul>
<?php endif;
