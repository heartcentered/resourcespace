<?php
include dirname(__FILE__)."/../../../include/db.php";
include_once dirname(__FILE__)."/../../../include/general.php";
include dirname(__FILE__)."/../../../include/authenticate.php";if (!checkperm("o")) {exit ("Permission denied.");}
global $baseurl;


# Check if it's necessary to upgrade the database structure
include dirname(__FILE__) . "/../upgrade/upgrade.php";
    

$offset=getvalescaped("offset",0);
if (array_key_exists("findtext",$_POST)) {$offset=0;} # reset page counter when posting
$findtext=getvalescaped("findtext","");

$delete=getvalescaped("delete","");
if ($delete!="" && enforcePostRequest(false))
	{
	# Delete news
	delete_news($delete);
	}

if (getval("create","")!="")
	{
	redirect("plugins/news/pages/news_content_edit.php?ref=new");
	}

include dirname(__FILE__)."/../../../include/header.php";

?>

<div class="BasicsBox"> 
  <h1><?php echo $lang["news_manage"]?></h1>
  <h2><?php echo $lang["news_intro"]?></h2>
 
<?php 
$news=get_news("","",$findtext);

# pager
$per_page=15;
$results=count($news);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$url="news_edit.php?findtext=".urlencode($findtext)."&offset=". $offset;
$jumpcount=1;
?>

<div class="BasicsBox">
	<form method="post">
        <?php generateFormToken("news_add"); ?>
		<label for="buttons"> </label>		
		<input name="create" type="submit" value="<?php echo $lang["news_add"]?>"/>
	</form>
</div>

<div class="TopInpageNav"><?php pager();	?></div>


<form method=post id="newsform">
    <?php generateFormToken("newsform"); ?>
<input type=hidden name="delete" id="newsdelete" value="">


<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["news_headline"]?></td>
<td><?php echo $lang["news_body"]?></td>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
for ($n=$offset;(($n<count($news)) && ($n<($offset+$per_page)));$n++)
	{
	?>
	<tr>
	<td><div class="ListTitle"><?php echo highlightkeywords($news[$n]["date"],$findtext,true);?></div></td>
	
	<td><div class="ListTitle"><?php echo "<a href=\"" . $baseurl . "/plugins/news/pages/news.php?ref=" . $news[$n]["ref"] . "\">" . highlightkeywords($news[$n]["title"],$findtext,true);?></a></div></td>
	
	<td><?php echo highlightkeywords(tidy_trim(htmlspecialchars($news[$n]["body"]),100),$findtext,true)?></td>
	
	<td>
	<div class="ListTools">
		<a href="news_content_edit.php?ref=<?php echo $news[$n]["ref"]?>&backurl=<?php echo urlencode($url . "&offset=" . $offset . "&findtext=" . $findtext)?>"><?php echo LINK_CARET . $lang["action-edit"]?> </a>
		<a href="#" onclick="if (confirm('<?php echo $lang["confirm-deletion"]?>')) {document.getElementById('newsdelete').value='<?php echo $news[$n]["ref"]?>';document.getElementById('newsform').submit();} return false;"><?php echo LINK_CARET . $lang["action-delete"]?></a>
		</div>
	</td>
	</tr>
	<?php
	}
?>

</table>
</div>
<div class="BottomInpageNav"><?php pager(true); ?></div>
</div>

<div class="BasicsBox">
	<form method="post">
        <?php generateFormToken("news_search"); ?>
		<div class="Question">
			<label for="find"><?php echo $lang["news_search"]?><br/></label>
			<div class="tickset">
			 <div class="Inline">			
			<input type=text placeholder="<?php echo $lang['searchbytext']?>" name="findtext" id="findtext" value="<?php echo $findtext?>" maxlength="100" class="shrtwidth" />
			
			<input type="button" value="<?php echo $lang['clearbutton']?>" onClick="$('findtext').value='';form.submit();" />
			<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" />
			 
			</div>
			</div>
			<div class="clearerleft"> 
			</div>
		</div>
	</form>
</div>


<?php

include dirname(__FILE__)."/../../../include/footer.php";

?>

