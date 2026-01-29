# Online-Store
#Phase 1
An online store, also known as e-commence store, allows customers to browse and purchase items while providing secure payment processing and shipping.
In this project,we  designed and implemented a simplified online store. In our online store, there will be numerous products in many categories. Customers can browse the products, add them to their shopping carts, and
proceed to checkout.

The online store system supports two user roles: employee and customer. Administrators are responsible for creating employee accounts and assigning employee IDs. Customers, on the other hand, will create their own accounts when registering as new users. The system distinguishes between different types of accounts. The administrator account is a database-level account with direct access to manage the database, including creating employee accounts and performing other administrative tasks. In contrast, employee and customer accounts are application-level accounts, which interact with the online store through the
application interface, allowing employees to manage products and customers to shop and manage their orders.

Function for customers
1) Register as a new customer.
2) Browse categories and products.
3) Add products to the shopping cart.
4) View and update the shopping cart.
5) Checkout.
6) View previous orders.

Function for employees
1) Insert new category.
2) Insert new product.
3) Update the product information, such as price changes or restocking.
4) Generate various reports.

Core Information in the database
In our extremely online store, we need to handle various types of data.
1) employee information:
• Includes employee ID (assigned by the company), username, email, and password.
• Employee accounts are created by the DBA. They inform the employee their account name and temp password.
• DBA makes the setting such that the employee will be enforced to update their password upon first login.
• The password should be stored as hash value using SHA-256 or other hashing alg.

2) Category information:
• Includes the category’s name and description.

3) Product information:
• Includes product ID, name, description, price, advising threshold for stocking quantity, the actual stock quantity, and an associated image.
• Each product is assigned to a specific category.
• Employee will insert or update product information as needed. We keep track who performed the action along with the time of the action.
• Discontinued products are marked as “discontinued”, not deleted.
• Product change history is maintained.

4) Customer information:
• Include id (auto-assigned), username (chosen by the customer), password,
first name, last name, email, shipping address. The username serves as the
customer's login name. The password should be stored as hash value using
SHA-256.


5) Shopping cart:
• One cart for each customer. The content of the shopping cart should be stored on the server database.
• Cart is not an order
• No stick is deducted until checkout
• Prices in the cart are for display only, the final price is snapped at checkout into the order

6) Order information:
• This contains information about customer orders. It includes order ID, customer ID, order date, order status, and total order dollars.

7) Order items:
• Stores the product id, quantity, the price at the time of the order.

8) Product history information:
• Each product change must create a history record containing:
    o Action: INSERT, UPDATE, or DELETE
    o Who: Employee or customer responsible
    o When: Timestamp of the change
    o Details: Old and new values of price and stock (if applicable)
• UPDATE is used for both employee edits (price/stock) and customer actions (purchases or returns). Customer updates must also include the order ID for traceability.
• INSERT is used when employees add new products.
• DELETE is used when employees remove a product (the product is marked as
“discontinued”).

#Phase 2
Phase 2 of this project implements the web-based functionality of the online store for both customers and employees on top of the database from Phase 1.
​
On the customer side, the system supports account registration, login and logout, and changing passwords from the main page using session-based authentication. Customers can browse product categories, view product listings even before login, and after logging in they can add items to a shopping cart, view and manage the cart (update quantities, remove items, add new items), and complete a checkout flow that verifies stock, updates inventory, creates orders, and shows the order number on success. Customers can also view their past orders, including high-level order info and detailed line items, through a dedicated “view orders” feature. The interface is designed to be easy to navigate, with clear buttons and labels so users can move between browsing, cart, and account-related functions without extra instructions.
​
On the employee side, the application provides an employee login where first-time users are forced to reset the initial password set by the administrator. After logging in, employees have a main page that allows them to restock products, change product prices, review stock change history (including customer purchases), and review price history with percentage changes over time, with an optional feature to insert new products into the catalog.
​
















