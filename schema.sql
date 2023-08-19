-- Create the product_types table
CREATE TABLE product_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(255) NOT NULL
);

-- Insert sample types
INSERT INTO product_types (type) VALUES ('DVD'), ('Furniture'), ('Book');

-- Create the product_attributes table
CREATE TABLE product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attribute VARCHAR(255) NOT NULL
);

-- Insert sample attributes
INSERT INTO product_attributes (attribute) VALUES ('size'), ('weight'), ('height'), ('width'), ('length');

-- Create the products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    product_type_id INT NOT NULL,
    FOREIGN KEY (product_type_id) REFERENCES product_types(id) ON UPDATE RESTRICT ON DELETE RESTRICT
);

-- Create the product_attribute_associations table
CREATE TABLE product_attribute_associations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value DECIMAL(10, 0) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES product_attributes(id) ON UPDATE RESTRICT ON DELETE RESTRICT
);

-- Insert sample products
INSERT INTO products (sku, name, price, product_type_id)
VALUES
    ('JVC200123', 'Acme Disc', 1, 1), -- DVD
    ('GGWP0007', 'War and Peace', 20, 3), -- Book
    ('TR120555', 'Chair', 40, 2); -- Furniture

-- Insert product attributes
INSERT INTO product_attribute_associations (product_id, attribute_id, value)
VALUES
    (1, 1, 700), -- DVD Size
    (2, 2, 2), -- Book Weight
    (3, 3, 24), -- Furniture Height
    (3, 4, 45), -- Furniture Width
    (3, 5, 15); -- Furniture Length
