const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');

// Connect to database
const db = new sqlite3.Database('./database/proline.db', (err) => {
    if (err) {
        console.error('Error opening database:', err.message);
        return;
    }
    console.log('Connected to SQLite database successfully!');
});

// Create tables
db.serialize(() => {
    console.log('Creating tables...');
    
    // Create categories table
    db.run(`CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`, (err) => {
        if (err) console.error('Error creating categories table:', err);
        else console.log('Categories table created successfully');
    });
    
    // Create markas table
    db.run(`CREATE TABLE IF NOT EXISTS markas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`, (err) => {
        if (err) console.error('Error creating markas table:', err);
        else console.log('Markas table created successfully');
    });
    
    // Create products table
    db.run(`CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        category_id INTEGER,
        marka_id INTEGER,
        image TEXT,
        stock INTEGER DEFAULT 0,
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (marka_id) REFERENCES markas(id)
    )`, (err) => {
        if (err) console.error('Error creating products table:', err);
        else console.log('Products table created successfully');
        
        console.log('\nAll tables created successfully!');
        db.close();
    });
});