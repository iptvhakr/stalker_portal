SET NAMES 'utf8';

CREATE DATABASE IF NOT EXISTS stalker_db;
USE stalker_db;

DROP TABLE `video`;
CREATE TABLE `video`(
    `id` int NOT NULL auto_increment, 
    `owner` varchar(64) NOT NULL default '', 
    `name` varchar(255) NOT NULL default '', 
    `old_name` varchar(255) NOT NULL default '', 
    `o_name` varchar(255) NOT NULL default '', 
    `fname` varchar(128) NOT NULL default '', 
    `description` text,
    `pic` varchar(128) NOT NULL default '', 
    `cost` int NOT NULL default 0, 
    `time` varchar(64) NOT NULL default '', 
    `file` varchar(128) NOT NULL default '', 
    `path` varchar(255) NOT NULL default '',
    `protocol` varchar(64) NOT NULL default 'nfs',
    `rtsp_url` varchar(255) NOT NULL default '', 
    `censored` tinyint default 0, /* 0-off, 1-on */
    `hd` tinyint default 0,
    `series` text NOT NULL default '', 
    `volume_correction` int NOT NULL default 0,
    
    `category_id` int NOT NULL default 0,
    
    `genre_id` int NOT NULL default 0,
    
    `genre_id_1` int NOT NULL default 0,
    `genre_id_2` int NOT NULL default 0,
    `genre_id_3` int NOT NULL default 0,
    `genre_id_4` int NOT NULL default 0,
    
    `cat_genre_id_1` int NOT NULL default 0,
    `cat_genre_id_2` int NOT NULL default 0,
    `cat_genre_id_3` int NOT NULL default 0,
    `cat_genre_id_4` int NOT NULL default 0,
    
    `director` varchar(128) NOT NULL default '', 
    `actors` varchar(255) NOT NULL default '', 
    `year` varchar(128) NOT NULL default '', 
    
    `accessed` tinyint default 0, /* 0-off, 1-on */
    
    `status` tinyint default 0, /* 0-red, 1-green */
    
    `disable_for_hd_devices` tinyint default 0,
    
    `added` datetime,
    
    `count` int  NOT NULL default 0, 
    `count_first_0_5` int  NOT NULL default 0, 
    `count_second_0_5` int  NOT NULL default 0, 
    
    `vote_sound_good` int NOT NULL default 0,
    `vote_sound_bad` int NOT NULL default 0,
    `vote_video_good` int NOT NULL default 0,
    `vote_video_bad` int NOT NULL default 0,
    
    `rate` text NOT NULL default '', 
    `last_rate_update` date,
    `last_played` date,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `genre`;
CREATE TABLE `genre`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `cat_genre`;
CREATE TABLE `cat_genre`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '',
    `category_alias` varchar(255) NOT NULL default '', 
    PRIMARY KEY (`id`),
    INDEX(`category_alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into `genre` (title) VALUES ('Боевик');
insert into `genre` (title) VALUES ('Детектив');
insert into `genre` (title) VALUES ('Документальный');
insert into `genre` (title) VALUES ('Драма');
insert into `genre` (title) VALUES ('Исторический');
insert into `genre` (title) VALUES ('Клипы');
insert into `genre` (title) VALUES ('Комедия');
insert into `genre` (title) VALUES ('Мелодрама');
insert into `genre` (title) VALUES ('Мультфильм');
insert into `genre` (title) VALUES ('Приключения');
insert into `genre` (title) VALUES ('Триллер');
insert into `genre` (title) VALUES ('Ужасы');
insert into `genre` (title) VALUES ('Фантастика');
insert into `genre` (title) VALUES ('Эротика');

DROP TABLE `itv`;
CREATE TABLE `itv`(
    `id` int NOT NULL auto_increment, 
    `name` varchar(128) NOT NULL default '', 
    `number` int NOT NULL default 0, 
    `censored` tinyint default 0,
    `cmd` varchar(128) NOT NULL default '',
    `descr` text NOT NULL default '',
    `cost` int NOT NULL default 0,
    `count` int  NOT NULL default 0, 
    `status` tinyint unsigned NOT NULL default 1,
    `tv_genre_id` int NOT NULL default 0,
    `base_ch` tinyint default 0, /* 1 - base channel */
    `hd` tinyint default 0,
    `xmltv_id` varchar(128) NOT NULL default '',
    `service_id` varchar(32) NOT NULL default '',
    `bonus_ch` tinyint default 0, /* 1 - bonus channel */
    `volume_correction` int NOT NULL default 0,
    `use_http_tmp_link` tinyint default 0, 
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `tv_genre`;
CREATE TABLE `tv_genre`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '', 

    PRIMARY KEY (`id`),
    UNIQUE KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into tv_genre (title) VALUES ('информационный');
insert into tv_genre (title) VALUES ('развлечения');
insert into tv_genre (title) VALUES ('детский');
insert into tv_genre (title) VALUES ('кино');
insert into tv_genre (title) VALUES ('наука');
insert into tv_genre (title) VALUES ('спорт');
insert into tv_genre (title) VALUES ('музыка');
insert into tv_genre (title) VALUES ('бизнес');
insert into tv_genre (title) VALUES ('культура');
insert into tv_genre (title) VALUES ('для взрослых');

DROP TABLE `last_id`;
CREATE TABLE `last_id`(
    `id` int NOT NULL auto_increment, 
    `ident` varchar(64) NOT NULL default '',
    `last_id` int unsigned NOT NULL default 0,
    UNIQUE KEY (`ident`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `screenshots`;
CREATE TABLE `screenshots`(
    `id` int NOT NULL auto_increment, 
    `name` varchar(64) NOT NULL default '',
    `size` varchar(255) NOT NULL default '',
    `type` varchar(255) NOT NULL default '',
    `path` varchar(255) NOT NULL default '',
    `media_id` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `epg`;
CREATE TABLE `epg`(
    `id` int NOT NULL auto_increment, 
    `ch_id` int NOT NULL default 0,
    `time` timestamp not null,
    `time_to` timestamp not null,
    `duration` int NOT NULL default 0,
    `name` varchar(128) NOT NULL default '',
    `descr` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `karaoke`;
CREATE TABLE `karaoke`(
    `id` int NOT NULL auto_increment, 
    `owner` varchar(64) NOT NULL default '', 
    `name` varchar(128) NOT NULL default '', 
    `fname` varchar(128) NOT NULL default '', 
    `description` text,
    `protocol` varchar(64) NOT NULL default 'nfs',
    `rtsp_url` varchar(255) NOT NULL default '',
    `pic` varchar(128) NOT NULL default '', 
    `cost` int NOT NULL default 0, 
    `time` varchar(64) NOT NULL default '', 
    `file` varchar(128) NOT NULL default '', 
    `path` varchar(128) NOT NULL default '', 
    
    `genre_id` int NOT NULL default 0,
    `singer` varchar(128) NOT NULL default '', 
    `author` varchar(128) NOT NULL default '', 
    `year` varchar(128) NOT NULL default '', 
    
    `accessed` tinyint default 0,
    `status` tinyint default 0,
    `added` datetime,
    `add_by` int NOT NULL default 0,
    
    `done` tinyint default 0,
    `done_time` datetime,

    `archived` tinyint default 0,
    `archived_time` datetime,

    `returned` tinyint default 0,
    `reason` varchar(255) NOT NULL default '', 
    
    `count` int NOT NULL default 0, 
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `karaoke_genre`;
CREATE TABLE `karaoke_genre`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '', 

    PRIMARY KEY (`id`),
    UNIQUE KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into `karaoke_genre` (title) VALUES ('Поп');
insert into `karaoke_genre` (title) VALUES ('Рок');
insert into `karaoke_genre` (title) VALUES ('Шансон');
insert into `karaoke_genre` (title) VALUES ('Из мультфильмов');
insert into `karaoke_genre` (title) VALUES ('Детские');
insert into `karaoke_genre` (title) VALUES ('Народные');
insert into `karaoke_genre` (title) VALUES ('Джаз');
insert into `karaoke_genre` (title) VALUES ('Из кинофильмов');

DROP TABLE `user_log`;
CREATE TABLE `user_log`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(128) NOT NULL default '', 
    
    `uid` int NOT NULL default 0, 
    
    `action` varchar(128) NOT NULL default '', 
    `param` varchar(128) NOT NULL default '', 
    
    `time` datetime,
    
    `type` tinyint(4) default '0',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `users`;
CREATE TABLE `users`(
    `id` int NOT NULL auto_increment,
    
    `name` varchar(64) NOT NULL default '', 
    `sname` varchar(64) NOT NULL default '',
    
    `pass` varchar(64) NOT NULL default '',
    
    `parent_password` varchar(64) NOT NULL default '0000',
    
    `bright` varchar(64) NOT NULL default '200',
    `contrast` varchar(64) NOT NULL default '127',
    `saturation` varchar(64) NOT NULL default '127',
    
    `aspect` int NOT NULL default 16,

    `video_out` varchar(64) NOT NULL default 'rca',
    `volume` varchar(64) NOT NULL default '100',
    
    `mac` varchar(64) NOT NULL default '',
    `ip` varchar(128) NOT NULL default '',
    `ls` int not null default 0,
    `version` varchar(255) NOT NULL default '',
    
    `lang` varchar(32) NOT NULL default '',

    `locale` varchar(32) NOT NULL default '',
    `city_id` int NOT NULL default 0,

    `status` tinyint default 0,
    
    `hd` tinyint default 0,
    `main_notify` tinyint default 1,
    `fav_itv_on` tinyint default 0,
    
    `now_playing_start` timestamp default 0,
    `now_playing_type` tinyint default 0,
    `now_playing_content` varchar(255) NOT NULL default '',
    
    `additional_services_on` tinyint default 1,
    
    /*`lang` varchar(32) NOT NULL default 'ru',*/
    
    `time_last_play_tv` timestamp default 0,
    `time_last_play_video` timestamp default 0,
    
    `operator_id` int NOT NULL default 0,
    
    `storage_name` varchar(255) NOT NULL default '',
    `hd_content` tinyint default 0,
    `image_version` varchar(255) NOT NULL default '',
    
    `last_change_status` timestamp default 0,
    `last_start` timestamp default 0,
    `last_active` timestamp default 0,
    `keep_alive` timestamp default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `moderators`;
CREATE TABLE `moderators` (
    `id` int NOT NULL auto_increment,
    `name` varchar(128) NOT NULL default '',
    `mac` varchar(64) NOT NULL default '',
    `status` tinyint default 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`mac`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `vclub_news`;
CREATE TABLE `vclub_news`(
    `id` int NOT NULL auto_increment,
    
    `msg` text NOT NULL default '',
    `added` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `events`;
CREATE TABLE `events`(
    `id` int NOT NULL auto_increment,
    
    `uid` int NOT NULL default 0,
    
    `type` varchar(128) NOT NULL default '',
    `event` varchar(128) NOT NULL default '',
    `msg` text NOT NULL default '',
    `rec_id` int NOT NULL default 0,
    
    /*`status` tinyint default 1,   1-not sended, 0-sensed*/
    `sended` tinyint default 0,
    
    `need_confirm` tinyint default 0,
    `confirmed` tinyint default 0,
    `ended` tinyint default 0,
    `reboot_after_ok` tinyint default 0,
    `priority` tinyint default 2, /* 1-system events, 2-system message */
    
    `addtime` datetime,
    `eventtime` timestamp default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `fav_itv`;
CREATE TABLE `fav_itv`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `fav_ch` text NOT NULL default '',
    `addtime` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `audio`;
CREATE TABLE `audio`(
    `id` int NOT NULL auto_increment,
    `name` varchar(128) NOT NULL default '',
    
    `singer_id` int NOT NULL default 0,
    `album_id` int NOT NULL default 0,
    
    `time` int NOT NULL default 0,
    `count` int NOT NULL default 0,
    `lang` tinyint default 0, /*0-rus, 1-eng, 2-num*/
    `status` tinyint default 0,
    `addtime` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `singer`;
CREATE TABLE `singer`(
    `id` int NOT NULL auto_increment,
    `singer` varchar(128) NOT NULL default '',
    `path` varchar(128) NOT NULL default '',
    `lang` tinyint default 0, /*0-rus, 1-eng, 2-num*/
    `addtime` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`singer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `album`;
CREATE TABLE `album`(
    `id` int NOT NULL auto_increment,
    `name` varchar(128) NOT NULL default '',
    `singer_id` int NOT NULL default 0,
    `year` int NOT NULL default 0,
    `addtime` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `playlist`;
CREATE TABLE `playlist`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `tracks` text NOT NULL default '',
    `addtime` datetime,
    `edittime` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `radio`;
CREATE TABLE `radio`(
    `id` int NOT NULL auto_increment, 
    `name` varchar(128) NOT NULL default '', 
    `number` int NOT NULL default 0, 
    `cmd` varchar(128) NOT NULL default '',
    `count` int  NOT NULL default 0, 
    `status` tinyint unsigned NOT NULL default 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `fav_vclub`;
CREATE TABLE `fav_vclub`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `fav_video` text NOT NULL default '',
    `addtime` datetime,
    `edittime` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `administrators`;
CREATE TABLE `administrators`(
    `id` int NOT NULL auto_increment,
    `login` varchar(128) NOT NULL default '',
    `pass`  varchar(128) NOT NULL default '',
    `name` varchar(128) NOT NULL default '',
    `fname` varchar(128) NOT NULL default '',
    `access` tinyint default 0, /*1-admin, 2-moderator, 3-subscribe moderator*/
    `operator_id` int NOT NULL default 0,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `video_log`;
CREATE TABLE `video_log`(
    `id` int NOT NULL auto_increment,
    `moderator_id` int NOT NULL default 0,
    `action` varchar(128) NOT NULL default '',
    `video_id` int NOT NULL default 0,
    `actiontime` datetime,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE `played_video`;
CREATE TABLE `played_video`(
    `id` int NOT NULL auto_increment,
    `video_id` int NOT NULL default 0,
    `storage` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `playtime` timestamp not null,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `played_itv`;
CREATE TABLE `played_itv`(
    `id` int NOT NULL auto_increment,
    `itv_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `playtime` datetime,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `start_play_itv`;
CREATE TABLE `start_play_itv`(
    `id` int NOT NULL auto_increment,
    `media_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `starttime` datetime,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `start_play_video`;
CREATE TABLE `start_play_video`(
    `id` int NOT NULL auto_increment,
    `media_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `starttime` datetime,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `start_play_karaoke`;
CREATE TABLE `start_play_karaoke`(
    `id` int NOT NULL auto_increment,
    `media_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `starttime` datetime,
    
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `video_records`;
CREATE TABLE `video_records`(
    `id` int NOT NULL auto_increment,
    `descr` varchar(255) NOT NULL default '',
    `cmd` varchar(255) NOT NULL default '',
    `status` tinyint default 1, /* 1-on, 0-off */
    `accessed` tinyint default 0, /* 0-off, 1-on */
    `addtime` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `rec_files`;
CREATE TABLE `rec_files`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `t_start` timestamp not null,
    `t_stop`  timestamp default 0,
    `atrack`  varchar(32) NOT NULL default '',
    `vtrack`  varchar(32) NOT NULL default '',
    `length` int NOT NULL default 0,
    `ended`  tinyint default 0, /* 0-not ended, 1-ended */
    `uid` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `users_rec`;
CREATE TABLE `users_rec`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `file_id` int NOT NULL default 0,
    `t_start` timestamp not null,
    `t_stop`  timestamp default 0,
    `end_record`  timestamp default 0,
    `atrack`  varchar(32) NOT NULL default '',
    `vtrack`  varchar(32) NOT NULL default '',
    `length` int NOT NULL default 0,
    `ended`  tinyint default 0, /* 0-not ended, 1-ended */
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `moderator_tasks`;
CREATE TABLE `moderator_tasks`(
    `id` int NOT NULL auto_increment,
    `to_usr` int NOT NULL default 0,
    `media_type` int NOT NULL default 0,
    `media_id` int NOT NULL default 0,
    `media_length` int NOT NULL default 0,
    `start_time` datetime,
    `end_time` datetime,
    `ended` tinyint default 0,
    `rejected` tinyint default 0, /* 1 - rejected, 0 - ended */
    `archived` tinyint default 0,
    `archived_time` datetime default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `moderators_history`;
CREATE TABLE `moderators_history`(
    `id` int NOT NULL auto_increment,
    `task_id` int NOT NULL default 0,
    `from_usr` int NOT NULL default 0,
    `to_usr` int NOT NULL default 0,
    `comment` text NOT NULL default '',
    `send_time` datetime,
    `readed` tinyint default 0,
    `reply_to` int NOT NULL default 0,
    `read_time` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `tasks_archive`;
CREATE TABLE `tasks_archive`(
    `id` int NOT NULL auto_increment,
    `date` date NOT NULL default 0,
    `year` int NOT NULL default 0,
    `month` tinyint default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `daily_played_video`;
CREATE TABLE `daily_played_video`(
    `id` int NOT NULL auto_increment,
    `date` date NOT NULL default 0,
    `count` int NOT NULL default 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `itv_subscription`;
CREATE TABLE `itv_subscription`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `sub_ch` text NOT NULL default '',
    `bonus_ch` text NOT NULL default '',
    `addtime` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `operators`;
CREATE TABLE `operators`(
    `id` int NOT NULL auto_increment,
    `name` varchar(128) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `operators_ip`;
CREATE TABLE `operators_ip`(
    `id` int NOT NULL auto_increment,
    `operator_id` int NOT NULL default 0,
    `ip_n_mask` varchar(128) NOT NULL default '',
    `long_ip_from` int unsigned NOT NULL default 0,
    `long_ip_to` int unsigned NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `storages`;
CREATE TABLE `storages`(
    `id` int NOT NULL auto_increment,
    `storage_name` varchar(128) NOT NULL default '',
    `storage_ip` varchar(128) NOT NULL default '',
    `nfs_home_path` varchar(128) NOT NULL default '',
    `max_online` int NOT NULL default 0,
    `status` tinyint default 1,
    `for_moderator` tinyint default 0,
    UNIQUE KEY (`storage_name`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `stream_error`;
CREATE TABLE `stream_error`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `mac` varchar(128) NOT NULL default '',
    `error_time` timestamp not null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `karaoke_archive`;
CREATE TABLE `karaoke_archive`(
    `id` int NOT NULL auto_increment,
    `date` datetime NOT NULL default 0,
    `year` int NOT NULL default 0,
    `month` tinyint default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `permitted_video`;
CREATE TABLE `permitted_video`(
    `id` int NOT NULL auto_increment,
    `o_name` varchar(255) NOT NULL default '',
    `year` int not null default 0,
    `genre` tinyint default 0,
    `added` datetime NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `video_clips`;
CREATE TABLE `video_clips`(
    `id` int NOT NULL auto_increment,
    `name` varchar(128) NOT NULL default '',
    `singer` varchar(128) NOT NULL default '',
    `censored` tinyint default 0, /* 0-off, 1-on */

    `genre_id` int NOT NULL default 0,
    
    `accessed` tinyint default 0,
    `status` tinyint default 0,
    `added` datetime,
    `add_by` int NOT NULL default 0,

    `done` tinyint default 0,
    `done_time` datetime,

    `archived` tinyint default 0,
    `archived_time` datetime,

    `returned` tinyint default 0,
    `reason` varchar(255) NOT NULL default '',

    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `video_clip_genres`;
CREATE TABLE `video_clip_genres`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '', 

    PRIMARY KEY (`id`),
    UNIQUE KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `vclub_paused`;
CREATE TABLE `vclub_paused`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `mac` varchar(128) NOT NULL default '',
    `pause_time` timestamp not null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `storage_deny`;
CREATE TABLE `storage_deny`(
    `id` int NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `counter` int NOT NULL default 0,
    `updated` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `rss_cache_weather`;
CREATE TABLE `rss_cache_weather`(
    `id` int NOT NULL auto_increment,
    `url` varchar(255) NOT NULL default '',
    `content` text not NULL default '',
    `crc` varchar(64) NOT NULL default '',
    `updated` datetime,
    INDEX(`crc`),
    UNIQUE KEY (`crc`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `rss_cache_horoscope`;
CREATE TABLE `rss_cache_horoscope`(
    `id` int NOT NULL auto_increment,
    `url` varchar(255) NOT NULL default '',
    `content` text not NULL default '',
    `crc` varchar(64) NOT NULL default '',
    `updated` datetime,
    INDEX(`crc`),
    UNIQUE KEY (`crc`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `media_category`;
CREATE TABLE `media_category`(
    `id` int NOT NULL auto_increment,
    `category_name` varchar(255) NOT NULL default '',
    `category_alias` varchar(255) NOT NULL default '',
    `num` int NOT NULL default 0,
    UNIQUE KEY (`category_name`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `main_city_info`;
CREATE TABLE `main_city_info`(
    `id` int NOT NULL auto_increment,
    `num` int NOT NULL default 0,
    `title` varchar(255) NOT NULL default '',
    `number` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `other_city_info`;
CREATE TABLE `other_city_info`(
    `id` int NOT NULL auto_increment,
    `num` int NOT NULL default 0,
    `title` varchar(255) NOT NULL default '',
    `number` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `help_city_info`;
CREATE TABLE `help_city_info`(
    `id` int NOT NULL auto_increment,
    `num` int NOT NULL default 0,
    `title` varchar(255) NOT NULL default '',
    `number` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `anec`;
CREATE TABLE `anec`(
    `id` int NOT NULL auto_increment,
    `title` varchar(255) NOT NULL default '',
    `anec_body` text NOT NULL default '',
    `added` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `readed_anec`;
CREATE TABLE `readed_anec`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(64) NOT NULL default '',
    `readed` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `anec_bookmark`;
CREATE TABLE `anec_bookmark`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `anec_id` int NOT NULL default 0,
    UNIQUE KEY (`uid`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `anec_rating`;
CREATE TABLE `anec_rating`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `anec_id` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `loading_fail`;
CREATE TABLE `loading_fail`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(64) NOT NULL default '',
    `ff_crash` int NOT NULL default 0,
    `added` timestamp not null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `gapi_cache_cur_weather`;
CREATE TABLE `gapi_cache_cur_weather`(
    `id` int NOT NULL auto_increment,
    `url` varchar(255) NOT NULL default '',
    `content` text not NULL default '',
    `crc` varchar(64) NOT NULL default '',
    `updated` datetime,
    INDEX(`crc`),
    UNIQUE KEY (`crc`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `gismeteo_day_weather`;
CREATE TABLE `gismeteo_day_weather`(
    `id` int NOT NULL auto_increment,
    `url` varchar(255) NOT NULL default '',
    `content` text not NULL default '',
    `crc` varchar(64) NOT NULL default '',
    `updated` datetime,
    INDEX(`crc`),
    UNIQUE KEY (`crc`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `updated_places`;
CREATE TABLE `updated_places`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `anec` int NOT NULL default 0,
    `vclub` int NOT NULL default 0,
    INDEX(`uid`),
    UNIQUE KEY (`uid`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
insert into updated_places (uid) select id from users;

DROP TABLE `mastermind_wins`;
CREATE TABLE `mastermind_wins`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `tries` int NOT NULL default 0,
    `total_time` int NOT NULL default 0,
    `points` int NOT NULL default 0,
    `added` timestamp not null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `vclub_not_ended`;
CREATE TABLE `vclub_not_ended`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `video_id` int NOT NULL default 0,
    `series` int NOT NULL default 0,
    `end_time` int NOT NULL default 0,
    `added` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `course_cache`;
CREATE TABLE `course_cache`(
    `id` int NOT NULL auto_increment,
    `url` varchar(255) NOT NULL default '',
    `content` text not NULL default '',
    `crc` varchar(64) NOT NULL default '',
    `updated` datetime,
    INDEX(`crc`),
    UNIQUE KEY (`crc`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*DROP TABLE `vclub_vote`;
CREATE TABLE `vclub_vote`(
    `id` int NOT NULL auto_increment,
    `media_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `vote_type` varchar(64) NOT NULL default '',
    `good` int NOT NULL default 0,
    `bad` int NOT NULL default 0,
    `added` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;*/

DROP TABLE `recipe_cats`;
CREATE TABLE `recipe_cats`(
    `id` int NOT NULL auto_increment,
    `title` varchar(128) NOT NULL default '',
    `num` int NOT NULL default 0,
    UNIQUE KEY (`title`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `recipes`;
CREATE TABLE `recipes`(
    `id` int NOT NULL auto_increment,
    `recipe_cat_id_1` int NOT NULL default 0,
    `recipe_cat_id_2` int NOT NULL default 0,
    `recipe_cat_id_3` int NOT NULL default 0,
    `recipe_cat_id_4` int NOT NULL default 0,
    `name` varchar(255) NOT NULL default '',
    `descr` text not NULL default '',
    `ingredients` text not NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `fav_recipes`;
CREATE TABLE `fav_recipes`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `fav_recipes` text NOT NULL default '',
    `addtime` datetime,
    `edittime` datetime,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `stb_played_video`;
CREATE TABLE `stb_played_video`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `video_id` int NOT NULL default 0,
    `playtime` timestamp default null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `storage_cache`;
CREATE TABLE `storage_cache`(
    `id` int NOT NULL auto_increment,
    `cache_key` varchar(64) NOT NULL default '',
    `media_type` varchar(64) NOT NULL default '',
    `media_id` int NOT NULL default 0,
    `storage_name` varchar(255) NOT NULL default '',
    `storage_data` text NOT NULL,
    `status` tinyint default 1,
    `changed` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`cache_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `master_log`;
CREATE TABLE `master_log`(
    `id` int NOT NULL auto_increment,
    `log_txt` varchar(255) NOT NULL default '',
    `added` datetime,
    PRIMARY KEY (`id`),
    INDEX(`added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `acl_roles`;
CREATE TABLE `acl_roles`(
    `id` int NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `parent_id` int NOT NULL default 0,
    `rights` text NOT NULL default '',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `acl_resources`;
CREATE TABLE `acl_resources`(
    `id` int NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `actions` text NOT NULL default '',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `media_claims`;
CREATE TABLE `media_claims`(
    `id` int NOT NULL auto_increment,
    `media_type` varchar(64) NOT NULL default '',
    `media_id` int NOT NULL default 0,
    `sound_counter` int NOT NULL default 0,
    `video_counter` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `media_claims_log`;
CREATE TABLE `media_claims_log`(
    `id` int NOT NULL auto_increment,
    `media_type` varchar(64) NOT NULL default '',
    `media_id` int NOT NULL default 0,
    `type` varchar(128) NOT NULL default '',
    `uid` int NOT NULL default 0,
    `added` timestamp not null,
    PRIMARY KEY (`id`),
    INDEX(`added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `daily_media_claims`;
CREATE TABLE `daily_media_claims`(
    `id` int NOT NULL auto_increment,
    `date` date NOT NULL default 0,
    `vclub_sound` int NOT NULL default 0,
    `vclub_video` int NOT NULL default 0,
    `itv_sound` int NOT NULL default 0,
    `itv_video` int NOT NULL default 0,
    `karaoke_sound` int NOT NULL default 0,
    `karaoke_video` int NOT NULL default 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `epg_setting`;
CREATE TABLE `epg_setting`(
    `id` int NOT NULL auto_increment,
    `uri` varchar(255) NOT NULL default '',
    `etag` varchar(255) NOT NULL default '',
    `updated` datetime,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `stb_groups`;
CREATE TABLE `stb_groups`(
    `id` int NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `stb_in_group`;
CREATE TABLE `stb_in_group`(
    `id` int NOT NULL auto_increment,
    `stb_group_id` int NOT NULL default 0,
    `uid` int NOT NULL default 0,
    `mac` varchar(64) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `playlists`;
CREATE TABLE `playlists`(
    `id` int NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `group_id` int NOT NULL default 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `playlist_members`;
CREATE TABLE `playlist_members`(
    `id` int NOT NULL auto_increment,
    `playlist_id` int NOT NULL default 0,
    `time` int NOT NULL default -1, /* minutes from day beginning */
    `video_id` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE `testers`;
CREATE TABLE `testers`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(64) NOT NULL default '',
    `status` tinyint default 1,
    UNIQUE KEY (`mac`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `generation_time`(
    `time` varchar(32),
    `counter` int unsigned NOT NULL default 0,
    INDEX(`time`)
) ENGINE=MEMORY;

INSERT INTO `generation_time` (`time`) values ('0ms'), ('100ms'), ('200ms'), ('300ms'), ('400ms'), ('500ms');

CREATE TABLE `weatherco_cache`(
    `id` int NOT NULL auto_increment,
    `city_id` int NOT NULL default 0,
    `current` text,
    `forecast` text,
    `updated` datetime,
    UNIQUE KEY (`city_id`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tv_reminder`;
CREATE TABLE `tv_reminder`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(64) NOT NULL default '',
    `ch_id` int NOT NULL default 0,
    `tv_program_id` int NOT NULL default 0,
    `fire_time` timestamp not null,
    `added` datetime,
    PRIMARY KEY (`id`),
    INDEX `tv_program_id` (`tv_program_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries`(
    `id` int NOT NULL,
    `iso2` varchar(8) NOT NULL default '',
    `iso3` varchar(8) NOT NULL default '',
    `name` varchar(64) NOT NULL default '',
    `name_en` varchar(64) NOT NULL default '',
    `region` varchar(64) NOT NULL default '',
    `region_id` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities`(
    `id` int NOT NULL,
    `name` varchar(64) NOT NULL default '',
    `name_en` varchar(64) NOT NULL default '',
    `region` varchar(64) NOT NULL default '',
    `country` varchar(64) NOT NULL default '',
    `country_id` int NOT NULL default 0,
    `timezone` varchar(64) NOT NULL default '',
    PRIMARY KEY (`id`),
    INDEX `country_id` (`country_id`),
    INDEX `timezone` (`timezone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;