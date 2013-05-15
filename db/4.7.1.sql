set names utf8;

update cat_genre set title='biography' where title='биография';
update cat_genre set title='action' where title='боевик';
update cat_genre set title='western' where title='вестерн';
update cat_genre set title='military' where title='военный';
update cat_genre set title='detective' where title='детектив';
update cat_genre set title='children\'s' where title='детское';
update cat_genre set title='house/country' where title='дом/дача';
update cat_genre set title='drama' where title='драма';
update cat_genre set title='foreign' where title='зарубежные';
update cat_genre set title='health' where title='здоровье';
update cat_genre set title='art' where title='искусство';
update cat_genre set title='historical' where title='исторический';
update cat_genre set title='history' where title='история';
update cat_genre set title='yoga' where title='йога';
update cat_genre set title='catastrophe' where title='катастрофы';
update cat_genre set title='comedy' where title='комедия';
update cat_genre set title='criminal' where title='криминал';
update cat_genre set title='cookery' where title='кулинария';
update cat_genre set title='melodrama' where title='мелодрама';
update cat_genre set title='mysticism' where title='мистика';
update cat_genre set title='musical' where title='музыкальный';
update cat_genre set title='science' where title='наука';
update cat_genre set title='ours' where title='наши';
update cat_genre set title='teach' where title='обучающее';
update cat_genre set title='teach' where title='обучающие';
update cat_genre set title='hunting' where title='охота';
update cat_genre set title='adventure' where title='приключения';
update cat_genre set title='nature' where title='природа';
update cat_genre set title='travels' where title='путешествия';
update cat_genre set title='fishing' where title='рыбалка';
update cat_genre set title='series' where title='сериал';
update cat_genre set title='sketch-show' where title='скетч-шоу';
update cat_genre set title='sport' where title='спорт';
update cat_genre set title='dancing' where title='танцы';
update cat_genre set title='show' where title='телешоу';
update cat_genre set title='technique' where title='техника';
update cat_genre set title='thriller' where title='триллер';
update cat_genre set title='horror' where title='ужасы';
update cat_genre set title='fiction' where title='фантастика';
update cat_genre set title='aerobics' where title='фитнес';
update cat_genre set title='fantasy' where title='фэнтези';
update cat_genre set title='erotica' where title='эротика';
update cat_genre set title='humourist' where title='юмористы';

update media_category set category_name='Cartoon' where category_name='Мультфильмы';
update media_category set category_name='Ours cinema' where category_name='Наше кино';
update media_category set category_name='World cinema' where category_name='Мировое кино';
update media_category set category_name='Documentary' where category_name='Документальное';
update media_category set category_name='Humour' where category_name='Юмор';
update media_category set category_name='Hobbies' where category_name='Увлечения';
update media_category set category_name='Our Serial' where category_name='Наш Сериал';
update media_category set category_name='Foreign Serial' where category_name='Зарубежный Сериал';

update genre set title='Action' where title='Боевик';
update genre set title='Detective' where title='Детектив';
update genre set title='Documentary' where title='Документальный';
update genre set title='Drama' where title='Драма';
update genre set title='Historical' where title='Исторический';
update genre set title='Clips' where title='Клипы';
update genre set title='Comedy' where title='Комедия';
update genre set title='Melodrama' where title='Мелодрама';
update genre set title='Humour' where title='Юмор';
update genre set title='Adventures' where title='Приключения';
update genre set title='Thriller' where title='Триллер';
update genre set title='Horrors' where title='Ужасы';
update genre set title='Fiction' where title='Фантастика';
update genre set title='Erotica' where title='Эротика';
update genre set title='Fantasy' where title='Фэнтези';
update genre set title='Animation' where title='Анимация';
update genre set title='Childrens' where title='Детский';
update genre set title='Musical' where title='Музыкальный';
update genre set title='Western' where title='Вестерн';
update genre set title='Serial' where title='Сериал';
update genre set title='Sports' where title='Спорт';
update genre set title='Teach' where title='Обучающее';
update genre set title='Information' where title='Информационный';

update tv_genre set title='information' where title='информационный';
update tv_genre set title='entertainments' where title='развлечения';
update tv_genre set title='children\'s' where title='детский';
update tv_genre set title='cinema' where title='кино';
update tv_genre set title='science' where title='наука';
update tv_genre set title='sports' where title='спорт';
update tv_genre set title='music' where title='музыка';
update tv_genre set title='business' where title='бизнес';
update tv_genre set title='culture' where title='культура';
update tv_genre set title='for adults' where title='для взрослых';

update karaoke_genre set title='Pop' where title='Поп';
update karaoke_genre set title='Rock' where title='Рок';
update karaoke_genre set title='Chanson' where title='Шансон';
update karaoke_genre set title='From cartoon films' where title='Из мультфильмов';
update karaoke_genre set title='Nurseries' where title='Детские';
update karaoke_genre set title='National' where title='Народные';
update karaoke_genre set title='Jazz' where title='Джаз';
update karaoke_genre set title='From films' where title='Из кинофильмов';

alter table users add `locale` varchar(32) NOT NULL default '';

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
    INDEX `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table `users` add `city_id` int NOT NULL default 0;

alter table `weatherco_cache` add `city_id` int NOT NULL default 0;
alter table `weatherco_cache` add UNIQUE INDEX (`city_id`);
alter table `weatherco_cache` drop `url`;
truncate `weatherco_cache`;

alter table `cities` add INDEX `timezone` (`timezone`);

alter table `epg` add `time_tmp` timestamp default 0;
alter table `epg` add `time_to_tmp` timestamp default 0;
update `epg` set `time_tmp` = `time`;
update `epg` set `time_to_tmp` = `time_to`;
alter table `epg` drop `time`;
alter table `epg` drop `time_to`;
alter table `epg` change `time_tmp` `time` timestamp default 0;
alter table `epg` change `time_to_tmp` `time_to` timestamp default 0;

alter table `tv_reminder` modify `fire_time` timestamp default 0;

alter table `users` modify `now_playing_start` timestamp default 0;
alter table `users` modify `time_last_play_tv` timestamp default 0;
alter table `users` modify `time_last_play_video` timestamp default 0;
alter table `users` modify `last_change_status` timestamp default 0;
alter table `users` modify `last_start` timestamp default 0;
alter table `users` modify `last_active` timestamp default 0;
alter table `users` modify `keep_alive` timestamp default 0;

alter table `events` modify `eventtime` timestamp default 0;

alter table `played_video` modify `playtime` timestamp not null;

alter table `rec_files` modify `t_start` timestamp not null;
alter table `rec_files` modify `t_stop` timestamp default 0;

alter table `users_rec` modify `t_start` timestamp not null;
alter table `users_rec` modify `t_stop` timestamp default 0;
alter table `users_rec` modify `end_record` timestamp default 0;

alter table `stream_error` modify `error_time` timestamp not null;

alter table `vclub_paused` modify `pause_time` timestamp not null;

alter table `loading_fail` modify `added` timestamp not null;

alter table `mastermind_wins` modify `added` timestamp not null;

alter table `stb_played_video` modify `playtime` timestamp not null;

alter table `media_claims_log` modify `added` timestamp not null;


