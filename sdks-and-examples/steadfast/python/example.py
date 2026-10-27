from steadfast import SteadfastClient

client = SteadfastClient(
    api_key="YOUR_API_KEY",
    secret_key="YOUR_SECRET_KEY",
)

print("1. Gateway Ping...")
ping = client.ping()
print(ping)

print("\n2. Live Wallet Balance...")
balance = client.get_balance()
print(balance)

print("\n3. Customer Risk Score...")
fraud = client.check_fraud_score("01712345678")
print(fraud)
