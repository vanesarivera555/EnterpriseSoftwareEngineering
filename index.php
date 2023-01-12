<?php
echo "<p>Hello world, I am PHP!</p>";
echo "<p>This is a paragraphhh</p>";
$hostname="localhost";
$username="webuser";
$password="OGB_yHm[H/)W_9CK";
$db="temp";
$mysqli=new mysqli($hostname,$username,$password,$db);
if (mysqli_connect_errno())
{
 die("Error connecting to databse: ".mysqli_connect_error());
}
/*$sql="Select * from `user_input` where 1";
$result=$mysqli->query($sql) or
    die("Something went wrong with $sql".$mysqli->error);
[/{
    echo "<p>Entry $data[auto_id]: $data[input] - $data[user_id]</p>";
    
}

*/
$sql="insert into `user_input` (`input`,`user_id`) values ('input from web','webuser@mail.com')";

$mysqli->query($sql) or
    die("Something went wrong with $sql ".$mysqli->error);
echo "<p>Executed $sql</p>";
?>