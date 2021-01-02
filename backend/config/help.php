<?php
return [
    "help"     => [
        "description"   => "
        Muestra el menu de ayuda con los comandos
        parámetros: 
            all:        muestra crons y projectos
            projects:   muestra solo projectos
        ",
    ],

    "cron.dbbackup"             => [
        "description"   => "Realiza backup de las bases de datos",
        //"params"        => [""=>""],
    ],

    "cron.dbreplicator"         => [
        "description"   => "Hace copias en una bd secundaria normalmente ro",
        //"params"        => [""=>""],
    ],

    "cron.cleaner.dump"         => [
        "description"   => "Limpia los backups más antiguos",
        //"params"        => [""=>""],
    ],

    "cron.cleaner.repeated"     => [
        "description"   => "Limpia los backups más antiguos",
        //"params"        => [""=>""],
    ],

    "exclude-ip"     => [
        "description"   => "Agrega la ip para evitar ser registrada en las peticiones y que se salte analytics",
        //"alias"         => ["h"]
        "params"        => ["ip"=>"segundo parametro"],
        "examples"      => ["exclude-ip <ip>"]
    ],

    "clean-request"     => [
        "description"   => "Limpia ipblocker de las peticiones de una ip y peticiones icon",
        "params"        => ["ip"=>"segundo parametro"],
        "examples"      => ["clean-request <ip>"]
    ],

    "check-dbconn"      => [
        "description"   => "Comprueba las conexiones a la bd configuradas en projects.php",
        //"params"        => ["ip"=>"segundo parametro"],
        "examples"      => ["check-dbconn"]
    ],

    "check-domain"      => [
        "description"   => "Comprueba las respuestas por curl de domains.php",
        "examples"      => ["check-domain"]
    ],

    "env"                       => "App\\Services\\Command\\EnvService",
    "email"                     => "App\\Services\\Command\\EmailService",
    "get-useragent"             => "App\\Services\\Command\\Ipblocker\\UseragentService",

    //params: all,
    "get-bot"                   => "App\\Services\\Command\\Ipblocker\\BotService",
];