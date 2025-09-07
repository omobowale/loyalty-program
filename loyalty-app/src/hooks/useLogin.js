// src/hooks/useLogin.js
const mockUsers = [
  { id: 1, userType: "admin", password: "admin123", isAdmin: true },
  { id: 2, userType: "user", password: "user1", isAdmin: false },
  { id: 3, userType: "user", password: "user2", isAdmin: false },
  { id: 4, userType: "user", password: "user3", isAdmin: false },
];

export const useLogin = (setUser, setLoggedIn, setError) => {
  const login = ({ userType, password }) => {
    setError("");
    const matchedUser = mockUsers.find(u => u.userType === userType && u.password === password);

    if (matchedUser) {
      const authUser = { id: matchedUser.id, isAdmin: matchedUser.isAdmin };
      setUser(authUser);
      setLoggedIn(true);
      localStorage.setItem("loggedIn", "true");
      localStorage.setItem("user", JSON.stringify(authUser));
    } else {
      setUser(null);
      setLoggedIn(false);
      setError("Invalid details");
      localStorage.setItem("loggedIn", "false");
      localStorage.removeItem("user");
    }
  };

  const logout = () => {
    setUser(null);
    setLoggedIn(false);
    localStorage.setItem("loggedIn", "false");
    localStorage.removeItem("user");
  };

  return { login, logout };
};
