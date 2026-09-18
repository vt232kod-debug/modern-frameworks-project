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

Готові запити для PhpStorm HTTP Client — `http/symfony.http` та `http/laravel.http`.
