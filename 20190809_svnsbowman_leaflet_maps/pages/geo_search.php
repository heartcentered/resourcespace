<?php
// Geographic Map Search for Resources Using Leaflet.js and Various Leaflet Plugins
// Last Update: 11/22/2019, Steve D. Bowman

include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; 
include "../include/resource_functions.php";
include "../include/header.php";
include "../include/map_functions.php";

// Setup initial map variables.
global $default_display, $geo_search_modal_results, $baseurl, $mapsearch_height, $map_default,  $map_centerview, $map_zoomslider, $map_zoomnavbar, $map_kml, $map_kml_file, $map_default_cache, $map_layer_cache, $map_retina;
$zoomslider = 'false';
$zoomcontrol = 'true';

// Set Leaflet map search view height and layer control container height based on $mapheight.
if (isset($mapsearch_height))
    {
    $map1_height = $mapsearch_height;
    $layer_controlheight = $mapsearch_height - 40;
    }
else // Default values.
    {
    $map1_height = 500;
    $layer_controlheight = 460;
    }

// Show zoom slider instead of default Leaflet zoom control?
if ($map_zoomslider)
    {
    $zoomslider = 'true';
    $zoomcontrol = 'false';
    }

$display = getvalescaped("display", $default_display);
if ($default_display == "map" || $display == "map")
    {
    $geo_search_modal_results = false;
    }

?>
<!--Leaflet.js v1.6.0 files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.min.js"></script>

<!--Leaflet Providers v1.9.0 plugin files-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-providers-1.9.0/leaflet-providers.babel.min.js"></script>

<!--Leaflet PouchDBCached v1.0.0 plugin file with PouchDB v7.1.1 file-->
<?php if ($map_default_cache || $map_layer_cache)
    { ?>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/pouchdb-7.1.1/pouchdb-7.1.1.min.js"></script>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-PouchDBCached-1.0.0/L.TileLayer.PouchDBCached.min.js"></script> <?php
    } ?>

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

<!--Leaflet Control Geocoder 1.10.0 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.min.js"></script>

<!--Polyfill for Internet Explorer and Edge browser compatibility-->
<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2Ces2016%2Ces5%2Ces6%2Ces2017%2Cdefault%2Ces2018%2Ces7"></script>

<div class="BasicsBox">
<h1><?php echo $lang["geographicsearch"] ?></h1>

<!--Map introtext-->
<div id="map_introtext" style="margin-top:0px; margin-bottom:0px; width: 99%;">
    <p> <?php echo $lang['search_map_introtext'];?> </p>
</div>

<!-- Drag mode selector -->
<div id="GeoDragMode">
    <?php echo $lang["geodragmode"] ?>:&nbsp;
    <input type="radio" name="dragmode" id="dragmodepan" checked="checked" onClick="" /><label for="dragmodepan"><?php echo $lang["geodragmodepan"] ?></label>
        &nbsp;
    <input type="radio" name="dragmode" id="dragmodearea" onClick="map1.editTools.startRectangle()" /><label for="dragmodearea"><?php echo $lang["geodragmodeareaselect"] ?></label>
</div>

<!--Setup Leaflet map container with sizing-->
<div id="search_map" style="width: 99%; margin-top:0px; margin-bottom:0px; height: <?php echo $map1_height;?>px; display:block; border:1px solid black; float:none; overflow: hidden;">
</div>

<script type="text/javascript">
    var Leaflet = L.noConflict();

    // Setup and define the Leaflet map with the initial view using leaflet.js and L.Control.Zoomslider.js.
    var map1 = new L.map('search_map', {
        editable: true,
        preferCanvas: true,
        renderer: L.canvas(),
        zoomsliderControl: <?php echo $zoomslider?>,
        zoomControl: <?php echo $zoomcontrol?>
    }).setView(<?php echo $map_centerview;?>);

    <!--Define available Leaflet basemaps groups and layers from ../include/map_functions.php-->
    <?php
    echo leaflet_osm_basemaps();
    echo leaflet_esri_basemaps();
    echo leaflet_stamen_basemaps();
    echo leaflet_hydda_basemaps();
    echo leaflet_nasa_basemaps();
    echo leaflet_thunderforest_basemaps();
    echo leaflet_mapbox_basemaps();
    ?>

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

    <!--Define default Leaflet basemap layer using leaflet.js, leaflet.providers.js, and L.TileLayer.PouchDBCached.js-->
    var defaultLayer = new L.tileLayer.provider('<?php echo $map_default;?>', {
        useCache: '<?php echo $map_default_cache;?>', <!--Use browser caching of tiles (recommended)?-->
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
                <?php if ($map_osmhot) { ?> "<?php echo $lang["map_osmhot"];?>" : osm_hot, <?php } ?>
                <?php if ($map_osmmtb) { ?> "<?php echo $lang["map_osmmtb"];?>" : osm_mtb, <?php } ?>
                <?php if ($map_osmhikebike) { ?> "<?php echo $lang["map_osmhikebike"];?>" : osm_hikebike, <?php } ?>
                <?php if ($map_otm) { ?> "<?php echo $lang["map_otm"];?>" : osm_otm, <?php } ?>
                <?php if ($map_omsroads) { ?> "<?php echo $lang["map_omsroads"];?>" : oms_roads <?php } ?>
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
                <?php if ($map_tfpioneer) { ?> "<?php echo $lang["map_tfpioneer"];?>" : tf_pioneer, <?php } ?>
                <?php if ($map_tfmobileatlas) { ?> "<?php echo $lang["map_tfmobileatlas"];?>" : tf_mobileatlas, <?php } ?>
                <?php if ($map_tfneighbourhood) { ?> "<?php echo $lang["map_tfneighbourhood"];?>" : tf_neighbourhood <?php } ?>
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
        group_maxHeight: "380px",
        exclusive: false
    };

    var control = L.Control.styledLayerControl(baseMaps,options);
    map1.addControl(control);

    <!--Add geocoder search bar using control.geocoder.min.js-->
    L.Control.geocoder().addTo(map1);

    <!--Show zoom history navigation bar and add to Leaflet map using Leaflet.NavBar.min.js-->
    <?php if ($map_zoomnavbar)
        { ?>
        L.control.navbar().addTo(map1); <?php
        } ?>

    <!--Add a scale bar to the Leaflet map using leaflet.min.js-->
    new L.control.scale().addTo(map1);

    <!--Add download map button to the Leaflet map using bundle.min.js-->
    L.easyPrint({
        title: '<?php echo $lang['map_download'];?>',
        position: 'bottomleft',
        sizeModes: ['Current', 'A4Landscape', 'A4Portrait'],
        exportOnly: true,
        filename: 'search_results_map',
        customWindowTitle: '<?php echo $lang['map_print_title'];?>'
    }).addTo(map1);

    <!--Add a KML overlay to the Leaflet map using leaflet-omnivore.min.js-->
    <?php if ($map_kml)
        { ?>
        omnivore.kml('<?php echo $baseurl?>/filestore/system/<?php echo $map_kml_file?>').addTo(map1); <?php
        } ?>

    <!--Fix for Microsoft Edge and Internet Explorer browsers-->
    map1.invalidateSize(true);

    <!--Add an Area of Interest (AOI) selection box to the Leaflet map using leaflet-shades.js-->
    var shades = new L.LeafletShades().addTo(map1);

    <!--Get AOI coordinates-->
    shades.on('shades:bounds-changed', function(e) {
        <!--Get AOI box coordinates in World Geodetic System of 1984 (WGS84, EPSG:4326)-->
        var trLat = e['bounds']['_northEast']['lat'];
        var trLon = e['bounds']['_northEast']['lng'];
        var blLat = e['bounds']['_southWest']['lat'];
        var blLon = e['bounds']['_southWest']['lng'];

        <!--Create specially encoded geocoordinate search string to avoid keyword splitting-->
        var url = "<?php echo $baseurl_short?>pages/search.php?search=!geo" + (blLat + "b" + blLon + "t" + trLat + "b" + trLon).replace(/\-/gi,'m').replace(/\./gi,'p');

        <!--Store the map window coordinate position to make it easier when returning for another search-->
        var mapCenter = map1.getCenter();
        SetCookie("geobound",mapCenter[1] + "," + mapCenter[0] + "," + map1.getZoom());

        <?php // Show the map in a modal.
        if ($geo_search_modal_results)
            { ?>
            ModalClose();
            ModalLoad(url);
            <?php
            }

        // Show the map in a new window.
        if ($display == "map" && !$geo_search_modal_results)
            { ?>
            window.open(url, '_blank'); <?php
            }
        elseif (!$geo_search_modal_results)
            { ?>
            window.location.href = url; <?php
            } ?>
    });

    <?php if (isset($_COOKIE["geobound"]))
        {
        $bounds = $_COOKIE["geobound"];
        }
    else
        {
        $bounds = $geolocation_default_bounds;
        }
    $bounds = explode(",",$bounds);
    ?>
</script>
</div>

<?php
include "../include/footer.php";
?>

