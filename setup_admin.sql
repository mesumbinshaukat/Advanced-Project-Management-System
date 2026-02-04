INSERT IGNORE INTO auth_groups_users (user_id, `group`, created_at) VALUES (1, 'admin', NOW());
UPDATE auth_identities SET secret = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE user_id = 1 AND type = 'email_password';
