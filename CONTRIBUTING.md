# Contributing to Bangladesh API Hub

Thank you for considering contributing to the **Bangladesh API Hub & Developer Documentation** repository!

This project is created and maintained by **Refat Rahman** ([@refatbd](https://github.com/refatbd)) with the mission to provide unified, up-to-date, and standardized documentation for Bangladeshi APIs.

---

## How to Add a New Provider

If you want to document a new API provider (e.g., Pathao, RedX, eCourier, bKash, Nagad, Shurjopay):

1. **Fork the Repository** to your GitHub account.
2. **Create a Feature Branch**:
   ```bash
   git checkout -b provider/pathao-courier
   ```
3. **Follow the Standard Directory Layout**:
   ```text
   docs/<provider-name>/
   sdks-and-examples/<provider-name>/
   postman/<Provider_API_v1>.postman_collection.json
   openapi/<provider-openapi>.yaml
   ```
4. **Adhere to the Documentation Standard**:
   - Always state the active **Production Base URL** and **Sandbox/UAT URL**.
   - Provide exact request and response headers (`Content-Type`, `Api-Key`, `Authorization`, etc.).
   - Explicitly document rate limits, character truncation, and error codes.
   - Include code snippets in **cURL**, **PHP**, **JavaScript**, and **Python**.
5. **Update Main `README.md`**: Add your documented provider to the master table.
6. **Submit a Pull Request** with a concise description of the endpoints added or updated.

---

## Reporting Issues & Security

If you encounter an outdated endpoint or broken code snippet, please open an Issue on GitHub or reach out directly:
- **Telegram**: [@refatbd](https://t.me/refatbd)
- **Email/Web**: [refat.ovh](https://refat.ovh)
