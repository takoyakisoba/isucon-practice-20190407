ALTER TABLE login_log ADD INDEX (ip,succeeded );
ALTER TABLE login_log ADD INDEX (user_id,succeeded);
