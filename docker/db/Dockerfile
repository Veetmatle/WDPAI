FROM postgres:15

ENV POSTGRES_USER=docker
ENV POSTGRES_PASSWORD=docker
ENV POSTGRES_DB=db

COPY docker/db/init.sql /docker-entrypoint-initdb.d/

EXPOSE 5432