<?php
// Resource Map Geolocation Edit Using Leaflet.js and Various Leaflet Plugins
// Last Edit 11/24/2019, Steve D. Bowman

// Check if geolocation/maps have been disabled.
global $disable_geocoding, $lang;
if($disable_geocoding)
    {
    header('HTTP/1.1 403 Forbidden');
    exit($lang['error-geocodingdisabled']);
    }

include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";
include "../include/header.php";
include "../include/map_functions.php";

// Setup initial map variables.
global $default_display, $baseurl, $mapsearch_height, $map_default, $map_centerview, $map_zoomslider, $map_zoomnavbar, $map_kml, $map_kml_file, $map_default_cache, $map_layer_cache, $map_retina, $mapedit_mapheight, $layer_controlheight;
$zoomslider = 'false';
$zoomcontrol = 'true';

// Show zoom slider instead of default Leaflet zoom control?
if ($map_zoomslider)
    {
    $zoomslider = 'true';
    $zoomcontrol = 'false';
    }

// Fetch the resource data.
$ref = getvalescaped('ref', '', true);
$geo_lat = getvalescaped('new_lat', '');
$geo_long = getvalescaped('new_long', '');
$zoom = getvalescaped('new_zoom', '');

// See if we came from the ../pages/geolocate_collection.php page.
$geocol = getvalescaped('geocol', '', true);
if ($ref == '')
    {
    die;
    }
$resource = get_resource_data($ref);
if ($resource == false)
    {
    die;
    }

// Check if the user is allowed to edit this resource.
if (!get_edit_access($ref, $resource["archive"], false, $resource))
    {
    exit($lang["error-permissiondenied"]);
    }

?>
<?php
// Update database with geolocation.
if (isset($_POST['submit']) && enforcePostRequest(false))
    {
    $s = explode(",", getvalescaped('geo-loc', ''));
    if (count($s) == 2)
        {
        $map_zoom = getvalescaped('map-zoom', '');
        if ($map_zoom >= 2 && $map_zoom <= 21)
            {
            sql_query("UPDATE resource SET geo_lat='" . escape_check($s[0]) . "',geo_long='" . escape_check($s[1]) . "',mapzoom='" . escape_check($map_zoom) . "' WHERE ref='$ref'");
            }
        else
            {
            sql_query("UPDATE resource SET geo_lat='" . escape_check($s[0]) . "',geo_long='" . escape_check($s[1]) . "',mapzoom=null WHERE ref='$ref'");
            }
        hook("savelocationextras");
        }
    elseif (getval('geo-loc', '') == '')
        {
        // Blank geolocation.
        sql_query("UPDATE resource SET geo_lat=null,geo_long=null,mapzoom=null WHERE ref='$ref'");
        hook("removelocationextras");
        }

    // Reload resource data.
    $resource = get_resource_data($ref, false);
    }

?>
<div class="RecordBox">
<div class="RecordPanel">
<div class="Title"><?php echo $lang['location-title']; ?></div>

<?php if (!hook("customgeobacklink"))
    { ?>
    <p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short . ($geocol != '' ? "pages/geolocate_collection.php?ref=" . $geocol : "pages/view.php?ref=" . $ref) ?>"><?php echo LINK_CARET_BACK . ($geocol != '' ? $lang['backtogeolocatecollection'] : $lang['backtoresourceview']) ?></a></p>
<?php } ?>

<!--Map introtext-->
<div id="map_introtext" style="margin-top:0px; margin-bottom:0px; width: 99%;">
    <p> <?php echo $lang['edit_map_introtext'];?> </p>
</div>

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

<!--Leaflet Control Geocoder 1.10.0 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-control-geocoder-1.10.0/dist/Control.Geocoder.min.js"></script>

<!--Polyfill for Internet Explorer and Edge browser compatibility-->
<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2Ces2016%2Ces5%2Ces6%2Ces2017%2Cdefault%2Ces2018%2Ces7"></script>

<!--Setup Leaflet map container with sizing-->
<div id="map_edit" style="width: 99%; margin-top:0px; margin-bottom:0px; height: <?php echo $mapedit_mapheight;?>px; display:block; border:1px solid black; float:none; overflow: hidden;">
</div>

<script type="text/javascript">
    var Leaflet = L.noConflict();

    <!--Setup and define the Leaflet map with the initial view using leaflet.js and L.Control.Zoomslider.js-->
    var map2 = new Leaflet.map('map_edit', {
        renderer: Leaflet.canvas(),
        zoomsliderControl: <?php echo $zoomslider?>,
        zoomControl: <?php echo $zoomcontrol?>
    }).setView(<?php echo $map_centerview; ?>);

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

    <!--Define default Leaflet basemap layer using leaflet.js, leaflet.providers.js, and L.TileLayer.PouchDBCached.js-->
    var defaultLayer = new Leaflet.tileLayer.provider('<?php echo $map_default;?>', {
        useCache: '<?php echo $map_default_cache;?>', <!--Use browser caching of tiles (recommended)?-->
        detectRetina: '<?php echo $map_retina;?>', <!--Use retina high resolution map tiles?-->
        attribution: default_attribute
    }).addTo(map2);

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
        group_maxHeight: "180px",
        exclusive: false
    };

    var control = Leaflet.Control.styledLayerControl(baseMaps,options);
    map2.addControl(control);

    <!--Add geocoder search bar using control.geocoder.min.js-->
    Leaflet.Control.geocoder().addTo(map2);

    <!--Show zoom history navigation bar and add to Leaflet map using Leaflet.NavBar.min.js-->
    <?php if ($map_zoomnavbar)
        { ?>
        Leaflet.control.navbar().addTo(map2); <?php
        } ?>

    <!--Add a scale bar to the Leaflet map using leaflet.min.js-->
    new Leaflet.control.scale().addTo(map2);

    <!--Add download map button to the Leaflet map using bundle.min.js-->
    Leaflet.easyPrint({
        title: "<?php echo $lang['map_download'];?>",
        position: 'bottomleft',
        sizeModes: ['Current', 'A4Landscape', 'A4Portrait'],
        exportOnly: true,
        filename: 'resource_edit_map',
        customWindowTitle: "<?php echo $lang['map_print_title'];?>"
    }).addTo(map2);

    <!--Add a KML overlay to the Leaflet map using leaflet-omnivore.min.js-->
    <?php if ($map_kml)
        { ?>
        omnivore.kml('<?php echo $baseurl?>/filestore/system/<?php echo $map_kml_file?>').addTo(map2); <?php
        } ?>

    <!--Fix for Microsoft Edge and Internet Explorer browsers-->
    map2.invalidateSize(true);

    <!--Limit geocoordinate values to six decimal places for display on marker hover-->
    function georound(num) {
        return +(Math.round(num + "e+6") + "e-6");
    }

    <!--Add a marker to the map if the resource has valid coordinates-->
    var resourceMarker = {}; <?php
    if (leaflet_coordinate_check($resource["geo_lat"], "latitude") && leaflet_coordinate_check($resource["geo_long"], "longitude"))
        {
        $resource["mapzoom"] = leaflet_map_zoom($resource["mapzoom"]); ?>
        resourceLat = <?php echo $resource["geo_lat"]; ?>;
        resourceLong = <?php echo $resource["geo_long"]; ?>;
        resourceZoom = <?php echo $resource["mapzoom"]; ?>;

        resourceMarker = Leaflet.marker([resourceLat, resourceLong], {
            title: georound(resourceLat) + ", " + georound(resourceLong) + " (WGS84)"
        }).addTo(map2);
        map2.setView([resourceLat, resourceLong], resourceZoom); <?php
        } ?>

    <!--Place a marker on the map when clicked-->
    currentZoom = map2.getZoom();
    map2.on('click', function(e) {
        geoLat = e.latlng.lat;
        geoLong = e.latlng.lng;
        currentZoom = map2.getZoom();
        console.log(geoLat, geoLong, currentZoom);

        <!--Clear existing marker when locating a new marker as we only want one marker for the resource-->
        if (resourceMarker != undefined) {
            map2.removeLayer(resourceMarker);
        };

        <!--Add a marker to show where you clicked on the map last and center the map on the marker-->
        resourceMarker = L.marker([geoLat, geoLong], {
            title: georound(geoLat) + ", " + georound(geoLong) + " (WGS84)"
        }).addTo(map2);
        map2.setView([geoLat, geoLong], currentZoom);

        <!--Set the resource marker geolocation value-->
        document.getElementById('map-input').value=georound(geoLat) + ', ' + georound(geoLong);
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $baseurl_short; ?>pages/geo_edit.php",
            dataType: "text",
            data: {
                new_lat: geoLat,
                new_long: geoLong,
                new_zoom: currentZoom
            }
        });
    });
</script>

<p></p>
<?php
hook("rendermapfooter"); ?>

<!--Resource marker latitude and longitude form-->
<form id="map-form" method="post" action="<?php echo $baseurl_short?>pages/geo_edit.php">
    <?php generateFormToken("map-form"); ?>
    <input name="ref" type="hidden" value="<?php echo $ref; ?>" />
    <input name="geocol" type="hidden" value="<?php echo $geocol; ?>" />
    <input name="map-zoom" type="hidden" value="<?php echo $zoom; ?>" id="map-zoom" />
    <?php echo $lang['marker'] . " " . strtolower($lang['latlong']); ?>: <input name="geo-loc" type="text" size="50" value="<?php echo $resource["geo_long"] == ""?"" : ($resource["geo_lat"] . "," . $resource["geo_long"]) ?>" id="map-input" />
    <?php hook("renderlocationextras"); ?>
    <input name="submit" type="submit" value="<?php echo $lang['save']; ?>" />
</form>

</div>
</div>
<?php
include "../include/footer.php";
?>
