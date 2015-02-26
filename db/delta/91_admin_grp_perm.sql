--

--
-- Table structure for table `adm_grp_action_access`
--

DROP TABLE IF EXISTS `adm_grp_action_access`;
CREATE TABLE `adm_grp_action_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller_name` varchar(45) NOT NULL DEFAULT 'index',
  `action_name` varchar(45) NOT NULL DEFAULT 'index',
  `is_ajax` tinyint(1) NOT NULL DEFAULT '0',
  `view_access` tinyint(1) NOT NULL DEFAULT '0',
  `edit_access` tinyint(1) NOT NULL DEFAULT '0',
  `action_access` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL DEFAULT 'undefined action',
  `group_id` tinyint(4) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping data for table `adm_grp_action_access`
--

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES
-- -----------------------------------------------------------------------------

('admins',          '',                                     0, 'Управление администраторами системы, их группами и правами', 0),
('admins',          'admins-list',                          0, 'Список администраторов',  0),
('admins',          'admins-list-json',                     1, 'Список администраторов  по-странично + фильтры',  0),
('admins',          'check-admins-login',                   1, 'Проверка допустимости логина администратора',  0),
('admins',          'save-admin',                           1, 'Просмотр и редактирование администратора',  0),
('admins',          'remove-admin',                         1, 'Удаление администратора',  0),
('admins',          'admins-groups',                        0, 'Список групп администраторов',  0),
('admins',          'admins-groups-list-json',              1, 'Список групп администраторов  по-странично + фильтры',  0),
('admins',          'check-admins-group-name',              1, 'Проверка допустимости названия группы',  0),
('admins',          'save-admins-group',                    1, 'Просмотр и редактирование группы',  0),
('admins',          'remove-admins-group',                  1, 'Удаление группы',  0),
('admins',          'admins-groups-permissions',            0, 'Форма просмотра прав текущей группы',  0),
('admins',          'save-admins-group-permissions',        1, 'Просмотр и редактирование прав текущей группы',  0),

-- -----------------------------------------------------------------------------

('audio-club',      '',                                     0, 'Аудио клуб',  0),
('audio-club',      'audio-albums',                         0, 'Список альбомов',  0),
('audio-club',      'audio-albums-list-json',               1, 'Список альбомов по-странично + фильтры',  0),
('audio-club',      'remove-audio-albums',                  1, 'Удалить альбом',  0),
('audio-club',      'toggle-audio-albums',                  1, 'Изменить состояние вкл/выкл альбома',  0),
('audio-club',      'add-audio-albums',                     0, 'Добавить альбом',  0),
('audio-club',      'edit-audio-albums',                    0, 'Просмотр и редактирование альбома',  0),
('audio-club',      'edit-audio-cover',                     1, 'Изменение обложки альбома',  0),
('audio-club',      'audio-albums-composition-list-json',   1, 'Список композиций альбома',  0),
('audio-club',      'audio-track-reorder',                  1, 'Изменение порядка композиций в альбоме',  0),
('audio-club',      'audio-tracks-manage',                  1, 'Редактирование композиции',  0),
('audio-club',      'remove-audio-album-track',             1, 'Удаление композиции',  0),
('audio-club',      'toggle-audio-album-track',             1, 'Изменить состояние вкл/выкл композиции',  0),
('audio-club',      'audio-artists',                        0, 'Список исполнителей',  0),
('audio-club',      'audio-artists-list-json',              1, 'Список исполнителей по-странично + фильтры',  0),
('audio-club',      'add-audio-artists',                    1, 'Добавить исполнителя',  0),
('audio-club',      'edit-audio-artists',                   1, 'Редактировать исполнителя',  0),
('audio-club',      'remove-audio-artists',                 1, 'Удалить исполнителя',  0),
('audio-club',      'check-audio-artists-name',             1, 'Проверка допустимости имени исполнителя',  0),
('audio-club',      'audio-genres',                         0, 'Список аудио-жанров',  0),
('audio-club',      'audio-genres-list-json',               1, 'Список аудио-жанров по-странично + фильтры',  0),
('audio-club',      'add-audio-genres',                     1, 'Добавить аудио-жанр',  0),
('audio-club',      'edit-audio-genres',                    1, 'Редактировать аудио-жанр',  0),
('audio-club',      'remove-audio-genres',                  1, 'Удалить аудио-жанр',  0),
('audio-club',      'check-audio-genres-name',              1, 'Проверка допустимости названия аудио-жанра',  0),
('audio-club',      'audio-languages',                      0, 'Список языков исполнения',  0),
('audio-club',      'audio-languages-list-json',            1, 'Список языков исполнения по-странично + фильтры',  0),
('audio-club',      'add-audio-languages',                  1, 'Добавить язык исполнения',  0),
('audio-club',      'edit-audio-languages',                 1, 'Редактировать язык исполнения',  0),
('audio-club',      'remove-audio-languages',               1, 'Удалить язык исполнения',  0),
('audio-club',      'check-audio-languages-name',           1, 'Проверка допустимости названия языка исполнения',  0),
('audio-club',      'audio-years',                          0, 'Список годов выпуска',  0),
('audio-club',      'audio-years-list-json',                1, 'Список годов выпуска по-странично + фильтры',  0),
('audio-club',      'add-audio-years',                      1, 'Добавить год выпуска',  0),
('audio-club',      'edit-audio-years',                     1, 'Редактировать год выпуска',  0),
('audio-club',      'remove-audio-years',                   1, 'Удалить год выпуска',  0),
('audio-club',      'check-audio-years-name',               1, 'Проверка допустимости названия года выпуска',  0),
('audio-club',      'audio-logs',                           0, 'Проверка допустимости названия года выпуска',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES

('auth-user',       '',                                     0,            1,              1,           1,  'Блок авторизованного пользователя системы',  1),
('auth-user',       'auth-user-profile',                    0,            1,              1,           1,  'Профиль',  1),
('auth-user',       'auth-user-messages',                   0,            1,              1,           1,  'Сообщения',  1),
('auth-user',       'tasks-list',                           0,            1,              1,           1,  'Задания',  1),
('auth-user',       'auth-user-settings',                   0,            1,              1,           1,  'Настройки',  1),
('auth-user',       'auth-user-logout',                     0,            1,              1,           1,  'Выход',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('broadcast-servers',   '',                               0,  'Сервера вещания',  0),
('broadcast-servers',   'broadcast-servers-list',           0,  'Список серверов вещания',  0),
('broadcast-servers',   'broadcast-servers-list-json',      1,  'Список серверов вещания по-странично + фильтры',  0),
('broadcast-servers',   'remove-server',                    1,  'Удаление серверов вещания',  0),
('broadcast-servers',   'toggle-server-status',             1,  'Изменить статус вкл/выкл серверов вещания',  0),
('broadcast-servers',   'save-server',                      1,  'Добавление и редактирование серверов вещания',  0),
('broadcast-servers',   'broadcast-zone-list',              0,  'Зоны вещания',  0),
('broadcast-servers',   'broadcast-zone-list-json',         1,  'Зоны вещания вещания по-странично + фильтры',  0),
('broadcast-servers',   'add-zone',                         0,  'Добавление новой зоны',  0),
('broadcast-servers',   'edit-zone',                        0,  'Редактирование существующей зоны',  0),
('broadcast-servers',   'remove-zone',                      1,  'Удаление существующей зоны',  0),

-- -----------------------------------------------------------------------------

('events',          '',                                   0, 'Список событиий',  0),
('events',          'events-list-json',                     1, 'Список событиий по-странично + фильтры',  0),
('events',          'add-event',                            1, 'Добавление события',  0),
('events',          'upload-list-addresses',                1, 'Загрузка списка адресов для рассылки события',  0),
('events',          'clean-events',                         1, 'Очистить список событий',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('index',     '',                                   0,            1,              1,              1,      'Стартовая страница', 1),
('index',     'set-dropdown-attribute',             1,            1,              1,              1,      'Сохранение параметров выпадающего меню пользователя', 1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('infoportal',      '',                                   0, 'Инфопортал',  0),
('infoportal',      'phone-book',                           0, 'Телефоный справочник',  0),
('infoportal',      'phone-book-list-json',                 1, 'Телефоный справочник по-странично + фильтры',  0),
('infoportal',      'save-phone-book-item',                 1, 'Добавление и редактирование телефонов',  0),
('infoportal',      'remove-phone-book-item',               1, 'Удаление телефонов',  0),
('infoportal',      'humor',                                0, 'Юмор',  0),
('infoportal',      'humor-list-json',                      1, 'Юмор по-странично + фильтры',  0),
('infoportal',      'save-humor-item',                      1, 'Просмотр, добавление и редактирование анекдотов',  0),
('infoportal',      'remove-humor-item',                    1, 'Удаление анекдотов',  0),

-- -----------------------------------------------------------------------------

('information',     '',                                   0, 'Справка', 0),

-- -----------------------------------------------------------------------------

('karaoke',         '',                                   0, 'Караоке',  0),
('karaoke',         'karaoke-list',                         0, 'Список караоке',  0),
('karaoke',         'karaoke-list-json',                    1, 'Список караоке по-странично + фильтры',  0),
('karaoke',         'save-karaoke',                         1, 'Просмотр, добавление и редактирование караоке',  0),
('karaoke',         'remove-karaoke',                       1, 'Удалить караоке',  0),
('karaoke',         'toggle-karaoke-done',                  1, 'Изменить состояние вкл/выкл выполнения задания по элементу караоке',  0),
('karaoke',         'toggle-karaoke-accessed',              1, 'Изменить состояние вкл/выкл доступности элемента Караоке',  0),
('karaoke',         'check-karaoke-source',                 1, 'Иформация о источнике елемента караоке',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('login',         '',                                   0,            1,              1,              1, 'Логин. Вход в систему',  1),

-- -----------------------------------------------------------------------------

('logout',         '',                                  0,            1,              1,              1, 'Выход из системы',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('radio',           '',                                   0, 'Список радио',  0),
('radio',           'radio-list-json',                      1, 'Список радио по-странично + фильтры',  0),
('radio',           'toggle-radio',                         1, 'Изменить состояние вкл/выкл радио',  0),
('radio',           'remove-radio',                         1, 'Удалить радио',  0),
('radio',           'add-radio',                            0, 'Добавление радио',  0),
('radio',           'edit-radio',                           0, 'Просмотр и редактирование радио',  0),
('radio',           'radio-check-name',                     1, 'Проверка допустимости названия радио',  0),
('radio',           'radio-check-number',                   1, 'Проверка допустимости номера радио',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('register',         '',                                   0,            1,              1,           1, 'Регистрация в системе',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('settings',        '',                                     0, 'Настройки',  0),
('settings',        'epg',                                  0, 'EPG',  0),
('settings',        'epg-list-json',                        1, 'EPG по-странично + фильтры',  0),
('settings',        'save-epg-item',                        1, 'Просмотр и редактирование EPG',  0),
('settings',        'remove-epg-item',                      1, 'Удалить EPG',  0),
('settings',        'toggle-epg-item-status',               1, 'Изменить состояние вкл/выкл EPG',  0),
('settings',        'epg-check-uri',                        1, 'Проверка допустимости URL EPG',  0),
('settings',        'update-epg',                           1, 'Обновить или принудительно обновить EPG',  0),
('settings',        'themes',                               0, 'Просмотр доступных скинов',  0),
('settings',        'set-current-theme',                    1, 'Установка скина',  0),
('settings',        'common',                               0, 'Обновления прошивки',  0),
('settings',        'common-list-json',                     1, 'Обновления прошивки  по-странично + фильтры',  0),
('settings',        'save-common-item',                     1, 'Просмотр и редактирование обновлений прошивки ',  0),
('settings',        'remove-common-item',                   1, 'Удалить обновление прошивки',  0),
('settings',        'toggle-common-item-status',            1, 'Изменить состояние вкл/выкл обновление прошивки',  0),

-- -----------------------------------------------------------------------------

('statistics',      '',                                   0, 'Статистика',  0),
('statistics',      'stat-video',                           0, 'Статистика видео помесячно',  0),
('statistics',      'stat-video-list-json',                 1, 'Статистика видео помесячно по-странично + фильтры',  0),
('statistics',      'stat-tv',                              0, 'Статистика ТВ',  0),
('statistics',      'stat-tv-list-json',                    1, 'Статистика ТВ по-странично + фильтры',  0),
('statistics',      'stat-tv-archive',                      0, 'Статистика ТВ архива',  0),
('statistics',      'stat-tv-archive-list-json',            1, 'Статистика ТВ архива по-странично + фильтры',  0),
('statistics',      'stat-timeshift',                       0, 'Статистика TimeShift',  0),
('statistics',      'stat-timeshift-list-json',             1, 'Статистика TimeShift по-странично + фильтры',  0),
('statistics',      'stat-moderators',                      0, 'Статистика модераторов',  0),
('statistics',      'stat-moderators-list-json',            1, 'Статистика модераторов по-странично + фильтры',  0),
('statistics',      'stat-abonents',                        0, 'Абонентская статистика',  0),
('statistics',      'stat-abonents-list-json',              1, 'Абонентская статистика по-странично + фильтры',  0),
('statistics',      'stat-abonents-unactive',               0, 'Неактивные абоненты',  0),
('statistics',      'stat-abonents-unactive-list-json',     1, 'Неактивные абоненты по-странично + фильтры',  0),
('statistics',      'stat-claims',                          0, 'Статистика жалоб',  0),
('statistics',      'stat-claims-list-json',                1, 'Статистика жалоб по-странично + фильтры',  0),
('statistics',      'stat-claims-logs',                     0, 'Логи жалоб',  0),
('statistics',      'stat-claims-logs-list-json',           1, 'Логи жалоб по-странично + фильтры',  0),

-- -----------------------------------------------------------------------------

('storages',         '',                                    0, 'Хранилища',  0),
('storages',         'storages-list',                       0, 'Список хранилищ',  0),
('storages',         'storages-list-json',                  1, 'Список хранилищ по-странично + фильтры',  0),
('storages',         'reset-cache',                         1, 'Сброс кэша хранилищ',  0),
('storages',         'refresh-cache',                       1, 'Обновление кэша хранилищ',  0),
('storages',         'get-storage',                         1, 'Просмотр и редактирование хранилищ',  0),
('storages',         'save-storage',                        1, 'Добавить хранилище',  0),
('storages',         'toggle-storages-status',              1, 'Изменить состояние вкл/выкл хранилища',  0),
('storages',         'remove-storage',                      1, 'Удалить хранилище',  0),
('storages',         'storages-video-search',               0, 'Поиск видео',  0),
('storages',         'storages-video-search-json',          1, 'Поиск видео по-странично + фильтры',  0),
('storages',         'storages-logs',                       0, 'Логи хранилищ',  0),
('storages',         'storages-logs-json',                  1, 'Логи хранилищ по-странично + фильтры',  0),

-- -----------------------------------------------------------------------------

('tariffs',         '',                                   0, 'Тарифы',  0),
('tariffs',         'service-packages',                     0, 'Список пакетов услуг',  0),
('tariffs',         'service-packages-list-json',           1, 'Список пакетов услуг по-странично + фильтры',  0),
('tariffs',         'remove-service-package',               1, 'Удалить пакет услуг',  0),
('tariffs',         'add-service-package',                  0, 'Добавить пакет услуг',  0),
('tariffs',         'edit-service-package',                 0, 'Просмотр редактирование пакета услуг',  0),
('tariffs',         'get-services',                         1, 'Получение списка медиаконтента для сервиса (форма добавления/редактирования пакета)',  0),
('tariffs',         'check-external-id',                    1, 'Проверка допустимости внешнего ID для пакета',  0),
('tariffs',         'tariff-plans',                         0, 'Список тарифных планов',  0),
('tariffs',         'tariff-plans-list-json',               1, 'Список тарифных планов по-странично + фильтры',  0),
('tariffs',         'add-tariff-plans',                     0, 'Добавить тарифный план',  0),
('tariffs',         'edit-tariff-plan',                     0, 'Просмотр и редактирование тарифного плана',  0),
('tariffs',         'remove-tariff-plan',                   1, 'Удалить тарифный план',  0),

-- -----------------------------------------------------------------------------

('tasks',           '',                                   0, 'Задания',  0),
('tasks',           'tasks-list',                           0, 'Список заданий',  0),
('tasks',           'tasks-list-json',                      1, 'Список заданий по-странично + фильтры',  0),
('tasks',           'task-state-change',                    1, 'Изменение состояния в категории',  0),
('tasks',           'task-detail-video',                    0, 'Детализация по видео-заданию',  0),
('tasks',           'send-task-message-video',              0, 'Отправка сообщений, изменение состояния видео-задания',  0),
('tasks',           'task-detail-karaoke',                  0, 'Детализация по караоке-заданию',  0),
('tasks',           'send-task-message-karaoke',            0, 'Отправка сообщений, изменение состояния кароаке-задания',  0),
('tasks',           'tasks-report',                         0, 'Отчет',  0),
('tasks',           'tasks-report-json',                    1, 'Отчет по-странично + фильтры',  0),

-- -----------------------------------------------------------------------------

('tv-channels',     '',                                   0, 'IPTV каналы',  0),
('tv-channels',     'iptv-list',                            0, 'Список каналов',  0),
('tv-channels',     'enable-channel',                       1, 'Включить канал',  0),
('tv-channels',     'disable-channel',                      1, 'Отключить канал',  0),
('tv-channels',     'remove-channel',                       1, 'Удалить канал',  0),
('tv-channels',     'add-channel',                          0, 'Добавить канал',  0),
('tv-channels',     'edit-channel',                         0, 'Редактировать канал',  0),
('tv-channels',     'edit-logo',                            1, 'Изменить лого канала',  0),
('tv-channels',     'delete-logo',                          1, 'Удалить лого канала',  0),
('tv-channels',     'move-channel',                         0, 'Переместить канал - Порядок каналов',  0),
('tv-channels',     'move-apply',                           1, 'Переместить канал - Сохранить изменение порядка каналов',  0),
('tv-channels',     'toogle-lock-channel',                  1, 'Изменить состояние вкл/выкл блокировки перемещения канала',  0),

-- -----------------------------------------------------------------------------

('users',           '',                                   0, 'Пользователи',  0),
('users',           'users-list',                           0, 'Список пользователей',  0),
('users',           'users-list-json',                      1, 'Список пользователей по-странично + фильтры',  0),
('users',           'toggle-user',                          1, 'Изменить состояние вкл/выкл пользователя',  0),
('users',           'remove-user',                          1, 'Удалить пользователя',  0),
('users',           'add-users',                            0, 'Добавление пользователя',  0),
('users',           'edit-users',                           0, 'Просмотр и редактирование пользователя',  0),
('users',           'check-login',                          1, 'Проверка допустимости логина пользователя',  0),
('users',           'reset-users-parent-password',          1, 'Сброс пароля родительского контроля  пользователя',  0),
('users',           'reset-user-fav-tv',                    1, 'Сброс избранного ТВ пользователя',  0),
('users',           'users-consoles-groups',                0, 'Список группы приставок',  0),
('users',           'add-console-group',                    1, 'Добавление группы приставок',  0),
('users',           'edit-console-group',                   1, 'Просмотр редактирование названия приставок',  0),
('users',           'remove-console-group',                 1, 'Удалить группу приставок',  0),
('users',           'check-console-name',                   1, 'Проверка допустимости названия группы приставок',  0),
('users',           'users-groups-consoles-list',           0, 'Список приставок в группе',  0),
('users',           'users-groups-consoles-list-json',      1, 'Список приставок в группе по-странично + фильтры',  0),
('users',           'add-console-item',                     1, 'Добавление приставки в группу',  0),
('users',           'remove-console-item',                  1, 'Удалить приставку из группы',  0),
('users',           'check-console-item',                   1, 'Проверка возможности добавления приставки в группу',  0),
('users',           'users-consoles-logs',                  0, 'Логи активности приставок',  0),
('users',           'users-consoles-logs-json',             1, 'Логи активности приставок по-странично + фильтры',  0),
('users',           'users-consoles-report',                0, 'Отчет по изменению состояния приставок',  0),
('users',           'users-consoles-report-json',           1, 'Отчет по изменению состояния приставок по-странично + фильтры',  0),

-- -----------------------------------------------------------------------------

('video-club',      '',                                   0, 'Видео клуб',  0),
('video-club',      'video-list',                           0, 'Список фильмов',  0),
('video-club',      'video-list-json',                      1, 'Список фильмов по-странично + фильтры',  0),
('video-club',      'video-info',                           1, 'Иформация о видео-источнике',  0),
('video-club',      'remove-video',                         1, 'Удалить видео',  0),
('video-club',      'disable-video',                        1, 'Отключить видео',  0),
('video-club',      'enable-video',                         1, 'Включить или добавить в рассписание',  0),
('video-club',      'get-md5',                              1, 'Посчитать md5 фильма',  0),
('video-club',      'create-tasks',                         1, 'Установить задание по фильму',  0),
('video-club',      'add-video',                            0, 'Добавить фильм',  0),
('video-club',      'edit-video',                           0, 'Просмотр и редактирование фильма',  0),
('video-club',      'check-name',                           1, 'Проверка допустимости названия фильма',  0),
('video-club',      'edit-cover',                           1, 'Изменение обложки фильма',  0),
('video-club',      'update-rating-kinopoisk',              1, 'Обновить рейтинг фильма с портала kinopoisk.ru',  0),
('video-club',      'get-kinopoisk-info-by-name',           1, 'По названию фильма заполнить форму добавления фильма используя данные с портала kinopoisk.ru',  0),
('video-club',      'get-kinopoisk-info-by-id',             1, 'По ID фильма на потрале kinopoisk.ru, заполнить форму добавления фильма используя данные с портала kinopoisk.ru',  0),
('video-club',      'get-image',                            1, 'Установить обложку фильма как на потрале kinopoisk.ru',  0),
('video-club',      'video-schedule',                       0, 'Расписание включения видео',  0),
('video-club',      'remove-tasks',                         1, 'Удалить видео из рассписания на включение',  0),
('video-club',      'video-advertise',                      0, 'Список рекламных блоков',  0),
('video-club',      'remove-video-ads',                     1, 'Удалить рекламный блок',  0),
('video-club',      'toggle-video-ads-status',              1, 'Изменить состояние вкл/выкл рекламного блока',  0),
('video-club',      'add-video-ads',                        0, 'Добавить рекламный блок',  0),
('video-club',      'edit-video-ads',                       0, 'Просмотр и редактирование рекламного блока',  0),
('video-club',      'video-moderators-addresses',           0, 'Адреса модераторов, информация о приставках модераторов',  0),
('video-club',      'remove-video-moderators',              1, 'Удалить модератора',  0),
('video-club',      'toggle-video-moderators-status',       1, 'Изменить состояние вкл/выкл модератора',  0),
('video-club',      'add-video-moderators',                 0, 'Добавить модератора',  0),
('video-club',      'edit-video-moderators',                0, 'Просмотр и редактирование модератора',  0),
('video-club',      'check-moderator-mac',                  1, 'Проверка допустимости MAC-адреса приставки модератора',  0),
('video-club',      'video-logs',                           0, 'Логи видео',  0),
('video-club',      'video-logs-json',                      1, 'Логи видео по-странично + фильтры',  0);

--//@UNDO

DROP TABLE IF EXISTS `adm_grp_action_access`;

--