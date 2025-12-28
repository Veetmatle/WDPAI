# Wersja jako system bazowy - nginx w wersji 1.25 na alpine linux (lekka wersja)
FROM nginx:1.25-alpine

# kopiuje z kompa do kontenera plik kongfiguracyjny 
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# kopiuje kod aplikacji do kontenera w katalog /app
COPY . /app

# info jaki port jest otwarty do nasłuchiwania
EXPOSE 80



# PODSUMOWANIE:
# 1. docker compose up -d --build, Docker czyta ten plik i wykonuje:
# 2. Pobiera lekkiego Linuxa (Alpine).
# 3. Wrzuca do niego plik konfiguracyjny (podmieniając stary).
# 4. Wrzuca do niego cały kod PHP/HTML
# 5. Zamyka to w obraze i uruchamia jako kontener
# 6. Nginx w kontenerze nasłuchuje na porcie 80 i obsługuje żądania HTTP zgodnie z konfiguracją w nginx.conf
# 7. Nginx obsługuje statyczne pliki bezpośrednio, a dynamiczne przekazuje do kontenera PHP-FPM
# 8. Kontener PHP-FPM przetwarza kod PHP i zwraca wynik do Nginx, który odsyła go do klienta
