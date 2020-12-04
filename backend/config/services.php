<?php
/*
20 11 * * * /usr/bin/php7.4 <path-this>/crons/backend/public/index.php service=cron.dbbackupservice
20 11 * * * /usr/bin/php7.4 <path-this>/crons/backend/public/index.php service=cron.dumpscleaner

php	backend/public/index.php service=<command-alias>
./cmd <command-alias> <parameters>

mapping:
    <command-alias> => <namespace-class>
*/
return [
    "cron.dbbackup"             => "App\\Services\\Cron\\DbBackupService",
    "cron.dbreplicator"         => "App\\Services\\Cron\\DbReplicatorService",

    "cron.cleaner.dump"         => "App\\Services\\Cron\\Cleaner\\DumpService",
    "cron.cleaner.repeated"     => "App\\Services\\Cron\\Cleaner\\RepeatedService",

    "help"                      => "App\\Services\\Command\\HelpService",
        "h"                     => "App\\Services\\Command\\HelpService",
    "excludeip"                 => "App\\Services\\Command\\ExcludeIpService",
    "cleanrequest"              => "App\\Services\\Command\\CleanRequestService",
    "check-dbconn"              => "App\\Services\\Command\\CheckDbConnService",
    "check-domain"              => "App\\Services\\Command\\CheckDomainService",
    "env"                       => "App\\Services\\Command\\EnvService",
    "email"                     => "App\\Services\\Command\\EmailService",
];