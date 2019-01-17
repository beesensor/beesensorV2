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
    "devdir"=>"beesensorV2"
);

$appConfig = array(
    "nomApp"=>"Beesensor intranet",
    "menuNomApp"=>"Bee<b>sensor</b>",
    "menuNomAppS"=>"B<b>s</b>",
    "appDesc"=>"Intranet de Beesensor",
    "author"=>"Miquel &Agrave;ngel Mayol",
    "OMToken"=>"pk.eyJ1IjoiYmVlc2Vuc29yIiwiYSI6ImNqcDFoZjB6YTM1NXgzbW5yMnhtb2g1OWgifQ.EM1XQcft-9ota29_33Flyw"
);
?>
