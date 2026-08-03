#!/usr/bin/env python3
"""
Flousi Win - Data generator for MySQL (compatible MySQL 8)
Generates users, subscriptions, categories, transactions, goals, financial_reports, notifications.
Usage:
    python generate_flousi_data.py --users 1000 --months 1 --tx_per_user 35 --out flousi_data.sql
"""
import argparse
import random
import datetime
import math
import json
from itertools import count

FIRST_NAMES = [
    'Ahmed','Mohamed','Ali','Hassen','Walid','Youssef','Omar','Khaled','Nour','Sarra',
    'Emna','Fatma','Amina','Meriem','Lina','Yasmine','Yosra','Nadia','Hedia','Aicha'
]
LAST_NAMES = [
    'Ben Salah','Trabelsi','Bouzid','Kacem','Jabri','Masmoudi','Saidi','Fakhfakh','Gharbi','Cherif',
    'Khalifa','Mehri','Zribi','Farhat','Sghaier'
]
CITIES = ['Tunis','Sfax','Sousse','Nabeul','Ariana','Bizerte','Gabes','Kairouan','Mahdia','Gafsa']
PROFESSIONS = ['Enseignant','Ingénieur','Commercial','Etudiant','Fonctionnaire','Médecin','Technicien','Freelance','Artisan','Commerçant']
CATEGORIES = ['Nourriture','Café','Transport','Essence','Loyer','STEG/SONEDE','Internet','Shopping','Santé','Loisirs','Formation','Sport','Abonnement','Tabac','Autres']
PAY_METHODS = ['Cash','Carte','D17','Flouci','Virement']
GOAL_TITLES = ['Acheter une voiture','Acheter un téléphone','Voyage','Formation','Projet personnel','Mariage','Épargne urgence']

# category distribution template (percentages of monthly expenses)
CATEGORY_PERCENTS = {
    'Nourriture': 0.25,
    'Transport': 0.12,
    'Café': 0.05,
    'STEG/SONEDE': 0.10,
    'Internet': 0.03,
    'Shopping': 0.08,
    'Santé': 0.05,
    'Loisirs': 0.07,
    'Tabac': 0.05,
    'Essence': 0.05,
    'Loyer': 0.10,
    'Formation': 0.02,
    'Sport': 0.01,
    'Abonnement': 0.02,
    'Autres': 0.00
}

idc = count(1)

def next_id():
    return next(idc)

def tunisian_phone():
    # +216 XX XXX XXX
    prefixes = ['20','21','22','23','24','25','26','27','28','29','50','51','52','53','54','55','56','57','58','59']
    p = random.choice(prefixes)
    return '+216' + p + ' ' + str(random.randint(100,999)) + ' ' + str(random.randint(100,999))

def random_email(first,last,i):
    domain = random.choice(['gmail.com','hotmail.com','yahoo.com','outlook.com'])
    return f"{first.lower()}.{last.split()[0].lower()}{i}@{domain}"

def salary_by_distribution():
    # keep distribution but we will override per profile in generate_users
    r = random.random()
    if r < 0.20:
        return round(random.uniform(200, 499),2)
    elif r < 0.65:
        return round(random.uniform(500, 1000),2)
    elif r < 0.90:
        return round(random.uniform(1000, 2000),2)
    else:
        return round(random.uniform(2000, 4500),2)


def generate_users(n):
    users = []
    for i in range(1, n+1):
        first = random.choice(FIRST_NAMES)
        last = random.choice(LAST_NAMES)
        email = random_email(first,last,i)
        phone = tunisian_phone()
        age = random.randint(18,65)
        prof = random.choice(PROFESSIONS)
        city = random.choice(CITIES)
        # assign profile and salary ranges
        profile_roll = random.random()
        if profile_roll < 0.18:
            profile = 'Etudiant'
            salary = round(random.uniform(300, 800),2)
        elif profile_roll < 0.95:
            profile = 'Employe'
            salary = round(random.uniform(900, 2500),2)
        else:
            profile = 'Entrepreneur'
            salary = round(random.uniform(3000, 10000),2)
        registered_at = datetime.date.today() - datetime.timedelta(days=random.randint(0,365*2))
        is_premium = 1 if random.random() < 0.10 else 0
        users.append({
            'id': i,
            'first_name': first,
            'last_name': last,
            'email': email,
            'phone': phone,
            'age': age,
            'profession': prof,
            'city': city,
            'salary': salary,
            'profile': profile,
            'created_at': registered_at.isoformat(),
            'is_premium': is_premium
        })
    return users


def generate_categories():
    return [{'id': i+1, 'name': name} for i,name in enumerate(CATEGORIES)]


def generate_goals(users, pct_with_goals=0.7):
    goals = []
    gid = 1
    for u in users:
        if random.random() < pct_with_goals:
            num_goals = random.choices([1,2,3], weights=[70,25,5])[0]
            for _ in range(num_goals):
                title = random.choice(GOAL_TITLES)
                target = round(random.uniform(200, 8000),2)
                current = round(random.uniform(0, target*0.6),2)
                days = random.randint(30, 365*2)
                deadline = (datetime.date.today() + datetime.timedelta(days=days)).isoformat()
                status = 'active' if current < target else 'completed'
                goals.append({
                    'id': gid,
                    'user_id': u['id'],
                    'title': title,
                    'target_amount': target,
                    'current_amount': current,
                    'deadline': deadline,
                    'status': status
                })
                gid += 1
    return goals


def generate_transactions(users, categories, months=1, tx_per_user_per_month=35):
    txs = []
    tx_id = 1
    start_date = datetime.date.today() - datetime.timedelta(days=30*months)
    for u in users:
        # incomes: 1 per month
        for m in range(months):
            date = start_date + datetime.timedelta(days=30*m + random.randint(0,6))
            txs.append({
                'id': tx_id,
                'user_id': u['id'],
                'type': 'income',
                'category': 'Salaire',
                'amount': u['salary'],
                'description': 'Salaire',
                'date': date.isoformat(),
                'payment_method': random.choice(PAY_METHODS)
            })
            tx_id += 1
        # realistic expenses: each user spends between 50% and 90% of salary per month
        for m in range(months):
            month_date = start_date + datetime.timedelta(days=30*m)
            # target expenses between 60% and 90% of salary for realistic saving rate ~10-40%
            expense_target = round(u['salary'] * random.uniform(0.6, 0.9),2)
            # compute per-category amounts based on CATEGORY_PERCENTS
            per_cat_amount = {}
            total_pct = sum(CATEGORY_PERCENTS.values())
            for cat, pct in CATEGORY_PERCENTS.items():
                # normalize in case total_pct !=1
                share = pct / total_pct if total_pct>0 else 0
                per_cat_amount[cat] = round(expense_target * share,2)
            # ensure rounding error fix: adjust 'Autres' or 'Nourriture'
            allocated = sum(per_cat_amount.values())
            diff = round(expense_target - allocated,2)
            if diff != 0:
                # add diff to 'Autres' if exists else to 'Nourriture'
                if 'Autres' in per_cat_amount:
                    per_cat_amount['Autres'] += diff
                else:
                    per_cat_amount['Nourriture'] += diff

            # decide how many expense tx to create per category proportional to amount
            txs_for_user = tx_per_user_per_month
            # calculate tentative tx counts per category
            cat_tx_counts = {}
            total_amount = sum(per_cat_amount.values())
            for cat, amt in per_cat_amount.items():
                # at least 0 tx for zero amt categories
                if amt <= 0:
                    cat_tx_counts[cat] = 0
                else:
                    # proportionally allocate number of txs
                    cat_tx_counts[cat] = max(1, int(round((amt / total_amount) * txs_for_user)))
            # adjust total txs to equal txs_for_user
            s = sum(cat_tx_counts.values())
            # if too many or too few, adjust by adding/removing from largest categories
            if s != txs_for_user:
                diff_tx = txs_for_user - s
                # sort categories by amount desc
                cats_sorted = sorted(per_cat_amount.items(), key=lambda x: x[1], reverse=True)
                idx = 0
                while diff_tx != 0:
                    c = cats_sorted[idx % len(cats_sorted)][0]
                    if diff_tx > 0:
                        cat_tx_counts[c] += 1
                        diff_tx -= 1
                    else:
                        if cat_tx_counts[c] > 1:
                            cat_tx_counts[c] -= 1
                            diff_tx += 1
                    idx += 1

            # now for each category, create the transactions splitting the category amount
            for cat, count_tx in cat_tx_counts.items():
                amt_total = per_cat_amount.get(cat,0)
                if count_tx <= 0 or amt_total <= 0:
                    continue
                # create random splits that sum to amt_total
                remaining = amt_total
                for i_tx in range(count_tx):
                    if i_tx == count_tx - 1:
                        amt = round(remaining,2)
                    else:
                        # sample a share
                        max_share = remaining - (count_tx - i_tx - 1) * 1.0
                        share = round(random.uniform(0.05, min(0.5, max_share/remaining)),3)
                        amt = round(max(0.5, remaining * share),2)
                    remaining = round(remaining - amt,2)
                    # spread date within month
                    date = month_date + datetime.timedelta(days=random.randint(0,29))
                    txs.append({
                        'id': tx_id,
                        'user_id': u['id'],
                        'type': 'expense',
                        'category': cat,
                        'amount': amt,
                        'description': cat + ' dépense',
                        'date': date.isoformat(),
                        'payment_method': random.choice(PAY_METHODS)
                    })
                    tx_id += 1
    return txs


def compute_financial_reports(users, transactions, goals):
    reports = []
    for u in users:
        uid = u['id']
        user_txs = [t for t in transactions if t['user_id']==uid]
        incomes = sum(t['amount'] for t in user_txs if t['type']=='income')
        expenses = sum(t['amount'] for t in user_txs if t['type']=='expense')
        months = max(1, len([t for t in user_txs if t['type']=='income']))
        avg_income = incomes / months
        avg_expenses = expenses / months
        saving_rate = round(((avg_income - avg_expenses) / avg_income) * 100,2) if avg_income>0 else 0
        # top category
        cats = {}
        for t in user_txs:
            if t['type']=='expense':
                cats[t['category']] = cats.get(t['category'],0)+t['amount']
        if cats:
            top_cat = max(cats.items(), key=lambda x:x[1])[0]
        else:
            top_cat = None
        score = int(max(0, min(100, 50 + (saving_rate - 10))))
        recs = []
        if top_cat and cats.get(top_cat,0) > avg_income * 0.15:
            recs.append(f"Tu dépenses beaucoup en {top_cat}. Réduire de 10% peut t'aider.")
        if saving_rate < 5:
            recs.append('Ton taux d\'épargne est faible; augmente ton objectif ou réduis dépenses.')
        reports.append({
            'user_id': uid,
            'avg_income': round(avg_income,2),
            'avg_expenses': round(avg_expenses,2),
            'saving_rate': saving_rate,
            'top_category': top_cat,
            'score': score,
            'recommendations': recs
        })
    return reports


def dump_sql(out_file, users, categories, transactions, goals, reports):
    with open(out_file,'w',encoding='utf-8') as f:
        f.write('-- Flousi Win generated dataset\n')
        f.write('SET FOREIGN_KEY_CHECKS=0;\n')
        f.write('START TRANSACTION;\n')
        f.write('\n-- users\n')
        for u in users:
            f.write("INSERT INTO users (id, first_name, last_name, email, phone, age, profession, city, salary, created_at, is_premium) VALUES ");
            vals = (u['id'], u['first_name'].replace("'","\\'"), u['last_name'].replace("'","\\'"), u['email'], u['phone'], u['age'], u['profession'], u['city'], u['salary'], u['created_at'], u['is_premium'])
            f.write("(%d, '%s', '%s', '%s', '%s', %d, '%s', '%s', %.2f, '%s', %d);\n" % vals)
        f.write('\n-- categories\n')
        for c in categories:
            f.write("INSERT INTO categories (id, name) VALUES (%d, '%s');\n" % (c['id'], c['name']))
        f.write('\n-- goals\n')
        for g in goals:
            f.write("INSERT INTO goals (id, user_id, title, target_amount, current_amount, deadline, status) VALUES (%d, %d, '%s', %.2f, %.2f, '%s', '%s');\n" % (g['id'], g['user_id'], g['title'].replace("'","\\'"), g['target_amount'], g['current_amount'], g['deadline'], g['status']))
        f.write('\n-- transactions\n')
        for t in transactions:
            f.write("INSERT INTO transactions (id, user_id, type, category, amount, description, date, payment_method) VALUES (%d, %d, '%s', '%s', %.2f, '%s', '%s', '%s');\n" % (t['id'], t['user_id'], t['type'], t['category'].replace("'","\\'"), t['amount'], t['description'].replace("'","\\'"), t['date'], t['payment_method']))
        f.write('\n-- financial_reports\n')
        rid = 1
        for r in reports:
            payload = json.dumps({
                'avg_income': r['avg_income'],
                'avg_expenses': r['avg_expenses'],
                'saving_rate': r['saving_rate'],
                'top_category': r['top_category'],
                'recommendations': r['recommendations']
            }, ensure_ascii=False)
            f.write("INSERT INTO financial_reports (id, user_id, payload, score, created_at) VALUES (%d, %d, '%s', %d, '%s');\n" % (rid, r['user_id'], payload.replace("'","\\'"), r['score'], datetime.date.today().isoformat()))
            rid += 1
        f.write('\nCOMMIT;\n')
        f.write('SET FOREIGN_KEY_CHECKS=1;\n')
    print('Wrote', out_file)


def dump_phpmyadmin(out_file, users, transactions, goals):
    # create a phpMyAdmin-friendly dump with basic schema compatible with user's DB
    with open(out_file, 'w', encoding='utf-8') as f:
        f.write('-- phpMyAdmin SQL Dump generated by Flousi Win generator\n')
        f.write('-- Generated on: %s\n' % datetime.date.today().isoformat())
        f.write('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";\n')
        f.write('START TRANSACTION;\n')
        f.write('SET time_zone = "+00:00";\n\n')
        f.write('/*!40101 SET NAMES utf8mb4 */;\n\n')

        # CREATE TABLE users
        f.write('--\n-- Table structure for table `users`\n--\n\n')
        f.write('CREATE TABLE `users` (\n')
        f.write('  `id` int(11) NOT NULL,\n')
        f.write('  `name` varchar(255) NOT NULL,\n')
        f.write('  `email` varchar(255) NOT NULL,\n')
        f.write('  `password` varchar(255) NOT NULL,\n')
        f.write('  `created_at` datetime DEFAULT current_timestamp()\n')
        f.write(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n')

        # CREATE TABLE user_profiles
        f.write('--\n-- Table structure for table `user_profiles`\n--\n\n')
        f.write('CREATE TABLE `user_profiles` (\n')
        f.write('  `id` int(11) NOT NULL,\n')
        f.write('  `user_id` int(11) NOT NULL,\n')
        f.write('  `monthly_salary` decimal(12,2) DEFAULT 0.00,\n')
        f.write('  `additional_income` decimal(12,2) DEFAULT 0.00,\n')
        f.write('  `total_monthly_income` decimal(12,2) GENERATED ALWAYS AS (`monthly_salary` + `additional_income`) STORED,\n')
        f.write('  `created_at` datetime DEFAULT current_timestamp(),\n')
        f.write('  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()\n')
        f.write(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n')

        # CREATE TABLE transactions
        f.write('--\n-- Table structure for table `transactions`\n--\n\n')
        f.write('CREATE TABLE `transactions` (\n')
        f.write('  `id` int(11) NOT NULL,\n')
        f.write('  `user_id` int(11) NOT NULL,\n')
        f.write("  `type` enum('income','expense') NOT NULL,\n")
        f.write('  `amount` decimal(12,2) NOT NULL,\n')
        f.write('  `category` varchar(100) DEFAULT NULL,\n')
        f.write('  `description` text DEFAULT NULL,\n')
        f.write('  `date` date NOT NULL,\n')
        f.write('  `created_at` datetime DEFAULT current_timestamp()\n')
        f.write(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n')

        # CREATE TABLE goals
        f.write('--\n-- Table structure for table `goals`\n--\n\n')
        f.write('CREATE TABLE `goals` (\n')
        f.write('  `id` int(11) NOT NULL,\n')
        f.write('  `user_id` int(11) NOT NULL,\n')
        f.write('  `name` varchar(255) NOT NULL,\n')
        f.write('  `target_amount` decimal(12,2) NOT NULL,\n')
        f.write('  `saved_amount` decimal(12,2) DEFAULT 0.00,\n')
        f.write('  `deadline` date DEFAULT NULL,\n')
        f.write("  `status` varchar(20) DEFAULT 'active',\n")
        f.write('  `priority` int(11) DEFAULT 1,\n')
        f.write('  `created_at` datetime DEFAULT current_timestamp(),\n')
        f.write('  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()\n')
        f.write(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n')

        # write INSERTs for users and user_profiles
        f.write('--\n-- Dumping data for table `users`\n--\n')
        for u in users:
            name = (u['first_name'] + ' ' + u['last_name']).replace("'","\\'")
            email = u['email']
            pwd = '$2y$10$exampleplaceholderhashstring...............'  # placeholder
            created = u['created_at']
            f.write("INSERT INTO `users` (`id`,`name`,`email`,`password`,`created_at`) VALUES (%d, '%s', '%s', '%s', '%s');\n" % (u['id'], name, email, pwd, created))

        f.write('\n--\n-- Dumping data for table `user_profiles`\n--\n')
        up_id = 1
        for u in users:
            f.write("INSERT INTO `user_profiles` (`id`,`user_id`,`monthly_salary`,`additional_income`,`created_at`,`updated_at`) VALUES (%d, %d, %.2f, 0.00, '%s', '%s');\n" % (up_id, u['id'], u['salary'], u['created_at'], u['created_at']))
            up_id += 1

        f.write('\n--\n-- Dumping data for table `goals`\n--\n')
        for g in goals:
            name = g['title'].replace("'","\\'") if 'title' in g else g.get('name', 'goal').replace("'","\\'")
            f.write("INSERT INTO `goals` (`id`,`user_id`,`name`,`target_amount`,`saved_amount`,`deadline`,`status`,`priority`,`created_at`,`updated_at`) VALUES (%d, %d, '%s', %.2f, %.2f, '%s', '%s', %d, '%s', '%s');\n" % (g['id'], g['user_id'], name, g.get('target_amount', g.get('target',0)), g.get('current_amount', g.get('saved_amount',0)), g.get('deadline','1970-01-01'), g.get('status','active'), 1, datetime.date.today().isoformat(), datetime.date.today().isoformat()))

        f.write('\n--\n-- Dumping data for table `transactions`\n--\n')
        for t in transactions:
            desc = t.get('description','').replace("'","\\'")
            cat = t.get('category','').replace("'","\\'")
            f.write("INSERT INTO `transactions` (`id`,`user_id`,`type`,`amount`,`category`,`description`,`date`,`created_at`) VALUES (%d, %d, '%s', %.2f, '%s', '%s', '%s', '%s');\n" % (t['id'], t['user_id'], t['type'], t['amount'], cat, desc, t['date'], t['date']))

        # indexes and constraints (simple)
        f.write('\n--\n-- Indexes for dumped tables\n--\n')
        f.write('ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);\n')
        f.write('ALTER TABLE `user_profiles` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `user_id` (`user_id`);\n')
        f.write('ALTER TABLE `transactions` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);\n')
        f.write('ALTER TABLE `goals` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);\n')

        f.write('\n-- AUTO_INCREMENT\n')
        f.write('ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=%d;\n' % (len(users)+1))
        f.write('ALTER TABLE `user_profiles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=%d;\n' % (len(users)+1))
        f.write('ALTER TABLE `transactions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=%d;\n' % (max(t['id'] for t in transactions)+1))
        f.write('ALTER TABLE `goals` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=%d;\n' % (max(g['id'] for g in goals)+1 if goals else 1))

        # foreign keys
        f.write('\n-- Constraints\n')
        f.write('ALTER TABLE `user_profiles` ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;\n')
        f.write('ALTER TABLE `transactions` ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;\n')
        f.write('ALTER TABLE `goals` ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;\n')

        f.write('COMMIT;\n')
    print('Wrote phpMyAdmin dump', out_file)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--users', type=int, default=1000)
    parser.add_argument('--months', type=int, default=1)
    parser.add_argument('--tx_per_user', type=int, default=35)
    parser.add_argument('--out', type=str, default='flousi_data.sql')
    args = parser.parse_args()

    users = generate_users(args.users)
    categories = generate_categories()
    goals = generate_goals(users, pct_with_goals=0.7)
    transactions = generate_transactions(users, categories, months=args.months, tx_per_user_per_month=args.tx_per_user)
    reports = compute_financial_reports(users, transactions, goals)

    dump_sql(args.out, users, categories, transactions, goals, reports)
    # also create a phpMyAdmin-compatible dump
    php_out = args.out.replace('.sql', '_phpmyadmin.sql')
    dump_phpmyadmin(php_out, users, transactions, goals)

    # print quick stats
    total_revenues = sum(u['salary'] for u in users)
    total_transactions = len(transactions)
    total_goals = len(goals)
    avg_expenses = sum(r['avg_expenses'] for r in reports)/len(reports)
    avg_saving_rate = sum(r['saving_rate'] for r in reports)/len(reports)
    print('Users:', len(users))
    print('Total revenues (monthly):', round(total_revenues,2))
    print('Transactions generated:', total_transactions)
    print('Goals generated:', total_goals)
    print('Avg monthly expenses per user:', round(avg_expenses,2))
    print('Avg saving rate (%):', round(avg_saving_rate,2))

if __name__=='__main__':
    main()
