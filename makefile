phpexe=php
OS := $(shell uname)
ifeq ($(OS),Darwin)
	phpexe=php
else ifeq ($(OS),Linux)
	phpexe=/usr/bin/php7.4-cli
else

endif

server:
	${phpexe} -S 0.0.0.0:4000 -t ./backend/public

test:
	${php} backend/vendor/bin/phpunit backend/tests

remlogs:
	rm -fr backend/logs/*

show-services:
	clear
	cat backend/config/services.php

show-projects:
	clear
	cat backend/config/projects.php

cron-dbbackup:
	clear
	${php} backend/public/index.php service=cron.dbbackup

cron-cleanerdump:
	clear
	${php} backend/public/index.php service=cron.cleaner.dump

cron-cleanerrepeated:
	clear
	${php} backend/public/index.php service=cron.cleaner.repeated

cmd-excludeip:
	clear
	${php} backend/public/index.php excludeip 127.1.1.8

cmd-check-dbconn:
	clear
	${php} backend/public/index.php check-dbconn

help:
	clear
	cat makefile
