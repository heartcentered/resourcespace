<?php
// auto_accession Plugin v1.0 ../pages/alternative_files.php Hook File

function HookAuto_accessionAlternative_filesAlternativefileslist()
    {
    global $lang, $auto_accession_alt_accession;

    if ($auto_accession_alt_accession)
        { ?>
        <td><?php echo $lang['auto_accession_list_heading']; ?></td> <?php
        }

    return;
    }

function HookAuto_accessionAlternative_filesAlternativefileslist2($files)
    {
    global $lang, $auto_accession_alt_accession;

    if ($auto_accession_alt_accession && $files["alt_accession"] != "")
        { ?>
        <td><?php echo htmlspecialchars($files["alt_accession"]) ?>&nbsp;</td> <?php
        }
    elseif ($auto_accession_alt_accession)
        { ?>
        <td></td> <?php
        }

    return;
    }
