
---

### `/frontend/README.md`  

```markdown
# Loyalty Program Frontend (React)

## 📌 Overview
This is the frontend for the Loyalty Program.  
It includes:
- **Customer Dashboard**: Achievements, badges, cashback balance
- **Admin Panel**: Manage users and view their achievements
- **Mock Authentication**: Simulated login for both admin and customer roles

## 🏗 Design Choices
- **React + React Router** for SPA navigation
- **React Query** for data fetching and caching
- **Context API (`MockAuthContext`)** for authentication
- **Tailwind CSS** for styling
- **Component-driven architecture**: AchievementsList, BadgeStatus, CashbackDisplay, etc.
- **Unit & Integration Tests** with **Vitest + React Testing Library**

## ⚙️ Setup
- Please setup the backend first before doing this.
1. Navigate to frontend:
   ```bash
   http://localhost:5173
   ```

=> Running the app

Test credentials are just passwords

For user/customer, we have
First user has password : user1
Second user has password : user2

For admin, we have
password: admin123

Admin can also login as user with same password



