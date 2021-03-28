<?php
return [
    "help"     => [
        "description"   => "
        Muestra el menu de ayuda con los comandos
        parámetros: 
            crons: muestra solo los crons, all:  muestra crons y proyectos, projects: muestra solo proyectos
        ",
    ],

    "cron.dbbackup"             => [
        "description"   => "
        Realiza backup de las bases de datos
        ",
    ],

    "cron.codebackup"             => [
        "description"   => "
        Realiza backup de las rutas configuradas
        parámetros:
            c: key en config/code.php
        ",
    ],

    "cron.dbreplicator"         => [
        "description"   => "
        Hace copias en una bd secundaria normalmente ro
        ",
    ],

    "cron.cleaner.dump"         => [
        "description"   => "
        Limpia los backups más antiguos dejando los 15 últimos
        ",
    ],

    "cron.cleaner.repeated"     => [
        "description"   => "
        Limpia los backups repetidos dejando el más antiguo
        ",
    ],

    "exclude-ip"     => [
        "description"   => "
        Agrega la ip para evitar ser registrada en las peticiones y que se salte analytics
        parámetros: ip
        ejemplo:
            exclude-ip 123.4.5.6
        ",
    ],

    "clean-request"     => [
        "description"   => "
        Limpia ipblocker de las peticiones de una ip y peticiones icon
        parámetros: ip
        ejemplo:
            clean-request 123.4.5.6
        ",
    ],

    "check-dbconn"      => [
        "description"   => "
        Comprueba las conexiones a la bd configuradas en projects.php
        ",
    ],

    "check-domain"      => [
        "description"   => "
        Comprueba las respuestas por curl de domains.php
        ",
    ],

    "env"               => [
        "description"   => "
        Imprime por pantalla las variables de entorno relacionadas con este proyecto
        "
    ],
    "email"                     => [
        "description"   => "
        Realiza un envío de email
        parámetros:
            s[string opt]: subject, c [string opt]: content, p [string opt]: ruta al adjunto
        ejemplo:
            email s='hola' c='este es el cuertpo <b>:)</b>' p='/some/path/to/file.csv'    
        "
    ],
    "get-useragent"     => [
        "description"   => "
        Imprime por pantalla todas las cabeceras user-agent enviadas por una ip determinada
        parámetros: ip
        ejemplo:
            get-useragent 123.4.5.6
        "
    ],

    "get-bot"       => [
        "description"   => "
        Imprime por pantalla todas las ips que han enviado cabeceras tipo bot
        parámetros: 
            all: todos los bots, top: los últimos 15, name: solo los nombres 
        ejemplo:
            get-bot all
            get-bot top
            get-bot name
        "
    ],

    "config" => [
        "description"   => "
        Imprime por pantalla el contenido de los ficheros en config
        parámetros:
            domains, emails, logs, projects, services[default]
        "
    ],

    "chalantest-fix" => [
        "description"   => "
            Actualiza la bd de El Chalan Test con el dominio de prueba
        "
    ],
];