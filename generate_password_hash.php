<?php

// Test password hashing and verification
$password1 = 'admin123';
$password2 = '123456';

// Generate new hashes
$hash1 = password_hash($password1, PASSWORD_DEFAULT);
$hash2 = password_hash($password2, PASSWORD_DEFAULT);

echo "New password hashes:\n";
echo "admin123: $hash1\n";
echo "123456: $hash2\n\n";

// Test existing hashes from database
$existing_hash1 = '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW';
$existing_hash2 = '$2y$10$f2QJGX73hgvlXoqC/uXMmOZvsa5720aU7x6LdU/7Wj62.1CJHQYjO';

echo "Testing existing hashes:\n";
echo "admin123 vs existing hash1: " . (password_verify($password1, $existing_hash1) ? 'MATCH' : 'NO MATCH') . "\n";
echo "123456 vs existing hash2: " . (password_verify($password2, $existing_hash2) ? 'MATCH' : 'NO MATCH') . "\n\n";

// Test new hashes
echo "Testing new hashes:\n";
echo "admin123 vs new hash1: " . (password_verify($password1, $hash1) ? 'MATCH' : 'NO MATCH') . "\n";
echo "123456 vs new hash2: " . (password_verify($password2, $hash2) ? 'MATCH' : 'NO MATCH') . "\n";

?>