-- --------- PHASE ONE ---------------
--  CREATE SQL STATEMENTS : QUESTION 2C

-- DROP TABLES IF THEY EXIST
USE ttmpofu;
SET SQL_SAFE_UPDATES = 0;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Shopping_carts;
DROP TABLE IF EXISTS Customers;
DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS Products;
DROP TABLE IF EXISTS Categories;
DROP TABLE IF EXISTS Product_history;
DROP TABLE IF EXISTS Cart_contains;
DROP TABLE IF EXISTS Order_info;
DROP TABLE IF EXISTS Order_items;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Employees
CREATE TABLE Employees(
    employee_id INT PRIMARY KEY AUTO_INCREMENT,
    user_name VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password CHAR(64) NOT NULL,
    new_account BOOL NOT NULL DEFAULT TRUE
);

-- 2. Customers
CREATE TABLE Customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    user_name VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password CHAR(64) NOT NULL,   -- have to create this using SHA256
    address VARCHAR(255) NOT NULL
);

-- 3. Categories
CREATE TABLE Categories (
	name VARCHAR(50) PRIMARY KEY,
    description TEXT
);

-- 4. Products
CREATE TABLE Products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_name VARCHAR(50) NOT NULL REFERENCES Categories(name),
    price DECIMAL(10, 2) NOT NULL CHECK (price > 0), -- refer to lab5
    stock_qty INT NOT NULL CHECK (stock_qty >= 0),
    image VARCHAR(50), -- Link to an image
    restock_quantity INT NOT NULL CHECK (restock_quantity >= 0),
    discontinued BOOL NOT NULL,
    last_modified TIMESTAMP,
    last_modified_by INT REFERENCES Employees(employee_id)
);
--  have to add something about referential integrity constraints for tables: products, category etc
    
-- 5. Shopping_carts
CREATE TABLE Shopping_carts (
    cart_id INT PRIMARY KEY REFERENCES Customers(customer_id)
);
CREATE TABLE Cart_contains (
	cart_id INT NOT NULL,
    product_id INT NOT NULL REFERENCES Products(product_id),
    quantity INT NOT NULL CHECK (quantity > 0),
    PRIMARY KEY (cart_id, product_id),
    FOREIGN KEY (cart_id) REFERENCES Shopping_carts(cart_id) ON DELETE CASCADE
);

-- 6. Order_info
CREATE TABLE Order_info (
	order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL REFERENCES Customers(customer_id),
    date DATE NOT NULL,
    status ENUM('Placed', 'En Route', 'Delivered', 'Cancelled', 'Returned') NOT NULL,
    total DECIMAL(10, 2) NOT NULL CHECK (total > 0)
);

-- 7. Order_items
CREATE TABLE Order_items (
	order_id INT NOT NULL REFERENCES Order_info(order_id),
    product_id INT NOT NULL REFERENCES Product(product_id),
    quantity INT NOT NULL CHECK (quantity > 0),
    price DECIMAL(10, 2) NOT NULL CHECK (price > 0),
    PRIMARY KEY (order_id, product_id)
);
-- 8. Product_history
CREATE TABLE Product_history (
	action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    product_id INT NOT NULL REFERENCES Products(product_id),
    time TIMESTAMP NOT NULL,
    order_id INT REFERENCES Order_info(customer_id),
    employee_id INT REFERENCES Employees(employee_id),
    old_price DECIMAL(10, 2) CHECK (old_price > 0),
    new_price DECIMAL(10, 2) CHECK (new_price > 0),
    old_quantity INT CHECK (old_quantity > 0),
    new_quantity INT CHECK (new_quantity > 0),
    PRIMARY KEY (product_id, time)
);

-- VIEW AVAILABLE TABLES
select *
from Products;
SHOW TABLES;

UPDATE Products
SET image = 'images/granola.jpg'
WHERE product_id = 110;







