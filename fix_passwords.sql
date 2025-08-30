-- Fix password hashes in database
-- Run this in phpMyAdmin or MySQL console

-- Update admin@prolinege.com password to 'admin123'
UPDATE users SET password = '$2y$10$S/uCFgZl7NR6FLbI.isGBeAW5LTxdMtn8gi6JHrgpfA4JxbMz/o2a' WHERE email = 'admin@prolinege.com';

-- Update info@prolinege.com password to '123456'
UPDATE users SET password = '$2y$10$8cFTd2sSsxW/8UamGJr7LeAeMSKmo3cqLSj16Fdh90qZKLjdz07ie' WHERE email = 'info@prolinege.com';

-- Verify the updates
SELECT id, username, email, role, LEFT(password, 20) as password_start FROM users;

-- Success message
SELECT 'Password hashes updated successfully!' as message;