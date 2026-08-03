Flousi Win — Data generation tool

This folder contains a Python script to generate a realistic dataset for Flousi Win (MySQL 8 compatible).

Files:
- generate_flousi_data.py : Generates users, categories, transactions, goals, financial_reports and writes SQL INSERTs to an output file.

Usage:
1. Make sure you have Python 3.8+ installed.
2. From this folder run:

```powershell
python generate_flousi_data.py --users 1000 --months 1 --tx_per_user 35 --out ../flousi_data.sql
```

This will create `flousi_data.sql` in the repository root (one month of data, ~35k transactions for 1000 users).

SQL schema expectations (minimal tables):
- users
- categories
- goals
- transactions
- financial_reports

Suggested CREATE TABLE DDL (minimal) :

CREATE TABLE `users` (
  `id` INT PRIMARY KEY,
  `first_name` VARCHAR(80),
  `last_name` VARCHAR(120),
  `email` VARCHAR(200),
  `phone` VARCHAR(40),
  `age` TINYINT,
  `profession` VARCHAR(120),
  `city` VARCHAR(120),
  `salary` DECIMAL(10,2),
  `created_at` DATE,
  `is_premium` TINYINT(1)
) ENGINE=InnoDB;

CREATE TABLE `categories` (
  `id` INT PRIMARY KEY,
  `name` VARCHAR(120)
) ENGINE=InnoDB;

CREATE TABLE `goals` (
  `id` INT PRIMARY KEY,
  `user_id` INT,
  `title` VARCHAR(255),
  `target_amount` DECIMAL(12,2),
  `current_amount` DECIMAL(12,2),
  `deadline` DATE,
  `status` VARCHAR(50),
  INDEX(user_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE `transactions` (
  `id` BIGINT PRIMARY KEY,
  `user_id` INT,
  `type` VARCHAR(20),
  `category` VARCHAR(120),
  `amount` DECIMAL(12,2),
  `description` TEXT,
  `date` DATE,
  `payment_method` VARCHAR(50),
  INDEX(user_id),
  INDEX(date),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE `financial_reports` (
  `id` INT PRIMARY KEY,
  `user_id` INT,
  `payload` JSON,
  `score` INT,
  `created_at` DATE,
  INDEX(user_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

Performance & indexing suggestions:
- Index `transactions(user_id, date)` and `transactions(date, category)`.
- Use partitioning by month on large `transactions` table if data grows (PARTITION BY RANGE (TO_DAYS(`date`))).
- Add composite indexes for queries used by the dashboard (eg. (user_id, date) and (user_id, category)).

Cache strategy:
- Cache dashboards and computed summaries per user for 5-15 minutes in Redis.
- Use Redis for user session store and worker queues (Bull or similar via Redis).
- Precompute heavy forecasts in background workers and store results in `forecasts` or `financial_reports` table.

Scaling to 50k users:
- Use read replicas for heavy read queries (dashboards, reports).
- Move heavy analytics to background workers (PHP CLI, Python workers) and cache results.
- Consider using a columnar store or analytics DB for large historical queries.

Security notes:
- Do not store raw sensitive data. Use hashing/encryption for PII where needed.
- Ensure proper DB user privileges for import scripts.

Contact:
If you want, I can also generate the SQL directly and commit `flousi_data.sql` (it will be large ~ tens of MB)."}