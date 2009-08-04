<?php
#
# PDF Contact Sheet Functionality
# Contributed by Tom Gleason
#

foreach ($_GET as $key => $value) {$$key = stripslashes(utf8_decode(trim($value)));}
include('../../lib/fpdf/fpdf.php');
include('../../lib/fpdf/fpdf_imagealpha.php');
include('../../include/db.php');
include('../../include/general.php');
include('../../include/authenticate.php');
include('../../include/search_functions.php');
include('../../include/resource_functions.php');
include('../../include/collections_functions.php');
include('../../include/image_processing.php');

# Still making variables manually when not using Prototype: 
$collection=getval("c","");
$size=getval("size","");
$column=getval("columns","");
$orientation=getval("orientation","");
$sheetstyle=getval("sheetstyle","");
if(getval("preview","")!=""){$preview=true;} else {$preview=false;}

$imgsize="pre";

if ($preview==true){$imgsize="col";}
if ($size == "a4") {$width=210/25.4;$height=297/25.4;} // convert to inches
if ($size == "a3") {$width=297/25.4;$height=420/25.4;}

if ($size == "letter") {$width=8.5;$height=11;}
if ($size == "legal") {$width=8.5;$height=14;}
if ($size == "tabloid") {$width=11;$height=17;}

#configuring the sheet:
$pagewidth=$pagesize[0]=$width;
$pageheight=$pagesize[1]=$height;
$date= date("m-d-Y h:i a");
$titlefontsize=10;
$refnumberfontsize=8;
if ($orientation=="landscape"){$pagewidth=$pagesize[0]=$height; $pageheight=$pagesize[1]=$width;}

if ($sheetstyle=="thumbnails")
{
$columns=$column;

	#calculating sizes of cells, images, and number of rows:
	$cellsize[0]=$cellsize[1]=($pagewidth-1.7)/$columns;
	$imagesize=$cellsize[0]-0.3;
	$rowsperpage=($pageheight-1.2-$cellsize[1])/$cellsize[1];
	$page=1;
}
else if ($sheetstyle=="list")
{ 
	#calculating sizes of cells, images, and number of rows:
	$columns=1;
	$imagesize=1.0;
	$cellsize[0]=$pagewidth-1.7;
	$cellsize[1]=1.2;
	$rowsperpage=($pageheight-1.2-$cellsize[1])/$cellsize[1];
	$page=1;
}

#Get data
$collectiondata= get_collection($collection);
$result=do_search("!collection" . $collection);

$user= get_user($collectiondata['user']);

#Start PDF, set metadata, etc.
$pdf=new PDF_ImageAlpha("P","in",$pagesize);
$pdf->SetTitle($collectiondata['name']." ".$date);
$pdf->SetAuthor($user['fullname']." ".$user['email']);
$pdf->SetSubject($applicationname." Contact Sheet");
$pdf->SetMargins(1,1.2,.7);
$pdf->SetAutoPageBreak(true,0);
$pdf->AddPage();

#Title on sheet
$pdf->SetFont('helvetica','',$titlefontsize);
$title = $applicationname." - ". $collectiondata['name']." - ".$date;
$pagenumber = " - p.". $page;
$pdf->Text(1,.8,utf8_decode($title.$pagenumber),0,0,"L");$pdf->ln();
$pdf->SetFontSize($refnumberfontsize);

#Begin loop through resources, collecting Keywords too.
$i=0;
$j=0;


for ($n=0;$n<count($result);$n++)			
		{
		$ref=$result[$n]["ref"];
		$preview_extension=$result[$n]["preview_extension"];
		$resourcetitle="";
		if ($print_contact_title) {	$resourcetitle = " - " . $result[$n]["title"];}
    	$i++;

		if ($ref!==false)
			{
			# Find image
			$imgpath = get_resource_path($ref,true,$imgsize,false,$preview_extension);
			
			if (!file_exists($imgpath)){
			$imgpath="../../gfx/".get_nopreview_icon($result[$n]['resource_type'],$result[$n]['file_extension'],false,true); }
			    $preview_extension=explode(".",$imgpath);
				if(count($preview_extension)>1){
				$preview_extension=trim(strtolower($preview_extension[count($preview_extension)-1]));
				} 
			if (file_exists($imgpath))
			{
				
				# Two ways to size image to cell, either by height or by width.
				$thumbsize=getimagesize($imgpath);
					if ($thumbsize[0]>$thumbsize[1]){
					
					if ($sheetstyle=="thumbnails")
					{
						$pdf->Text($pdf->Getx(),$pdf->Gety()-.05,$ref.$resourcetitle);		
					}
					else if ($sheetstyle=="list")
					{
						$pdf->Text($pdf->Getx()+$imagesize+0.1,$pdf->Gety()+0.2,$ref);	
						for($ff=0; $ff<count($config_sheetlist_fields); $ff++)
							$pdf->Text($pdf->Getx()+$imagesize+0.1,$pdf->Gety()+(0.2*($ff+2)),get_data_by_field($ref, $config_sheetlist_fields[$ff]));			
					}
						
						$pdf->Cell($cellsize[0],$cellsize[1],$pdf->Image($imgpath,$pdf->GetX(),$pdf->GetY(),$imagesize,0,$preview_extension,$baseurl. "/?r=" . $ref),0,0);
					
					}
					
					else{
						
					if ($sheetstyle=="thumbnails")
					{
						$pdf->Text($pdf->Getx(),$pdf->Gety()-.05,$ref.$resourcetitle);	
					}
					else if ($sheetstyle=="list")
					{
						$pdf->Text($pdf->Getx()+$imagesize+0.1,$pdf->Gety()+0.2,$ref);			
						for($ff=0; $ff<count($config_sheetlist_fields); $ff++)
							$pdf->Text($pdf->Getx()+$imagesize+0.1,$pdf->Gety()+(0.2*($ff+2)),get_data_by_field($ref, $config_sheetlist_fields[$ff]));			
					}

						$pdf->Cell($cellsize[0],$cellsize[1],$pdf->Image($imgpath,$pdf->GetX(),$pdf->GetY(),0,$imagesize,$preview_extension,$baseurl. "/?r=" . $ref),0,0);
						
					}
			$n=$n++;
					if ($i == $columns){
					
						$pdf->ln(); $i=0;$j++;
							
							if ($j > $rowsperpage){
						    $page = $page+1;
							$j=0; 
							if (($preview==true) && ($page>1)){break;} else{
							if ($n<count($result)-1){ //avoid making an additional page if it will be empty							
								$pdf->AddPage();
								}
							}
							
							if ($n<count($result)-1){// avoid adding header if this is the last page and the next would be empty
								#When moving to a new page, get current coordinates, place a new page header.
								$pagestartx=$pdf->GetX();
								$pagestarty=$pdf->GetY();
								$pdf->SetFont('helvetica','',$titlefontsize);
								$pagenumber = " - p.". $page;
								$pdf->Text(1,.8,utf8_decode($title.$pagenumber),0,0,"L");$pdf->ln();
								#then restore the saved coordinates and fontsize to continue as usual.
								$pdf->SetFontSize($refnumberfontsize);
								$pdf->Setx($pagestartx);
								$pdf->SetY($pagestarty);
								}
							}			
					}
				}
			}
		}	

#Make AJAX preview?:
	if ($preview==true && isset($imagemagick_path)) 
		{
		if(!is_dir($storagedir."/tmp")){mkdir($storagedir."/tmp",0777);}
		if (file_exists($storagedir."/tmp/contactsheet.jpg")){unlink($storagedir."/tmp/contactsheet.jpg");}
		if (file_exists($storagedir."/tmp/contactsheet.pdf")){unlink($storagedir."/tmp/contactsheet.pdf");}
		$pdf->Output($storagedir."/tmp/contactsheet.pdf","F"); 
		# Set up ImageMagick 
		putenv("MAGICK_HOME=" . $imagemagick_path); 
		putenv("DYLD_LIBRARY_PATH=" . $imagemagick_path . "/lib"); 
		putenv("PATH=" . $ghostscript_path . ":" . $imagemagick_path . ":" . $imagemagick_path . "/bin"); # Path 
		$command=$imagemagick_path . "/bin/convert";
		if (!file_exists($command)) {$command=$imagemagick_path . "/convert.exe";}
		if (!file_exists($command)) {$command=$imagemagick_path . "/convert";}
		if (!file_exists($command)) {exit("Could not find ImageMagick 'convert' utility at location '$command'");}	
		$command.= " -resize 250x250 -quality 90 -colorspace RGB \"".$storagedir."/tmp/contactsheet.pdf\"[0] \"".$storagedir."/tmp/contactsheet.jpg\"";
		shell_exec($command);
		exit();
		}
	
#check configs, decide whether PDF outputs to browser or to a new resource.
if ($contact_sheet_resource==true){
	$newresource=create_resource(1,0);

	update_field($newresource,8,$collectiondata['name']." ".$date);
	update_field($newresource,$filename_field,$newresource.".pdf");

#Relate all resources in collection to the new contact sheet resource
relate_to_collection($newresource,$collection);	

	#update file extension
	sql_query("update resource set file_extension='pdf' where ref='$newresource'");
	
	# Create the file in the new resource folder:
	$path=get_resource_path($newresource,true,"",true,"pdf");
	$pdf->Output($path,"F");
	
	#Create thumbnails and redirect browser to the new contact sheet resource
	create_previews($newresource,true,"pdf");
	redirect("pages/view.php?ref=" .$newresource);
	}

else

	#to browser
	{$pdf->Output($collectiondata['name'].".pdf","D");}


?>
