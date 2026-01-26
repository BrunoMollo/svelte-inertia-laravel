FROM ubuntu:24.04

LABEL maintainer="Bruno"

ARG WWWGROUP=1000
ARG NODE_VERSION=22

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# PHP 8.4, Node, and base dependencies
RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 \
       libcap2-bin libpng-dev dnsutils nano \
    # PHP PPA
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.4-cli php8.4-dev php8.4-sqlite3 php8.4-gd \
       php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip php8.4-bcmath \
       php8.4-intl php8.4-readline php8.4-redis \
    # Composer
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    # Node.js
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g pnpm \
    # ========== OCR DEPENDENCIES ==========
    && apt-get install -y python3 python3-pip python3-venv \
       tesseract-ocr tesseract-ocr-spa tesseract-ocr-eng \
       poppler-utils \
    # Cleanup
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Create sail user
RUN groupadd --force -g $WWWGROUP sail \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# Create virtualenv for OCR
RUN python3 -m venv /var/www/html/venv \
    && /var/www/html/venv/bin/pip install --upgrade pip \
    && /var/www/html/venv/bin/pip install pytesseract Pillow pdf2image

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
