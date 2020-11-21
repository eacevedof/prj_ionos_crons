server:
	php -S 0.0.0.0:4000 -t ./backend/public

test:
	backend/vendor/bin/phpunit ./backend/tests

remlogs:
	rm -fr backend/logs/*

showservices:
	cat backend/config/services.php

showprojects:
	cat backend/config/projects.php