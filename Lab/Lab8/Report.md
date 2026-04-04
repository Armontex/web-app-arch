# Report 8

## 1

![1](/screenshots/01-mysql-status.png)

## 2

![2](/screenshots/02-db-charset.png)

почему utf8mb4, а не utf8? Что такое collation и зачем unicode_ci?

> Потому что UTF8 - только BMP, а utf8mb4 - все языки + эмодзи, короче, стандарт.
> collation - это набор правил, которые говорят, как сравнивать и сортировать символы внутри charset.
> unicode_ci - вариант этих правил, основанный на Unicode и нечувствительный к регистру

## 3

![3](/screenshots/03-phpmyadmin.png)

## 4

![4](/screenshots/04-tables-cli.png)

![5](/screenshots/05-tables-pma.png)

что такое FOREIGN KEY и ON DELETE CASCADE? Зачем? Какой движок используется и почему?

> Foreign Key - это внешний ключ, ссылка на родителя, обычно на UNIQUE или PK столбцы. ON DELETE CASCADE - это правило поведения FK, значит, что при удалении родителя, удаляются и все его дети.
> FK нужен для связи, а ON DELETE CASCADE чтобы дети удалялись автоматически.
> Используется движок InnoDB. Потому что нужен ACID и транзакции и нужны FK и блокировки, а они в этом движке поддерживаются.

## 5

![6](/screenshots/06-schema-sql.png)

## 6

![7](/screenshots/07-data-cli.png)

![8](/screenshots/08-data-pma.png)

## 7

![9](/screenshots/09-join.png)

зачем JOIN? Как получить имя автора без него?

> JOIN чтобы в одном запрос получить данные сразу из двух таблиц, связав их по author_id == users.id
> Через подзапросы можно, но так читается хуже, и, насколько я знаю, медленнее.

## 8

![10](/screenshots/10-fk-error.png)

## 9

![11](/screenshots/11-cascade.png)

## 10

![12](/screenshots/12-injection.png)

как работает SQL-инъекция? Как prepared statement защищает?

> SQL-инъекция работает, когда мы вставляем то, что прислал клиент прямо в строку запроса, к примеру в f-строку в python. PS защищает тем, что данные не идут прямо в запрос, а считываются как аргументы

## 11

![13](/screenshots/13-db-php.png)

## 12

![14](/screenshots/14-submit.png)

![15](/screenshots/15-submit-pma.png)

## 13

![16](/screenshots/16-messages.png)

## 14

![17](/screenshots/17-api-messages.png)

![18](/screenshots/18-api-users.png)

## 15

почему aiomysql, а не обычный mysql-connector? Что будет с event loop при синхронном драйвере?

> aiomysql - асинхронный коннектор, чтобы не простаивать, пока ждём ответ от DB, у нас FastAPI - async и синхронным драйвером мы просто будет блокировать поток, event loop не сможет передать управление и мы не сможем, к примеру, принять запрос от другого пользователя в это время.
