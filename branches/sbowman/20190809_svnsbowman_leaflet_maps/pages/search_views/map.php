<?php
// Map Search View Using Leaflet.js and Various Leaflet Plugins
// Last Edit 8/24/2019, Steve D. Bowman

// Check if geolocation/maps have been disabled.
global $disable_geocoding, $lang;
if($disable_geocoding)
    {
    header('HTTP/1.1 403 Forbidden');
    exit($lang['error-geocodingdisabled']);
    }

include "../include/map_functions.php";

// Setup initial Leaflet map variables.
global $baseurl, $mapsearch_height, $map_default, $geomarker, $preview_paths, $map_centerview, $map_zoomslider, $map_zoomnavbar, $map_kml, $map_kml_file, $map_retina, $marker_resource_preview, $marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8;
$marker_color_def = array($marker_color1, $marker_color2, $marker_color3, $marker_color4, $marker_color5, $marker_color6, $marker_color7, $marker_color8);
$display_selector_dropdowns = false;
$zoomslider = 'false';
$zoomcontrol = 'true';

// Set Leaflet map search view height and layer control container height based on $mapsearch_height.
if (isset($mapsearch_height))
    {
    $map1_height = $mapsearch_height;
    $layer_controlheight = $mapsearch_height - 40;
    }
else // Default values.
    {
    $map1_height = "500";
    $layer_controlheight = 460;
    }

// Show zoom slider instead of default Leaflet zoom control?
if ($map_zoomslider)
    {
    $zoomslider = 'true';
    $zoomcontrol = 'false';
    }

?>
<!--Map introtext-->
<div id="map1_introtext" style="margin-top:0px; margin-bottom:0px; width: 99%;">
    <p> <?php echo $lang["map_introtext1"];?> </p>
</div>

<!--Leaflet.js v1.5.1 files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_1.5.1/leaflet.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_1.5.1/leaflet.min.js"></script>

<!--Leaflet Providers v1.8.0 plugin files-->
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-providers-1.8.0/leaflet-providers.babel.min.js"></script>

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
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-easyPrint-2.1.9/dist/bundle.min.js"></script>

<!--Leaflet StyledLayerControl v5/16/2019 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/css/styledLayerControl.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/src/styledLayerControl.min.js"></script>

<!--Leaflet Zoomslider v0.7.1 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.min.js"></script>

<!--Polyfill for Internet Explorer and Edge browser compatibility-->
<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2Ces2016%2Ces5%2Ces6%2Ces2017%2Cdefault%2Ces2018%2Ces7"></script>

<!--Setup Leaflet map container with sizing-->
<div id="map_results" style="width: 99%; margin-top:0px; margin-bottom:0px; height: <?php echo $map1_height;?>px; display:block; border:1px solid black; float:none; overflow: hidden;">
</div>

<script type="text/javascript">
    var Leaflet = L.noConflict();
    
    <!--Setup and define the Leaflet map with the initial view using leaflet.js and L.Control.Zoomslider.js-->
    var map1 = new L.map('map_results', {
        renderer: L.canvas(),
        zoomsliderControl: <?php echo $zoomslider?>,
        zoomControl: <?php echo $zoomcontrol?>
    }).setView(<?php echo $map_centerview;?>);

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
        group_maxHeight: "180px",
        exclusive: false
    };

    var control = L.Control.styledLayerControl(baseMaps,options);
    map1.addControl(control);

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

    <!--If no data (markers), only show the empty Leaflet map-->
    <?php if (!empty($geomarker))
        { ?>
        <!--Setup and configure initial marker info from resource data-->
        var geomarker = <?php echo str_replace(array('"', '\\'), '', json_encode($geomarker))?>;
        var previewPaths = <?php echo json_encode($preview_paths); ?>;
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
            var preview = previewPaths[i]; <!--Resource preview image path-->

            <!--Check for resources without geolocation or invalid coordinates and skip those-->
            if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180)
                { <?php
                // Check if using a custom metadata field for coloring the markers and redefine rtype.
                if (isset($marker_metadata_field))
                    {
                    for ($i = 0; $i < 8; $i++)
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
                <?php if ($marker_resource_preview)
                    { ?>
                    var marker = new L.marker([lat, lon], {
                        icon: iconColor,
                        riseOnHover: true,
                        win_url: geomarker[i][2]
                    }); 
                    
                    <!--Show the resource preview image-->
                    var imagePath = "<img src='" + preview + "'/>";
                    var text1 = "<?php echo $lang["resourceid"]; ?>";
                    var imageLink = '<a href=' + baseurl + '/pages/view.php?ref=' + rf + " target='_blank'" + '>' + '<img src=' + preview + '>' + '</a>';
                    marker.bindPopup(imageLink + text1 + " " + rf, {
                        minWidth: 155,
                        autoPan: true,
                        autoPanPaddingTopLeft: 5,
                        autoPanPaddingBottomRight: 5
                    }); <?php
                    } 
                else // Show resource ID in marker tooltip.
                    { ?> 
                    var marker = new L.marker([lat, lon], {
                        icon: iconColor,
                        title: 'ID# ' + rf,
                        riseOnHover: true,
                        win_url: geomarker[i][2]
                    }).on('click', showModal); <?php
                    } ?>

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
<p style="margin-top:4px;margin-bottom:0px;"> <?php

    // Resource type color markers legend.
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
        } ?>
</p>

