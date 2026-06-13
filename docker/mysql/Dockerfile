FROM mysql:8.0

ARG MYSQL_DATABASE
ARG MYSQL_USER
ARG MYSQL_PASSWORD
ARG MYSQL_ROOT_PASSWORD

ENV MYSQL_DATABASE=${MYSQL_DATABASE} \
  MYSQL_USER=${MYSQL_USER} \
  MYSQL_PASSWORD=${MYSQL_PASSWORD} \
  MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}

COPY docker/mysql/my.cnf /etc/mysql/conf.d/my.cnf

# Fix permissions
RUN chmod 755 /etc/mysql/conf.d/my.cnf && \
    mkdir -p /var/lib/mysql && \
    chown -R mysql:mysql /var/lib/mysql && \
    chmod -R 755 /var/lib/mysql

RUN mkdir -p /var/log/mysql && \
    touch /var/log/mysql/error.log && \
    chown -R mysql:mysql /var/log/mysql

HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD mysqladmin ping -h localhost -u root -p"$MYSQL_ROOT_PASSWORD"