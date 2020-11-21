<?php
//mapping namespace=command
return [
    "cron.dbbackup" => "App\\Services\\Cron\\DbbackupService",
    "cron.dumpscleaner" => "App\\Services\\Cron\\DumpscleanerService",
    "cron.repeatedcleaner" => "App\\Services\\Cron\\DbbackupRepeatedService",
];