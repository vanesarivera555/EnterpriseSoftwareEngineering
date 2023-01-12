<link href="assets/css/bootstrap.css" rel="stylesheet" />
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<script src="assets/js/jquery-1.12.4.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-fileupload.js"></script>
<?php
include("functions.php");

echo '<div id="page-inner">';
    if (isset($_REQUEST['msg']) && ($_REQUEST['msg']=="success"))
    {
	echo '<div class="alert alert-success alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
	echo 'Document successfully uploaded!</div>';
    }
    if (isset($_REQUEST['msg']) && ($_REQUEST['msg']=="failed"))
    {
    $error=$_REQUEST['error_message'];
	echo '<div class="alert alert-success alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
	echo "Document failed to upload $error</div>";
    }

    echo '<nav class="navbar navbar-dark bg-primary">';
    echo '<h1 class="page-head-line">Upload a new file</h1>';
    echo '</nav>';
    echo '<div class="panel-body">'; 
    echo '<form method="post" enctype="multipart/form-data" action="">';
        echo '<input type="hidden" name="uploadedby" value="user@test.mail">';
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="10000000">';
        echo '<div class="form-group">';
        echo '<label class="control-label col-lg-4">File Upload</label>';
        echo '<div class="">';
        echo '<div class="fileupload fileupload-new" data-provides="fileupload">';
        echo '<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>';
        echo '<div class="row">';//buttons
        echo '<div class="col-md-2">';
        echo '<span class="btn btn-file btn-primary">';
        echo '<span class="fileupload-new">Select File</span>';
        echo '<span class="fileupload-exists">Change</span>';
        echo '<input name="userfile" type="file"></span></div>'; //traslates to var
        echo '<div class="col-md-2"><a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a></div>';
        echo '</div>';//end buttons
        echo '</div>';//end fileupload fileupload-new
        echo '</div>';//end ""
        echo '<div>';
        echo '<br>';
        echo '<label>Please select a category Enter loan number</label>';
        echo '<br>';
        echo '<br>';
        echo '<select name="category">';
        echo '<option value="none">Select Category</option>';
        echo '<option value="Personal">Personal</option>';
        echo '<option value="Internal">Internal</option>';
        echo '<option value="Legal">Legal</option>';
        echo '<option value="Financial">Financial</option>';
        echo '<option value="Closing">Closing</option>';
        echo '</select>';
        echo '<br>';
        echo '<br>';
        echo '<input type="text" name="loan_number" placeholder="Enter Loan Number">';
        echo '</div>';
        echo '</div>';//end form-group
        echo '<hr>';
        echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
    echo '</form>';
    echo '</div>';//end panel-body
    echo '</div>';//end page-inner



$hostname="localhost";
$username="webuser";
$password="OGB_yHm[H/)W_9CK";
$db="docstorage";
$dblink=new mysqli($hostname,$username,$password,$db);
if (mysqli_connect_errno())
{
    die("Error connecting to database: ".mysqli_connect_error());   
}

if (isset($_POST['submit']) )
{
	$file_name=$_FILES['userfile']['name']; 
	$category = "";
	$loan_number = "";
	$errorMessage = "";
    $index = strpos($file_name, ".") + 1; 
    $extension = substr($file_name, $index);
    $errorMessage="";
    $errorFound= FALSE;
   
    if($_POST['category'] != 'none' && $_POST['loan_number'] != NULL )
    {
        $category = $_POST['category'];
		$loan_number = $_POST['loan_number'];
        $timestamp=date("Y-m-d_H_i_s"); 
		$file_name = $loan_number."-".$category."-".$timestamp.".".$extension;
    }
    else
    {
        $errorMessage="Blank Field";
        $errorFound=TRUE;
		redirect("upload-new.php?sid=$sid&msg=fail&error=$errorMessage");
	}
		

    if( $extension == "pdf")
    {
        $name_array=explode("-",$file_name);
        $tmpName=$_FILES['userfile']['tmp_name'];
        $fp=fopen($tmpName, 'r');
        $content=fread($fp, filesize($tmpName));
        fclose($fp);
        $contentsClean=addslashes($content);
        $patternLoanNumber = "/([0-9]+-)/i";
        if(!(preg_match($patternLoanNumber, $file_name)))
        {
            $errorMessage="Invalid loan number"; 
            $errorFound = TRUE;
               
        }
        else
        {
            save_new_file($dblink, $username, $file_name, $loan_number, $category, $contentsClean, NULL );             
        }
    }
    else
    {
        $errorFound=TRUE;
        $errorMessage="Only PDFs allowed";
        log_incomplete_file($dblink, $file_name, $errorMessage, NULL, NULL, NULL, NULL, $username);  
    }
    
    if($errorFound == TRUE)
    {
        redirect("upload-new.php?sid=$sid&msg=fail&error=$errorMessage"); 
    }
    else
    {
        redirect("upload-new.php?sid=$sid&msg=success");
    }
	
}

?>