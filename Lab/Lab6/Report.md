# Report 6

## 1

![1](/screenshots/01-fcgiwrap.png)

## 2

![2](/screenshots/02-test-cgi.png)

## 3

![3](/screenshots/03-nginx-cgi.png)

- `fastcgi_pass` - директива, которая говорит nginx, куда отправлять запросы из этого location. В нашем случае на unix-сокет, из которого уже будет читать процесс fcgiwrap
- `include fastcgi_params`- то, какие переменные окружения будут переданы в бэкенд
- `fastcgi_param SCRIPT_FILENAME /var/www/boardy$fastcgi_script_name` - в fcgiwrap будет передаваться полный путь к скрипту. `$fastcgi_script_name` - uri, про который знает nginx. `/var/www/boardy$fastcgi_script_name` - полный путь к скрипту для fcgiwrap

## 4

![4](/screenshots/04-curl-submit.png)

## 5

![5](/screenshots/05-form-submit.png)

## 6

![6](/screenshots/06-messages-file.png)

## 7

![7](/screenshots/07-messages-page.png)

## 8

![8](/screenshots/08-full-cycle.png)

## 9

```mermaid
sequenceDiagram
    participant Browser
    participant TLS as HTTPS/TLS
    participant Nginx
    participant FCGI as fcgiwrap
    participant Script as submit.sh
    participant File as messages.txt

    Browser->>Browser: Пользователь заполнил форму, нажал "Отправить"
    Browser->>TLS: Формирование HTTPS-запроса (POST /submit)
    TLS->>Nginx: Расшифровка и передача HTTP-запроса
    Nginx->>FCGI: fastcgi_pass: POST /submit как FastCGI-запрос
    FCGI->>Script: Запуск submit.sh, передача данных в stdin
    Script->>File: Парсинг stdin и запись в messages.txt
    Script->>FCGI: Ответ в stdout ("Спасибо!" в HTML)
    FCGI->>Nginx: Возврат ответа FastCGI
    Nginx->>TLS: Обёртка ответа в HTTPS
    TLS->>Browser: Ответ браузеру ("Спасибо!")
```

## 10

1. CGI - Common Gateway Interface, решил проблему динамичных страниц, ранее были только статичные
2. Через stdin
3. Потому что на каждый запрос поднимается процесс, а это дорого по ресурсам и времени
4. fastcgi_pass - мы передаём данные через специальный FastCGI протокол так сказать, а при proxy_pass мы просто передаёт всё, что получили на другой порт/сервер.
5. Потому что Apache не подходит для сильно-нагруженных серверов, а Nginx - да, вот только Nginx не умеет запускать CGI напрямую, поэтому нужен fcgiwrap
