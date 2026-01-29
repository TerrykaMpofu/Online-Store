SET SQL_SAFE_UPDATES = 0;

SET @BLOCK_PRODUCT_DELETION = 0;
DELETE FROM Employees;
DELETE FROM Customers;
DELETE FROM Categories;
DELETE FROM Products;
DELETE FROM Shopping_carts;
DELETE FROM Cart_contains;
DELETE FROM Order_info;
DELETE FROM Order_items;
DELETE FROM Product_history;
SET @BLOCK_PRODUCT_DELETION = 1;

# Employees
CALL create_employee(
200,
'angela',
'angela@jimmy.com',
'yuqgfvyreufq#'
);
CALL create_employee(
201,
'mikey',
'mike@jimmy.com',
'Mike234$'
);
UPDATE Employees
	SET new_account = false
	WHERE employee_id = 201;

# Customers
INSERT INTO Customers VALUES(
	3001,
	'terry',
	'Terence',
	'Mpofu',
	'terry@gmail.com',
	SHA2('Terry@123', 256),
	'2009 Woodmar Dr,Houghton, MI'
), (
	3002,
	'Dani',
	'Dani',
	'Reed',
	'dani@gmail.com',
	SHA2('dAni123#', 256),
	'23 Columbus Ave, Hancock, MI'
), (
	3003,
	'doomsday',
	'Tendai',
	'Ncube',
	'tendai.ncube@gmail.com',
	SHA2('WhatWhat123$', 256),
	'1900 Woodmar Dr, Houghton, MI'
);

# Categories
CALL insert_category('apparel', 'Clothing and accessories');
CALL insert_category('kitchen', 'Cookware and utensils');
CALL insert_category('electronics', 'Gadgets and accessories');
CALL insert_category('grocery', 'Food items');

# Products
CALL insert_product(
	101,
	'Classic Beanie',
	'Warm knit hat',
	'apparel',
	20,
	35,
    'beanie.jpg',
	10,
	false
);
CALL insert_product(
	102,
	'Graphic T-Shirt',
	'Cotton tee, unisex',
	'apparel',
	18,
	10,
    NULL,
	15,
	false
);
CALL insert_product(
	103,
	'Chef’s Pot (5qt)',
	'Stainless steel stock pot',
	'kitchen',
	80,
	20,
    'pot.jpg',
	8,
	true
);
CALL insert_product(
	104,
	'Nonstick Fry Pan (10\")',
	'Aluminum, PFOA-free',
	'kitchen',
	45,
	25,
    NULL,
	10,
	false
);
CALL insert_product(
	106,
	'Wireless Earbuds',
	'BT 5.3, charging case',
	'electronics',
    60,
	100,
    NULL,
	15,
	true
);
CALL insert_product(
	107,
	'USB-C Cable (1m)',
	'60W fast-charge',
	'electronics',
	9.99,
	29,
    NULL,
	30,
	true
);
CALL insert_product(
	108,
	'Orange (bag, 1kg)',
	'Fresh citrus',
	'grocery',
	5,
	42,
    'orange.jpg',
	25,
	false
);
CALL insert_product(
	105,
	'Electric Kettle',
	'1.7L auto-shutoff',
	'electronics',
	39.99,
	20,
	NULL,
	12,
	false
);
CALL insert_product(
	109,
	'Arabica Coffee (1lb)',
	'Medium roast',
	'grocery',
	14,
	42,
    'coffee.jpg',
	20,
	false
);
CALL insert_product(
	110,
	'Organic Granola (500g)',
	'Honey-almond',
	'grocery',
	8.50,
	99,
    NULL,
	20,
	false
);

# Shopping Carts
INSERT INTO Shopping_carts VALUES(3001), (3002), (3003);

# Cart Items
INSERT INTO Cart_contains VALUES(3001, 101, 1),
(3001, 103, 1),
(3001, 108, 3),
(3002, 106, 1),
(3002, 107, 2);

# Order Info
INSERT INTO Order_info VALUES(
	7001,
	3001,
	'2023-10-01',
	'Placed',
	115.0
), (
	7002,
	3002,
	'2023-10-02',
	'Placed',
	45.0
), (
	7003,
	3003,
	'2023-10-03',
	'Placed',
	59.0
), (
	7004,
	3001,
	'2023-10-04',
	'Delivered',
	28.0
), (
	7005,
	3002,
	'2023-10-05',
	'Delivered',
	14.0
), (
	7006,
	3003,
	'2023-10-06',
	'Delivered',
	98.0
), (
	7007,
	3001,
	'2023-10-07',
	'Delivered',
	39.0
), (
	7008,
	3002,
	'2023-10-08',
	'En Route',
	27.0
), (
	7009,
	3003,
	'2023-10-09',
	'Returned',
	59.0
), (
	7010,
	3001,
	'2023-10-10',
	'Delivered',
	18.0
);

# Order Items
INSERT INTO Order_items VALUES(
	7001,
	101,
	1,
	20.0
), (
	7001,
	103,
	1,
	80.0
), (
	7001,
	108,
	3,
	5.0
), (
	7002,
	104,
	1,
	45.0
), (
	7003,
	106,
	1,
	59.0
), (
	7004,
	110,
	2,
	14.0
), (
	7005,
	109,
	1,
	14.0
), (
	7006,
	105,
	2,
	39.0
), (
	7006,
	107,
	2,
	10.0
), (
	7007,
	105,
	1,
	39.0
), (
	7008,
	107,
	3,
	9.0
), (
	7009,
	106,
	1,
	59.0
), (
	7010,
	102,
	1,
	18.0
);

# Product History
CALL log_product_update(
	102,
	'UPDATE',
	17,
	18,
	20,
	20,
	201,
	NULL
);
CALL log_product_update(
	110,
	'INSERT',
	NULL,
	8.50,
	NULL,
	99,
	201,
	NULL
);

CALL checkout(3001, @a, @b);
SELECT @a, @b;

SET SQL_SAFE_UPDATES = 1;

SELECT * FROM Employees;
SELECT * FROM Customers;
SELECT * FROM Categories;
SELECT * FROM Products;
SELECT * FROM Shopping_carts;
SELECT * FROM Cart_contains;
SELECT * FROM Order_info;
SELECT * FROM Order_items;
SELECT * FROM Product_history;