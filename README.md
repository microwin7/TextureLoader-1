# TextureLoader

Базовая реализация TextureProvider и TextureLoader для GravitLauncher 5.5  
Спасибо microwin7 за помощь в работе над этим проектом  

- Поддержка `slim`
- Поддержка загрузки скинов из лаунчера (GravitLauncher 5.5+)
- Отсутствие дубликатов
- Поддержка MySQL и PostgreSQL
- PHP 8.2+
- Ограничение на размер загружаемого скина/плаща

### Ограничения

- Файлы скинов должны находится на том же сервере что и скрипт
- Не поддерживается указание разных настроек высоты, ширины и размера скина для разных пользователей
- Файлы хранятся в формате `hash.png`, для сопоставления с пользователями используется таблица в БД
- Используется только с типом авторизации MySQL/PostgreSQL

# Настройка

- Клонируйте репозиторий в удобное для вас место(или скачайте ZIP архив с кодом)
- Создайте базу данных в вашей СУБД(если у вас её еще нет)
- Выполните sql скрипт для вашей БД для создания таблицы (скрипты находятся в папке sql проекта)
- Выполните `composer update` для загрузки необходимых библиотек
- Скопируйте файл `ecdsa_id.pub` из директории `.keys` лаунчсервера в `config/ecdsa_id.pub`
- Настройте подключение к БД в файле `config/Config.php`
- Настройте nginx (предполагается что у вас уже настроено исполнение PHP)
```nginx
location /assets/ {
    alias PATH_TO_TEXTURELOADER/public/assets/;
}
location /assetloader/ {
    alias PATH_TO_TEXTURELOADER/public/;
}
```
- Настройте конфиг лаунчсервера:  
TextureProvider
```json
"textureProvider": {
  "url": "https://example.com/assetloader/get.php?uuid=%uuid%",
  "type": "json"
}
```
TextureLoader
```json
"mixes": {
  "textureLoader": {
    "urls": {
      "SKIN": "http://example.com/assetloader/upload.php?type=SKIN",
      "CAPE": "http://example.com/assetloader/upload.php?type=CAPE"
    },
    "slimSupportConf": "USER",
    "type": "uploadAsset"
  }
},
```
