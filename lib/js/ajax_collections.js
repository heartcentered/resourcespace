// Functions to support collections.

// Prevent caching
jQuery.ajaxSetup({ cache: false });
 
function PopCollection(thumbs) {
    if(thumbs == "hide" && collections_popout) {
        ToggleThumbs();
    }
}

function ChangeCollection(collection,k,last_collection,searchParams) {
    console.log("changecollection");
    if(typeof last_collection == 'undefined'){last_collection='';}
    if(typeof searchParams == 'undefined') {searchParams='';}
    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    // Set the collection and update the count display
    CollectionDivLoad(baseurl_short + 'pages/collections.php?collection=' + collection + '&thumbs=' + thumbs + '&last_collection=' + last_collection + '&k=' + k + '&' +searchParams);
}

function UpdateCollectionDisplay(k) {
    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    // Update the collection count display
    jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?thumbs=' + thumbs + '&k=' + k);
}

function AddResourceToCollection(event,resource,size, collection_id) {

    // Optional params
    if(typeof collection_id === 'undefined') {
        collection_id = '';
    }

    if(event.shiftKey == true) {
        if (typeof prevadded != 'undefined') {
            lastchecked = jQuery('#check' + prevadded);
            if (lastchecked.length != 0) {
                var resourcelist = [];
                addresourceflag = false;
                jQuery('.checkselect').each(function () {
                    if(jQuery(this).attr("id") == lastchecked.attr("id")) {
                        if(addresourceflag == false) {   
                            // Set flag to mark start of resources to add
                            addresourceflag = true;
                        }
                        else { 
                            // Clear flag to mark end of resources to add
                            addresourceflag = false;  
                        }
                    }
                    else if(jQuery(this).attr("id") == 'check'+resource) {
                        // Add resource to list before clearing flag
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).prop('checked',true);
                        if(addresourceflag == false) {
                            addresourceflag = true;
                        }
                        else {
                            addresourceflag = false;
                        }
                    }

                    if(addresourceflag) {
                        // Add resource to list 
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).prop('checked',true);
                    }
                });
                resource = resourcelist.join(",");
            }
        }
    }
    prevadded = resource;

    thumbs = getCookie("thumbs");
    PopCollection(thumbs);

    jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?add=' + resource + '&toCollection=' + collection_id + '&size=' + size + '&thumbs=' + thumbs + '&ajax=true');
    delete prevremoved;
    if(collection_bar_hide_empty){
	CheckHideCollectionBar();
	}
}

function RemoveResourceFromCollection(event,resource,pagename, collection_id) {
    // Optional params
    if(typeof collection_id === 'undefined') {
        collection_id = '';
    }

    if(event.shiftKey == true) {
        if (typeof prevremoved != 'undefined') {
            lastunchecked=jQuery('#check' + prevremoved)
            if (lastunchecked.length != 0) {
                var resourcelist = [];
                removeresourceflag = false;
                jQuery('.checkselect').each(function () {
                    if(jQuery(this).attr("id") == lastunchecked.attr("id")) {
                        if(removeresourceflag == false) { 
                            // Set flag to mark start of resources to remove
                            removeresourceflag = true;
                        }
                        else { 
                            // Clear flag to mark end of resources to remove
                            removeresourceflag = false;
                        }
                    }
                    else if(jQuery(this).attr("id") == 'check'+resource) {
                        // Add resource to list before clearing flag
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).removeAttr('checked');
                        if(removeresourceflag == false) {
                            removeresourceflag = true;
                        }
                        else {
                            removeresourceflag = false;
                        }
                    }

                    if(removeresourceflag) {
                        // Add resource to list to remove
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).prop('checked',false);
                    }
                });
                resource = resourcelist.join(",");
            }
        }
    }
    prevremoved = resource;

    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    CollectionDivLoad( baseurl_short + 'pages/collections.php?remove=' + resource + '&fromCollection=' + collection_id + '&thumbs=' + thumbs);
    // jQuery('#ResourceShell' + resource).fadeOut(); //manual action (by developers) since now we can have a case where we remove from collection bar but keep it in central space because it's for a different collection
    delete prevadded;
    if(collection_bar_hide_empty){
	CheckHideCollectionBar();
	}
}


function UpdateHiddenCollections(checkbox, collection, post_data) {
    var action = (checkbox.checked) ? 'showcollection' : 'hidecollection';
    jQuery.ajax({
        type: 'POST',
        url: baseurl_short + 'pages/ajax/showhide_collection.php?action=' + action + '&collection=' + collection,
        data: post_data,
        success: function(data) {
            if (data.trim() == "HIDDEN") {
                jQuery(checkbox).prop('checked',false);
            }
            else if (data.trim() == "UNHIDDEN") {
                jQuery(checkbox).prop('checked',true);
            }
        },
        error: function (err) {
            console.log("AJAX error : " + JSON.stringify(err, null, 2));
            if(action == 'showcollection') {
                jQuery(checkbox).removeAttr('checked');
            }
            else {
                jQuery(checkbox).prop('checked','checked');
            }
        }
    }); 
}


function ToggleCollectionResourceSelection(elem)
    {
    var input = jQuery(elem);
    var resource = input.data("resource");
    var csrf_token_identifier = input.data("csrf-token-identifier");
    var csrf_token = input.data("csrf-token");

    var default_post_data = {};
    default_post_data[csrf_token_identifier] = csrf_token;
    var post_data = Object.assign({}, default_post_data);
    post_data.ajax = true;
    post_data.resource = resource;

    if(input.prop("checked"))
        {
        post_data.action = "add_resource";
        console.debug("ToggleCollectionResourceSelection: adding resource %i to collection", resource);
        }
    else
        {
        post_data.action = "remove_resource";
        console.debug("ToggleCollectionResourceSelection: removing resource %i from collection", resource);
        }
    console.debug("ToggleCollectionResourceSelection: post_data = %o", post_data);

    CentralSpaceShowLoading();

    jQuery.ajax({
        type: 'POST',
        url: baseurl + "/pages/ajax/collections.php",
        data: post_data,
        dataType: "json"
        })
        .done(function(response, textStatus, jqXHR)
            {
            console.log(response);
            styledalert("TODO", "Implement this!");
            })
        .fail(function(data, textStatus, jqXHR)
            {
            if(typeof data.responseJSON === 'undefined')
                {
                return;
                }

            var response = data.responseJSON;
            styledalert(jqXHR, response.data.message);
            })
        .always(function()
            {
            CentralSpaceHideLoading();
            });

    return true;
    }