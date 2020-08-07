<?php
// Leaflet.js Map Processing

?>
<!--Define available Leaflet basemaps groups and layers using leaflet.providers.js, L.TileLayer.PouchDBCached.js, and styledLayerControl.js based on map_functions.php-->
    <?php
    echo leaflet_osm_basemaps();
    echo leaflet_esri_basemaps();
    echo leaflet_stamen_basemaps();
    echo leaflet_hydda_basemaps();
    echo leaflet_nasa_basemaps();
    echo leaflet_thunderforest_basemaps();
    echo leaflet_mapbox_basemaps(); ?>

    <!-- Define Leaflet default basemap attribution-->
    <?php switch ($map_default)
        {
        case ('OpenStreetMap.Mapnik' || 'OpenStreetMap.DE' || 'OpenStreetMap.BZH' || 'OpenStreetMap.HOT' || 'MtbMap' || 'HikeBike.HikeBike'):
            ?> var default_attribute = osm_attribute; <?php
            break;

        case ('OpenStreetMap.France'):
            ?> var default_attribute = osm_fr_attribute; <?php
            break;

        case ('OpenTopoMap'):
            ?> var default_attribute = osm_otm_attribute; <?php
            break;

        case ('OpenMapSurfer.Roads'):
            ?> var default_attribute = oms_attribute; <?php
            break;

        default:
            ?> var default_attribute = ''; <?php
        } ?>
