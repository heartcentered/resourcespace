<?php

function HookRse_versionEditEdit_all_extra_modes()
    {
    global $lang;
    ?>
    <option value="Revert"><?php echo $lang["revertmetadatatodatetime"] ?></option>
    <?php
    }
    
    #edit_all_mode_js
    #edit_all_after_findreplace
    

function HookRse_versionEditEdit_all_mode_js()
    {
    # Add to the JS executed when a mode selector is changed on 'edit all'
    global $n;
    ?>var r=document.getElementById('revert_<?php echo $n?>');
    if (this.value=='Revert')
        {
         /* 
        hide default input field(s) - q var
        show revert to state as of datetime - r var
        hide findreplace fr var
        */
       
        q.style.display='none';
        r.style.display='block';
        fr.style.display='none';  
        
        /* cf refers to copy from field - not always present, e.g. date field */
        if (typeof cf !== 'undefined') 
            {
            // the variable is defined
            cf.style.display='none';
            }

        }
    else 
        {
        r.style.display='none';
        }
    <?php
    }
    
function HookRse_versionEditEdit_all_after_findreplace($field,$n)
    {
    # Add a revert date/time box after 'edit all' mode selector when reversion mode selected.
    global $lang;
    ?>
    <div class="Question" id="revert_<?php echo $n?>" style="display:none;border-top:none;">
    <label>&nbsp;</label>
    <input type="text" name="revert_<?php echo $field["ref"]?>" class="stdwidth" value="<?php echo date("Y-m-d H:i"); ?>" />
    </div>
    <?php
    }
    
    
function HookRse_versionEditSave_resource_data_multi_extra_modes($ref,$field)
    {
    # Process the batch revert action - hooks in to the save operation (save_resource_data_multi())
    				
    # Remove text/option(s) mode?
    if (getval("modeselect_" . $field["ref"],"")=="Revert")
            {
            $revert_date=getvalescaped("revert_" . $field["ref"],"");
            
            # Find the value of this field as of this date and time in the resource log.
            $value=sql_value("select previous_value value from resource_log where resource='$ref' and resource_type_field='" . $field["ref"] . "' and (type='e' or type='m') and date>'$revert_date' and previous_value is not null order by date limit 1",-1);
           
            if ($value!=-1) {return $value;}
            }
    return false;
    }


function HookRse_versionEditBefore_status_question()
    {
    global $lang;
    ?>
    <script>
    jQuery(document).ready(function()
        {
        jQuery("#editthis_status").click(function()
            {
            var edit_mode_status          = jQuery("#edit_mode_status");
            var question_status           = jQuery("#question_status");
            var modeselect_status         = jQuery("#modeselect_status");
            var edit_multi_revert_status  = jQuery("#edit_multi_revert_status");

            if(jQuery(this).is(":checked") && question_status.is(":visible") !== false)
                {
                question_status.show();
                edit_mode_status.show();

                modeselect_status_onchange(modeselect_status);

                return true;
                }

            question_status.hide();
            edit_mode_status.hide();
            edit_multi_revert_status.hide();

            return true;
            });
        });
    </script>
    <div class="Question" id="edit_mode_status" style="display: none; padding-bottom: 0px; margin-bottom: 0px;">
        <label><?php echo $lang["editmode"]; ?></label>
        <select id="modeselect_status" class="stdwidth" name="modeselect_status" onchange="modeselect_status_onchange(this);">
            <option value=""></option>
            <option value="Revert"><?php echo $lang["revertmetadatatodatetime"]; ?></option>
        </select>
        <script>
        function modeselect_status_onchange(el)
            {
            var modselect_status = jQuery(el);
            var question_status  = jQuery("#question_status");
            var edit_multi_revert_status  = jQuery("#edit_multi_revert_status");

            if(modselect_status.val() == "Revert")
                {
                question_status.hide();
                edit_multi_revert_status.show();

                return true;
                }

            question_status.show();
            edit_multi_revert_status.hide();

            return true;
            }
        </script>
        <div class="clearerleft"></div>
    </div>

    <div class="Question" id="edit_multi_revert_status" style="display: none; border-top: none;">
        <label></label>
        <input type="text" name="edit_multi_revert_status" class="stdwidth" value="<?php echo date("Y-m-d H:i"); ?>" />
        <div class="clearerleft"></div>
    </div>
    <?php
    }