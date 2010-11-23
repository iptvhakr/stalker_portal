ALTER TABLE moderators_history ADD INDEX msqs (`task_id`, `to_usr`, `readed`);
ALTER TABLE moderator_tasks ADD INDEX to_user (`ended`, `archived`, `to_usr`);
