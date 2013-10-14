<?php

    $string = file_get_contents("config.json");//get the file
    $data = json_decode($string, true);//get the variables
    $host=$data[0]["host"];//get host
    $user=$data[0]["user"];//get user
    $password=$data[0]["password"];//get password
    $db=$data[0]["db"];//get db
    /*
     * the last variables were all the argumets 
     * that we need to connect the database 
     */

   // echo $host.$user.$password.$db;
?>
