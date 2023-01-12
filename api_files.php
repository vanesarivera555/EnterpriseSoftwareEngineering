<?php
include("functions.php");
$dblink=db_connect("docstorage");
$username="moi920";
$password="FY3bdCJV7WZXqc"; 
$data="username=$username&password=$password";
$ch=curl_init('https://cs4743.professorvaladez.com/api/create_session');//open up curl connection
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //turn into var and not output to terminal
curl_setopt($ch, CURLOPT_HTTPHEADER,array(      //sending length of content every time and content type
    'content-type: application/x-www-form-urlencoded',
    'content-length: ' . strlen($data))
);
$time_start = microtime(true);
$result = curl_exec($ch);
$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);  //turning results into array since data is outputed as JSON data

if ($cinfo[0]== "Status: OK" && $cinfo[1] == "MSG: Session Created"){ //if first array ok and second element is session created, means that there will be a session id in the third field
    
        $sid=$cinfo[2];
        $data="sid=$sid&uid=$username";
        echo "\r\nSession Created Successfully!\r\n";
        echo "SID: $sid\r\r";
        echo "Create Sesssion Execution Time: $execution_time\r\n"; 
        log_session($dblink, $username, $sid, "Session Created");
        //Logic for create query
        $ch=curl_init('https://cs4743.professorvaladez.com/api/query_files');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(      //sending length of content every time and content type
            'content-type: application/x-www-form-urlencoded',
            'content-length: ' . strlen($data))
        );
        $time_start = microtime(true);
        $result = curl_exec($ch);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60; 
        curl_close($ch);
        $cinfo=json_decode($result, true);
        if ($cinfo[0]=="Status: OK")
        {
             if ($cinfo[1]=="Action: None")
             {
                echo "\r\n No New Files to import found\r\n";
                //echo "Session Successfully closed!\r\n";
                echo "SID: $sid\r\n";
                echo "Username: $username\r\n";
                echo "Query Files Excution Time : $execution_time\r\n";
            }
            else 
            {
                    $tmp=explode(":", $cinfo[1]);
                    $files=explode(",", $tmp[1]);
                    echo "Number of new files to import found: ".count($files)."\r\n";
                    $message = "Number of new files to import: ".count($files);
                    log_session_message($dblink, $username, $sid, "Query Received", $message);                
                    echo "Files:\r\n";
                    foreach($files as $key=>$value)
                    {
                            $path=$value;
                            echo $value."\r\n";
                            $path_array=explode("/",$value); 
                            $file_name="";
                            if(count($path_array) == 5)
                            {
                                $file_name=$path_array[4]; 
                                $uploaded_by=$path_array[3];
                                $name_array=explode("-",$file_name);    
                                $data="sid=$sid&uid=$username&fid=$file_name";
                                $ch=curl_init('https://cs4743.professorvaladez.com/api/request_file');
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(     //sending length of content every time and content type
                                    'content-type: application/x-www-form-urlencoded',
                                    'content-length: ' . strlen($data))
                                );
                                $time_start = microtime(true);
                                $result = curl_exec($ch);
                                $time_end = microtime(true);
                                $execution_time = ($time_end - $time_start)/60; 
                                if ( $result == NULL)
                                {
                                    $message="Empty file recieved"; 
                                    $log_type="Query Received";
                                    log_session_error($dblink, $uploaded_by, $sid, $log_type ,$message);   
                                     
                                }
                                else
                                {
                                    $content=addslashes($result);  //to store in DB
                                    if (count($name_array) == 1)
                                    {
                                        $errorMessage="Loan number and Catagory missing"; 
                                        log_incomplete_file($dblink, $file_name, $errorMessage, NULL, $path, NULL, $content, $uploaded_by); 
                                       
                                    }
                                    else if(count($name_array) == 2)
                                    {
                                        //Loan Number or Catagory is missing and Log it
                                        $patternLoanNumber = "/([0-9]+-)/i";
                                        $patternCategory = "/([a-zA-Z]+-)/i";
                                        if(!(preg_match($patternLoanNumber, $file_name)))
                                        {
                                            $errorMessage="Loan Number Missing";
                                            $category=$name_array[0];
                                            log_incomplete_file($dblink, $file_name,$errorMessage, NULL ,$path, $category, $content, $uploaded_by); 

                                        }
                                        else if(!(preg_match($patternCategory, $file_name)))
                                        {
                                            $errorMessage="Loan Category Missing";
                                            $loan_number=$name_array[0];
                                            $category=NULL;
                                            log_incomplete_file($dblink, $file_name,$errorMessage, $loan_number,$path, NULL, $content, $uploaded_by); 
                                        }
                                    }
                                    else
                                    {
                                       
                                         $loan_number=$name_array[0];
                                         $category=$name_array[1];
                                         save_new_file($dblink, $uploaded_by, $file_name, $loan_number, $category, $content, $path ); 
                                         echo"\r\n  $file_name wriiten to DB \n";
                                               
                                    }  

                                }
                                
                               

                            }
                            else
                            {
                                $message="Unnamed File";
                                $log_type="Query Recieved";
                                log_session_error($dblink, $username, $sid, $log_type ,$message);
                                
                            }
                            
                          curl_close($ch);   
                            
                    }
                    echo "Query Files Execution Time :$execution_time\r\n";
                }  
           }
           else
           {
               
              
                echo $cinfo[0];   
                echo "\r\n";
                echo $cinfo[1];
                echo "\r\n";
                echo $cinfo[2];
                $message= $cinfo[0].", ".$cinfo[1].", ".$cinfo[2];
                $log_type="Create Query";
                log_session_error($dblink, $username, $sid, $log_type ,$message);               
           }
        
    
            $ch=curl_init('https://cs4743.professorvaladez.com/api/close_session');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(      //sending length of content every time and content type
                'content-type: application/x-www-form-urlencoded',
                'content-length: ' . strlen($data))
        );
        $time_start = microtime(true);
        $result = curl_exec($ch);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60; 
        curl_close($ch);
        $cinfo=json_decode($result, true);
    
    
        if ($cinfo[0]=="Status: OK")
        {
            echo "Session Successfully closed!\r\n";
            echo "SID: $sid\r\n";
            echo "Close Session execution time: $execution_time\r\n";
        }
        else
        {         
           
            echo $cinfo[0];     
            echo "\r\n";
            echo $cinfo[1];
            echo "\r\n";
            echo $cinfo[2];
            $message= $cinfo[0].", ".$cinfo[1].", ".$cinfo[2];
            $log_type="Close Session";
            log_session_error($dblink, $username,$sid, $log_type, $message);        
         
        }
    
}
else 
{    
            echo $cinfo[0];
            echo "\r\n";
            echo $cinfo[1];
            echo "\r\n";
            echo $cinfo[2];
            echo "\r\n";
            echo "\r\n";
            $log_type= "Create Session";
            $message= $cinfo[0].", ".$cinfo[1].", ".$cinfo[2]; 

            log_connection_error($dblink, $username, $log_type ,$message);
    
}

?>