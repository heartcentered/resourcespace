<?php
// auto_accession Plugin v1.0 ../pages/alternative_file.php Hook File

function HookAuto_accessionAlternative_FileAlternative_file_question()
    { 
    global $lang, $file, $auto_accession_alt_accession;

    if ($auto_accession_alt_accession)
        { ?>
        <div class="Question">
        <label for="name"><?php echo $lang['auto_accession_list_heading'] ?></label><input type=text class="stdwidth" name="alt_accession" id="alt_accession" value="<?php echo htmlspecialchars($file["alt_accession"]) ?>" maxlength="200">
        <div class="clearerleft"> </div>
        </div> <?php
        }
    }
