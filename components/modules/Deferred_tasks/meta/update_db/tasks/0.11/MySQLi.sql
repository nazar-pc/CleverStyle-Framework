ALTER TABLE `[prefix]deferred_tasks_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]deferred_tasks_tasks`;
OPTIMIZE TABLE `[prefix]deferred_tasks_tasks`;
