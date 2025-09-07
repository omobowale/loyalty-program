

## End-to-End Test: Purchase → Achievement → Badge Flow

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
