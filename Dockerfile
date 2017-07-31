FROM chrisandchris/ap7

RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data

CMD service apache2 start \
    && tail -f /var/log/apache2/error.log
