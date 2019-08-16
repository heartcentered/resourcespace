<?php
// Leaflet.js Map Functions
// Last Update: 8/16/2019, Steve D. Bowman

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
        });

        var oms_attribute = 'Imagery from <a href=\"http://giscience.uni-hd.de/\">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>';
        var oms_roads = L.tileLayer.provider('OpenMapSurfer.Roads', {
            useCache: '<?php echo $map_default_cache;?>',
            detectRetina: '<?php echo $map_retina;?>',
            maxZoom: 19,
            attribution: oms_attribute
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

// Determine the map zoom from the coordinates numeric precision.
function leaflet_map_zoom($resource)
    {
    $zoom = $resource["mapzoom"];
    if (!($zoom >= 2 && $zoom <= 21))
        {
        $zoom = 18;
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
function leaflet_polygon_parsing($fields)
    {
    global $map_polygon_field;

    foreach ($fields as $field)
        {
        if ($field['resource_type_field'] == $map_polygon_field)
            {
            // Strip coordinate pair parathenses from polygon array.
            $values = str_replace(")", "", str_replace("(", "", explode(",", $field['value'])));

            // Determine minimum and maximum latitude values.
            $lat_values = array($values[0], $values[2], $values[4], $values[6]);
            $polygon['lat_min'] = min($lat_values);
            $polygon['lat_max'] = max($lat_values);

            // Determine minimum and maximum longitude values.
            $long_values = array($values[1], $values[3], $values[5], $values[7]);
            $polygon['long_min'] = min($long_values);
            $polygon['long_max'] = max($long_values);

            // Format polygon string for Leaflet footprint display below.
            $polygon1 = str_replace("(", "[", $field['value']);
            $polygon1 = str_replace(")", "]", $polygon1);
            $polygon['values'] = "[" . $polygon1 . "]";

            break;
            }
        }
    return $polygon;
    }
