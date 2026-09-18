# Floussiwin — Smart Personal Finance for Tunisia

**Floussiwin** is a modern personal finance web application designed to help users manage their income, spending, savings goals, and daily financial habits with clarity and confidence.

Built for the Tunisian market, Floussiwin gives users a simple and elegant way to track their money, set saving targets, and make better financial decisions.

## 🚀 Features

- **Smart Dashboard**: Get a quick view of your balance, recent activity, and financial health.
- **Income & Expense Tracking**: Add, edit, and delete transactions with a clear overview of cash flow.
- **Savings Goals**: Create custom financial goals and monitor progress toward each target.
- **Budget Planning**: Manage recurring expenses and compare planned vs. actual spending.
- **Financial Insights**: Receive recommendations and smart suggestions based on user behavior.
- **Secure Authentication**: Login and registration flow with session protection and CSRF safeguards.
- **Reports & Saving Challenges**: Follow progress through reports, smart saving plans, and financial challenges.
- **Responsive Design**: Optimized for desktop and mobile usage.

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Architecture**: MVC pattern
- **Access Layer**: PDO for secure database interactions

## 📂 Project Structure

```text
.
├── config/                 # Database and app configuration
├── controllers/            # MVC controllers
├── models/                 # Data models
├── services/               # Business logic and finance calculations
├── views/                  # UI templates and pages
├── assets/                 # Static files (CSS, JS, images)
├── db/                     # Database-related files
├── index.php               # Main entry point
├── flousi_data.sql         # Sample dataset
├── flousi_data_phpmyadmin.sql
├── README.md               # Project documentation
└── .htaccess / other config files
```

## 🔧 Installation & Setup

1. **Clone the repository**

```bash
git clone https://github.com/ahmedmhirsi/floussiwin.git
cd floussiwin
```

2. **Create the database**

```sql
CREATE DATABASE flousiwin;
```

3. **Import the sample data**

```bash
mysql -u root -p flousiwin < flousi_data.sql
```

4. **Configure the database connection**

Edit `config/database.php` and update your credentials:

```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'flousiwin';
$DB_USER = 'root';
$DB_PASS = '';
```

5. **Run the application**

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/
```

## 🧭 Main Routes

The app uses a lightweight router based on the `route` parameter in the URL:

- `index.php?route=login`
- `index.php?route=register`
- `index.php?route=dashboard`
- `index.php?route=transactions`
- `index.php?route=goals`
- `index.php?route=profile`
- `index.php?route=saving_challenge`
- `index.php?route=reports`

## 🔒 Security

Floussiwin includes basic application security measures such as:

- user session validation
- CSRF protection
- sanitized input handling
- PDO-based database access with error handling

## 🤝 Contributing

1. Create a dedicated branch for your feature or fix.
2. Make your changes locally.
3. Test the app in your local environment.
4. Open a pull request with a clear description.

## 📌 About

Floussiwin aims to make personal finance simpler, smarter, and more accessible. It combines budgeting, savings tracking, and financial insights in one clean and modern platform.

© 2025 Floussiwin. Made with ❤️ in Tunisia.

