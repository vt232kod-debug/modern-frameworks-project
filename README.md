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
