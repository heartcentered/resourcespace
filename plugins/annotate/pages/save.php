<?php


include_once "../../../include/db.php";
include_once "../../../include/authenticate.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
$ref=getvalescaped("ref","");
if ($ref==""){die("no");}

$top=getvalescaped('top','');
$left=getvalescaped('left','');
$width=getvalescaped('width','');
$height=getvalescaped('height','');
$text=getvalescaped('text','');
$id=getvalescaped('id','');
$preview_width=getvalescaped('pw','');
$preview_height=getvalescaped('ph','');
$text = str_replace(array(chr(13), chr(10)), '<br />', $text);

sql_query("delete from annotate_notes where ref='$ref' and note_id='$id'");

if (substr($text,0,strlen($username))!=$username){$text=$username.": ".$text;}

sql_query("insert into annotate_notes (ref,top_pos,left_pos,width,height,preview_width,preview_height,note,user) values ('$ref','$top','$left','$width','$height','$preview_width','$preview_height','$text','$userref') ");

echo sql_insert_id();

$notes=sql_query("select * from annotate_notes where ref='$ref'");
sql_query("update resource set annotation_count=".count($notes)." where ref=$ref");

add_keyword_mappings($ref,$text,-1);
