<?php
// Resource View Leaflet Map Using Leaflet.js and Various Leaflet Plugins

include "map_functions.php";

// Setup initial Leaflet map variables.
global $lang, $geo_search_restrict, $baseurl, $baseurl_short, $view_mapheight, $map_default, $map_zoomslider, $map_zoomnavbar, $map_kml, $map, $map_kml_file, $map_retina, $map_polygon_field, $modal, $fields;
$zoomslider = 'false';
$zoomcontrol = 'true';
$polygon = "";
$modal = (getval("modal", "") == "true");

// Set Leaflet map search view height and layer control container height based on $mapheight.
if (isset($view_mapheight))
    {
    $map1_height = $view_mapheight;
    $layer_controlheight = $view_mapheight - 40;
    }
else // Default values.
    {
    $map1_height = 300;
    $layer_controlheight = 250;
    }

// Show zoom slider instead of default Leaflet zoom control?
if ($map_zoomslider)
    {
    $zoomslider = 'true';
    $zoomcontrol = 'false';
    }

// If inside spatial restricted zone, do not show location data.
if (count($geo_search_restrict) > 0)
    {
    foreach ($geo_search_restrict as $zone)
        {
        if ($resource["geo_lat"] >= $zone[0] && $resource["geo_lat"] <= $zone[2] && $resource["geo_long"] >= $zone[1] && $resource["geo_long"] <= $zone[3])
            {
            return false;
            }
        }
    }
?>
<!--Leaflet.js v1.6.0 files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_1.6.0/leaflet.min.js"></script>

<!--Leaflet Providers v1.9.0 plugin file-->
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
<?php if ($map1_height >= 335)
    { ?>
    <script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-easyPrint-2.1.9/dist/bundle.js"></script> <?php
    } ?>

<!--Leaflet StyledLayerControl v5/16/2019 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/css/styledLayerControl.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-StyledLayerControl-5-16-2019/src/styledLayerControl.min.js"></script>

<!--Leaflet Zoomslider v0.7.1 plugin files-->
<link rel="stylesheet" href="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.css"/>
<script src="<?php echo $baseurl?>/lib/leaflet_plugins/leaflet-zoomslider-0.7.1/src/L.Control.Zoomslider.min.js"></script>

<!--Polyfill for Internet Explorer and Edge browser compatibility-->
<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2Ces2016%2Ces5%2Ces6%2Ces2017%2Cdefault%2Ces2018%2Ces7"></script>

<?php
if($hide_geolocation_panel && !isset($geolocation_panel_only))
    { ?>
    <script>
        function ShowGeolocation()
            {
            if(!jQuery("#GeolocationData").length){
                jQuery.ajax({
                    type:"GET",
                    url: '<?php echo $baseurl_short?>pages/ajax/geolocation_loader.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>',
                    success: function(data){
                        jQuery("#GeolocationHideLink").after(data);
                        }
                    });
                }

            jQuery("#GeolocationData").slideDown();
            jQuery("#GeolocationHideLink").show();
            jQuery("#GeolocationShowLink").hide();
            }
        function HideGeolocation()
            {
            jQuery("#GeolocationData").slideUp();
            jQuery("#GeolocationShowLink").show();
            jQuery("#GeolocationHideLink").hide();
            }
    </script> <?php
    }

// Begin geolocation section.
if (!isset($geolocation_panel_only))
    { ?>
    <div class="RecordBox">
    <div class="RecordPanel"> <?php

    if ($hide_geolocation_panel)
        { ?>
        <div id="GeolocationShowLink" class="CollapsibleSection" ><?php echo "<a href=\"javascript: void(0)\" onClick=\"ShowGeolocation();\">&#x25B8;&nbsp;" . $lang["showgeolocationpanel"] . "</a>";?></div>
        <div id="GeolocationHideLink" class="CollapsibleSection" style="display:none"><?php echo "<a href=\"javascript: void(0)\" onClick=\"HideGeolocation();return false;\">&#x25BE;&nbsp;" . $lang["hidegeolocationpanel"] . "</a>";?></div> <?php
        }
    }

if(!$hide_geolocation_panel || isset($geolocation_panel_only))
    { ?>
    <div id="GeolocationData">
    <div class="Title"><?php echo $lang['location-title']; ?></div>
    <?php

    if ($resource["geo_lat"] != "" && $resource["geo_long"] != "")
        { ?>
        <?php if ($edit_access)
            { ?>
            <p><?php echo LINK_CARET ?><a href="<?php echo $baseurl_short?>pages/geo_edit.php?ref=<?php echo urlencode($ref); ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang['location-edit']; ?></a></p><?php }
    $zoom = leaflet_map_zoom($resource["mapzoom"]);

    // Check for modal view.
    if (!$modal)
        {
        $map_container = "map_id";
        }
    else
        {
        $map_container = "map_id";
        $map_container = "map_id_modal";
        }

    ?>
    <!--Setup Leaflet map container with sizing-->
    <div id=<?php echo $map_container; ?> style="width: 99%; margin-top:0px; margin-bottom:0px; height: <?php echo $map1_height;?>px; display:block; border:1px solid black; float:none; overflow: hidden;">
    </div>

    <script type="text/javascript">
        var LeafletView = L.noConflict();

        <!--Setup and define the Leaflet map with the initial view using leaflet.js and L.Control.Zoomslider.js-->
        var geo_lat = <?php echo $resource["geo_lat"]; ?>;
        var geo_long = <?php echo $resource["geo_long"]; ?>;
        var zoom = <?php echo $zoom; ?>;

        var map = new LeafletView.map(<?php echo $map_container; ?>, {
            preferCanvas: true,
            renderer: LeafletView.canvas(),
            zoomsliderControl: <?php echo $zoomslider?>,
            zoomControl: <?php echo $zoomcontrol?>
        }).setView([geo_lat, geo_long], zoom);

        <?php if ($modal)
            { ?>
            map.remove();
            var map = new LeafletView.map(<?php echo $map_container; ?>, {
                preferCanvas: true,
                renderer: LeafletView.canvas(),
                zoomsliderControl: <?php echo $zoomslider?>,
                zoomControl: <?php echo $zoomcontrol?>
            }).setView([geo_lat, geo_long], zoom);
            map.invalidateSize(); <?php
            }
        ?>

        jQuery.noConflict();
        jQuery(function($) {
            $(document).ready(function(){
                $('#map_id').on('shown.bs.modal', function(){
                    map.invalidateSize();
                });
            });
        });

        // Define available Leaflet basemaps groups and layers using leaflet.providers.js, L.TileLayer.PouchDBCached.js, and styledLayerControl.js based on ../include/map_functions.php.
        <?php
        echo leaflet_osm_basemaps();
        echo leaflet_esri_basemaps();
        echo leaflet_stamen_basemaps();
        echo leaflet_hydda_basemaps();
        echo leaflet_nasa_basemaps();
        echo leaflet_thunderforest_basemaps();
        echo leaflet_mapbox_basemaps();

        // Define Leaflet default basemap attribution.
        switch ($map_default)
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
        var defaultLayer = new LeafletView.tileLayer.provider('<?php echo $map_default;?>', {
            useCache: '<?php echo $map_default_cache;?>', <!--Use browser caching of tiles (recommended)?-->
            detectRetina: '<?php echo $map_retina;?>', <!--Use retina high resolution map tiles?-->
            attribution: default_attribute
        }).addTo(map);
        map.invalidateSize(true);

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

        var control = LeafletView.Control.styledLayerControl(baseMaps,options);
        map.addControl(control);

        <!--Show zoom history navigation bar and add to Leaflet map using Leaflet.NavBar.min.js-->
        <?php if ($map_zoomnavbar && $view_mapheight >= 400)
            { ?>
            LeafletView.control.navbar().addTo(map); <?php
            } ?>

        <!--Add a scale bar to the Leaflet map using leaflet.min.js-->
        new LeafletView.control.scale().addTo(map);

        <!--Add download map button to the Leaflet map using bundle.min.js-->
        <?php if ($map1_height >= 335)
            { ?>
            LeafletView.easyPrint({
                title: "<?php echo $lang['leaflet_mapdownload']; ?>",
                position: 'bottomleft',
                sizeModes: ['Current', 'A4Landscape', 'A4Portrait'],
                exportOnly: true,
                filename: 'search_results_map',
                customWindowTitle: "<?php echo $lang['map_print_title']; ?>"
            }).addTo(map);
            <?php
            } ?>

        <!--Add a KML overlay to the Leaflet map using leaflet-omnivore.min.js-->
        <?php if ($map_kml)
            { ?>
            omnivore.kml('<?php echo $baseurl?>/filestore/system/<?php echo $map_kml_file?>').addTo(map); <?php
            } ?>

        <!--Limit geocoordinate values to six decimal places for display on marker hover-->
        function georound(num) {
            return +(Math.round(num + "e+6") + "e-6");
        }

        <!--Add a marker for the resource-->
        LeafletView.marker([geo_lat, geo_long], {
            title: georound(geo_lat) + ", " + georound(geo_long) + " (WGS84)"
        }).addTo(map);

        <!--Add the resource footprint polygon to the map and pan/zoom to the polygon-->
        <?php if (is_numeric($map_polygon_field))
            {
            $polygon = leaflet_polygon_parsing($fields, false);
            if (!is_null($polygon['values']) && $polygon['values'] != "" && $polygon['values'] != "[]")
                { ?>
                var refPolygon = LeafletView.polygon([<?php echo $polygon['values']; ?>]).addTo(map);
                map.fitBounds(refPolygon.getBounds(), {
                    padding: [25, 25]
                }); <?php
                }
            }
        else // Pan to the marker location.
            { ?>
            map.setView([geo_lat, geo_long], zoom); <?php
            }

        ?>
        <!--Fix for Microsoft Edge and Internet Explorer browsers-->
        map.invalidateSize(true);

    </script>

    <!--Show resource geolocation value-->
    <div id="resource_coordinate" style="margin-top:0px; margin-bottom:0px; width: 99%;">
        <p> <?php echo $lang['marker'] . " " . strtolower($lang['latlong']) . ": "; echo round($resource["geo_lat"], 6) . ", "; echo round($resource["geo_long"], 6) . " (WGS84)"; ?> </p>
    </div>
    <?php
    }
else
    { ?>
    <a href="<?php echo $baseurl_short?>pages/geo_edit.php?ref=<?php echo urlencode($ref); ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_PLUS ?><?php echo $lang['location-add'];?></a> <?php
    }

?>
<script>

</script>

<?php
if($view_panels)
    { ?>
    <script>
    jQuery(document).ready(function ()
        {
        jQuery("#GeolocationData").children(".Title").attr("panel", "GeolocationData").appendTo("#Titles1");
        removePanel = jQuery("#GeolocationData").parent().parent(".RecordBox");
        jQuery("#GeolocationData").appendTo("#Panel1").addClass("TabPanel").hide();
        removePanel.remove();

        <!--Function to switch tab panels-->
        jQuery('.ViewPanelTitles').children('.Title').click(function()
            {
            jQuery(this).parent().parent().children('.TabPanel').hide();
            jQuery(this).parent().children('.Title').removeClass('Selected');
            jQuery(this).addClass('Selected');
            jQuery('#' + jQuery(this).attr('panel')).show();
            map.invalidateSize(true);
            });
        });
    </script> <?php
    } ?>
    </div> <?php
    }

if (!isset($geolocation_panel_only))
    { ?>
    </div> <!--End of RecordPanel-->
    </div> <!--End of RecordBox--> <?php
    }
