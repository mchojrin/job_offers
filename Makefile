build:
	docker build . -t job_offers

install: build
	docker run -v $(shell pwd):/app job_offers composer install