-- STORED PROCEDURES
 -- create_employee() – Creates a new employee account, requiring the password to be reset upon first login.
USE ttmpofu;

DROP PROCEDURE IF EXISTS create_employee;
DELIMITER //
CREATE PROCEDURE create_employee(
IN new_id INT,
IN new_username VARCHAR(50),
IN email VARCHAR(100),
IN passwd CHAR(64)
)
BEGIN
START TRANSACTION;
INSERT INTO Employees(employee_id, user_name, email, password, new_account)
	VALUES (new_id, new_username, email, SHA2(passwd,256), true);	
COMMIT;
END //
DELIMITER ;

-- insert_category() – Creates a new product category.

DROP PROCEDURE IF EXISTS insert_category;
DELIMITER //
CREATE PROCEDURE insert_category(
IN new_cat_name VARCHAR(50),
IN new_cat_description TEXT
)
BEGIN
START TRANSACTION;
INSERT INTO Categories(name, description)
	VALUES (new_cat_name, new_cat_description);	
COMMIT;
END //
DELIMITER ;

-- insert_product() – Inserts a new product into an existing category

DROP PROCEDURE IF EXISTS insert_product;
DELIMITER //
CREATE PROCEDURE insert_product(
IN new_id INT,
IN p_name VARCHAR(100),
IN p_description TEXT,
IN category VARCHAR(50),
IN price DECIMAL(10, 2),
IN stock_qty INT, 
IN new_image BLOB,
IN restock_qty INT ,
IN discontinued BOOL
)
BEGIN
START TRANSACTION;
INSERT INTO Products(product_id, name, description, category_name, price, stock_qty, image, restock_quantity, discontinued, last_modified, last_modified_by)
	VALUES (new_id, p_name, p_description, category, price, stock_qty, new_image, restock_qty, discontinued, NULL, NULL);	
COMMIT;
END //
DELIMITER ;

-- log_product_update() – Inserts a update record in the history table
DROP PROCEDURE IF EXISTS log_product_update;
DELIMITER //
CREATE PROCEDURE log_product_update(
IN in_product_id INT,
IN in_action_type ENUM('INSERT', 'UPDATE', 'DELETE'),
IN in_old_price DECIMAL(10,2),
IN in_new_price DECIMAL(10,2),
IN in_old_stock INT,
IN in_new_stock INT,
IN in_employee_id INT,
IN in_order_id INT
)
BEGIN
START TRANSACTION;
INSERT INTO Product_history(action, product_id, time, order_id, employee_id, old_price, new_price, old_quantity, new_quantity)
	VALUES (in_action_type, in_product_id, CURRENT_TIMESTAMP, in_order_id, in_employee_id, in_old_price, in_new_price, in_old_stock, in_new_stock);
UPDATE Products
	SET last_modified = CURRENT_TIMESTAMP,
    last_modified_by = in_employee_id
	WHERE product_id = in_product_id;
COMMIT;
END //
DELIMITER ;

DROP PROCEDURE IF EXISTS checkout;
DELIMITER //
CREATE PROCEDURE checkout(
IN p_customer_id INT,
OUT p_order_id INT,
OUT p_out_of_stock_product INT
)
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE id INT;
    DECLARE qty INT;
    
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM Cart_contains WHERE cart_id = p_customer_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    SET p_out_of_stock_product = 0;
    
	START TRANSACTION;
	SET p_order_id = (SELECT AUTO_INCREMENT
			FROM INFORMATION_SCHEMA.TABLES
			WHERE TABLE_SCHEMA = 'ttmpofu' AND TABLE_NAME = 'Order_info');
	INSERT INTO Order_info(order_id, customer_id, date, status, total) VALUES(p_order_id, p_customer_id, CURRENT_DATE, 'Placed', (SELECT sum(price * quantity) FROM Cart_contains NATURAL JOIN Products WHERE cart_id = p_customer_id));
    
    OPEN cur;
    lup: LOOP
		FETCH NEXT FROM cur INTO id, qty;
        IF done THEN LEAVE lup; END IF;
        
        IF (SELECT stock_qty FROM Products WHERE product_id = id) >= qty THEN
			INSERT INTO Order_items(order_id, product_id, quantity, price) VALUES(p_order_id, id, qty, (SELECT price FROM Products WHERE product_id = id) * qty);
            UPDATE Products
				SET stock_qty = stock_qty - qty
				WHERE product_id = id;
		ELSE
			ROLLBACK;
            SET p_out_of_stock_product = 1;
            LEAVE lup;
		END IF;
	END LOOP lup;
    CLOSE cur;
    
    IF NOT p_out_of_stock_product THEN
		DELETE FROM Shopping_carts WHERE cart_id = p_customer_id; # This will cascade to all its contents
        COMMIT;
    END IF;
END //
DELIMITER ;

DROP TRIGGER IF EXISTS Update_restrict;
DELIMITER //
CREATE TRIGGER Update_restrict
BEFORE UPDATE ON Products
FOR EACH ROW
BEGIN
	IF NEW.product_id != OLD.product_id THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The prod id is not allowed to be changed';
    END IF;
END //
DELIMITER ;

DROP TRIGGER IF EXISTS Delete_restrict;
DELIMITER //
CREATE TRIGGER Delete_restrict
BEFORE DELETE ON Products
FOR EACH ROW
BEGIN
	SET @BLOCK_PRODUCT_DELETION = ifnull(@BLOCK_PRODUCT_DELETION, 1);
	IF @BLOCK_PRODUCT_DELETION THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Products should be marked as discontinued instead of deleted';
    END IF;
END //
DELIMITER ;