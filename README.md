# 📚 Oxford Dictionaries API Integration

This PHP application integrates with the [Oxford Dictionaries API](https://developer.oxforddictionaries.com/), enabling users to fetch definitions, translations, related linguistic data and audio pronunciations for supported words and languages.


🎥 **[Watch the demo video](https://github.com/AnnaCM/oxford-dictionaries-app/issues/2#issue-3601247147)**

---


## ✅ Features

- Fetch word definitions by language
- Retrieve translations between supported language pairs
- Listen to audio pronunciations for both definitions and translations (via proxy streaming to ensure CORS-safe playback)
- Autocomplete powered by Redis, supporting accented characters across multiple languages
- Caching with Redis for faster performance and fewer API requests
- Graceful API error handling with custom Twig templates
- Functional test coverage for key routes and exception handling

---

## 🛠 Requirements

- PHP `>=8.1`
- Composer
- [Symfony CLI](https://symfony.com/download) *(recommended)*
- An [Oxford Dictionaries API](https://developer.oxforddictionaries.com/) account
- Redis *(recommended for caching and autocomplete)*

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

## 🧠 Redis for Caching & Autocomplete
The app uses Redis to:
- cache results from the Oxford Dictionaries API to speed up repeated queries;
- provide autocomplete suggestions as you type, making lookups faster and more user-friendly.

### Run Redis locally
You can run Redis in a Docker container:

```bash
docker run --name redis-cache -p 6379:6379 -d redis
```
Or, if installed directly:

```bash
brew install redis
brew services start redis
```

### Load dictionary words into Redis
Before using autocomplete, preload dictionary words into Redis with the provided Symfony command:

```bash
php bin/console app:load-dictionary-words
```

This will:
- import available Unicode-safe dictionary words into Redis;
- enable fast prefix-based searches for autocomplete.

Redis is optional. If unavailable, the app logs a warning and continues to operate without caching or autocomplete.

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

This project uses the Symfony PHPUnit Bridge to run tests and detect deprecated Symfony APIs.

To execute the full test suite:
```bash
composer test
```

Ensure you have development dependencies installed:
```bash
composer install --dev
```

---

## 🪝 Git Hooks (optional for contributors)

This project includes a **pre-commit Git hook** to help maintain code quality.

### What the hook does

When enabled, the hook will:

- Automatically run **PHP-CS-Fixer** to format code.
- Check staged PHP files with the **PHP linter**.
- Prevent commits that contain syntax errors or unformatted code.

This is optional but recommended. CI checks still enforce coding standards on pull requests.

---

### How to enable Git hooks

Run the following command **once** after cloning the repository:

```bash
sh bin/setup-hooks
```

This will:

- Set Git to use the `.githooks` directory in the repository
- Make the hook files executable
- Enable the pre-commit hook

To disable hooks later:

```bash
git config --unset core.hooksPath
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
