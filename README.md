# Modern Frameworks Project — Кінотеатр

Лабораторні роботи з дисципліни «Сучасні фреймворки вебпрограмування» (2026).
Тема: **№9 Кінотеатр** — фільми, сеанси, замовлення квитків, клієнти.

Кожна лабораторна — окрема гілка; кожна наступна гілка продовжує код попередньої.

| Гілка | Зміст |
|---|---|
| `lab-1` | Встановлення Symfony 6.4 та Laravel 11, тестові контролери, Xdebug |
| `lab-2` | CRUD-операції на Symfony та Laravel |
| `lab-3` | MySQL, 5 таблиць на кожен фреймворк, CRUD для всіх таблиць зі зв'язками |
| `lab-4` | Фільтрація по кожному полю, пагінація з `itemsPerPage` |
| `lab-5` | JWT-автентифікація, ролі Client / Manager / Admin |

## Структура

```
Symfony/   — Symfony 6.4
Laravel/   — Laravel 11
```

## Запуск

```bash
# Symfony
cd Symfony && composer install && symfony server:start

# Laravel
cd Laravel && composer install && cp .env.example .env && php artisan key:generate && composer run dev
```

Вимоги: PHP 8.2+, Composer, Symfony CLI, MySQL.

## База даних (з lab-3)

MySQL, окрема БД на кожен фреймворк: `cinema_symfony` та `cinema_laravel` (користувач `cinema` / `cinema`).

```sql
CREATE USER 'cinema'@'localhost' IDENTIFIED BY 'cinema';
CREATE DATABASE cinema_symfony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE cinema_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL ON cinema_symfony.* TO 'cinema'@'localhost';
GRANT ALL ON cinema_laravel.* TO 'cinema'@'localhost';
```

Міграції: `php bin/console doctrine:migrations:migrate` (Symfony), `php artisan migrate` (Laravel).

| Таблиця | Поля | Зв'язки |
|---|---|---|
| `movies` | назва, опис, жанр, тривалість, дата виходу, віковий рейтинг | 1 → N `screenings` |
| `halls` | назва (унікальна), тип (2D/3D/IMAX/VIP), к-сть рядів, місць у ряду | 1 → N `screenings` |
| `screenings` | початок, ціна, мова | N → 1 `movies`, N → 1 `halls`, 1 → N `tickets` |
| `customers` | ім'я, прізвище, email (унікальний), телефон, дата народження | 1 → N `tickets` |
| `tickets` | ряд, місце, ціна, статус (reserved/paid/cancelled), час покупки | N → 1 `screenings`, N → 1 `customers`; місце на сеансі унікальне |

## API

Для кожної таблиці — окремий контролер з повним CRUD (однаково на Symfony `:8000` та Laravel `:8001`):

| Метод | Шлях | Дія |
|---|---|---|
| GET | `/api/{resource}` | Список |
| GET | `/api/{resource}/{id}` | Один запис |
| POST | `/api/{resource}` | Створити |
| PUT / PATCH | `/api/{resource}/{id}` | Оновити (PATCH — частково) |
| DELETE | `/api/{resource}/{id}` | Видалити |

`{resource}`: `movies`, `halls`, `screenings`, `customers`, `tickets`.
Зв'язки передаються через id: Symfony — `movieId`, `hallId`, `screeningId`, `customerId`; Laravel — `movie_id`, `hall_id`, `screening_id`, `customer_id`.

## Фільтрація, сортування, пагінація (з lab-4)

`GET /api/{resource}` приймає фільтр **по кожному полю** таблиці (Symfony — camelCase, Laravel — snake_case):

| Тип поля | Приклад | Як працює |
|---|---|---|
| рядок | `?title=the` | входження, без урахування регістру |
| число / ціна | `?price=250`, `?price[gte]=100&price[lte]=300` | точне значення або діапазон (`gte`, `lte`, `gt`, `lt`) |
| дата | `?releaseDate[gte]=2024-01-01` | `Y-m-d`, точне значення або діапазон |
| дата-час | `?startsAt=2026-09-20`, `?startsAt[gte]=2026-09-20%2018:00` | день цілком, `Y-m-d H:i` або діапазон |
| enum | `?status=paid`, `?type=IMAX` | точне значення |
| зв'язок | `?movieId=3` / `?movie_id=3` | id пов'язаного запису |

- Пагінація: `?page=2&itemsPerPage=20` (за замовчуванням 10, максимум 100).
- Сортування: `?sort=<поле>&order=asc|desc` (за замовчуванням `id asc`).
- Неправильні параметри → `400` з поясненням.

Відповідь списку:

```json
{
  "data": [ ... ],
  "meta": { "page": 2, "itemsPerPage": 20, "totalItems": 150, "totalPages": 8 }
}
```

Логіка винесена в сервіси: `Symfony/src/Service/{QueryFilter,Paginator}.php`, `Laravel/app/Services/{QueryFilter,Paginator}.php`;
перелік фільтрів кожної сутності — `FILTERS` у репозиторіях (Symfony) та моделях (Laravel).

Тестові дані (30 фільмів, 5 залів, 60 сеансів, 50 клієнтів, 150 квитків):

```bash
# Symfony
php bin/console doctrine:schema:drop --full-database --force && php bin/console doctrine:migrations:migrate -n && php bin/console doctrine:fixtures:load -n
# Laravel
php artisan migrate:fresh --seed
```

Готові запити для PhpStorm HTTP Client — `http/symfony.http` та `http/laravel.http`.
