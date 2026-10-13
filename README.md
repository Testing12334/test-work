# Focus

Небольшой адаптивный демонстрационный лендинг для проверки GitHub → Railway.
Форма сохраняет одну задачу в localStorage этого браузера. Сервер не получает и не хранит задачи.

## Локальный запуск

Нужен Node.js 22 или новее. Внешних зависимостей нет.

```sh
npm start
```

Откройте http://localhost:3000. Проверка здоровья: `/healthz`.

## Railway

1. Подключите этот репозиторий и выберите ветку с кодом.
2. Railway определяет Node.js по `package.json` и запускает `npm start`; приложение слушает `0.0.0.0:$PORT`.
3. В Settings → Networking → Public Networking нажмите Generate Domain.
4. Проверьте главную страницу и `/healthz` по публичному адресу.

Публичная версия: https://test-work-production.up.railway.app

В Settings → Deploy задайте Healthcheck Path `/healthz`. Автодеплой должен быть включён для ветки `master`.
Новые сервисы Railway больше не поддерживают подключение `railway.json`; параметры задаются в панели.

Для деплоя не нужны секреты, отдельная БД или сборка фронтенда.
