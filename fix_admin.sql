-- Activate the admin user
UPDATE users SET active = 1 WHERE username = 'admin';

-- Update password to 'admin123' (bcrypt hash)
UPDATE auth_identities 
SET secret = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE user_id = 1 AND type = 'email_password';

-- Verify the changes
SELECT u.id, u.username, u.email, u.active, ag.group 
FROM users u 
LEFT JOIN auth_groups_users ag ON u.id = ag.user_id 
WHERE u.username = 'admin';
