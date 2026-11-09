# График смен

Тестовое задание: интерфейс графика смен сотрудников.

- `front/` — Vue 3, TypeScript, Vite. Код разложен по Feature-Sliced Design: `app` (оболочка и роутер), `pages` (список и редактор), `entities/schedule` (график), `shared` (запросы, календарь, иконки). Сотрудники и смены живут внутри страницы редактора.
- `back/` — Laravel 13, REST API, SQLite

Фронт ходит в `/api`, Vite проксирует эти запросы на `http://127.0.0.1:8000`.

Браузерный пароль включён всегда. `APP_ENV=local` его не снимает. Чтобы открыть локально без окна пароля, в `back/.env` поставьте `BASIC_AUTH_DISABLED=true`. На сервере оставьте `false` и заполните `BASIC_AUTH_EMAIL` и `BASIC_AUTH_PASSWORD`: их сравнивает middleware, без пользователя в базе и без bcrypt на каждый запрос.

## Запуск

Фронт:

```bash
cd front
npm install
npm run dev
```

Бэк:

```bash
cd back
composer install
cp .env.example .env
# для локального запуска откройте back/.env и поставьте BASIC_AUTH_DISABLED=true, иначе API ответит 401
php artisan key:generate
php artisan migrate --seed
npm run dev
```

`npm run dev` в `back/` запускает `php artisan serve`. Сидер кладёт три графика «ТЦ Метрополис», восьмерых сотрудников и шаблоны Утро, День и Вечер. Прогресс у новых графиков 0, пока в них нет смен.

## Сборка на сервер

Laravel отдаёт собранный фронт из `back/storage/app/frontend`. Файл по пути отдаётся как есть. Путь без точки, если файла нет, открывает `index.html`. Пока каталога нет, корень показывает страницу Laravel.

```bash
cd front
npm run build
rm -rf ../back/storage/app/frontend
mkdir -p ../back/storage/app/frontend
cp -r dist/. ../back/storage/app/frontend/
```
