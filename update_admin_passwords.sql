-- Update admin passwords with correct hashes
-- Run this in phpMyAdmin to fix login issues

-- Update admin@prolinege.com password to 'admin123'
UPDATE `users` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE `email` = 'admin@prolinege.com';

-- Update info@prolinege.com password to '123456'
UPDATE `users` SET `password` = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' WHERE `email` = 'info@prolinege.com';

-- Verify the updates
SELECT id, username, email, role, status FROM `users` WHERE role = 'admin';

-- Show success message
SELECT 'Admin passwords updated successfully!' as message;