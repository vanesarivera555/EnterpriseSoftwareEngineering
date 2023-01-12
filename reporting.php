<?php
$page="reporting.php";
include("functions.php");
$dblink=db_connect("docstorage");



function sizeTotalBlob($dblink)   
{  
    $sql="Select SUM(OCTET_LENGTH(`content`)) as total_num from `documents`";
    $rst=$dblink->query($sql) or     
            die("Something went wrong with: $sql<br>".$dblink->error);
    $data=$rst->fetch_array(MYSQLI_ASSOC);
    $total= $data['total_num'] / 1024;
    echo '<div>The total size of all Documents across loans is: ' . $total.'</div>';  
    
    $sql="Select count(`content`) as total_count from `documents`";
    $result=$dblink->query($sql) or 
        die("Something went wrong with: $sql<br>".$dblink->error);
    $data_count=$result->fetch_array(MYSQLI_ASSOC);
    $totalCount=$data_count['total_count'];
    $average = $total / $totalCount;
    
     echo '<div>The average size of all Documents across loans is: ' . $average.'</div>';    
}


function unique_loan_numbers($dblink)
{
    $sql="Select count( DISTINCT `loan_number`)  from `documents`";
    $result=$dblink->query($sql) or     
        die("Something went wrong with: $sql<br>".$dblink->error);
    $data=$result->fetch_array(MYSQLI_NUM);
    echo "Number of Unique Loan Numbers: " . $data[0];
}


function TotalDocs($dblink)
{
    $sql="Select count( `name`)  from `documents`";
    $result=$dblink->query($sql) or     
        die("Something went wrong with: $sql<br>".$dblink->error);
    $data=$result->fetch_array(MYSQLI_NUM);
    echo "Total Documents Recieved " . $data[0];
}


function loan_numbers($dblink)  
{
   
    $sql="Select DISTINCT `loan_number` from `documents`";
    $result=$dblink->query($sql) or 
        die("Something went wrong with: $sql<br>".$dblink->error);
    while($data=$result->fetch_array(MYSQLI_ASSOC))
    {
        echo '<div> Loan Number:'.$data['loan_number'].'</div>';
        
    }

}


     
function docs_recieved($dblink)  
{
    $sql="Select * from `documents`";
    $result=$dblink->query($sql) or 
        die("Something went wrong with: $sql<br>".$dblink->error);
    
    $sql="Select count( `name`)  from `documents`";
    $resultdocs=$dblink->query($sql) or     
        die("Something went wrong with: $sql<br>".$dblink->error);
    $datadocs=$resultdocs->fetch_array(MYSQLI_NUM);
    $sql="Select count( DISTINCT `loan_number`)  from `documents`";
    
    $resultU=$dblink->query($sql) or     
        die("Something went wrong with: $sql<br>".$dblink->error);
    $dataU=$resultU->fetch_array(MYSQLI_NUM);
    $average_docs = $datadocs[0]/$dataU[0];
    echo '<div>The Average Across all other loans is: '.$average_docs.'</div>';
    echo '<br>';
    $loanArray=array();
    while($data=$result->fetch_array(MYSQLI_ASSOC))
    {
        $tmp=explode("-",$data['name']);
        $loanArray[]=$tmp[0];
    }
    
    $loanUnique=array_unique($loanArray);
    foreach($loanUnique as $key=>$value)
    {
        $sql="Select count(`name`) from `documents` where `name` like '%$value%'";
        $rst=$dblink->query($sql) or     
            die("Something went wrong with: $sql<br>".$dblink->error);
        $tmp=$rst->fetch_array(MYSQLI_NUM);

        switch(true){
            case( $tmp[0] < $average_docs):
            echo '<div>Loan Number: '.$value.' has '.$tmp[0].' number of documents recieved.  Below Average</div>';
                break;
            case ( $tmp[0] > $currAverage):
            echo '<div>Loan Number: '.$value.' has '.$tmp[0].' number of documents recieved. Above Average</div>';
                break;
            case ( $tmp[0] == $currAverage):
            echo '<div>Loan Number: '.$value.' has '.$tmp[0].' number of documents recieved.  Average</div>';
                break;
        }
    
    }      
}


function getAverageDocsForAllLoan($dblink){
	
	$sql = "SELECT COUNT(`category`) as total FROM `documents`";
	$sql2 = "SELECT COUNT(DISTINCT `loan_number`) as loanCount FROM `documents`";
	
	$result = $dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblink->error);
	
	$data=$result->fetch_array(MYSQLI_ASSOC);
	$totalDocs = $data['total'];
	
	$result = $dblink->query($sql2) or
		die("Something went wrong with $sql2<br>".$dblink->error);
	
	$data=$result->fetch_array(MYSQLI_ASSOC);
	$loanCount = $data['loanCount']; 
	
	$total =  $totalDocs / $loanCount;
	
	return number_format((float)$total, 2, '.', '');
	
	echo $total;
}


function incomplete($dblink) 
{	
    $flag = false;
    $sql="Select * from `documents`";
    $result=$dblink->query($sql) or 
        die("Something went wrong with: $sql<br>".$dblink->error);
    $loanArray=array();
    while($data=$result->fetch_array(MYSQLI_ASSOC))
    {
        $tmp=explode("-",$data['name']);
        $loanArray[]=$tmp[0];
    }

    $loanUnique=array_unique($loanArray);
    foreach($loanUnique as $key=>$value)
    {
    	$category_List=array("Credit"=>"Credit", "Closing"=>"Closing", "Internal"=>"Internal", "Legal"=>"Legal","Financial"=>"Financial", "Personal"=>"Personal", "Title"=>"Title", "Other"=>"Other"); 
		$sql= "SELECT * FROM `documents` WHERE loan_number = $value";
		$result = $dblink->query($sql) or
			die("Something went wrong with $sql<br>".$dblink->error);
        while ($data=$result->fetch_array(MYSQLI_ASSOC))
        {		
			$category = $data['category'];
			if (array_key_exists($category,$category_List))
            {
  				unset($category_List["$category"]);
				
  			}				
		}
		
		if( count($category_List) <= 7)
        {
			$flag = true;
			echo "Loan number: ".$value." missing documents: "; 
			foreach($category_List as $x=>$x_value){
  		    echo $x_value." ";
  		}
			echo '<br>';
		}
		
	}
	
	return $flag;
}


function completed($dblink)  
{
    	
	$flag = false;
    $sql="Select * from `documents`";
    $result=$dblink->query($sql) or 
        die("Something went wrong with: $sql<br>".$dblink->error);
    $loanArray=array();
    while($data=$result->fetch_array(MYSQLI_ASSOC))
    {
        $tmp=explode("-",$data['name']);
        $loanArray[]=$tmp[0];
    }

    $loanUnique=array_unique($loanArray);
    foreach($loanUnique as $key=>$value)
    {
        $category_List=array("Credit"=>"Credit", "Closing"=>"Closing", "Internal"=>"Internal", "Legal"=>"Legal","Financial"=>"Financial", "Personal"=>"Personal", "Title"=>"Title", "Other"=>"Other"); 
        $count = 0;
        $sql= "SELECT * FROM `documents` WHERE loan_number = $value";
        $result = $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$dblink->error);
        while ($data=$result->fetch_array(MYSQLI_ASSOC))
        {	
            $category = $data['category'];
            if (array_key_exists($category,$category_List))
            {
                unset($category_List["$category"]);
            }					
        }
        if( count($category_List) == 0)
        {
            $flag = true;
            echo "Loan number: ".$value;
            echo '<br>';
        }	
    }

return $flag;
}


echo '<div>Q1: Total number of unique loan numbers generted:</div>';
echo '<br>';
unique_loan_numbers($dblink);
echo '<br>';
echo '<br>';
loan_numbers($dblink);
echo '<br>';
echo '<br>';
echo '<br>';

echo '<div>Q2: Total size of all documents recieved and the average size of all: </div>';
echo '<br>';
sizeTotalBlob($dblink);
echo '<br>';
echo '<br>';
//sizeBlob($dblink);
echo '<br>';
echo '<br>';

echo '<div>Q3: Total number of documents recieved and the average compared</div>';
echo '<br>';
docs_recieved($dblink);
echo '<br>';
echo '<br>';
echo '<br>';

echo '<div>Q4: List of all loan numbers that are incomplete in Category:</div>';
echo '<br>';
incomplete($dblink);
echo '<br>';
echo '<br>';
echo '<br>';

echo '<div>Q4: List of all loan numbers that are complete in Categories:</div>';
echo '<br>';
completed($dblink); 
echo'NONE';
echo '<br>';
echo '<br>';
echo '<br>';
 
?>
