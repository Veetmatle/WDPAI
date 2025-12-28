FROM postgres:15

# ENV POSTGRES_USER=docker
# ENV POSTGRES_PASSWORD=docker
# ENV POSTGRES_DB=db

COPY docker/db/init.sql /docker-entrypoint-initdb.d/

EXPOSE 5432

# PODSUMOWANIE:
# 1. docker compose up -d --build, Docker czyta ten plik i wykonuje:
# 2. Pobiera oficjalny obraz Postgresa w wersji 15
# 3. Ustawia zmienne środowiskowe dla użytkownika, hasła i nazwy bazy danych
# 4. Kopiuje plik init.sql do katalogu inicjalizacyjnego Postgresa, który zostanie wykonany przy pierwszym uruchomieniu kontenera
# 5. Zamyka to w obrazie i uruchamia jako kontener
