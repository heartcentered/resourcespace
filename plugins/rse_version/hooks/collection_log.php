<?php
function HookRse_versionCollection_logLog_extra_columns_header()
    {
    global $lang;
    ?>
    <td width="5%">
        <div class="ListTools"><?php echo $lang["tools"]; ?></div>
    </td>
    <?php
    return;
    }


function HookRse_versionCollection_logLog_extra_columns_row($log, array $collection_info)
    {
    global $lang;

    if(!$log['revert_state_enabled'])
        {
        ?>
        <td></td>
        <?php
        return;
        }
    ?>
    <td>
        <div class="ListTools">
        <a href="../plugins/rse_version/pages/revert.php?collection=<?php echo $collection_info["ref"]; ?>"
           onclick="CentralSpaceLoad(this, true); return false;"><?php echo LINK_CARET . $lang["rse_version_revert_state"]; ?></a>
        </div>
    </td>
    <?php
    return;
    }


function HookRse_versionCollection_logCollection_log_extra_fields()
    {
    return ",
            IF(
                   (`type` = 'a' AND BINARY `type` <> BINARY UPPER(`type`))
                OR `type` = 'r', true, false
            ) AS revert_state_enabled";
    }