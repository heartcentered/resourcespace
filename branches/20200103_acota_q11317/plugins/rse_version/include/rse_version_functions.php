<?php
namespace RseVersion;

function get_reverse_state_process($type)
    {
    if(trim($type) == "")
        {
        return false;
        }

    global $baseurl;

    $process_list = array(
        // Process for reverting removed/added resources
        "remove" => array(
            "callback" => function($collection, $ref) use ($baseurl)
                {
                $collection_escaped = escape_check($collection);
                $ref_escaped        = escape_check($ref);

                $logs = sql_query("
                      SELECT ref, `type`, resource
                        FROM collection_log
                       WHERE collection = '{$collection_escaped}'
                         AND (
                            `type` = 'a' AND BINARY `type` <> BINARY UPPER(`type`)
                            # Ignore LOG_CODE_COLLECTION_REMOVED_ALL_RESOURCES (R) as individual logs will be available
                            # anyway as LOG_CODE_COLLECTION_REMOVED_RESOURCE (r)
                            OR `type` = 'r' AND BINARY `type` <> BINARY UPPER(`type`)
                         )
                         AND ref < '{$ref_escaped}'
                    ORDER BY ref ASC;
                ");

                if(count($logs) == 0)
                    {
                    return;
                    }

                remove_all_resources_from_collection($collection);

                foreach($logs as $log)
                    {
                    if($log["type"] === LOG_CODE_COLLECTION_ADDED_RESOURCE)
                        {
                        add_resource_to_collection($log['resource'], $collection);
                        }
                    else if($log["type"] === LOG_CODE_COLLECTION_REMOVED_RESOURCE)
                        {
                        remove_resource_from_collection($log['resource'], $collection);
                        }
                    }

                redirect("{$baseurl}/pages/collection_log.php?ref={$collection}");

                return;
                }
        ),
        // Process for reverting deleted resources
        "delete" => array(
            "callback" => function()
                {
                return;
                }
        ),
    );
    $type_process_mapping = array(
        LOG_CODE_COLLECTION_REMOVED_ALL_RESOURCES => $process_list["remove"],
        LOG_CODE_COLLECTION_REMOVED_RESOURCE      => $process_list["remove"],
        LOG_CODE_COLLECTION_ADDED_RESOURCE        => $process_list["remove"],

        LOG_CODE_COLLECTION_DELETED_ALL_RESOURCES => $process_list["delete"],
        LOG_CODE_COLLECTION_DELETED_RESOURCE      => $process_list["delete"],
    );

    if(!array_key_exists($type, $type_process_mapping))
        {
        return false;
        }

    return $type_process_mapping[$type];
    }

function is_valid_revert_state_request()
    {
    $collection = (int) getval("collection", 0, true);
    $ref        = (int) getval("ref", 0, true);

    $process = get_reverse_state_process(getval("type", ""));
    if($process !== false)
        {
        return true;
        }

    return false;
    }


function render_revert_state_form()
    {
    global $lang, $baseurl_short;

    $collection = (int) getval("collection", 0, true);
    $ref        = (int) getval("ref", 0, true);
    $type       = trim(getval("type", ""));

    $change_summary = str_replace("%COLLECTION", $collection, $lang['rse_version_rstate_changes']);
    ?>
    <div class="BasicsBox">
        <p>
            <a href="<?php echo $baseurl_short ?>pages/collection_log.php?ref=<?php echo $collection; ?>"
               onclick="CentralSpaceLoad(this, true); return false;"><?php echo LINK_CARET_BACK ?><?php echo $lang["back"]; ?></a>
       </p>
        <h1><?php echo $lang["rse_version_revert_state"]; ?></h1>
        <p><?php echo $change_summary; ?></p>
        <!-- @todo: add information for the selected record -->
        <form method="post"
              name="rse_version_revert_state_form" 
              id="rse_version_revert_state_form"
              action="<?php echo $baseurl_short ?>plugins/rse_version/pages/revert.php" onsubmit="CentralSpacePost(this, true); return false;">
            <input type="hidden" name="collection" value="<?php echo $collection; ?>">
            <input type="hidden" name="ref" value="<?php echo $ref; ?>">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <input type="hidden" name="action" value="revert_state">
            <?php generateFormToken("rse_version_revert_state_form"); ?>
            <div class="QuestionSubmit">
                <label for="buttons"> </label>
                <input name="revert" type="submit" value="<?php echo $lang["revert"]; ?>">
            </div>
        </form>
    </div>
    <?php
    return;
    }


function process_revert_state_form()
    {
    $revert_state = getval("action", "") == "revert_state" ? true : false;
    if(!$revert_state)
        {
        return;
        }

    $process = get_reverse_state_process(getval("type", ""));
    if($process === false)
        {
        // @todo: show error back to the user
        /*
        include "../../../include/header.php";
        echo error html
        include "../../../include/footer.php";
        exit();
        */
        return;
        }

    if(!is_callable($process["callback"]))
        {
        // @todo: error here
        return;
        }

    $collection = (int) getval("collection", 0, true);
    $ref        = (int) getval("ref", 0, true);

    $process["callback"]($collection, $ref);

    return;
    }