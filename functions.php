<?php
function db_connect($db)
{
    $hostname="localhost";
    $username="webuser";
    $password="OGB_yHm[H/)W_9CK";
    //$db="temp";
    
    $dblink=new mysqli($hostname,$username,$password,$db);
    if (mysqli_connect_errno())
    {
        die("Error connection to database: ".mysqli_connect_error());
    }
    return $dblink;
}


function redirect ( $uri ) //accepts uri function
{ ?>
    <script type="text/javascript"> //switch from php to js
    <!--
    document.location.href="<?php echo $uri; ?>";
    -->
    </script>
<?php die;}


function log_session( $dblink, $uploaded_by, $session_id, $log_type){
    date_default_timezone_set("America/Chicago");
    $date_created = date("Y-m-d H:i:s");
    $time_stamp =date("Y-m-d");
    $status_code = "SUCCESS";
    $sql="INSERT INTO `log_session`(`user_id`,`session_id`, `log_type`, `status_code`,`message`, `date_created`,`time_stamp`) VALUES ('$uploaded_by', '$session_id', '$log_type', '$status_code', 'NULL', '$date_created', '$time_stamp')";
    
     $dblink->query($sql);
}

function log_session_message ( $dblink, $uploaded_by, $session_id, $log_type, $message){
    date_default_timezone_set("America/Chicago");
    $date_created = date("Y-m-d");
    $time_stamp =date("Y-m-d H:i:s");
    $status_code = "SUCCESS";
    $sql="INSERT INTO `log_session`(`user_id`, `session_id`, `log_type`, `status_code`, `message`, `date_created`, `time_stamp`) VALUES ('$uploaded_by', '$session_id', '$log_type', '$status_code', '$message', '$date_created', '$time_stamp')";
    
     $dblink->query($sql);
}


function log_session_error($dblink, $user_id, $session_id, $log_type ,$message){
    date_default_timezone_set("America/Chicago");
	$date_created = date("Y-m-d");
    $time_stamp =date("Y-m-d H:i:s");
	$status_code= 'ERROR';
	$sql="INSERT INTO `log_session`(`user_id`, `session_id`, `log_type`, `status_code`, `message`, `date_created`, `time_stamp`) VALUES ('$user_id', '$session_id', '$log_type', '$status_code', '$message', '$date_created', '$time_stamp')";
	
	$dblink->query($sql);	
}
	
function log_connection_error($dblink, $username, $log_type ,$message){
    date_default_timezone_set("America/Chicago");
	$date_created = date("Y-m-d");
	$time_created = date("Y-m-d H:i:s");
	$status= 'ERROR';
	$sql="INSERT INTO `log_session`(`username`, `session_id`, `log_type`, `status_code`, `message`, `date_created`, `time_stamp`) VALUES  ('$username', NULL,'$log_type', '$status' ,
	'$message','$date_created','$time_created')";
    
	$dblink->query($sql);
}


function log_incomplete_file($dblink, $file_name, $errorMessage, $loan_number, $path, $category, $contentsClean, $uploaded_by)
{  
    date_default_timezone_set("America/Chicago");
    $date_uploaded = date("Y-m-d H:i:s");
    $status_code = "FAILED";
    $sql="INSERT INTO `incomplete_files`(`id`, `file_name`, `status`, `message`, `loan_number`, `path`, `category`, `content`, `uploaded_by`, `date_uploaded`) VALUES (NULL,'$file_name','$status_code','$errorMessage','$loan_number','$path','$category','$contentsClean','$uploaded_by',' $date_uploaded')";
     
    $dblink->query($sql);
}

function save_new_file($dblink, $uploaded_by, $file_name, $loan_number, $category, $content, $path)
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    date_default_timezone_set("America/Chicago");
    $date_created = date("Y-m-d H:i:s");
    $sql="INSERT INTO `documents`(`auto_id`, `name`, `loan_number`, `category`, `upload_by`, `upload_date`, `content`, `path`) VALUES (NULL,'$file_name','$loan_number','$category','$uploaded_by','$date_created','$content','$path')";
     
    $dblink->query($sql);
}  


function displayPDF($content){
    ?>
        <object data="data:application/pdf;base64,<?php echo base64_encode($content) ?>" type="application/pdf" style="height:100%;width:100%"></object>

    <?php
}
function search_file($dblink, $file_id){
    $sql="Select * from `documents` where `auto_id` like '%$file_id%'";
    $result =$dblink->query($sql) or
       	die("Something went wrong with $sql<br>".$dblink->error);
    return $result;
}


function viewed_file($dblink, $file_name, $user_id, $message){
    date_default_timezone_set("America/Chicago");
    $viewed_date = date("Y-m-d H:i:s");
    $sql="INSERT INTO `view_log`(`auto_id`, `user_id`, `file_name`, `viewed_date`, `message`) VALUES (NULL,'$user_id','$file_name','$viewed_date','$message')";
    
    $dblink->query($sql);
}
    
?>
