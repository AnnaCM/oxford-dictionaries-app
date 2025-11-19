# 📚 Oxford Dictionaries API Integration

This PHP application integrates with the [Oxford Dictionaries API](https://developer.oxforddictionaries.com/), enabling users to fetch definitions, translations, related linguistic data and audio pronunciations for supported words and languages.


🎥 **[Watch the demo video](https://github.com/AnnaCM/oxford-dictionaries-app/issues/2#issue-3601247147)**

---


## ✅ Features

- Fetch word definitions by language
- Retrieve translations between supported language pairs
- Stream audio pronunciations safely via a proxy (CORS-safe)
- Autocomplete powered by Redis with full Unicode/accents support
- Response caching using Redis to improve performance and reduce API calls
- Graceful error handling with custom Twig error pages
- Functional test coverage for core routes and exception scenarios

---

## 🛠 Requirements

- PHP `>=8.3`
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

---

## 🚀 Scalability & Future Improvements

This project is intentionally lightweight, but it has a clear path toward becoming a more scalable, robust, and production-ready service.
Below are several areas for enhancement, especially relevant for higher-traffic or distributed environments.

### 1. Enhanced Redis Strategy
Redis currently powers caching and autocomplete. Future improvements could include:

- Adding tag-based cache invalidation for selective clearing
- Preloading Redis with the most queried words at deploy time
- Tracking autocomplete popularity with Redis sorted-set scoring

### 2. Asynchronous Processing
Some tasks can be moved out of the request/response cycle for better performance:

- Introduce Symfony Messenger with Redis/RabbitMQ for background jobs
- Create background workers for:
    - Dictionary ingestion
    - Audio metadata refresh
    - Cache warm-ups
    - Bulk language updates

This keeps the UI responsive even when processing large datasets.

### 3. Rate Limiting & Resilience
To make the system more resilient and avoid API throttling:

- Add Symfony RateLimiter to throttle autocomplete and audio requests
- Implement retry/backoff for Oxford API failures
- Introduce a circuit breaker pattern if the external API becomes slow or unresponsive
- Cache audio metadata to reduce dependency on real-time HEAD/GET checks

### 4. Horizontal Scalability
The app can scale across multiple servers or containers:

- Stateless design → works well behind a load balancer
- Shared Redis cluster ensures consistent autocomplete and caching
- Dockerizing the app enables:
    - Kubernetes on cloud providers
    - Rapid spin-up for ephemeral environments

### 5. Observability & Monitoring
For production-level insight:

- Add Sentry for error tracking
- Implement application health endpoints
- Monitor Redis connection health and latency

### 6. Security Enhancements
Production security can be improved with:

- Strict parameter validation on all routes
- Using Symfony Secrets Vault instead of .env for sensitive data

### 7. CI/CD Pipeline Improvements
The existing GitHub Actions CI can be extended to:

- Run PHPStan or Psalm for static analysis
- Add a deployment workflow for staging and production environments
- Add cache warm-up jobs after deployments

### 8. Improved Dictionary Ingestion
More scalable ingestion could include:

- Scheduled nightly jobs for dictionary updates
- Higher-level search features (fuzzy matching, ranking, typo tolerance)

### 9. User Accounts & Personalization
Additional user-focused features could turn the application into a more interactive and personalized learning tool:

- User authentication (Symfony Security / OAuth / JWT)
- Store favourite words for each user in a relational database
- Personalized “Word of the Day”, selected from favourites or recent searches
- Scheduled background jobs to automatically rotate or refresh the Word of the Day
- Optional notification or email hooks for daily word delivery

---

## 📄 License

This project is licensed under the [MIT License](LICENSE.md).
You are free to use, modify, and distribute it with proper attribution.
