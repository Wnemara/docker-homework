CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    registration_date DATE DEFAULT CURRENT_DATE
);

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES customers(id),
    amount DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT NOW()
);

INSERT INTO customers (name, email) VALUES
('John Doe', 'john@example.com'),
('Alice Smith', 'alice@test.com');

INSERT INTO orders (customer_id, amount) VALUES
(1, 150.50),
(2, 99.99);