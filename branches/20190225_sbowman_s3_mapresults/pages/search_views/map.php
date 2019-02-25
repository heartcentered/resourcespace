<?php
// Map Search View Using Leaflet.js and Various Leaflet Plugins v1.0, 1/30/2019, Steve D. Bowman

// Check if geocoding-maps have been disabled.
global $disable_geocoding, $lang;
if($disable_geocoding)
    {
    header('HTTP/1.1 403 Forbidden');
    exit($lang['error-geocodingdisabled']);
    }

// Setup initial Leaflet map variables.
global $js_cdn, $baseurl, $mapheight, $js_cidr, $leaflet_defaultmap, $geomarker, $leaflet_map_centerview, $leaflet_zoomslider, $leaflet_zoomnavbar, $leaflet_kml, $leaflet_kml_file, $map_retina, $marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8;
$zoomslider = 'false';
$zoomcontrol = 'true';
$marker_color_def = array($marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8);
$display_selector_dropdowns = false;

// Set Leaflet map search view height and layer control container height based on $mapheight.
if (isset($mapheight))
    {
    $map1_height = $mapheight;
    $layer_controlheight = $mapheight - 40;
    }
else // Default values.
    {
    $map1_height = "500";
    $layer_controlheight = 460;
    }

// Show zoom slider instead of default Leaflet zoom control?
if ($leaflet_zoomslider)
    {
    $zoomslider = 'true';
    $zoomcontrol = 'false';
    }

// Check user's IP address against a CIDR value for using CDN-hosted JavaScript files.
$cidrMatch = false;
if (isset($js_cidr))
    {
    include_once ("../include/cidr_match/CIDRmatch.php");

    $ip = $_SERVER['REMOTE_ADDR'];
    $cidrMatch = match($ip, $js_cidr);
    }

?>
<!--Map introtext-->
<div id="map1_introtext" style="margin-top:0px; margin-bottom:0px; width: 99%;">
    <p> <?php echo $lang["map_introtext2"];?> </p>
</div>

<?php
// Option to use Content Delivery Network (CDN)-hosted Javascript files to reduce server load and bandwidth (~409 kB).
if($js_cdn && !$cidrMatch)
    { ?>
    <!--Leaflet.js v1.4.0 CDN-hosted files-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin="anonymous"/>
    <script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js" integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg==" crossorigin="anonymous"></script>

    <!--Leaflet Providers v1.5.0 plugin CDN-hosted files-->
    <script src="https://unpkg.com/leaflet-providers@1.5.0/leaflet-providers.js" integrity="sha384-qhl5cJzmX1MC4ZSbS53PTNBbfAtxKk0JUsp0IyB5b1T2Fm/QGvdwImgD5AuYtFBu" crossorigin="anonymous"></script>

    <!--Leaflet MarkerCluster v1.4.1 plugin CDN-hosted files-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" integrity="sha384-lPzjPsFQL6te2x+VxmV6q1DpRxpRk0tmnl2cpwAO5y04ESyc752tnEWPKDfl1olr" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" integrity="sha384-5kMSQJ6S4Qj5i09mtMNrWpSi8iXw230pKU76xTmrpezGnNJQzj0NzXjQLLg+jE7k" crossorigin="anonymous"/>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js" integrity="sha384-RLIyj5q1b5XJTn0tqUhucRZe40nFTocRP91R/NkRJHwAe4XxnTV77FXy/vGLiec2" crossorigin="anonymous"></script>

    <!--Leaflet MarkerCluster plugin Placement Strategies v0.1.7 subplugin CDN-hosted files-->
    <script src="https://unpkg.com/leaflet.markercluster.placementstrategies@0.1.7/dist/leaflet-markercluster.placementstrategies.js" integrity="sha384-vYAOQEUfYfl5VdHW4e5xQKL5p0HvoJdwi7CtjASZvP/cQx+enAjBpF0HnHPBdFsg" crossorigin="anonymous"></script>

    <!--Leaflet PouchDBCached v0.3.0 plugin files with PouchDB v7.0.0 CDN-hosted files-->
    <script src="https://unpkg.com/pouchdb@7.0.0/dist/pouchdb.min.js" integrity="sha384-FpxIRxpKRj0HDO5MlMrx4GC7b4FdAoT/lvS5ijJ1VhRGYfxVpdwCS299rT8q5sPA" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/leaflet.tilelayer.pouchdbcached@0.3.0/L.TileLayer.PouchDBCached.js" integrity="sha384-m/DV4I5eltTuv2uFtVagIMhogM8AocphHIthjVz/U6W+DjY260ZFJb0ccfSpT7UX" crossorigin="anonymous"></script>

    <!--Leaflet NavBar v1.0.0 plugin CDN-hosted files-->
    <?php if ($leaflet_zoomnavbar)
        { ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet-navbar@1.0.0/Leaflet.NavBar.css" integrity="sha384-Bn4H4r3DfQMWf8vAg/FDk5KLX8U0XTyKVBJJQNKvLmX496qDfsmHa/+eqg3ThjKO" crossorigin="anonymous"/>
        <script src="https://unpkg.com/leaflet-navbar@1.0.0/index.js" integrity="sha384-TUG+Zr0i1Yfp+deZwN9oL20OUWrMOx3mAbD2fDrjozG5P4ZU0QeVrNBSvDHK0dt7" crossorigin="anonymous"></script> <?php
        } ?>

    <!--Leaflet Omnivore v0.3.1 plugin CDN-hosted files-->
    <?php if ($leaflet_kml)
        { ?>
        <script src="https://unpkg.com/leaflet-omnivore@0.3.1/leaflet-omnivore.min.js" integrity="sha384-qhsiITse/qx9/hyk3zeRbeTDEY+UNlaJoJyxV2JWlaLiXMjwZ1BP7nmri1/EyzLV" crossorigin="anonymous"></script> <?php
        } ?>

    <!--Leaflet EasyPrint v2.1.9 plugin CDN-hosted files-->
    <script src="https://unpkg.com/leaflet-easyprint@2.1.9/dist/bundle.js" integrity="sha384-wf9ute6lQpx/ed7uU0TgJU56BOH/crnOVq13XbOxZKBpmB15XJMsdwfhZoommN+4" crossorigin="anonymous"></script>

    <?php
    }
else // Use the local server files.
    { ?>
    <!--Leaflet.js v1.4.0 files-->
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_1.4.0/leaflet.css"/>
    <script src="<?php echo $baseurl?>/lib/leaflet_1.4.0/leaflet.min.js"></script>

    <!--Leaflet Providers v1.5.0 plugin files-->
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-providers-1.5.0/leaflet-providers.min.js"></script>

    <!--Leaflet MarkerCluster v1.4.1 plugin files-->
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/MarkerCluster.css"/>
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/MarkerCluster.Default.css"/>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/leaflet.markercluster.min.js"></script>

    <!--Leaflet MarkerCluster plugin Placement Strategies v0.1.7 subplugin files-->
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-placementstrategies/dist/leaflet-markercluster.placementstrategies.min.js"></script>

    <!--Leaflet PouchDBCached v0.3.0 plugin files with PouchDB v7.0.0 files-->
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/pouchdb-7.0.0/pouchdb-7.0.0.min.js"></script>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-PouchDBCached-0.3.0/L.TileLayer.PouchDBCached.min.js"></script>

    <!--Leaflet NavBar v1.0.1 plugin files-->
    <?php if ($leaflet_zoomnavbar)
        { ?>
        <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-NavBar-1.0.1/src/Leaflet.NavBar.css"/>
        <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-NavBar-1.0.1/src/Leaflet.NavBar.min.js"></script> <?php
        } ?>

    <!--Leaflet Omnivore v0.3.1 plugin file-->
    <?php if ($leaflet_kml)
        { ?>
        <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-omnivore-0.3.1/leaflet-omnivore.min.js"></script> <?php
        } ?>

    <!--Leaflet EasyPrint v2.1.9 plugin files-->
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-easyPrint-2.1.9/dist/bundle.min.js"></script>

    <?php
    } ?>

<!--Leaflet plugin files not available on a CDN, always load from the local server-->
    <!--Leaflet ColorMarkers v1.0.0 plugin files-->
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-colormarkers-1.0.0/js/leaflet-color-markers.min.js"></script>

    <!--Leaflet StyledLayerControl v10/2/2018 plugin files-->
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-10-2-2018/css/styledLayerControl.css"/>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-10-2-2018/src/styledLayerControl.min.js"></script>

    <!--Leaflet Zoomslider v0.7.1 plugin files (avaialble at api.mapbox.com, but no SRI info-->
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.css"/>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.min.js"></script>

<!--Setup Leaflet map container with sizing-->
<div id="map_results" style="width: 99%; margin-top:0px; margin-bottom:0px; height: <?php echo $map1_height;?>px; display:block; border:1px solid black; float:none; overflow: hidden;">
</div>

<script type="text/javascript">
    <!--Setup and define the Leaflet map with the initial view using leaflet.js and L.Control.Zoomslider.js-->
    var map1 = new L.map('map_results', {
        renderer: L.canvas(),
        zoomsliderControl: <?php echo $zoomslider?>,
        zoomControl: <?php echo $zoomcontrol?>
    }).setView(<?php echo $leaflet_map_centerview;?>);

    <!--Define available Leaflet basemaps groups and layers using leaflet.providers.js, L.TileLayer.PouchDBCached.js, and styledLayerControl.js-->
    <!--OpenStreetMap (OSM) basemap group-->
        var osm_attribute = 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';

        var osm_mapnik = L.tileLayer.provider('OpenStreetMap.Mapnik', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_de = L.tileLayer.provider('OpenStreetMap.DE', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_fr_attribute = '&copy; Openstreetmap France | &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';
        var osm_fr = L.tileLayer.provider('OpenStreetMap.France', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_fr_attribute
        });

        var osm_bzh = L.tileLayer.provider('OpenStreetMap.BZH', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_bw = L.tileLayer.provider('OpenStreetMap.BlackAndWhite', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_hot = L.tileLayer.provider('OpenStreetMap.HOT', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_hikebike = L.tileLayer.provider('HikeBike.HikeBike', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_mtb = L.tileLayer.provider('MtbMap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_otm_attribute = 'Map data: &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
        var osm_otm = L.tileLayer.provider('OpenTopoMap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_otm_attribute
        });

        var oms_attribute = 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';
        var oms_roads = L.tileLayer.provider('OpenMapSurfer.Roads', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: oms_attribute
        });

        var oms_gray = L.tileLayer.provider('OpenMapSurfer.Grayscale', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: oms_attribute
        });

    <!--ESRI basemap group-->
        var esri_street_attribute = 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012';
        var esri_street = L.tileLayer.provider('Esri.WorldStreetMap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_street_attribute
        });

        var esri_delorme_attribute = 'Tiles &copy; Esri &mdash; Copyright: &copy;2012 DeLorme';
        var esri_delorme = L.tileLayer.provider('Esri.DeLorme', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_delorme_attribute
        });

        var esri_topo_attribute = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community';
        var esri_topo = L.tileLayer.provider('Esri.WorldTopoMap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_topo_attribute
        });

        var esri_imagery_attribute = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
        var esri_imagery = L.tileLayer.provider('Esri.WorldImagery', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_imagery_attribute
        });

        var esri_terrain_attribute = 'Tiles &copy; Esri &mdash; Source: USGS, Esri, TANA, DeLorme, and NPS';
        var esri_terrain = L.tileLayer.provider('Esri.WorldTerrain', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_terrain_attribute
        });

        var esri_relief_attribute = 'Tiles &copy; Esri &mdash; Source: Esri';
        var esri_relief = L.tileLayer.provider('Esri.WorldShadedRelief', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_relief_attribute
        });

        var esri_physical_attribute = 'Tiles &copy; Esri &mdash; Source: US National Park Service';
        var esri_physical = L.tileLayer.provider('Esri.WorldPhysical', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_physical_attribute
        });

        var esri_ocean_attribute = 'Tiles &copy; Esri &mdash; Sources: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri';
        var esri_ocean = L.tileLayer.provider('Esri.OceanBasemap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_ocean_attribute
        });

        var esri_natgeo_attribute = 'Tiles &copy; Esri &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC';
        var esri_natgeo = L.tileLayer.provider('Esri.NatGeoWorldMap', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_natgeo_attribute
        });

        var esri_gray_attribute = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ';
        var esri_gray = L.tileLayer.provider('Esri.WorldGrayCanvas', {
            useCache: '<?php echo $default_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_gray_attribute
        });

        <!--Stamen basemap group-->
        var stamen_attribute = 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';

        var stamen_toner = L.tileLayer.provider('Stamen.Toner', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_tonerlt = L.tileLayer.provider('Stamen.TonerLite', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_tonerback = L.tileLayer.provider('Stamen.TonerBackground', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_terrain = L.tileLayer.provider('Stamen.Terrain', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_terrainback = L.tileLayer.provider('Stamen.TerrainBackground', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_relief = L.tileLayer.provider('Stamen.TopOSMRelief', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

        var stamen_watercolor = L.tileLayer.provider('Stamen.Watercolor', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: stamen_attribute
        });

    <!--Hydda basemap group-->
        var hydda_attribute = 'Tiles courtesy of <a href="http://openstreetmap.se/" target="_blank">OpenStreetMap Sweden</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';

        var hydda_full = L.tileLayer.provider('Hydda.Full', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: hydda_attribute
        });

        var hydda_base = L.tileLayer.provider('Hydda.Base', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: hydda_attribute
        });

    <!--NASA GIBS basemap group-->
        var nasa_attribute = 'Imagery provided by services from the Global Imagery Browse Services (GIBS), operated by the NASA/GSFC/Earth Science Data and Information System (<a href="https://earthdata.nasa.gov">ESDIS</a>) with funding provided by NASA/HQ.';

        var nasa_gibscolor = L.tileLayer.provider('NASAGIBS.ModisTerraTrueColorCR', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: nasa_attribute
        });

        var nasa_gibsfalsecolor = L.tileLayer.provider('NASAGIBS.ModisTerraBands367CR', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: nasa_attribute
        });

        var nasa_gibsnight = L.tileLayer.provider('NASAGIBS.ViirsEarthAtNight2012', {
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: nasa_attribute
        });

    <!--Thunderforest basemap group (requires an API key)-->
        var tf_attribute = '&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';

        var tf_ocm = L.tileLayer.provider('Thunderforest.OpenCycleMap', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

        var tf_transport = L.tileLayer.provider('Thunderforest.Transport', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

        var tf_transportdark = L.tileLayer.provider('Thunderforest.TransportDark', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

        var tf_landscape = L.tileLayer.provider('Thunderforest.Landscape', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

        var tf_outdoors = L.tileLayer.provider('Thunderforest.Outdoors', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

        var tf_pioneer = L.tileLayer.provider('Thunderforest.Pioneer', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: tf_attribute
        });

    <!--Mapbox basemaps group (requires API keys)-->
        var mapbox = L.tileLayer.provider('MapBox', {
            id: '<?php echo $map_mapboxid?>',
            accessToken: '<?php echo $map_mapboxtoken?>',
            useCache: '<?php echo $all_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: '<?php echo $map_mapboxattribution?>'
        });

    <!-- Define Leaflet default basemap attribution-->
    <?php switch ($leaflet_defaultmap)
        {
        case ('OpenStreetMap.Mapnik' || 'OpenStreetMap.DE' || 'OpenStreetMap.BZH' || 'OpenStreetMap.BlackAndWhite' || 'OpenStreetMap.HOT' || 'MtbMap' || 'HikeBike.HikeBike'):
            ?> var default_attribute = osm_attribute; <?php
            break;

        case ('OpenStreetMap.France'):
            ?> var default_attribute = osm_fr_attribute; <?php
            break;

        case ('OpenTopoMap'):
            ?> var default_attribute = osm_otm_attribute; <?php
            break;

        case ('OpenMapSurfer.Roads' || 'OpenMapSurfer.Grayscale'):
            ?> var default_attribute = oms_attribute; <?php
            break;

        default:
            ?> var default_attribute = ''; <?php
        } ?>

    <!--Define default Leaflet basemap layer using leaflet.js, leaflet.providers.js, and L.TileLayer.PouchDBCached.js-->
    var defaultLayer = new L.tileLayer.provider('<?php echo $leaflet_defaultmap;?>', {
        useCache: '<?php echo $default_layer_cache;?>', <!--Use browser caching of tiles (recommended)?-->
        detectRetina: '<?php echo $map_retina;?>', <!--Use retina high resolution map tiles?-->
        attribution: default_attribute
    }).addTo(map1);

    <!--Determine basemaps and map groups for user selection-->
    var baseMaps = [
        { groupName: "<?php echo $lang["map_osm_group"];?>", <!--OSM group-->
            expanded: true,
            layers: {
                <?php if ($map_osm) { ?> "<?php echo $lang["map_osm"];?>" : osm_mapnik, <?php } ?>
                <?php if ($map_osmde) { ?> "<?php echo $lang["map_osmde"];?>" : osm_de, <?php } ?>
                <?php if ($map_osmfr) { ?> "<?php echo $lang["map_osmfr"];?>" : osm_fr, <?php } ?>
                <?php if ($map_osmbzh) { ?> "<?php echo $lang["map_osmbzh"];?>" : osm_bzh, <?php } ?>
                <?php if ($map_osmbw) { ?> "<?php echo $lang["map_osmbw"];?>" : osm_bw, <?php } ?>
                <?php if ($map_osmhot) { ?> "<?php echo $lang["map_osmhot"];?>" : osm_hot, <?php } ?>
                <?php if ($map_osmmtb) { ?> "<?php echo $lang["map_osmmtb"];?>" : osm_mtb, <?php } ?>
                <?php if ($map_osmhikebike) { ?> "<?php echo $lang["map_osmhikebike"];?>" : osm_hikebike, <?php } ?>
                <?php if ($map_otm) { ?> "<?php echo $lang["map_otm"];?>" : osm_otm, <?php } ?>
                <?php if ($map_omsroads) { ?> "<?php echo $lang["map_omsroads"];?>" : oms_roads, <?php } ?>
                <?php if ($map_omsgray) { ?> "<?php echo $lang["map_omsgray"];?>" : oms_gray <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_esri_group"];?>", <!--ESRI group-->
            expanded: true,
            layers: {
                <?php if ($map_esristreet) { ?> "<?php echo $lang["map_esristreet"];?>" : esri_street, <?php } ?>
                <?php if ($map_esridelorme) { ?> "<?php echo $lang["map_esridelorme"];?>" : esri_delorme, <?php } ?>
                <?php if ($map_esritopo) { ?> "<?php echo $lang["map_esritopo"];?>" : esri_topo, <?php } ?>
                <?php if ($map_esriimagery) { ?> "<?php echo $lang["map_esriimagery"];?>" : esri_imagery, <?php } ?>
                <?php if ($map_esriterrain) { ?> "<?php echo $lang["map_esriterrain"];?>" : esri_terrain, <?php } ?>
                <?php if ($map_esrirelief) { ?> "<?php echo $lang["map_esrirelief"];?>" : esri_relief, <?php } ?>
                <?php if ($map_esriphysical) { ?> "<?php echo $lang["map_esriphysical"];?>" : esri_physical, <?php } ?>
                <?php if ($map_esriocean) { ?> "<?php echo $lang["map_esriocean"];?>" : esri_ocean, <?php } ?>
                <?php if ($map_esrinatgeo) { ?> "<?php echo $lang["map_esrinatgeo"];?>" : esri_natgeo, <?php } ?>
                <?php if ($map_esrigray) { ?> "<?php echo $lang["map_esrigray"];?>" : esri_gray <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_stamen_group"];?>", <!--Stamen group-->
            expanded: true,
            layers: {
                <?php if ($map_stamentoner) { ?> "<?php echo $lang["map_stamentoner"];?>" : stamen_toner, <?php } ?>
                <?php if ($map_stamentonerlt) { ?> "<?php echo $lang["map_stamentonerlt"];?>" : stamen_tonerlt, <?php } ?>
                <?php if ($map_stamentonerback) { ?> "<?php echo $lang["map_stamentonerback"];?>" : stamen_tonerback, <?php } ?>
                <?php if ($map_stamenterrain) { ?> "<?php echo $lang["map_stamenterrain"];?>" : stamen_terrain, <?php } ?>
                <?php if ($map_stamenterrainback) { ?> "<?php echo $lang["map_stamenterrainback"];?>" : stamen_terrainback, <?php } ?>
                <?php if ($map_stamenrelief) { ?> "<?php echo $lang["map_stamenrelief"];?>" : stamen_relief, <?php } ?>
                <?php if ($map_stamenwatercolor) { ?> "<?php echo $lang["map_stamenwatercolor"];?>" : stamen_watercolor <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_hydda_group"];?>", <!--Hydda group-->
            expanded: true,
            layers: {
                <?php if ($map_hyddafull) { ?> "<?php echo $lang["map_hyddafull"];?>" : hydda_full, <?php } ?>
                <?php if ($map_hyddabase) { ?> "<?php echo $lang["map_hyddabase"];?>" : hydda_base <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_nasagibs_group"];?>", <!--NASA GIBS group-->
            expanded: true,
            layers: {
                <?php if ($map_nasagibscolor) { ?> "<?php echo $lang["map_nasagibscolor"];?>" : nasa_gibscolor, <?php } ?>
                <?php if ($map_nasagibsfalsecolor) { ?> "<?php echo $lang["map_nasagibsfalsecolor"];?>" : nasa_gibsfalsecolor, <?php } ?>
                <?php if ($map_nasagibsnight) { ?> "<?php echo $lang["map_nasagibsnight"];?>" : nasa_gibsnight <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_tf_group"];?>", <!--Thunderforest group-->
            expanded: true,
            layers: {
                <?php if ($map_tfocm) { ?> "<?php echo $lang["map_tfocm"];?>" : tf_ocm, <?php } ?>
                <?php if ($map_tftransport) { ?> "<?php echo $lang["map_tftransport"];?>" : tf_transport, <?php } ?>
                <?php if ($map_tftransportdark) { ?> "<?php echo $lang["map_tftransportdark"];?>" : tf_transportdark, <?php } ?>
                <?php if ($map_tflandscape) { ?> "<?php echo $lang["map_tflandscape"];?>" : tf_landscape, <?php } ?>
                <?php if ($map_tfoutdoors) { ?> "<?php echo $lang["map_tfoutdoors"];?>" : tf_outdoors, <?php } ?>
                <?php if ($map_tfpioneer) { ?> "<?php echo $lang["map_tfpioneer"];?>" : tf_pioneer <?php } ?>
            }
        },

        { groupName: "<?php echo $lang["map_mapbox_group"];?>", <!--Mapbox group-->
            expanded: true,
            layers: {
                <?php if ($map_mapbox) { ?> "<?php echo $lang["map_mapbox"];?>" : mapbox <?php } ?>
            }
        }

    ];

    <!--Set styled layer control options for basemaps and add to the Leaflet map using styledLayerControl.js-->
    var options = {
        container_maxHeight: "<?php echo $layer_controlheight?>px",
        group_maxHeight: "180px",
        exclusive: false
    };

    var control = L.Control.styledLayerControl(baseMaps,options);
    map1.addControl(control);

    <!--Show zoom history navigation bar and add to Leaflet map using Leaflet.NavBar.min.js-->
    <?php if ($leaflet_zoomnavbar)
        { ?>
        L.control.navbar().addTo(map1); <?php
        } ?>

    <!--Add a scale bar to the Leaflet map using leaflet.min.js-->
    new L.control.scale().addTo(map1);

    <!--Add download map button to the Leaflet map using bundle.min.js-->
    L.easyPrint({
        title: '<?php echo $lang['leaflet_mapdownload'];?>',
        position: 'bottomleft',
        sizeModes: ['Current', 'A4Landscape', 'A4Portrait'],
        exportOnly: true,
        filename: 'search_results_map',
        customWindowTitle: 'Search Results Map'
    }).addTo(map1);

    <!--Add a KML overlay to the Leaflet map using leaflet-omnivore.min.js-->
    <?php if ($leaflet_kml)
        { ?>
        omnivore.kml('<?php echo $baseurl?>/filestore/system/<?php echo $leaflet_kml_file?>').addTo(map1); <?php
        } ?>

    <!--If no data (markers), only show the empty Leaflet map-->
    <?php if (!empty($geomarker))
        { ?>
        <!--Setup and configure initial marker info from resource data-->
        var geomarker = <?php echo str_replace(array('"','\\'),'',json_encode($geomarker))?>;
        var markerArray = [];
        var win_url;

        <!--Setup marker clustering using leaflet.markercluster.js for many overlapping markers common in low zoom levels-->
        var markers = L.markerClusterGroup({
            maxClusterRadius: 75,
            disableClusteringAtZoom: 14,
            chunkedLoading: true, <!--Load markers in chunks to avoid slow browser response-->
            elementsPlacementStrategy: 'original-locations' <!--Cluster items placement strategy-->
        });

        <!--Cycle through the resources to create markers as needed and colored by resource type-->
        for (var i=0; i<geomarker.length; i++)
            {
            var lon = geomarker[i][0]; <!--Resource longitude value-->
            var lat = geomarker[i][1]; <!--Resource latitude value-->
            var rf = geomarker[i][2]; <!--Resource reference value-->
            var rtype = geomarker[i][3]; <!--Resource type-->
            var cmfm = geomarker[i][4]; <!--Custom metadata field marker coloring-->

            <!--Check for resources without geolocation or invalid coordinates and skip those-->
            if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180)
                { <?php
                // Check if using a custom metadata field for coloring the markers and redefine rtype.
                if (isset($marker_metadata_field))
                    {
                    for ($i=0; $i<8; $i++)
                        { ?>
                        if (cmfm >= <?php echo $marker_metadata_array[$i]['min']?> && cmfm <= <?php echo $marker_metadata_array[$i]['max']?>)
                            {
                            rtype = <?php echo ($i + 1);?>;
                            } <?php
                        }
                    } ?>

                <!--Set each resource marker color based on resource type or metadata field to marker color mapping up to eight-->
                switch(rtype) {
                    case 1:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[0]]);?>Icon;
                        break;
                    case 2:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[1]]);?>Icon;
                        break;
                    case 3:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[2]]);?>Icon;
                        break;
                    case 4:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[3]]);?>Icon;
                        break;
                    case 5:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[4]]);?>Icon;
                        break;
                    case 6:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[5]]);?>Icon;
                        break;
                    case 7:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[6]]);?>Icon;
                        break;
                    case 8:
                        iconColor = <?php echo strtolower($marker_colors[$marker_color_def[7]]);?>Icon;
                        break;
                    default:
                        iconColor = blackIcon;
                    }

                <!--Define the marker arrays for the markers, zoom to the markers, and marker click function using leaflet.js and leaflet-color-markers.js-->
                <!--Create a marker for each resource for map zoom to the markers-->
                markerArray.push(new L.marker([lat, lon], {
                    opacity: 0
                }).addTo(map1));

                <!--Create a marker for each resource-->
                var marker = new L.marker([lat, lon], {
                    icon: iconColor,
                    title: 'ID# ' + rf,
                    riseOnHover: true,
                    win_url: geomarker[i][2]
                }).on('click', showModal);

                <!--Add markers to the layer array-->
                markers.addLayer(marker);
                }
            }

        <!--Add the markers layer to the map-->
        map1.addLayer(markers);

        <!--Zoom to the markers on the map regardless of the initial view-->
        var group = L.featureGroup(markerArray);
        map1.fitBounds(group.getBounds().pad(0.1));

        <!--On marker click, open a modal corresponding to the specific resource-->
        function showModal(e)
            {
            ModalLoad(baseurl + '/pages/view.php?ref=' + this.options.win_url);
            }

  <?php } ?>
</script>

<!--Create a map marker legend below the map and only show for defined types up to eight.-->
<p style="margin-top:4px;margin-bottom:0px;">
    <b> <?php

    // Resource type color markers legend.
    if (!isset($marker_metadata_field) || $lang['custom_metadata_markers'] == "")
        {
        echo $lang["legend_text"]?>&nbsp;</b> <?php

        for ($i = 1; $i < 9; $i++) // Start at 1, since we are not using the Global resource type.
            {
            if (!empty(get_resource_type_name($i)))
                {
                $ic = $i - 1; // Start at 0 for $marker_color_def array.

                ?> <img src="../lib/leaflet_plugins/leaflet-colormarkers-1.0.0/img/marker-icon-<?php echo strtolower($marker_colors[$marker_color_def[$ic]])?>.png" alt="<?php echo $marker_colors[$marker_color_def[$ic]]?> Icon" style="width:19px;height:31px;"> <?php echo get_resource_type_name($i); ?> &nbsp; <?php
                }
            }
        }
    else // Custom metadata field color markers legend.
        {
        echo $lang['custom_metadata_markers']?>&nbsp;</b> <?php

        // Loop through and create the custom color marker legend text.
        for ($i = 0; $i < 8; $i++)
            {
            $ltext[$i] = $marker_metadata_array[$i]['min'] . "-" . $marker_metadata_array[$i]['max'];
            }

        for ($i = 0; $i < 8; $i++)
            {
            ?> <img src="../lib/leaflet_plugins/leaflet-colormarkers-1.0.0/img/marker-icon-<?php echo strtolower($marker_colors[$marker_color_def[$i]])?>.png" alt="<?php echo $marker_colors[$marker_color_def[$i]]?> Icon" style="width:19px;height:31px;"> <?php echo $ltext[$i]; ?> &nbsp; <?php
            }
        } ?>
</p>

