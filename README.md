# test-assignment-api

This repository contains the backend implementation for a Product Management System. The system is designed to manage product data, including SKUs, names, prices, and specific attributes based on the product type.

## Backend Requirements

The backend of the Product Management System follows the principles of Object-Oriented Programming (OOP) and adheres to the PSR standards. It provides an API to perform Create, Read, and batch Delete operations on products.

**Key Features:**

- Utilizes OOP principles for code organization.
- Handles differences in product types without using conditional statements.
- Provides a single endpoint for product saving.
- Supports PHP ^7.0 and MySQL ^5.6.
- Implements error handling and input sanitization & validation.

## Local Testing Instructions

To test the backend locally, follow these steps:

1. Clone the repository to your local machine.
   git clone git@github.com:MhamadR/test-assignment-api.git
2. cd test-assignment-api
   composer install
3. Create a .env file in the root directory based on the provided .env.example. Set your database credentials and other environment variables.
4. Set Up the Database.
   Ensure you have a MySQL server running. Create a new database using the name specified in your .env file.
5. Import Database Schema.
   Import the database schema from the provided SQL file schema.sql into your created database.
6. Configure Web Server.
   For local testing, you can use the built-in PHP development server or your preferred web server.
7. Access the API.
   Access the API using your preferred API client (e.g., Postman, cURL) at http://localhost:8000.

   API Endpoints:
   GET /products: Get a list of all products.
   POST /products: Add a new product.
   {
        "sku": "JVC200123",
        "name": "Acme Disc",
        "price": "1",
        "type": "DVD",
        "size": "700"
   }
   DELETE /products: Delete products based on an array of IDs.
   {"ids": ["1", "2"]}

##Hosting Information
The backend API is hosted at: https://assignment00011.000webhostapp.com/products

Please note that the hosting provider, 000webhost.com, does not fully support the DELETE method. Therefore, the DELETE logic has been implemented within the POST method for compatibility.
