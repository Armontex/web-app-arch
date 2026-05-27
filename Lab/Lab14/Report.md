# Report

## 1

![1](/screenshots/01-passport-install.png)
![2](/screenshots/02-spa-client.png)

Публичный SPA-клиент не хранит client_secret, потому что код React выполняется в браузере, где secret
невозможно скрыть. PKCE заменяет secret одноразовой связкой code_verifier и code_challenge: браузер
сначала отправляет challenge, а при обмене code доказывает владение verifier. Это защищает от перехвата
authorization code.

## 2

![3](/screenshots/03-token-ttl.png)

Access token короткий, потому что он часто передаётся в запросах и при утечке должен быстро стать
бесполезным. Refresh token живёт дольше, потому что нужен для незаметного обновления access token, но его
нужно хранить безопаснее, например в HttpOnly cookie. Если сделать access token на 24 часа, украденный
токен почти сутки позволит обращаться к API без повторной авторизации.

## 3

![4](/screenshots/04-pkce-curl.png)

Curl-запрос прошёл OAuth Authorization Code Flow with PKCE: был сгенерирован `code_verifier`, из него
посчитан `code_challenge`, затем запрос `/oauth/authorize` выдал authorization `code`. После этого `code`
вместе с исходным `code_verifier` был отправлен на `/oauth/token`, и Passport вернул `access_token` и
`refresh_token`.

## 4

![5](/screenshots/05-boardy-api-db.png)
![6](/screenshots/06-comments-moved.png)

В `comments` нет внешних ключей на `posts` и `users`, потому что комментарии теперь лежат в отдельной БД
FastAPI, а MySQL не должен связывать таблицы разных сервисов жёсткими FK. Целостность поддерживается на уровне
приложения: Laravel остаётся источником истины для пользователей и постов, а FastAPI хранит `post_id`,
`author_id` и денормализованный `author_name`. Синхронизацию изменений нужно делать через события, в этой
практике — через Redis.

## 5

![7](/screenshots/07-fastapi-api-db.png)

## 6

![8](/screenshots/08-rs256-valid.png)
![9](/screenshots/09-rs256-invalid.png)

RS256 безопаснее HS256 для распределённых систем, потому что FastAPI хранит только публичный ключ и может лишь
проверять подпись токена. Подписывать новые токены может только Laravel Passport, у которого остаётся приватный
ключ. При HS256 один общий секрет одновременно подписывает и проверяет токены, поэтому утечка секрета из любого
сервиса позволяет выпускать поддельные JWT.

## 7

![10](/screenshots/10-comments-crud.png)

`author_name` передаётся в payload запроса, потому что это денормализованное бизнес-значение комментария на
момент создания. FastAPI хранит комментарии в своей БД и не должен ходить в Laravel-БД за именем пользователя.
Если зашить имя в JWT как custom claim, оно быстро устареет при переименовании пользователя, а токены уже выданы
и будут жить до истечения срока действия.

## 8

![11](/screenshots/11-owner-check.png)

Владелец проверяется в FastAPI перед изменением и удалением комментария: `comment.author_id` сравнивается с user id из Passport
JWT (`sub`). Если убрать эту проверку, любой авторизованный пользователь сможет изменять и удалять чужие комментарии, зная их id.

## 9

![12](/screenshots/12-cors-origin.png)

`allow_origins=["*"]` нельзя использовать вместе с `credentials=true`, потому что браузер не разрешает отправлять credentials при
wildcard-origin. Для запросов с cookie или Authorization-заголовками сервер должен вернуть конкретный `Access-Control-Allow-
  Origin`. Если бы браузер пропускал `*` с credentials, cookie могли бы уходить на API из любого стороннего origin, что ломало бы
границу доверия между сайтами.

## 10

![13](/screenshots/13-pkce-utils.png)

`code_challenge` передаётся на `/oauth/authorize`, чтобы сервер заранее запомнил хэш от `code_verifier` вместе с authorization
code. `code_verifier` передаётся позже на `/oauth/token`, чтобы доказать, что токен запрашивает тот же клиент, который начал flow.
Если перепутать их местами, Passport не сможет проверить PKCE-связку и вернёт `invalid_grant`.

## 11

![14](/screenshots/14-login-start.png)
![15](/screenshots/15-login-callback.png)

## 12

![16](/screenshots/16-token-exchange.png)

Если убрать проверку `state`, callback можно будет подменить: браузер примет чужой authorization code как свой. Это открывает CSRF/login injection атаку, где
пользовательский браузер завершает OAuth flow, начатый атакующим.

## 13

![17](/screenshots/17-refresh-cookie.png)

Если хранить refresh token в `localStorage`, то при XSS злоумышленник сможет прочитать его JavaScript-кодом и использовать для постоянного получения новых
access token. HttpOnly cookie недоступна из JavaScript, поэтому XSS не сможет напрямую украсть refresh token.

## 14

![18](/screenshots/18-silent-refresh.png)

## 15

![19](/screenshots/19-redis-ping.png)

## 16

![20](/screenshots/20-laravel-publish.png)

Redis::publish лучше, чем Http::post() в FastAPI, потому что Laravel не зависит синхронно от доступности FastAPI при создании поста. Laravel публикует
событие в Redis и завершает свой workflow, а FastAPI подписывается на канал и обрабатывает событие асинхронно. Это снижает связанность сервисов и убирает
прямой HTTP-callback между Laravel и FastAPI.

## 17

![21](/screenshots/21-subscriber-running.png)
![22](/screenshots/22-broadcast-flow.png)

## 18

![23](/screenshots/23-user-renamed.png)

UserObserver вызывается автоматически, потому что он зарегистрирован для модели User через User::observe(UserObserver::class) в AppServiceProvider. “Магия”
Laravel находится в Eloquent model events: при сохранении модели Laravel сам генерирует событие updated, находит зарегистрированный observer и вызывает метод
updated(User $user).

## 19

![24](/screenshots/24-denorm-before.png)
![25](/screenshots/25-denorm-after.png)

Eventual consistency — это согласованность, которая достигается не мгновенно, а через некоторое время после события. В этом задании Laravel сразу меняет имя
пользователя и публикует user.renamed в Redis, а FastAPI асинхронно получает событие и обновляет author_name в comments. Задержка возможна между сохранением
User в Laravel и обработкой события подписчиком FastAPI, например если подписчик занят, временно недоступен или Redis-сообщение обрабатывается позже.

## 20

![26](/screenshots/26-two-browsers-post.png)

## 21

![27](/screenshots/27-two-browsers-comment.png)

## 22

![28](/screenshots/28-no-http-callback.png)
![29](/screenshots/29-nginx-no-internal.png)
