# Report 5

## 1

![1](/screenshots/01-certbot-installed.png)

---

## 2

![2](/screenshots/02-certbot-success.png)

Примечание: _Ранее уже установил сертификат, для скриншота ещё раз ввёл команду_

---

## 3

![3](/screenshots/03-browser-lock.png)

![4](/screenshots/04-certificate-info.png)

---

## 4

![5](/screenshots/05-redirect.png)

Код: `301`
Location: `https://armontex.ai-info.ru/`

---

## 5

![6](/screenshots/06-nginx-ssl-config.png)

`listen 443 ssl; # managed by Certbot`

`ssl_certificate /etc/letsencrypt/live/armontex.ai-info.ru/fullchain.pem; # managed by Certbot`

`ssl_certificate_key /etc/letsencrypt/live/armontex.ai-info.ru/privkey.pem; # managed by Certbot`

`include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot`

`ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot`

---

## 6

![7](/screenshots/07-api-certbot.png)

---

## 7

![8](/screenshots/08-both-https.png)

---

## 8

![9](/screenshots/09-tls-handshake.png)

Примечание: _Тут ничего не видно, кроме версии TLS, поэтому остальные данные просто взял из `curl -v https://armontex.ai-info.ru`,_

Версия TLS: `TLSv1.3`
Алгоритм шифрования: `TLS_AES_256_GCM_SHA384`
Subject: `CN=armontex.ai-info.ru`
Issuer: `C=US; O=Let's Encrypt; CN=R13`
Срок действия: `expire date: Jun 16 20:37:09 2026 GMT`

---

## 9

![10](/screenshots/10-chain.png)

`armontex.ai-info.ru => Let's Encrypt R13 (промежуточный) => ISRG Root X1 (коревой CA)`

---

## 10

![11](/screenshots/11-compare-certs.png)

Отличаются `subject`, ну и немного даты, ибо в разное время сертификаты были созданы

---

## 11

![12](/screenshots/12-hsts.png)

`HTST` - такой механизм безопасности, который сообщает браузеру (через заголовок Strict-Transfer-Security), что подключаться нужно только по HTTPS протоколу. Предотвращает от атак по понижению протокола.

---

## 12

![13](/screenshots/13-cache-gzip.png)

---

## 13

![14](/screenshots/14-renew.png)
