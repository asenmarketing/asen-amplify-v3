<?php
// No Leaflet dependency: view.js loads it (data-leaflet URL) once a map nears the viewport.
return array( 'dependencies' => array(), 'version' => filemtime( __DIR__ . '/view.js' ) );
