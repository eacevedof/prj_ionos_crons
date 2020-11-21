server:
	php -S 0.0.0.0:4000 -t ./backend/public

test:
	backend/vendor/bin/phpunit ./backend/tests

remlogs:
	rm -fr backend/logs/*

showservices:
	clear
	cat backend/config/services.php

showprojects:
	clear
	cat backend/config/projects.php

cron-dbbackup:
	clear
	php	backend/public/index.php service=cron.dbbackup

cron-cleanerdump:
	clear
	php	backend/public/index.php service=cron.cleaner.dump

cron-cleanerrepeated:
	clear
	php	backend/public/index.php service=cron.cleaner.repeated

help:
	clear
	cat makefile
