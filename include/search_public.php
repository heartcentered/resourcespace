<?php

# Perform the search
if (!isset($collections)){
    $collections=search_public_collections($search,"theme","ASC",!$search_includes_themes,!$search_includes_public_collections,true);
}
for ($n=0;$n<count($collections);$n++)
	{
	$pub_url="search.php?search=" . urlencode("!collection" . $collections[$n]["ref"]);
	if ($display=="thumbs")
		{
		?>
		<div class="ResourcePanelShell" id="ResourceShell">
		<div class="ResourcePanel">
	
		<table  border="0" class="ResourceAlign"><tr><td>
		
		<div style="position: relative;height:140px;">
		<a href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($collections[$n]["name"])))?>">
		
		<?php 
		$resources=explode(",",$collections[$n]["resources"]);
		$images=0;
		for ($m=0;$m<count($resources) && $images<=4;$m++)
			{
			$ref=$resources[$m];
			if (file_exists(get_resource_path($ref, true, "col", false, "jpg", -1, 1, false)))
				{
				$images++;
				$space=3+($images-1)*18;
				?>
				<img style="position: absolute; top:<?php echo $space ?>px;left:<?php echo $space ?>px" src="<?php echo get_resource_path($ref,false,"col",false,"jpg",-1,1,false)?>" class="ImageBorder">
				<?php				
				}
			}
		?>
		</a>
		</div>
		</td>
		</tr></table>

		<div class="ResourcePanelInfo"><a href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($collections[$n]["name"])))?>"><?php echo highlightkeywords(htmlspecialchars(tidy_trim(i18n_get_translated($collections[$n]["name"]),32)),$search)?></a>&nbsp;</div>

		<div class="ResourcePanelCountry" style="float:right;">&gt;&nbsp;<a target="collections" href="collections.php?collection=<?php echo $collections[$n]["ref"]?>"><?php echo $lang["action-select"]?></a>&nbsp;&nbsp;&nbsp;&gt;&nbsp;<a href="<?php echo $pub_url?>"><?php echo $lang["action-view"]?></a></div>		

		<div class="clearer"></div>
		</div>
		<div class="PanelShadow"></div>
		</div>
	<?php } 
	
	
	
	
	
	
	
	
	if ($display=="smallthumbs")
		{
		?>
		<div class="ResourcePanelShellSmall" id="ResourceShell">
		<div class="ResourcePanelSmall">
	
		<table  border="0" class="ResourceAlignSmall"><tr><td>
		
		<div style="position: relative;height:70px;">
		<a href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($collections[$n]["name"])))?>">
		
		<?php 
		$resources=explode(",",$collections[$n]["resources"]);
		$images=0;
		for ($m=0;$m<count($resources) && $images<=4;$m++)
			{
			$ref=$resources[$m];
			$path=get_resource_path($ref, true, "col", false, "jpg", -1, 1, false);
			if (file_exists($path))
				{
				if (list($sw,$sh) = @getimagesize($path))
					{
					$images++;
					$space=3+($images-1)*9;
					?>
					<img width="<?php echo floor($sw/2)?>" height="<?php echo floor($sh/2)?>" style="position: absolute; top:<?php echo $space ?>px;left:<?php echo $space ?>px" src="<?php echo get_resource_path($ref,false,"col",false,"jpg",-1,1,false)?>" class="ImageBorder">
					<?php				
					}
				}
			}
		?>
		</a>
		</div>
		</td>
		</tr></table>

		<div class="ResourcePanelCountry" style="float:right;height:12px;">&gt;&nbsp;<a target="collections" href="collections.php?collection=<?php echo $collections[$n]["ref"]?>"><?php echo $lang["action-select"]?></a>&nbsp;&gt;&nbsp;<a href="<?php echo $pub_url?>"><?php echo $lang["action-view"]?></a></div>		

		<div class="clearer"></div>
		</div>
		<div class="PanelShadow"></div>
		</div>
	<?php } 
	
	if ($display=="list")
		{
		?>
		<tr>
		<td nowrap><div class="ListTitle"><a href="<?php echo $pub_url?>"><?php echo $lang["collection"] . ": " . highlightkeywords(tidy_trim(i18n_get_translated($collections[$n]["name"]),45),$search)?></a></div></td>
		<?php 
		if (!$use_resource_column_data){
			for ($x=0;$x<count($ldf)-1;$x++){
				?><td>&nbsp;</td><?php
				}
			}	
		?>
		<td>&nbsp;</td>
		<?php if ($id_column){?><td>&nbsp;</td><?php } ?>
		<?php if ($resource_type_column){?><td><?php echo $lang["collection"] ?></td><?php } ?>
		<?php if ($date_column){?><td><?php echo nicedate($collections[$n]["created"],false,true)?></td><?php } ?>
		<td><div class="ListTools"><a target="collections" href="collections.php?collection=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["action-select"]?></a>&nbsp;&nbsp;<a href="<?php echo $pub_url?>">&gt;&nbsp;<?php echo $lang["action-view"]?></a></div></td>
		</tr>
	<?php } ?>		
	
<?php } ?>
