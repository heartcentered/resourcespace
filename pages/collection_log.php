<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/collections_functions.php";

$ref=getvalescaped("ref","");

include "../include/header.php";
?>

<?php
# Fetch collection name
$colinfo=get_collection($ref);
$colname=$colinfo["name"];
?>


<div class="BasicsBox">
<h1><?php echo $lang["collectionlog"];?> - <a <a <?php if ($frameless_collections && !checkperm("b")){ ?>href onclick="ChangeCollection(<?php echo $ref;?>);"<?php } else {?>href="collections.php?collection=<?php echo $ref;?>" target="collections"<?php }?>><?php echo @$colname;?></a></h1>
</div>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["user"]?></td>
<td><?php echo $lang["action"]?></td>
<td><?php echo $lang["resourceid"]?></td>
<td><?php echo $lang["resourcetitle"]?></td>
</tr>

<?php
$log=get_collection_log($ref);
for ($n=0;$n<count($log);$n++)
	{
	?>
	<!--List Item-->
	<tr>
	<td><?php echo nicedate($log[$n]["date"],true)?></td>
	<td><?php echo $log[$n]["username"]?> (<?php echo $log[$n]["fullname"]?>)</td>
	<td><?php echo $lang["collectionlog-" . $log[$n]["type"]]?></td>
	<td><?php echo $log[$n]["resource"]?></td>
	<td><?php echo i18n_get_translated($log[$n]["title"])?></td>
	</tr>
	<?php
	}
?>
</table>
</div>
<?php
include "../include/footer.php";
?>
