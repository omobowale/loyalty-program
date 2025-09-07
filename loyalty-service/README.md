# Loyalty Program Backend (Laravel)

## 📌 Overview
This is the backend service for the Loyalty Program application.  
It is built with **Laravel** and provides APIs for:
- Customer purchases
- Achievements and badges
- Cashback system
- Admin dashboard data

## 🏗 Design Choices
- **Service-based architecture**: Business logic (e.g., cashback) lives in service classes instead of controllers.
- **Events & Listeners**: Purchases trigger `PurchaseMade` events which unlock achievements/badges asynchronously.
- **Queues**: Long-running tasks are queued to ensure scalability.
- **E2E & Feature Tests**: Achievements, badges, and purchase workflows are tested with PHPUnit.

## ⚙️ Setup
1. Clone the mono repository:
   ```bash
   git clone https://github.com/omobowale/loyalty-program.git
   cd loyalty-program
   ```

2. Build and run:
   ```bash
   docker compose up --build
    ```

3. Interact with the shell using:
    ```bash
    docker compose exec laravel bash
    ```
You can run your artisan commands right in there.



## End-to-End Test: Purchase → Achievement → Badge Flow

There are several tests in the application. To run please type:
```bash
    php artisan test
```

### Test File
A. `tests/Feature/PurchaseFlowE2ETest.php`

### Purpose
This end-to-end (E2E) test verifies the complete flow from a **user making purchases** to **unlocking achievements and badges** in the system. It ensures that database, cache, and event handling behave as expected.

---

### Test Steps

1. **Create Test Data**
   - A user is created using the `User` factory.
   - Multiple achievements are created with specific `points_required`.
   - A badge is created with a `min_achievements` threshold.

2. **Populate Cache**
   - Achievements and badges are cached to simulate application behavior:

3. **Fire Purchases**
   - Two `PurchaseMade` events are fired with amounts 1500 and 5000
   - These purchases trigger unlocking of achievements and badges according to thresholds.

4. **Assertions**
   - Database contains the unlocked achievements (`user_achievements`) and badge (`user_badges`)
   - Events AchievementUnlocked and BadgeUnlocked are dispatched the correct number of times.
   - Total counts for user achievements and badges match expectations (2 achievements, 1 badge).


### Additional Notes
- **Events Faked:** `AchievementUnlocked` and `BadgeUnlocked` are faked to allow dispatch assertions.  
- **Specific Purchases:** Two `PurchaseMade` events are fired (1500 and 5000) to trigger unlocking achievements and the badge.  
- **Assertions:** Verifies database entries, event dispatch counts, and total achievement/badge counts.  
- **Synchronous Flow:** No queue or bus fakes are needed; everything runs synchronously.
