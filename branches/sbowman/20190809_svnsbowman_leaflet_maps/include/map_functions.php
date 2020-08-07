<?php
// Leaflet.js Map Library Files and Functions

global $baseurl, $map_default_cache, $map_layer_cache, $map_zoomnavbar, $map_kml;

// Load Leaflet and plugin files.
?>
<!--Leaflet.js v1.6.0 files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.min.js"></script>

<!--Leaflet Providers v1.10.2 plugin files-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-providers-1.10.2/leaflet-providers.babel.min.js"></script>

<!--Leaflet PouchDBCached v1.0.0 plugin file with PouchDB v7.1.1 file-->
<?php if ($map_default_cache || $map_layer_cache)
    { ?>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/pouchdb-7.1.1/pouchdb-7.1.1.min.js"></script>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-PouchDBCached-1.0.0/L.TileLayer.PouchDBCached.min.js"></script> <?php
    } ?>

<!--Leaflet MarkerCluster v1.4.1 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/MarkerCluster.Default.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-markercluster-1.4.1/dist/leaflet.markercluster.min.js"></script>

<!--Leaflet ColorMarkers v1.0.0 plugin file-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-colormarkers-1.0.0/js/leaflet-color-markers.min.js"></script>

<!--Leaflet NavBar v1.0.1 plugin files-->
<?php if ($map_zoomnavbar)
    { ?>
    <link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-NavBar-1.0.1/src/Leaflet.NavBar.css"/>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-NavBar-1.0.1/src/Leaflet.NavBar.min.js"></script> <?php
    } ?>

<!--Leaflet Omnivore v0.3.1 plugin file-->
<?php if ($map_kml)
    { ?>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-omnivore-0.3.1/leaflet-omnivore.min.js"></script> <?php
    } ?>

<!--Leaflet EasyPrint v2.1.9 plugin file-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-easyPrint-2.1.9/dist/bundle.js"></script>

<!--Leaflet StyledLayerControl v5/16/2019 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/css/styledLayerControl.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/src/styledLayerControl.min.js"></script>

<!--Leaflet Zoomslider v0.7.1 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.min.js"></script>

<!--Leaflet Shades v1.0.2 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-shades-1.0.2/src/css/leaflet-shades.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-shades-1.0.2/leaflet-shades.min.js"></script>

<!--Leaflet EasyPrint v2.1.9 plugin file-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-easyPrint-2.1.9/dist/bundle.js"></script>

<!--Leaflet Control Geocoder 1.10.0 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.min.js"></script>

<!--Polyfill for Internet Explorer and Edge browser compatibility-->
<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2Ces2016%2Ces5%2Ces6%2Ces2017%2Cdefault%2Ces2018%2Ces7"></script>
<?php

// To add additional basemap sources, see http://leaflet-extras.github.io/leaflet-providers/preview/index.html for the provider names, attribution, maximum zoom level, and any other required provider parameters, and add to the appropriate basemap group below or create a new basemap group.  Will also need to add additional code into the <!--Determine basemaps and map groups for user selection--> section on each PHP page using Leaflet maps (../pages/geo_search.php), the Leaflet Providers section in ../include/config.default.php, and the appropriate providers group section in ../languages/en.php.

// Define available Leaflet basemaps groups and layers using leaflet.providers.js, L.TileLayer.PouchDBCached.js, and styledLayerControl.js.
function leaflet_osm_basemaps() // OpenStreetMap basemaps.
    {
    global $map_default_cache, $map_retina;

    $osm = "<!--OpenStreetMap (OSM) basemap group-->
        var osm_attribute = 'Map data © <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors';

        var osm_mapnik = L.tileLayer.provider('OpenStreetMap.Mapnik', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 19,
            attribution: osm_attribute
        });

        var osm_de = L.tileLayer.provider('OpenStreetMap.DE', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 18,
            attribution: osm_attribute
        });

        var osm_fr_attribute = '&copy; Openstreetmap France | &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>';
        var osm_fr = L.tileLayer.provider('OpenStreetMap.France', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 20,
            attribution: osm_fr_attribute
        });

        var osm_ch = L.tileLayer.provider('OpenStreetMap.CH', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 18,
            attribution: osm_attribute
        });

        var osm_bzh = L.tileLayer.provider('OpenStreetMap.BZH', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 19,
            attribution: osm_attribute
        });

        var osm_hot = L.tileLayer.provider('OpenStreetMap.HOT', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 19,
            attribution: osm_attribute
        });

        var osm_hikebike = L.tileLayer.provider('HikeBike.HikeBike', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 19,
            attribution: osm_attribute
        });

        var osm_mtb = L.tileLayer.provider('MtbMap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: osm_attribute
        });

        var osm_otm_attribute = 'Map data: &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>, <a href=\"http://viewfinderpanoramas.org\">SRTM</a> | Map style: &copy; <a href=\"https://opentopomap.org\">OpenTopoMap</a> (<a href=\"https://creativecommons.org/licenses/by-sa/3.0/\">CC-BY-SA</a>)';
        var osm_otm = L.tileLayer.provider('OpenTopoMap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 17,
            attribution: osm_otm_attribute
        }); ";

    return $osm;
    }

function leaflet_esri_basemaps() // ESRI basemaps.
    {
    global $map_default_cache, $map_retina;

    $esri = "<!--ESRI basemap group-->
        var esri_street_attribute = 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012';
        var esri_street = L.tileLayer.provider('Esri.WorldStreetMap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_street_attribute
        });

        var esri_delorme_attribute = 'Tiles &copy; Esri &mdash; Copyright: &copy;2012 DeLorme';
        var esri_delorme = L.tileLayer.provider('Esri.DeLorme', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 1,
            maxZoom: 11,
            attribution: esri_delorme_attribute
        });

        var esri_topo_attribute = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community';
        var esri_topo = L.tileLayer.provider('Esri.WorldTopoMap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_topo_attribute
        });

        var esri_imagery_attribute = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
        var esri_imagery = L.tileLayer.provider('Esri.WorldImagery', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: esri_imagery_attribute
        });

        var esri_terrain_attribute = 'Tiles &copy; Esri &mdash; Source: USGS, Esri, TANA, DeLorme, and NPS';
        var esri_terrain = L.tileLayer.provider('Esri.WorldTerrain', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 13,
            attribution: esri_terrain_attribute
        });

        var esri_relief_attribute = 'Tiles &copy; Esri &mdash; Source: Esri';
        var esri_relief = L.tileLayer.provider('Esri.WorldShadedRelief', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 13,
            attribution: esri_relief_attribute
        });

        var esri_physical_attribute = 'Tiles &copy; Esri &mdash; Source: US National Park Service';
        var esri_physical = L.tileLayer.provider('Esri.WorldPhysical', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 8,
            attribution: esri_physical_attribute
        });

        var esri_ocean_attribute = 'Tiles &copy; Esri &mdash; Sources: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri';
        var esri_ocean = L.tileLayer.provider('Esri.OceanBasemap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 13,
            attribution: esri_ocean_attribute
        });

        var esri_natgeo_attribute = 'Tiles &copy; Esri &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC';
        var esri_natgeo = L.tileLayer.provider('Esri.NatGeoWorldMap', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 16,
            attribution: esri_natgeo_attribute
        });

        var esri_gray_attribute = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ';
        var esri_gray = L.tileLayer.provider('Esri.WorldGrayCanvas', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 16,
            attribution: esri_gray_attribute
        }); ";

    return $esri;
    }

function leaflet_stamen_basemaps() // Stamen basemaps.
    {
    global $map_layer_cache, $map_retina;

    $stamen = "<!--Stamen basemap group-->
        var stamen_attribute = 'Map tiles by <a href=\"http://stamen.com\">Stamen Design</a>, <a href=\"http://creativecommons.org/licenses/by/3.0\">CC BY 3.0</a> &mdash; Map data &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>';

        var stamen_toner = L.tileLayer.provider('Stamen.Toner', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 20,
            ext: 'png',
            attribution: stamen_attribute
        });

        var stamen_tonerlt = L.tileLayer.provider('Stamen.TonerLite', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 20,
            ext: 'png',
            attribution: stamen_attribute
        });

        var stamen_tonerback = L.tileLayer.provider('Stamen.TonerBackground', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 20,
            ext: 'png',
            attribution: stamen_attribute
        });

        var stamen_terrain = L.tileLayer.provider('Stamen.Terrain', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 18,
            ext: 'png',
            attribution: stamen_attribute
        });

        var stamen_terrainback = L.tileLayer.provider('Stamen.TerrainBackground', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 18,
            ext: 'png',
            attribution: stamen_attribute
        });

        var stamen_relief = L.tileLayer.provider('Stamen.TopOSMRelief', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 0,
            maxZoom: 20,
            ext: 'jpg',
            attribution: stamen_attribute
        });

        var stamen_watercolor = L.tileLayer.provider('Stamen.Watercolor', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 1,
            maxZoom: 16,
            ext: 'jpg',
            attribution: stamen_attribute
        }); ";

    echo $stamen;
    }

function leaflet_hydda_basemaps() // Hydda basemaps.
    {
    global $map_layer_cache, $map_retina;

    $hydda = "<!--Hydda basemap group-->
        var hydda_attribute = 'Tiles courtesy of <a href=\"http://openstreetmap.se/\" target=\"_blank\">OpenStreetMap Sweden</a> &mdash; Map data &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>';

        var hydda_full = L.tileLayer.provider('Hydda.Full', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 18,
            attribution: hydda_attribute
        });

        var hydda_base = L.tileLayer.provider('Hydda.Base', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 18,
            attribution: hydda_attribute
        }); ";

    echo $hydda;
    }

function leaflet_nasa_basemaps() // NASA basemaps.
    {
    global $map_layer_cache, $map_retina;

    $nasa = "<!--NASA GIBS basemap group-->
        var nasa_attribute = 'Imagery provided by services from the Global Imagery Browse Services (GIBS), operated by the NASA/GSFC/Earth Science Data and Information System (<a href=\"https://earthdata.nasa.gov\">ESDIS</a>) with funding provided by NASA/HQ.';

        var nasa_gibscolor = L.tileLayer.provider('NASAGIBS.ModisTerraTrueColorCR', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 1,
            maxZoom: 9,
            format: 'jpg',
            attribution: nasa_attribute
        });

        var nasa_gibsfalsecolor = L.tileLayer.provider('NASAGIBS.ModisTerraBands367CR', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 1,
            maxZoom: 9,
            format: 'jpg',
            attribution: nasa_attribute
        });

        var nasa_gibsnight = L.tileLayer.provider('NASAGIBS.ViirsEarthAtNight2012', {
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            minZoom: 1,
            maxZoom: 8,
            format: 'jpg',
            attribution: nasa_attribute
        }); ";

    echo $nasa;
    }

function leaflet_thunderforest_basemaps() // Thunderforest basemaps.
    {
    global $map_layer_cache, $map_retina, $map_tfapi;

    $tf = "<!--Thunderforest basemap group (requires an API key)-->
        var tf_attribute = '&copy; <a href=\"http://www.thunderforest.com/\">Thunderforest</a>, &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>';

        var tf_ocm = L.tileLayer.provider('Thunderforest.OpenCycleMap', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });

        var tf_transport = L.tileLayer.provider('Thunderforest.Transport', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });

        var tf_transportdark = L.tileLayer.provider('Thunderforest.TransportDark', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });

        var tf_landscape = L.tileLayer.provider('Thunderforest.Landscape', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });

        var tf_outdoors = L.tileLayer.provider('Thunderforest.Outdoors', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });

        var tf_pioneer = L.tileLayer.provider('Thunderforest.Pioneer', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        }); 
        
        var tf_mobileatlas = L.tileLayer.provider('Thunderforest.MobileAtlas', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        });
        
        var tf_neighbourhood = L.tileLayer.provider('Thunderforest.Neighbourhood', {
            apikey: '<?php echo $map_tfapi?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 22,
            attribution: tf_attribute
        }); ";

    echo $tf;
    }

function leaflet_mapbox_basemaps() // Mapbox basemaps.
    {
    global $map_layer_cache, $map_retina, $map_mapboxid, $map_mapboxtoken, $map_mapboxattribution;

    $mapbox = "<!--Mapbox basemaps group (requires API keys)-->
        var mapbox = L.tileLayer.provider('MapBox', {
            id: '<?php echo $map_mapboxid?>',
            accessToken: '<?php echo $map_mapboxtoken?>',
            useCache: '<?php echo $map_layer_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            attribution: '<?php echo $map_mapboxattribution?>'
        }); ";

    echo $mapbox;
    }

// Determine the map zoom from the geolocation coordinates numeric precision.
function leaflet_map_zoom($map_zoom)
    {
    global $resource;

    // If no zoom level is set or is non-numeric, define as 0 to enable automatic zoom assignment below.
    $zoom = trim($map_zoom);
    if (is_null($zoom) || $zoom == "" || !is_numeric($zoom))
        {
        $zoom = 0;
        }

    if (!($zoom >= 2 && $zoom <= 21) && is_numeric($zoom))
        {
        $zoom = 16;
        $siglon = round(100000 * abs($resource["geo_long"]))%100000;
        $siglat = round(100000 * abs($resource["geo_lat"]))%100000;
        if ($siglon%100000 == 0 && $siglat%100000 == 0)
            {
            $zoom = 3;
            }
        elseif ($siglon%10000 == 0 && $siglat%10000 == 0)
            {
            $zoom = 6;
            }
        elseif ($siglon%1000 == 0 && $siglat%1000 == 0)
            {
            $zoom = 10;
            }
        elseif ($siglon%100 == 0 && $siglat%100 == 0)
            {
            $zoom = 15;
            }
        }

    return $zoom;
    }

// Parse the resource polygon string for latitude and longitude minimum and maximum and format polygon string.
function leaflet_polygon_parsing($fields, $minmax = true)
    {
    global $map_polygon_field;

    // Search resource $fields array for the $map_polygon_field.
    $key1 = array_search($map_polygon_field, array_column($fields, 'ref'));

    if ($minmax)
        {
        // Strip coordinate pair parathenses from polygon array.
        $values = str_replace(")", "", str_replace("(", "", explode(",", $fields[$key1]['value'])));

        // Determine minimum and maximum latitude values.
        $lat_values = array($values[0], $values[2], $values[4], $values[6]);
        $polygon['lat_min'] = min($lat_values);
        $polygon['lat_max'] = max($lat_values);

        // Determine minimum and maximum longitude values.
        $long_values = array($values[1], $values[3], $values[5], $values[7]);
        $polygon['long_min'] = min($long_values);
        $polygon['long_max'] = max($long_values);
        }

    // Format polygon string for Leaflet footprint display below.
    $polygon1 = str_replace("(", "[", $fields[$key1]['value']);
    $polygon1 = str_replace(")", "]", $polygon1);
    $polygon['values'] = "[" . $polygon1 . "]";

    return $polygon;
    }

// Check geolocation coordinates for valid numeric values.
function leaflet_coordinate_check($coordinate, $type)
    {
    $check = false;
    if (!is_numeric($coordinate))
        {
        return false;
        }

    if ($type == "latitude" && $coordinate >= -90 && $coordinate <= 90)
        {
        $check = true;
        }

    if ($type == "longitude" && $coordinate >= -180 && $coordinate <= 180)
        {
        $check = true;
        }

    return $check;
    }

// Create a map color markers legend.
function leaflet_markers_legend()
    {
    global $lang, $marker_metadata_field, $marker_colors, $marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8, $marker_metadata_array;

    $marker_color_def = array($marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8);

    if (!isset($marker_metadata_field) || $lang['custom_metadata_markers'] == "")
        { ?>
        <b> <?php echo $lang["legend_text"]?>&nbsp;</b> <?php

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
        { ?>
        <b> <?php echo $lang['custom_metadata_markers']?>&nbsp;</b> <?php

        // Loop through and create the custom color marker legend text.
        for ($i = 0; $i < 8; $i++)
            {
            $ltext[$i] = $marker_metadata_array[$i]['min'] . "-" . $marker_metadata_array[$i]['max'];
            }

        for ($i = 0; $i < 8; $i++)
            {
            ?> <img src="../lib/leaflet_plugins/leaflet-colormarkers-1.0.0/img/marker-icon-<?php echo strtolower($marker_colors[$marker_color_def[$i]])?>.png" alt="<?php echo $marker_colors[$marker_color_def[$i]]?> Icon" style="width:19px;height:31px;"> <?php echo $ltext[$i]; ?> &nbsp; <?php
            }
        }
    }
