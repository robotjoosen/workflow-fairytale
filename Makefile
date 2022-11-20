.PHONY: init
init:
	composer install \
	&& bin/console doc:dat:cre \
	&& bin/console doc:mig:mig -n \
	&& bin/console doc:fix:loa -n
