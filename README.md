# 📚 Oxford Dictionaries API Integration

This PHP application integrates with the [Oxford Dictionaries API](https://developer.oxforddictionaries.com/), enabling users to fetch definitions, translations, and related linguistic data for supported words and languages.

---

## ✅ Features

- Fetch word definitions by language
- Retrieve translations between supported language pairs
- Graceful API error handling with custom Twig templates
- Functional test coverage for key routes and exception handling

---

## 🛠 Requirements

- PHP `^7.4.5`
- Composer
- [Symfony CLI](https://symfony.com/download) *(recommended)*
- An [Oxford Dictionaries API](https://developer.oxforddictionaries.com/) account

---

## ⚙️ Setup

### 1. Clone the repository

```bash
git clone git@github.com:AnnaCM/oxford-dictionaries-app.git
cd oxford-dictionaries-app
```

### 2. Install dependencies

```bash
composer install
```

### 3. Add your Oxford Dictionaries credentials
Provide your unique credentials in the `.env` file:
```bash
APP_ID=your_oxford_app_id
APP_KEY=your_oxford_app_key
```
⚠️ These credentials are required for the app to make successful API requests.

---

## 🚀 Usage

### Run the application

- Using Symfony CLI:
```bash
symfony serve
```
- Or using PHP’s built-in server:
```bash
php -S localhost:8000 -t public
```
Then open your browser at: http://localhost:8000

### Run the Tests

To execute the test suite:
```bash
php bin/phpunit
```

Ensure you have development dependencies installed:
```bash
composer install --dev
```

---

## 🤝 Contributing

Forks, suggestions, improvements, and contributions are all welcome!  
If you have ideas or find issues, feel free to open an issue or submit a pull request.

Let's make this project better together. 🙌

---

## 📄 License

This project is licensed under the [MIT License](LICENSE.md).  
You are free to use, modify, and distribute it with proper attribution.
