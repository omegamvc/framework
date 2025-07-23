<?php

/**
 * Part of Omega - Tests\Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Database\RealDatabase;

use Omega\Database\Query\InnerQuery;
use Omega\Database\Query\Join\InnerJoin;
use Omega\Database\Query\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Test setup class for subquery-related database operations.
 *
 * This class prepares the database schema and populates mock data
 * for tables commonly involved in subqueries, such as users, orders,
 * products, sales, customers, and transactions.
 *
 * Each method is responsible for creating or seeding specific tables,
 * allowing subquery logic to be tested across realistic relational scenarios.
 *
 * Intended for use in test cases involving nested SELECTTs,
 * conditional filtering, aggregations, and joins.
 *
 * @category   Omega\Tests
 * @package    Databse
 * @subpackage RealDatabase
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(InnerQuery::class)]
#[CoversClass(InnerJoin::class)]
#[CoversClass(Select::class)]
class SubQueryTest extends AbstractDatabase
{
    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->createConnection();
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    /**
     * Creates the `users` table schema with columns for id, name, email, and timestamp.
     *
     * @return bool True if the table was created successfully.
     */
    protected function createUserSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                email VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );')
           ->execute();
    }

    /**
     * Creates the `orders` table schema with a foreign key reference to the `users` table.
     *
     * @return void
     */
    private function createOrderSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                total_amount DECIMAL(10, 2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );')
            ->execute();
    }

    /**
     * Creates the `products` table schema to store product names.
     *
     * @return void
     */
    private function createProductSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255)
            );')
            ->execute();
    }

    /**
     * Creates the `sales` table schema to store product sales data,
     * including quantity and price, with a reference to the `products` table.
     *
     * @return void
     */
    private function createSalesSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT,
                quantity INT,
                price DECIMAL(10, 2)
            );')
            ->execute();
    }

    /**
     * Creates the `customers` table schema with name and city fields.
     *
     * @return void
     */
    private function createCustomerSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                city VARCHAR(255)
            );')
            ->execute();
    }

    /**
     * Creates the `transactions` table schema to log customer payments,
     * including amount and transaction date, with a foreign key to `customers`.
     *
     * @return void
     */
    private function createTransactionSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT,
                amount DECIMAL(10, 2),
                transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            );')
            ->execute();
    }

    /**
     * Inserts mock records into the `users` table.
     *
     * @return void
     */
    private function createUsers(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO users (name, email) VALUES
                ("Alice", "alice@example.com"),
                ("Bob", "bob@example.com"),
                ("Charlie", "charlie@example.com")
            ;')
            ->execute();
    }

    /**
     * Inserts mock records into the `orders` table linked to users.
     *
     * @return void
     */
    private function createOrders(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO orders (user_id, total_amount) VALUES
                (1, 1200),
                (2, 800),
                (3, 1500)
            ;')
            ->execute();
    }

    /**
     * Inserts mock records into the `products` table.
     *
     * @return void
     */
    private function createProducts(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO products (name) VALUES
           (\'Laptop\'), (\'Phone\'), (\'Tablet\')
        ;')
            ->execute();
    }

    /**
     * Inserts mock records into the `sales` table with product references and pricing.
     *
     * @return void
     */
    private function createSales(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO sales (product_id, quantity, price) VALUES
            (1, 2, 1000),  -- Laptop
            (2, 3, 800),   -- Phone
            (3, 1, 600);   -- Tablet
        ')
            ->execute();
    }

    /**
     * Inserts mock records into the `customers` table with names and cities.
     *
     * @return void
     */
    private function createCustomers(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO customers (name, city) VALUES
                ("Alice", "New York"),
                ("Bob", "Los Angeles"),
                ("Charlie", "Chicago")
            ;')
            ->execute();
    }

    /**
     * Inserts mock records into the `transactions` table with customer references and timestamps.
     *
     * @return void
     */
    private function createTransactions(): void
    {
        $this
            ->pdo
            ->query('INSERT INTO transactions (customer_id, amount, transaction_date) VALUES
                (1, 600, "2024-12-01 10:00:00"),  -- Alice
                (1, 500, "2024-12-02 12:00:00"),  -- Alice
                (2, 400, "2024-12-01 11:00:00"),  -- Bob
                (3, 800, "2024-12-03 14:00:00");  -- Charlie
            ;')
            ->execute();
    }

    /**
     * Test it can select sub query using where.
     *
     * @return void
     */
    public function testItCanSelectSubQueryUsingWhere(): void
    {
        $this->createUserSchema();
        $this->createOrderSchema();
        $this->createUsers();
        $this->createOrders();

        $users = new Select('users', ['name', 'email'], $this->pdo);
        $users->whereIn('id', (new Select('orders', ['user_id'], $this->pdo))
            ->compare('total_amount', '>', 1000));
        $users = $users->get();

        $this->assertCount(2, $users);
        $this->assertSame('Alice', $users[0]['name']);
        $this->assertSame('Charlie', $users[1]['name']);
    }

    /**
     * Test iti can select subquery using from.
     *
     * @return void
     */
    public function testItCanSelectSubQueryUsingFrom(): void
    {
        $this->createUserSchema();
        $this->createOrderSchema();
        $this->createProductSchema();
        $this->createSalesSchema();
        $this->createUsers();
        $this->createOrders();
        $this->createProducts();
        $this->createSales();

        $products = new Select(
            new InnerQuery(
                (new Select(
                    'sales',
                    ['product_id', 'SUM(quantity) AS total_quantity', 'SUM(quantity * price) AS total_sales'],
                    $this->pdo
                ))->groupBy('product_id'),
                'sub'
            ),
            ['sub.product_id', 'sub.total_quantity', 'sub.total_sales'],
            $this->pdo
        );

        $products = $products->get();

        $this->assertCount(3, $products);
    }

    /**
     * Test it can select sub query using join.
     *
     * @return void
     */
    public function testItCanSelectSubQueryUsingJoin(): void
    {
        $this->createCustomerSchema();
        $this->createTransactionSchema();
        $this->createCustomers();
        $this->createTransactions();

        $customers = new Select(
            'customers',
            ['customers.name', 'sub.total_spent'],
            $this->pdo
        );

        $customers->join(
            InnerJoin::ref(
                new InnerQuery(
                    (new Select(
                        'transactions',
                        ['customer_id', 'SUM(amount) AS total_spent'],
                        $this->pdo
                    ))
                    ->groupBy('customer_id'),
                    'sub'
                ),
                'id',
                'customer_id'
            )
        );

        $customers = $customers->get();

        $this->assertCount(3, $customers);
    }
}
