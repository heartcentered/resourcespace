<?php
namespace RseVersion;

function is_valid_revert_state_request()
    {
    $collection = (int) getval("collection", 0, true);
    $date = getval("date", "");
    $resource = (int) getval("resource", 0, true);

    if($collection <= 0 || $resource <= 0 || $date == "")
        {
        return false;
        }

    // @todo: catch user requesting the state revert

    return true;
    }


function render_revert_state_form()
    {
    global $baseurl_short;
    ?>
    <div class="BasicsBox">
        <p>
            <a href="<?php echo $baseurl_short ?>pages/log.php?ref=<?php echo $resource ?>"
               onClick="CentralSpaceLoad(this,true);return false;"><?php echo LINK_CARET_BACK ?><?php echo $lang["back"]; ?></a>
       </p>

        <h1><?php echo $lang["rse_version_revert_state"]; ?></h1>

        @todo: continue the form showing what events will be replayed
        <form method=post name="form" id="form" action="<?php echo $baseurl_short ?>plugins/rse_version/pages/revert.php" onSubmit="CentralSpacePost(this,true);return false;">
            <input type="hidden" name="ref" value="<?php echo $ref ?>">
            <input type="hidden" name="action" value="revert">
            <?php generateFormToken("form"); ?>
            <div class="QuestionSubmit">
                <label for="buttons"> </label>
                <input name="revert" type="submit" value="<?php echo $lang["revert"]; ?>">
            </div>
        </form>
        <p><?php echo $lang['revertingclicktoproceed']; ?></p>
    </div>
    <?php
    return;
    }


function revert_collection_state()
    {
    return;
    }