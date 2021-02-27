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
    //jobs
    "cron.dbbackup"             => "App\\Services\\Cron\\DbBackupService",
    "cron.dbreplicator"         => "App\\Services\\Cron\\DbReplicatorService",

    "cron.cleaner.dump"         => "App\\Services\\Cron\\Cleaner\\DumpService",
    "cron.cleaner.repeated"     => "App\\Services\\Cron\\Cleaner\\RepeatedService",

    //commands
    "help"                      => "App\\Services\\Command\\HelpService",
        "h"                     => "App\\Services\\Command\\HelpService",
    "exclude-ip"                => "App\\Services\\Command\\ExcludeIpService",
    "clean-request"             => "App\\Services\\Command\\CleanRequestService",
    "check-dbconn"              => "App\\Services\\Command\\CheckDbConnService",
    "check-domain"              => "App\\Services\\Command\\CheckDomainService",
    "env"                       => "App\\Services\\Command\\EnvService",
    "email"                     => "App\\Services\\Command\\EmailService",
    "get-useragent"             => "App\\Services\\Command\\Ipblocker\\UseragentService",
    "get-bot"                   => "App\\Services\\Command\\Ipblocker\\BotService",
    "config"                    => "App\\Services\\Command\\ConfigService",
    "chalantest-fix"            => "App\\Services\\Command\\ChalantestFixService",
];