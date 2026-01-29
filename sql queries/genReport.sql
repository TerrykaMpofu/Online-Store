--  ------ Question 5: Write the SQL statements to generate the reports. -------

-- a) List the historic prices for a given product.
SELECT p.name AS product_name,ph.old_price,ph.new_price,ph.time
FROM Product_history ph
JOIN Products p ON ph.product_id = p.product_id
LEFT JOIN Employees e ON ph.employee_id = e.employee_id
WHERE p.name = 'Graphic T-Shirt' -- can change diff products here
ORDER BY p.product_id, ph.time;


-- b) List the highest and lowest price within a given period (start time and end time) for all products.
SELECT p.name AS Product_Name,MIN(ph.new_price) AS Min_Price,MAX(ph.new_price) AS Max_Price
FROM Products p
JOIN Product_history ph ON p.product_id = ph.product_id
GROUP BY p.product_id, p.name
UNION
SELECT p.name AS Product_Name, p.price AS Min_Price, p.price AS Max_Price
FROM Products p
ORDER BY Product_Name;


-- c) List how many qualities sold for each product within a specified time frame. You may ignore the ones that have not be sold.
SELECT p.name AS product_name,SUM(oi.quantity) AS total_sold
FROM Order_items as oi
JOIN Order_info o ON oi.order_id = o.order_id
JOIN Products p ON oi.product_id = p.product_id
WHERE o.date BETWEEN '2023-10-01' AND '2025-08-01'
GROUP BY p.product_id, p.name
ORDER BY total_sold DESC;


-- d) List products below the restocking threshold and the quantity needed to reach the threshold.
SELECT name as Product_name, stock_qty ,restock_quantity,
SUM(restock_quantity - stock_qty) AS quantity_Needed_To_Restock
FROM Products
WHERE stock_qty < restock_quantity
GROUP BY name,stock_qty,restock_quantity;





