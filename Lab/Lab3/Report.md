# Report

## Часть A

### 1

![1](/screenshots/01-nginx-status.png)

---

### 2

![2](/screenshots/02-browser-ip.png)

---

### 3

![3](/screenshots/03-curl.png)

GET / HTTP/1.1

HTTP/1.1 200 OK

Content-Type: text/html

---

### 4

Скриншота "До" - нет. Делал на практике.

![4](/screenshots/04-permissions.png)

---

### 5

`listen 80 default_server`: - `listen 80` - слушает порт 80 на всех IPv4-адресах. - `default_server` - делает этот сервер дефолтным для этого порта. Если ни один сервер c `listen 80` не подошёл по IP/порту/server_name, возьмут этот

`listen [::]:80 default_server` - тоже самое, только по IPv6

`root /var/www/html` - корневая папка сайта.

`server_name _` - список доменом, для которых срабатывает этот сервер-блок. `_` - заглушка, т.е. любой домен ведёт в этот сервер-блок на 80-м порту.

`index index.html index.htm index.nginx-debian.html` - это список файлов, которые nginx попытается отдать при заходе в `/`. Nginx берёт первый существующий.

---

## Часть B

### 6

![5](/screenshots/05-dns-zone.png)

---

### 7

У Beget, к сожалению нельзя менять TTL, но он по-умолчанию 300 стоит.

![TTL](/screenshots/ttl1.png)

![6](/screenshots/06-a-record.png)

---

### 8

![7](/screenshots/07-ping.png)

---

### 9

![8](/screenshots/08-dig.png)

`QUESTION SECTION`: `armontex.ai-info.ru.		IN	A` (спросили A-запись у `armontex.ai-info.ru`)

`ANSWER SECTION`: IP: `155.212.223.155` | TTL: `300` секунд.

`SERVER`: `8.8.8.8#53(8.8.8.8) (UDP)` (dns сервер гугла, который ответил на запрос, ip: 8.8.8.8, 53 - порт. UPD - протокол)

---

### 10

![9](/screenshots/09-dig-trace.png)

#### 1) корень (.)

```plaintext
.			437567	IN	NS	i.root-servers.net.
.			437567	IN	NS	b.root-servers.net.
.			437567	IN	NS	d.root-servers.net.
.			437567	IN	NS	l.root-servers.net.
.			437567	IN	NS	a.root-servers.net.
.			437567	IN	NS	f.root-servers.net.
.			437567	IN	NS	g.root-servers.net.
.			437567	IN	NS	m.root-servers.net.
.			437567	IN	NS	k.root-servers.net.
.			437567	IN	NS	c.root-servers.net.
.			437567	IN	NS	h.root-servers.net.
.			437567	IN	NS	e.root-servers.net.
.			437567	IN	NS	j.root-servers.net.
;; Received 503 bytes from 127.0.0.53#53(127.0.0.53) in 11 ms
```

#### 2) .ru

```plaintext
ru.			172800	IN	NS	a.dns.ripn.net.
ru.			172800	IN	NS	b.dns.ripn.net.
ru.			172800	IN	NS	d.dns.ripn.net.
ru.			172800	IN	NS	e.dns.ripn.net.
ru.			172800	IN	NS	f.dns.ripn.net.
ru.			86400	IN	DS	51575 8 2 34CF735353060D9BD6347FF81ECFAAC24EC8F11971DC800249C64A21 BC062775
ru.			86400	IN	RRSIG	DS 8 1 86400 20260314050000 20260301040000 21831 . To3GdpZ5/443hilEBEABpstDE6oBcivJ6tMXGbRPJDieFmn4+9qDCR8e OioIewiBsjkW/76SUHi6YFpTRmZQlFH5fuq2QZc/+Y2MRakra+jyiy5u Et7lJZWbwqtWmzMdCAdSRpoi/Bj7MqnVuYCgOk/QD2B+cW0cn77Oj0Ua kmXk7XAHjhR7nzCYgbCZs7XYKY378rLWA7WDoATsWRC2Q4XRPFSGG90r tGyfL/f1OnB24Emtx7nxa8rsqdsjV3gb5qop0K1aaSzcZWccMr+Bm0qP JAs/LH2UgWzifz3qi4H755RYB/o+eHOSU0C6zYuaGf/FG0/NNg4PiQzO wd7ouA==
;; Received 695 bytes from 193.0.14.129#53(k.root-servers.net) in 79 ms
```

#### 3) NS-серверы ai-info.ru

```plaintext
ai-info.ru.		345600	IN	NS	ns1.netangels.ru.
ai-info.ru.		345600	IN	NS	ns2.netangels.ru.
ai-info.ru.		345600	IN	NS	ns3.netangels.ru.
ai-info.ru.		345600	IN	NS	ns4.netangels.ru.
j20c0qkdhua3cumnkst289ff06u2sq91.ru. 3600 IN NSEC3 1 1 0 - J21LULR2UNPA28SERE28OVNJNJ67QP7V NS SOA RRSIG DNSKEY NSEC3PARAM
j20c0qkdhua3cumnkst289ff06u2sq91.ru. 3600 IN RRSIG NSEC3 8 2 3600 20260410191518 20260225021631 36654 ru. LP1+4WeyEaAaglM1hhsfoxgb7za7Zm87i6kQa1o8YInsNEHaVNo+Zjga k2sHgkxp6+dKD/NyMGKIYNjEfhZG1b6Tp7bY3aOiVqkrFEn/I1j+9hf5 oRbyMqOHETN2mazvNdyRLcA4bv3JK391d08MLHewgA2GRkssM7JNSseJ k1o=
3k42dh53eonu12hlkfsr1goo63cn8nju.ru. 3600 IN NSEC3 1 1 0 - 3KEMMSDPGL7PUUE4J88NLF5AKQDLLH8V NS DS RRSIG
3k42dh53eonu12hlkfsr1goo63cn8nju.ru. 3600 IN RRSIG NSEC3 8 2 3600 20260326103851 20260221101630 36654 ru. RlQHE+wWvMZZNqFrVm+5Yo2syDBitcjLLsWiOwW0jOQJcRc2QG7vQta+ QNMhp3puvbjH0Ofl6tnaUw0nlSgsOlPpgY60fdC3Bt7nmuyMJkTDsC7l lQEr+UwuQCpM0iFziksVL8DC1pEA//myuGUDlPSl5wxiXGdNPEVvYnub KO8=
;; Received 677 bytes from 194.190.124.17#53(d.dns.ripn.net) in 30 ms

armontex.ai-info.ru.	3600	IN	NS	ns1.beget.com.
;; Received 106 bytes from 45.86.39.2#53(ns3.netangels.ru) in 9 ms
```

#### 4) A-запись

```plaintext
armontex.ai-info.ru.	300	IN	A	155.212.223.155
ai-info.ru.		300	IN	NS	ns2.beget.ru.
ai-info.ru.		300	IN	NS	ns1.beget.ru.
ai-info.ru.		300	IN	NS	ns2.beget.pro.
ai-info.ru.		300	IN	NS	ns1.beget.pro.
ai-info.ru.		300	IN	NS	ns2.beget.com.
ai-info.ru.		300	IN	NS	ns1.beget.com.
;; Received 196 bytes from 5.101.159.11#53(ns1.beget.com) in 41 ms
```

---

### 11

![10](/screenshots/10-browser-domain.png)