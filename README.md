REST API Random Number Generator (Pure PHP) + PHP cURL Client

Задача
Нужно реализовать REST API сервер, который:
1) Генерирует случайное число и присваивает ему уникальный id
2) По id возвращает ранее сгенерированное число

Публичные методы:
- /random — генерирует число + id
- /get?id=... — возвращает число по id

Также нужно написать клиента/библиотеку для использования API через cURL.

Почему проект реализован именно так
1) Чистый PHP без фреймворков
   Я сделал привычную для продакшена декомпозицию: HTTP слой (Request/Response/Router), контроллеры, бизнес-логика (Service), слой данных (Repository), доменные модели (Domain).
   Это уменьшает связность и упрощает расширение: можно заменить хранилище, добавить логирование, метрики, новые эндпоинты, не переписывая всё.

2) Уникальные id через UUIDv4.
   Не нужен глобальный счётчик, не нужна БД, не нужны блокировки при параллельных запросах. Коллизия практически невозможна.
   Дополнительно есть валидация формата id на входе.

3) Хранилище результатов — файловое.
   Для тестового задания это самый простой и надёжный вариант без внешних зависимостей. Каждый результат сохраняется как отдельный JSON-файл.
   Запись сделана атомарно (tmp + rename), чтобы не получить “битые” файлы при сбоях.

4) Готовность к большому объёму данных.
   Файлы шардируются по подпапкам (по хешу id), чтобы не складывать миллионы файлов в одну директорию (это плохо для файловых систем).

5) Предсказуемые JSON-ошибки и трассировка.
   Единый формат ответа об ошибке + X-Request-Id в каждом ответе — удобно для отладки и логов.

Как проходит запрос (что куда и откуда)
1) public/index.php
   - Подключает src/bootstrap.php
   - Создаёт App и Request
   - Передаёт Request в $app->handle()
   - Отправляет Response

2) App.php
   - Создаёт зависимости (Repository → Service → Controller → Router)
   - Регистрирует маршруты:
     GET/POST /random
     GET /get
   - В handle() вызывает router->dispatch()
   - Ловит HttpException и Throwable и формирует JSON-ошибку

3) Router.php
   - По (method + path) находит обработчик
   - Вызывает метод контроллера

4) RandomController.php
   - /random: вызывает RandomService->generate(), возвращает JSON
   - /get: берёт id из query, валидирует, вызывает RandomService->getById()

5) RandomService.php
   - generate(): генерирует UUID, random_int(min,max), created_at, сохраняет через Repository
   - getById(): достаёт доменную модель через Repository

6) FileResultRepository.php
   - save(): атомарная запись JSON в tmp файл → rename в финальный путь
   - find(): читает JSON, восстанавливает RandomResult
   - resolvePath(): кладёт файл в подпапку-шард, чтобы директории не разрастались

API
1) GET /random
Response 200:
{
  "id": "xxxxxxxx-xxxx-4xxx-8xxx-xxxxxxxxxxxx",
  "value": 123456,
  "created_at": "2026-02-17T12:34:56+00:00"
}

2) GET /get?id=<uuidv4>
Response 200:
{
  "id": "xxxxxxxx-xxxx-4xxx-8xxx-xxxxxxxxxxxx",
  "value": 123456,
  "created_at": "2026-02-17T12:34:56+00:00"
}

Запуск сервера
Требования: PHP 8.1+ (рекомендуется 8.2/8.3)

Из корня проекта:
php -S 127.0.0.1:8000 -t public

Примеры запросов
curl -s http://127.0.0.1:8000/random
curl -s "http://127.0.0.1:8000/get?id=xxxxxxxx-xxxx-4xxx-8xxx-xxxxxxxxxxxx"

Конфигурация (env)
Опционально:
- APP_STORAGE_DIR=/absolute/path/to/storage
- RANDOM_MIN=1
- RANDOM_MAX=1000000

Запуск примера:
php client/examples/example.php

Что легко расширить дальше
- Заменить файловое хранилище на MySQL/Redis: достаточно написать новый Repository по интерфейсу ResultRepositoryInterface
- Добавить /list с пагинацией: добавить метод в Repository + Service + Controller + роут
- Добавить логирование: в App::handle() или middleware-подобный слой в Router
