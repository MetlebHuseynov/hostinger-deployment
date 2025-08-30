-- Clean duplicate markas from database
-- Run this in phpMyAdmin or MySQL console

-- First, let's see the duplicates
SELECT name, COUNT(*) as count FROM markas GROUP BY name HAVING COUNT(*) > 1;

-- Delete duplicates, keeping only the one with the lowest ID for each name
DELETE m1 FROM markas m1
INNER JOIN markas m2 
WHERE m1.id > m2.id AND m1.name = m2.name;

-- Verify the cleanup
SELECT COUNT(*) as total_markas FROM markas;
SELECT id, name, description FROM markas ORDER BY name;

-- Success message
SELECT 'Duplicate markas cleaned successfully!' as message;