server:
	php -S 0.0.0.0:4000 -t ./backend/public

test:
	backend/vendor/bin/phpunit ./backend/tests

remlogs:
	rm -fr backend/logs