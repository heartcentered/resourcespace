<?
include "include/db.php";
include "include/collections_functions.php";

# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("c",""),$k) && !check_access_key(getvalescaped("r",""),$k))) {include "include/authenticate.php";}

$topurl=$use_theme_as_home?"themes.php":"home.php";
$bottomurl="collections.php?k=" . $k;

if (getval("c","")!="")
	{
	# quick redirect to a collection (from e-mails, keep the URL nice and short)
	$c=getvalescaped("c","");
	$topurl="search.php?search=" . urlencode("!collection" . $c);
	$bottomurl="collections.php?collection=" . $c . "&k=" . $k;
	
	if ($k!="")
		{
		# External access user... set top URL to first resource
		$r=get_collection_resources($c);
		if (count($r)>0)
			{
			$topurl="view.php?ref=" . $r[0] . "&k=" . $k;		
			}
		else
			{
			$topurl="home.php";
			}
		}
	}

if (getval("r","")!="")
	{
	# quick redirect to a resource (from e-mails)
	$r=getvalescaped("r","");
	$topurl="view.php?ref=" . $r . "&k=" . $k;
	if ($k!="") {$bottomurl="";} # No bottom frame if anon. access for single resource
	}

if (getval("url","")!="")
	{
	# New URL for top section (when the frameset is lost)
	$topurl=getval("url",$topurl);
	}

if (checkperm("b")) {redirect($topurl);}
?>
<html>
<head>
<!--
ResourceSpace version <?=$productversion?>

http://www.montala.net/resourcespace.php
Copyright Oxfam GB 2006-2008
-->
<title><?=htmlspecialchars($applicationname)?></title>

<frameset rows="*<? if ($bottomurl!="") { ?><? if ($collection_resize!=true){?>,3<?}?>,138<? } ?>" id="topframe" framespacing="0" <? if ($collection_resize!=true){?>frameborder="no"<?}?>>
<frame name="main" id="main" src="<?=$topurl?>" <? if ($collection_resize!=true){?>frameborder="no"<?}?>>

<? if ($bottomurl!="") { ?>
<? if ($collection_resize!=true){?><frame src="frame-divider.htm" name="DivideFrame" frameborder="no" scrolling="no" noresize="noresize" marginwidth="0" marginheight="0" id="DivideFrame" /><?}?>
<frame name="collections" id="collections" src="<?=$bottomurl?>" frameborder=no>
<? } ?>

</frameset>


</head>
</html>