<link href="assets/css/bootstrap.css" rel="stylesheet" />
<script src="assets/js/jquery-1.12.4.js"></script>
<script src="assets/js/bootstrap.js"></script>

<?php
include("functions.php");   
$dblink=db_connect("docstorage"); 
$autoid=$_REQUEST['fid'];
$username="webuser";
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">View Files on DB</h1>';
echo '<div class="panel-body">';

$result=search_file($dblink, $autoid);
$data=$result->fetch_array(MYSQLI_ASSOC);
$content=$data['content'];
$file_name= $data['file_name'];
$index=strpos($file_name,".") + 1;
$extension=substr($file_name, $index);

if($extension =="pdf")
{
    displayPDF($content);
    
}
else
{
    $view_date=date("Y-m-d_H:i:s");
    $fname=date("Y-m-d_H:i:s")."-userid-file.pdf"; 
        if (!($fp=fopen("/var/www/html/uploads/$fname","w")))
        {
			echo "<p>File could not be loaded at this time</p>";
            
            $message="Error loading File";
            viewed_file($dblink, $fname, $username, $message);

        }
		else
		{
			fwrite($fp,$content);
			fclose($fp);
			echo '<p>File: <a href="uploads/'.$fname.'" target="_blank">'.$data['name'].'</a></p>';
            
            $message="Success loading File";
            viewed_file($dblink, $fname, $username, $message);
		}
}

echo '</div>';//end panel-body
echo '</div>';//end page-inner
?>