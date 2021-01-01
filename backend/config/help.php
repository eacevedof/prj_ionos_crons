<?php
return [
    //jobs
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

    "help"     => [
        "description"   => "Muestra el menu de ayuda",
        "alias"         => ["h"],
        //"params"        => [""=>""],
        "examples"       => ["help","h"]
    ],

    "exclude-ip"     => [
        "description"   => "Agrega la ip para evitar ser registrada en las peticiones y que se salte analytics",
        //"alias"         => ["h"]
        "params"        => ["ip"=>"segundo parametro"],
        "examples"       => ["exclude-ip <ip>"]
    ],

    "clean-request"             => "App\\Services\\Command\\CleanRequestService",
    "check-dbconn"              => "App\\Services\\Command\\CheckDbConnService",
    "check-domain"              => "App\\Services\\Command\\CheckDomainService",
    "env"                       => "App\\Services\\Command\\EnvService",
    "email"                     => "App\\Services\\Command\\EmailService",
    "get-useragent"             => "App\\Services\\Command\\Ipblocker\\UseragentService",

    //params: all,
    "get-bot"                   => "App\\Services\\Command\\Ipblocker\\BotService",
];