<?php

$dbConfig = array(
    "connections" => array(
        "main"=>array(
            "server"=>"162.214.30.76:3306",
            "user"=>"beesens_local",
            "password"=>"Marc_1201",
            "schema"=>"beesens_db"
        )
    )
);

$redisConfig = array(
    "connections"=> array(
        "main"=>array(
            "scheme"=>"tcp",
            "host"=>"127.0.0.1",
            "port"=>"6379",
            "persistent"=>"1"
        )
    )
);

$devConfig = array(
    "development"=>true,
    "devdir"=>"voltor"
);

$appConfig = array(

);
?>
