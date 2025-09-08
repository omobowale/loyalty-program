# Loyalty Program Backend (Laravel)

## 📌 Overview
This is the backend service for the Loyalty Program application.  
It is built with **Laravel** and provides support for:
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

2. Copy content of .env.example to .env (for frontend and backend):
   ```bash
   cp ./loyalty-service/.env.example ./loyalty-service/.env
   cp ./loyalty-app/.env.example ./loyalty-app/.env
    ```

2. Build and run:
   ```bash
   docker compose up --build
    ```

3. Interact with the shell using:
    ```bash
    docker compose exec laravel bash
    ```

4. Install dependencies (optional):
    If for some reason, they dependencies were not installed during build run:
    ```bash
    docker compose run --rm laravel composer install
    ```
    - Ensure your processes are running in the container with `docker compose up` 
    - before and after the installation


    
   - You will run your artisan commands right in this shell.

## ⚙️ Migrations and Seeds

1. Run the migrations:
   ```bash
   php artisan migrate
   ```
   - Necessary tables will be created. 

2. Run the seeders:
   ```bash
   php artisan db:seed
    ```
   - The badges, achievements and users tables will be seeded

## 🧪 Testing

### Automated Tests

Run the full test suite:
```bash
php artisan test
```

#### End-to-End Test: Purchase → Achievement → Badge Flow

**Test File:** `tests/Feature/PurchaseFlowE2ETest.php`

**Purpose:** This end-to-end (E2E) test verifies the complete flow from a **user making purchases** to **unlocking achievements and badges** in the system. It ensures that database, cache, and event handling behave as expected.

**Test Steps:**

1. **Create Test Data**
   - A user is created using the `User` factory.
   - Multiple achievements are created with specific `points_required`.
   - A badge is created with a `min_achievements` threshold.

2. **Populate Cache**
   - Achievements and badges are cached to simulate application behavior

3. **Fire Purchases**
   - Two `PurchaseMade` events are fired with amounts 1500 and 5000
   - These purchases trigger unlocking of achievements and badges according to thresholds.

4. **Assertions**
   - Database contains the unlocked achievements (`user_achievements`) and badge (`user_badges`)
   - Events `AchievementUnlocked` and `BadgeUnlocked` are dispatched the correct number of times.
   - Total counts for user achievements and badges match expectations (2 achievements, 1 badge).

**Additional Notes:**
- **Events Faked:** `AchievementUnlocked` and `BadgeUnlocked` are faked to allow dispatch assertions.  
- **Specific Purchases:** Two `PurchaseMade` events are fired (1500 and 5000) to trigger unlocking achievements and the badge.  
- **Assertions:** Verifies database entries, event dispatch counts, and total achievement/badge counts.  
- **Synchronous Flow:** No queue or bus fakes are needed; everything runs synchronously.

## 🔧 Manual Testing

### Prerequisites
The following data is already seeded in the database:
1. **Users** - User accounts for testing
2. **Achievements** - Available achievements that users can unlock
3. **Badges** - Available badges that users can earn

### Testing the Loyalty System

#### Step 1: Trigger a Purchase

Run the following command to simulate a purchase transaction:

```bash
php artisan loyalty:purchase 1 300
```

This command will:
- Create a purchase transaction of **300** for the user with ID **1**
- Add data to the `transactions` table

You can run this command:
- Anytime you want to trigger a purchase event.

#### Step 2: Process the Purchase

Execute the queue consumer to process cashback from the purchase and trigger rewards:

```bash
php artisan queue:consume-fake
```

This will:
- Trigger the event listener to process cashback
- Unlock any qualifying badges or achievements based on the purchase
- Update the loyalty system data

#### Step 3: Verify Results

After running the commands, check the following:

**Database Changes:**
- **`user_achievements` table** - May contain new achievement records for the user
- **`user_badges` table** - May contain new badge records for the user

**UI Changes:**
- Navigate to the user interface
- The changes should be reflected for the user with ID 1
- New badges and achievements should be visible

### Expected Behavior

The loyalty system should automatically:
1. Calculate cashback based on the purchase amount
2. Check if the user qualifies for any new badges or achievements
3. Update the user's profile with newly earned rewards
4. Display the updates in the user interface

### Troubleshooting

If the system doesn't work as expected:
- Ensure all database seeds have been run properly
- Check that the queue system is configured correctly
- Verify that event listeners are properly registered
- Review application logs for any errors during processing