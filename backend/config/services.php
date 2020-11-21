<?php
/*
mapping <command> = <namespace-class>

20 11 * * * /usr/bin/php7.4 <path-this>/crons/backend/public/index.php service=cron.dbbackupservice
20 11 * * * /usr/bin/php7.4 <path-this>/crons/backend/public/index.php service=cron.dumpscleaner

*/
return [
    "cron.dbbackup"             => "App\\Services\\Cron\\DbbackupService",
    "cron.cleaner.dump"         => "App\\Services\\Cron\\Cleaner\\DumpService",
    "cron.cleaner.repeated"     => "App\\Services\\Cron\\Cleaner\\RepeatedService",
];