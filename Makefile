all:
	@./build.sh
build:
	@./build.sh all
clean:
	rm -rf spiral
install: all
	cp spiral /usr/local/bin/spiral
uninstall:
	rm -f /usr/local/bin/spiral
test:
	composer update
	vendor/bin/phpunit