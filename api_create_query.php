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
//status
//message 
//action
if ($cinfo[0]== "Status: OK" && $cinfo[1] == "MSG: Session Created"){ //if first array ok and second element is session created, means that there will be a session id in the third field
    
        $sid=$cinfo[2];
    //log the session created
        $data="sid=$sid&uid=$username";
        echo "\r\nSession Created Successfully!\r\n";
        echo "SID: $sid\r\r";
        echo "Create Sesssion Execution Time: $execution_time\r\n"; 
        log_session($dblink, $username, $sid, "Session Created");
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
            echo "Files:\r\n";
                foreach($files as $key=>$value)
                {
                    echo $value."\r\n";
                }
            echo "Query Files Execution Time :$execution_time\r\n";
        }
           $data="sid=$sid";
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
         
        }
    
}
else 
{    //an error had occured to view errorrr    
            echo $cinfo[0];
            echo "\r\n";
            echo $cinfo[1];
            echo "\r\n";
            echo $cinfo[2];
            echo "\r\n";
            echo "\r\n";
}
}
    
?>