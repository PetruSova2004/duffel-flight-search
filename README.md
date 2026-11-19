# SkyRoute — поиск авиабилетов через Duffel

Небольшое приложение на Laravel: регистрация и вход клиентов, три страницы с разным доступом и поиск рейсов в одну сторону через [Duffel API v2](https://duffel.com/docs).

Репозиторий: https://github.com/PetruSova2004/duffel-flight-search

## Содержание

- [Стек](#стек)
- [Быстрый старт](#быстрый-старт)
- [Токен Duffel](#токен-duffel)
- [Проверки кода](#проверки-кода)
- [Возможности](#возможности)
- [Как это устроено](#как-это-устроено)
- [Принятые решения](#принятые-решения)
- [Безопасность](#безопасность)
- [Деплой на сервер](#деплой-на-сервер)

## Стек

| Что | Версия |
|---|---|
| PHP | 8.3+ (расширения `intl`, `pdo_sqlite` или `pdo_mysql`) |
| Laravel | 13 |
| БД | SQLite (по умолчанию) или MySQL 8 |
| Фронтенд | Blade + Tailwind CSS 4 + Alpine.js, сборка через Vite |
| Node.js | 20+ (в репозитории `.nvmrc` с версией 22) |
| Тесты и качество | PHPUnit, Laravel Pint, Larastan (PHPStan, уровень 8) |

Docker не нужен: проект запускается встроенным сервером Laravel и SQLite-файлом.

## Быстрый старт

```bash
git clone https://github.com/PetruSova2004/duffel-flight-search.git
cd duffel-flight-search

composer install
cp .env.example .env
php artisan key:generate

# впишите токен Duffel в .env: DUFFEL_TOKEN=duffel_test_...

touch database/database.sqlite
php artisan migrate

npm install
npm run build

php artisan duffel:warm-places   # загрузить страны и города (≈ 6 секунд)
php artisan serve
```

Откройте http://127.0.0.1:8000, зарегистрируйтесь и перейдите в «Поиск билетов».
Для разработки вместо `npm run build` удобнее `composer run dev` — он поднимает сервер, Vite и просмотр логов одной командой.

**Пример поиска:** Великобритания → London (LON), США → New York (NYC), любая дата в ближайшие месяцы.

## Токен Duffel

1. Регистрация: https://dashboard.duffel.com/join
2. В дашборде включён **Test mode**.
3. **More → Developers → Access tokens → Create**, достаточно прав **Read-only**.
4. Токен вида `duffel_test_…` указывается только в `.env` (`DUFFEL_TOKEN=`). В репозиторий он не попадает, а в браузер не уходит никогда: все запросы к Duffel идут с сервера.

В тестовом режиме Duffel возвращает реальные маршруты и тестовую авиакомпанию «Duffel Airways».

Необязательные настройки (значения по умолчанию в `config/services.php`):

| Ключ | По умолчанию | Зачем |
|---|---|---|
| `DUFFEL_TIMEOUT` | 45 | таймаут HTTP-запроса, сек (поиск может идти до 30 с) |
| `DUFFEL_CONNECT_TIMEOUT` | 10 | таймаут соединения, сек |
| `DUFFEL_SUPPLIER_TIMEOUT` | 20000 | сколько Duffel ждёт авиакомпании, мс |
| `DUFFEL_PLACES_TTL` | 86400 | срок кеша стран и городов, сек |

## Проверки кода

```bash
php artisan test     # 51 тест: авторизация, доступы, клиент Duffel, справочник, поиск
composer lint        # стиль кода (Laravel Pint, PSR-12)
composer analyse     # статический анализ (Larastan, уровень 8)
```

Тесты не ходят в настоящий Duffel: ответы подменяются через `Http::fake()`, реальные запросы из тестов запрещены. Для поиска используется урезанный настоящий ответ Duffel (`tests/Fixtures/duffel/offer_request.json`).

## Возможности

**Авторизация**
- Регистрация: имя, e-mail (уникальный, приводится к нижнему регистру), пароль не короче 8 символов с буквами и цифрами и его подтверждение. После регистрации клиент сразу авторизован и попадает на поиск.
- Вход с флажком «Запомнить меня». Не больше 5 попыток в минуту на пару «e-mail + IP», ошибка всегда одна: «Неверный e-mail или пароль».
- Выход только через POST с CSRF, сессия инвалидируется.
- Пароли хранятся только хешем (каст `hashed`).
- После входа клиент возвращается туда, куда шёл (`redirect()->intended()`).

**Страницы и навигация**

| Страница | Адрес | Доступ |
|---|---|---|
| Главная | `/` | все; гостю кнопки «Войти / Зарегистрироваться», клиенту «Искать билеты» |
| Вход, регистрация | `/login`, `/register` | только гость (`guest`), клиента перенаправляет на поиск |
| Поиск билетов | `/flights` | только клиент (`auth`), гостя отправляет на вход и возвращает обратно |

Доступ задают middleware в `routes/web.php`, а не проверки в шаблонах. Одна шапка на всех страницах, текущий пункт выделен, на телефоне меню сворачивается в «бургер». Страницы ошибок 404, 419, 429 и 500 сделаны в общем дизайне.

**Поиск через Duffel**
- Форма: дата (от сегодня до +330 дней), страна и город отправления, страна и город прибытия. Выпадающие списки с поиском и управлением с клавиатуры. Город неактивен, пока не выбрана страна. Город прибытия не может совпадать с городом отправления.
- Валидация через Form Request, введённые значения сохраняются после ошибки.
- Запрос `POST /air/offer_requests?return_offers=true`: один участок, один взрослый, эконом; заголовки `Authorization`, `Duffel-Version: v2`, `Accept`, `Accept-Encoding: gzip`.
- Обрабатываются ошибки 401, 422, 429, 5xx и отсутствие связи. Пользователь видит понятный текст, подробности (статус, `x-request-id`, коды ошибок Duffel) пишутся в лог без токена.
- Результаты: все предложения, по умолчанию сначала дешёвые. В карточке сразу видны время вылета и прилёта с пометкой «+1», длительность («7 ч 58 мин»), пересадки с ожиданием и цена («218,22 €»). Там же тариф, багаж, CO₂ и срок действия цены.
- Под кнопкой «Подробнее» — каждый сегмент: аэропорты и терминалы, тип самолёта, перевозчик-оператор, если он другой.
- Сортировка по цене, времени вылета и длительности.
- Во время поиска кнопка неактивна, виден индикатор «Ищем рейсы…», повторный клик не создаёт второй запрос. Пустой результат и ошибка показываются отдельными экранами с подсказкой.

**Дополнительно**
- Смена пароля (`/account/password`, ссылка с именем в шапке). Нужен старый пароль, после смены другие устройства разлогиниваются.
- Кнопка «показать пароль» в полях пароля.
- Дополнительные лимиты: 20 попыток входа в минуту с одного IP, 5 регистраций в минуту с IP, 10 поисков в минуту на пользователя.
- Защитные HTTP-заголовки (см. «Безопасность»).
- Команда `duffel:warm-places` и ежедневное обновление справочника по расписанию.

## Как это устроено

```
app/
├── Console/Commands/WarmDuffelPlaces.php   # прогрев кеша стран и городов
├── Data/Flights/                           # DTO: Offer, Segment, FlightPoint, Layover, Carrier, Money…
├── Http/
│   ├── Controllers/Auth/                   # Register, Login (вход и выход), Password
│   ├── Controllers/FlightSearchController  # форма и результаты
│   ├── Controllers/CityController          # JSON: города страны для формы
│   ├── Middleware/SecurityHeaders.php
│   └── Requests/                           # вся валидация (Form Request)
├── Rules/CityInCountry.php                 # город есть в справочнике для выбранной страны
├── Services/
│   ├── Duffel/DuffelClient.php             # только HTTP: заголовки, таймауты, ошибки
│   ├── Duffel/PlaceDirectory.php           # справочник стран и городов с кешем
│   └── Flights/                            # FlightSearchService + OfferMapper
└── Support/Format.php                      # даты, длительность и цена по-русски
lang/ru/                                    # все тексты интерфейса
resources/js/components/                    # combobox, форма поиска, сортировка
resources/views/                            # layout, компоненты, страницы
```

**Путь поискового запроса:**

```
GET /flights/search
  → FlightSearchRequest   проверяет дату, страны и города, собирает SearchCriteria
  → FlightSearchService   отправляет offer request через DuffelClient
  → OfferMapper           превращает JSON Duffel в DTO (Offer, Segment…)
  → Blade                 выводит готовые объекты, без разбора сырого ответа
```

Контроллеры тонкие: валидация в Form Request, работа с API в сервисах, преобразование данных в маппере, форматирование в `Support\Format`.

## Принятые решения

**Авторизация своя, без Breeze и Fortify.** Нужны только регистрация, вход, выход и смена пароля. Своя реализация — это три контроллера и три Form Request: весь код на виду, каждое правило лежит в понятном месте. Starter kit принёс бы много неиспользуемого (подтверждение почты, профиль, свои шаблоны).

**Как собираются страны и города.** Справочник строится из `GET /air/airports`: около 9 000 аэропортов, 46 страниц по 200 записей, примерно 6 секунд.
- Страны — это уникальные `iata_country_code` всех аэропортов. Названия по-русски даёт расширение `intl` (`Locale::getDisplayRegion`), сортировка по алфавиту через `Collator`.
- Город — это IATA-агломерация, если аэропорт в неё входит. Например, London (LON) объединяет Heathrow, Gatwick и другие аэропорты города. Иначе городом считается сам аэропорт. Duffel принимает оба вида кодов, поэтому в списке остаются только места, откуда реально можно лететь.
- Результат (235 стран, около 8 700 городов) хранится в кеше 24 часа. Его заранее заполняет команда `duffel:warm-places`, а расписание обновляет каждый день в 03:00. Если кеш пуст, данные строит только один запрос: остальные ждут его под блокировкой и не долбят Duffel параллельно.
- `GET /air/cities` не подошёл: в нём только 255 агломераций, и тысячи городов с одним аэропортом пропали бы. `places/suggestions` ищет по строке и не умеет «все города страны».

**Поиск обычным GET-запросом с серверной отрисовкой.** Ссылку на результаты можно обновить и переслать, а форма работает как обычная форма Laravel со старыми значениями и ошибками. Сортировка выполняется в браузере и мгновенно, без повторного запроса к Duffel.

**Результаты поиска не кешируются.** Сырой ответ весит до 2 МБ, а цены у Duffel меняются от запроса к запросу. Частоту поисков ограничивает лимит в 10 запросов в минуту.

## Безопасность

- CSRF на всех формах, выход только через POST.
- После входа и регистрации сессия пересоздаётся (защита от фиксации сессии), при выходе инвалидируется.
- Пароли хранятся хешем. Правила пароля заданы в одном месте (`Password::defaults()`).
- Ошибка входа не выдаёт, существует ли e-mail. Есть лимиты на вход, регистрацию и поиск.
- После смены пароля остальные сессии и cookie «Запомнить меня» перестают действовать (`AuthenticateSession`).
- Токен Duffel только в `.env` и только на сервере, в лог не пишется.
- Коды городов проверяются по своему справочнику, в Duffel уходят только проверенные значения.
- Все данные в Blade экранируются. В JavaScript данные передаются через `@js`.
- Заголовки `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, а по HTTPS ещё и `Strict-Transport-Security`.
- В кеш кладутся только массивы. В Laravel 13 по умолчанию `serializable_classes = false`, и десериализация объектов из кеша запрещена.

## Деплой на сервер

Пример для Ubuntu + nginx + PHP-FPM 8.3+.

**1. Код и зависимости**

```bash
git clone https://github.com/PetruSova2004/duffel-flight-search.git /var/www/skyroute
cd /var/www/skyroute

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
```

**2. `.env` для продакшена**

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
LOG_LEVEL=warning

SESSION_SECURE_COOKIE=true

# SQLite по умолчанию; для MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=skyroute
# DB_USERNAME=...
# DB_PASSWORD=...

DUFFEL_TOKEN=duffel_test_...
```

**3. База, кеши, права**

```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan duffel:warm-places

chown -R www-data:www-data storage bootstrap/cache database
```

**4. Планировщик** (обновляет справочник стран и городов раз в сутки), строка в `crontab -e` пользователя `www-data`:

```
* * * * * cd /var/www/skyroute && php artisan schedule:run >> /dev/null 2>&1
```

**5. nginx**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/skyroute/public;
    index index.php;

    # Страница результатов — до 2 МБ HTML, сжатие уменьшает её примерно в 10 раз
    gzip on;
    gzip_types text/html text/css application/javascript application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 60s;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

HTTPS удобно подключить через certbot: `certbot --nginx -d your-domain.com`.

**6. PHP.** Поиск у Duffel может длиться до 30 секунд, поэтому в `php.ini` для FPM нужно `max_execution_time = 60` (по умолчанию 30).

**Обновление после новых коммитов:**

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
