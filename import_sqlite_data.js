const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');

// Sample data to import
const categories = [
    {id: 1, name: 'Elektrik', description: 'Elektrik məhsulları', image: 'category-placeholder.svg'},
    {id: 2, name: 'Mexaniki', description: 'Mexaniki məhsullar', image: 'category-placeholder.svg'},
    {id: 3, name: 'Avtomatlaşdırma', description: 'Avtomatlaşdırma sistemləri', image: 'category-placeholder.svg'}
];

const markas = [
    {id: 1, name: 'Siemens', description: 'Siemens məhsulları', image: 'siemens-logo.svg'},
    {id: 2, name: 'ABB', description: 'ABB məhsulları', image: 'abb-logo.svg'},
    {id: 3, name: 'Schneider', description: 'Schneider məhsulları', image: 'schneider-logo.svg'},
    {id: 4, name: 'Bosch', description: 'Bosch məhsulları', image: 'bosch-logo.svg'},
    {id: 5, name: 'Danfoss', description: 'Danfoss məhsulları', image: 'danfoss-logo.svg'},
    {id: 6, name: 'Honeywell', description: 'Honeywell məhsulları', image: 'honeywell-logo.svg'}
];

const products = [
    {
        id: 1,
        name: 'Siemens S7-1200 PLC',
        description: 'Kompakt avtomatlaşdırma sistemi',
        price: 450.00,
        category_id: 3,
        marka_id: 1,
        image: 'product-placeholder.svg',
        stock: 25,
        status: 'active'
    },
    {
        id: 2,
        name: 'ABB Kontaktor',
        description: 'Yüksək keyfiyyətli elektrik kontaktoru',
        price: 85.50,
        category_id: 1,
        marka_id: 2,
        image: 'product-placeholder.svg',
        stock: 50,
        status: 'active'
    },
    {
        id: 3,
        name: 'Schneider Çərçivə',
        description: 'Elektrik paneli üçün çərçivə',
        price: 125.00,
        category_id: 1,
        marka_id: 3,
        image: 'product-placeholder.svg',
        stock: 30,
        status: 'active'
    },
    {
        id: 4,
        name: 'Bosch Sensor',
        description: 'Yüksək dəqiqlikli sensor',
        price: 75.25,
        category_id: 2,
        marka_id: 4,
        image: 'product-placeholder.svg',
        stock: 40,
        status: 'active'
    },
    {
        id: 5,
        name: 'Danfoss Frekans Çeviricisi',
        description: 'Motor sürücü sistemi',
        price: 320.00,
        category_id: 3,
        marka_id: 5,
        image: 'product-placeholder.svg',
        stock: 15,
        status: 'active'
    }
];

// Connect to database
const db = new sqlite3.Database('./database/proline.db', (err) => {
    if (err) {
        console.error('Error opening database:', err.message);
        return;
    }
    console.log('Connected to SQLite database successfully!');
});

// Clear existing data and import new data
db.serialize(() => {
    console.log('Clearing existing data...');
    
    // Clear tables
    db.run('DELETE FROM products');
    db.run('DELETE FROM categories');
    db.run('DELETE FROM markas');
    
    // Insert categories
    console.log('Importing categories...');
    const categoryStmt = db.prepare('INSERT OR REPLACE INTO categories (id, name, description, image, created_at, updated_at) VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))');
    categories.forEach(cat => {
        categoryStmt.run(cat.id, cat.name, cat.description, cat.image);
    });
    categoryStmt.finalize();
    
    // Insert markas
    console.log('Importing markas...');
    const markaStmt = db.prepare('INSERT OR REPLACE INTO markas (id, name, description, image, created_at, updated_at) VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))');
    markas.forEach(marka => {
        markaStmt.run(marka.id, marka.name, marka.description, marka.image);
    });
    markaStmt.finalize();
    
    // Insert products
    console.log('Importing products...');
    const productStmt = db.prepare('INSERT OR REPLACE INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))');
    products.forEach(product => {
        productStmt.run(product.id, product.name, product.description, product.price, product.category_id, product.marka_id, product.image, product.stock, product.status);
    });
    productStmt.finalize();
    
    // Verify import
    console.log('\n=== Verification ===');
    
    db.get('SELECT COUNT(*) as count FROM categories', (err, row) => {
        if (err) console.error(err);
        else console.log(`Categories imported: ${row.count}`);
    });
    
    db.get('SELECT COUNT(*) as count FROM markas', (err, row) => {
        if (err) console.error(err);
        else console.log(`Markas imported: ${row.count}`);
    });
    
    db.get('SELECT COUNT(*) as count FROM products', (err, row) => {
        if (err) console.error(err);
        else console.log(`Products imported: ${row.count}`);
        
        console.log('\nData import completed successfully!');
        db.close();
    });
});