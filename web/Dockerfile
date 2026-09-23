FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite headers autoindex
RUN printf "upload_max_filesize=5M\npost_max_size=6M\n" > /usr/local/etc/php/conf.d/interafas.ini
RUN printf '%s\n' \
'<Directory /var/www/html/assets/docs/public>' \
'    Options +Indexes' \
'    IndexOptions FancyIndexing HTMLTable NameWidth=* DescriptionWidth=* SuppressHTMLPreamble' \
'    Require all granted' \
'</Directory>' \
> /etc/apache2/conf-available/interafas-public-docs.conf \
&& a2enconf interafas-public-docs
WORKDIR /var/www/html
