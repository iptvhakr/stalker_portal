--

ALTER TABLE `ch_links` ADD `enable_balancer_monitoring` tinyint default 0;
ALTER TABLE `ch_link_on_streamer` ADD `monitoring_status` tinyint default 1;

DELETE FROM `ch_links` WHERE `ch_id` not in (SELECT `id` FROM `itv`);

--//@UNDO

ALTER TABLE `ch_links` DROP `enable_balancer_monitoring`;
ALTER TABLE `ch_link_on_streamer` DROP `monitoring_status`;

--