# Report 4

![1](/screenshots/01-directory.png)

---

![2](/screenshots/02-vhost-config.png)

- listen 80: слушать 80-й порт
- server_name armontex.ai-info.ru: для домена `armontex.ai-info.ru`
- root /var/www/boardy: в какой папке искать файлы
- access_log /var/log/nginx/boardy-access.log: логи для сайта
- error_log /var/log/nginx/boardy-error.log: error-логи для сайта
- try_files \$uri \$uri/ =404: Ищет файл, если не нашёл, то 404
- error_page 404 /404.html: ошибка и её файл

---

![3](/screenshots/03-landing.png)

---

![4](/screenshots/04-form.png)

---

![5](/screenshots/05-404.png)

---

![6](/screenshots/06-dns-api.png)

TTL - не виден, но он 600 стоит. К сожалению, самому менять нельзя, но я уже написал в поддержку, чтобы поменяли.

---

![7](/screenshots/07-dig-api.png)

---

![8](/screenshots/08-api-config.png)

---

![9](/screenshots/09-api-browser.png)

---

Скриншот всё не вмещает, поэтому вот:

```text
* Host armontex.ai-info.ru:80 was resolved.
* IPv6: (none)
* IPv4: 155.212.223.155
*   Trying 155.212.223.155:80...
* Connected to armontex.ai-info.ru (155.212.223.155) port 80
> GET / HTTP/1.1
> Host: armontex.ai-info.ru
> User-Agent: curl/8.5.0
> Accept: */*
>
< HTTP/1.1 200 OK
< Server: nginx/1.18.0 (Ubuntu)
< Date: Wed, 11 Mar 2026 23:42:03 GMT
< Content-Type: text/html
< Content-Length: 1021
< Last-Modified: Wed, 11 Mar 2026 23:19:12 GMT
< Connection: keep-alive
< ETag: "69b1f870-3fd"
< Accept-Ranges: bytes
<
<!doctype html>

<html lang="ru">
  <head>
    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Boardy</title>

    <link rel="stylesheet" href="/css/style.css" />
  </head>

  <body>
    <header>
      <h1>Boardy</h1>

      <p>Микро-доска объявлений</p>
    </header>

    <main>
      <section>
        <h2>О проекте</h2>

        <p>Boardy — учебный проект курса «Архитектура веб-приложений».</p>

        <p>
          Публикуйте посты, комментируйте, получайте уведомления в реальном
          времени.
        </p>
      </section>

      <section>
        <h2>Обратная связь</h2>

        <p><a href="/feedback.html">Написать сообщение</a></p>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 Boardy | Махонько(Armontex)</p>
    </footer>
  </body>
</html>
```

Стартовая строка: - Метод: `GET` - Путь: `/` - Версия: `HTTP/1.1`

Заголовок Host: `Host: armontex.ai-info.ru`

Стартовая строка ответа: - Код: `200` - Пояснение: `OK`

Content-Type: `text/html`
Content-Length: `1021`

---

Скринов так же не будет, ибо не влезают

## curl -H "Host: armontex.ai-info.ru"

```bash
curl -H "Host: armontex.ai-info.ru" http://155.212.223.155/
```

### Вывод 1

```html
<!doctype html>

<html lang="ru">
  <head>
    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Boardy</title>

    <link rel="stylesheet" href="/css/style.css" />
  </head>

  <body>
    <header>
      <h1>Boardy</h1>

      <p>Микро-доска объявлений</p>
    </header>

    <main>
      <section>
        <h2>О проекте</h2>

        <p>Boardy — учебный проект курса «Архитектура веб-приложений».</p>

        <p>
          Публикуйте посты, комментируйте, получайте уведомления в реальном
          времени.
        </p>
      </section>

      <section>
        <h2>Обратная связь</h2>

        <p><a href="/feedback.html">Написать сообщение</a></p>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 Boardy | Махонько(Armontex)</p>
    </footer>
  </body>
</html>
```

## curl -H "Host: api.armontex.ai-info.ru"

```bash
curl -H "Host: api.armontex.ai-info.ru" http://155.212.223.155/
```

### Вывод 2

```html
<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="utf-8" />

    <title>Boardy API</title>

    <style>
      body {
        font-family: monospace;
        text-align: center;
        padding: 60px;
        color: #333;
      }

      h1 {
        color: #27ae60;
        font-size: 36px;
      }

      p {
        margin-top: 10px;
        font-size: 18px;
      }
    </style>
  </head>

  <body>
    <h1>Boardy API</h1>

    <p style="color: #27ae60;">Service: OK</p>

    <p>REST API + WebSocket — coming soon</p>

    <p>Powered by FastAPI + Uvicorn</p>
  </body>
</html>
```

## curl -H "Host: unknown.ru"

```bash
curl -H "Host: unknown.ru" http://155.212.223.155
```

### Вывод 3

```html
<!doctype html>

<html lang="ru">
  <head>
    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Boardy</title>

    <link rel="stylesheet" href="/css/style.css" />
  </head>

  <body>
    <header>
      <h1>Boardy</h1>

      <p>Микро-доска объявлений</p>
    </header>

    <main>
      <section>
        <h2>О проекте</h2>

        <p>Boardy — учебный проект курса «Архитектура веб-приложений».</p>

        <p>
          Публикуйте посты, комментируйте, получайте уведомления в реальном
          времени.
        </p>
      </section>

      <section>
        <h2>Обратная связь</h2>

        <p><a href="/feedback.html">Написать сообщение</a></p>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 Boardy | Махонько(Armontex)</p>
    </footer>
  </body>
</html>

```

### Ответы на вопросы

1) Почему один IP возвращает разные страницы?

> Потому что разный `Host`

2) Что произошло с третьим запросом и почему?

> Третий запрос привёл к `armontex.ai-info`.ru, ибо Nginx не нашёл server_name `unknown.ru`, а дефолтного сервера нет, то просто выбрал первый в списке и это оказался boardy

---

Исправил форму (action на `/`), поэтому запрос выглядит так:

```bash
curl -v -X POST -d "name=Ivanov&message=Hello" http://фамилия.ai-info.ru/submit
```

![12](/screenshots/12-post-405.png)

Ответ на вопрос: Потому что Nginx не обрабатывает данные.

---

Чем отличается ответ на HEAD от GET?

> Запрос на ответ без тела.

Зачем нужен HEAD?

> Для быстрой проверки: существует ли, какой размер и тип

---

![13](/screenshots/13-logs.png)

188.68.80.8 GET / 200 "curl/8.5.0"

---

![14](/screenshots/14-log-stats.png)
