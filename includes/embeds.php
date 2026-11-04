<?php
/**
 * YouTube in the core Embed block renders as a thumbnail + play button; js/youtube-facade.js swaps in the
 * youtube-nocookie iframe on click, so the YouTube player (~1MB of JS) only loads for visitors who press play.
 */

function asen_register_youtube_facade() {
    wp_register_script( 'asen-youtube-facade', get_template_directory_uri() . '/js/youtube-facade.js', array(), filemtime( get_template_directory() . '/js/youtube-facade.js' ), true );
}
add_action( 'init', 'asen_register_youtube_facade' );

// autoembed (the_content, priority 8) has already turned the URL into WP's oEmbed iframe by the time blocks render.
function asen_youtube_embed_facade( $content, $block ) {
    if ( is_admin() || is_feed() || 'youtube' !== ( $block['attrs']['providerNameSlug'] ?? '' ) ) {
        return $content;
    }
    $url  = (string) ( $block['attrs']['url'] ?? '' );
    $id   = asen_youtube_id( $url );
    $tags = new WP_HTML_Tag_Processor( $content );
    if ( ! $id || ! $tags->next_tag( 'iframe' ) ) {
        return $content;
    }
    $title = (string) $tags->get_attribute( 'title' );

    // The oEmbed src keeps its params (playlist etc.); the host becomes youtube-nocookie, plus autoplay and the URL's start time.
    $src = (string) $tags->get_attribute( 'src' );
    $src = $src ? preg_replace( '#^https?://(www\.)?youtube\.com/#', 'https://www.youtube-nocookie.com/', $src ) : 'https://www.youtube-nocookie.com/embed/' . $id;
    parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
    $time = (string) ( $query['start'] ?? $query['t'] ?? '' );
    $args = array( 'autoplay' => 1 );
    if ( '' !== $time && preg_match( '/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s?)?$/', $time, $t ) ) {
        $args['start'] = (int) ( $t[1] ?? 0 ) * 3600 + (int) ( $t[2] ?? 0 ) * 60 + (int) ( $t[3] ?? 0 );
    }
    $src = add_query_arg( $args, $src );

    $thumb  = 'https://i.ytimg.com/vi/' . rawurlencode( $id ) . '/';
    $srcset = asen_youtube_has_maxres_thumbnail( $id ) ? ' srcset="' . esc_url( $thumb . 'hqdefault.jpg' ) . ' 480w, ' . esc_url( $thumb . 'maxresdefault.jpg' ) . ' 1280w" sizes="(max-width: 1100px) 100vw, 1100px"' : '';
    $facade = sprintf(
        '<button class="asen-youtube-facade" type="button" aria-label="%1$s" data-src="%2$s" data-title="%3$s"><img src="%4$s"%5$s width="480" height="360" alt=""><svg viewBox="0 0 576 512" aria-hidden="true" focusable="false"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></button>',
        esc_attr( $title ? sprintf( 'Play video: %s', $title ) : 'Play video' ),
        esc_url( $src ),
        esc_attr( $title ?: 'YouTube video' ),
        esc_url( $thumb . 'hqdefault.jpg' ),
        $srcset
    );

    $content = preg_replace_callback( '~(<div class="wp-block-embed__wrapper">).*?(</div>)~s', fn( $m ) => $m[1] . $facade . $m[2], $content, 1, $count );
    if ( $count ) {
        wp_enqueue_script( 'asen-youtube-facade' );
    }
    return $content;
}
add_filter( 'render_block_core/embed', 'asen_youtube_embed_facade', 10, 2 );

// Not every video has a 1280px thumbnail (maxresdefault 404s on old / low-res uploads); checked once per video, cached.
function asen_youtube_has_maxres_thumbnail( $id ) {
    $key    = 'asen_yt_maxres_' . $id;
    $cached = get_transient( $key );
    if ( false !== $cached ) {
        return 'yes' === $cached;
    }
    $response = wp_remote_head( 'https://i.ytimg.com/vi/' . rawurlencode( $id ) . '/maxresdefault.jpg', array( 'timeout' => 3 ) );
    if ( is_wp_error( $response ) ) {
        set_transient( $key, 'no', HOUR_IN_SECONDS );
        return false;
    }
    $has = 200 === (int) wp_remote_retrieve_response_code( $response );
    set_transient( $key, $has ? 'yes' : 'no', 30 * DAY_IN_SECONDS );
    return $has;
}
