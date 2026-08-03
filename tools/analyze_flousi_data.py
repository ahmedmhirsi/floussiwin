import re
import sys
from pathlib import Path

p = Path('c:/xampp/htdocs/flousi_win/flousi_data.sql')
if not p.exists():
    print('MISSING')
    sys.exit(2)

text = p.read_text(encoding='utf-8')
size = p.stat().st_size

counts = {
    'INSERT INTO users': len(re.findall(r"INSERT INTO users", text)),
    'INSERT INTO categories': len(re.findall(r"INSERT INTO categories", text)),
    'INSERT INTO goals': len(re.findall(r"INSERT INTO goals", text)),
    'INSERT INTO transactions': len(re.findall(r"INSERT INTO transactions", text)),
    'INSERT INTO financial_reports': len(re.findall(r"INSERT INTO financial_reports", text)),
}

# Count transaction lines and extract user_id occurrences
tx_lines = re.findall(r"INSERT INTO transactions .*?\);", text, flags=re.S)
num_tx = len(tx_lines)

user_ids_in_tx = []
for line in tx_lines:
    m = re.search(r"VALUES \((\d+), (\d+),", line)
    if m:
        tx_id = int(m.group(1))
        user_id = int(m.group(2))
        user_ids_in_tx.append(user_id)

max_user_id_in_tx = max(user_ids_in_tx) if user_ids_in_tx else 0
min_user_id_in_tx = min(user_ids_in_tx) if user_ids_in_tx else 0

# Count users inserted
user_lines = re.findall(r"INSERT INTO users .*?\);", text, flags=re.S)
num_users = len(user_lines)

# Count goals
goal_lines = re.findall(r"INSERT INTO goals .*?\);", text, flags=re.S)
num_goals = len(goal_lines)

print('file_size_bytes:', size)
print('num_users_insert_statements:', counts['INSERT INTO users'])
print('num_categories_insert_statements:', counts['INSERT INTO categories'])
print('num_goals_insert_statements:', counts['INSERT INTO goals'])
print('num_transactions_insert_statements:', counts['INSERT INTO transactions'])
print('num_transactions_lines_parsed:', num_tx)
print('num_users_parsed:', num_users)
print('num_goals_parsed:', num_goals)
print('max_user_id_in_transactions:', max_user_id_in_tx)
print('min_user_id_in_transactions:', min_user_id_in_tx)

# quick consistency checks
print('consistency_user_ids_ok:', 'YES' if max_user_id_in_tx <= num_users else 'NO')

# report a sample of transactions counts per first 10 users
from collections import Counter
cnt = Counter(user_ids_in_tx)
for uid in range(1,11):
    print(f'user {uid} tx_count:', cnt.get(uid,0))

print('done')
