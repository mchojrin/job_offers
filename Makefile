build:
	docker build docker -t job_offers

install: build
	docker run -v $(shell pwd):/app job_offers composer install --no-dev

run:
	docker run -v $(shell pwd):/app:rw -it job_offers php run.php $(args)

