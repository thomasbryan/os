#include env_make
PORTS = -p 80:80
VOLUMES = -v $(DIR)/pub:/var/www/pub -v $(DIR)/src:/var/www/src
ENV = 

NS = thomasbryan
VERSION ?= latest

REPO = os
NAME = os
INSTANCE = default

DIR := ${CURDIR}

.PHONY: build push shell run start stop rm release

build:
	docker build -t $(NS)/$(REPO):$(VERSION) .

push:
	docker push $(NS)/$(REPO):$(VERSION)

bash:
	docker exec -i -t $(shell docker ps | grep $(NS)/$(REPO):$(VERSION)|awk '{print $$1}') /bin/bash

shell:
	docker run --rm --name $(NAME)-$(INSTANCE) -i -t $(PORTS) $(VOLUMES) $(ENV) $(NS)/$(REPO):$(VERSION) /bin/bash

run:
	docker run --rm --name $(NAME)-$(INSTANCE) $(PORTS) $(VOLUMES) $(ENV) $(NS)/$(REPO):$(VERSION)

start:
	docker run -d --name $(NAME)-$(INSTANCE) $(PORTS) $(VOLUMES) $(ENV) $(NS)/$(REPO):$(VERSION)

stop:
	docker stop $(NAME)-$(INSTANCE)

rm:
	docker rm $(NAME)-$(INSTANCE)

release: build
	make push -e VERSION=$(VERSION)

default: build
