// src/contexts/MockAuthContext.jsx
import { createContext, useContext, useState, useEffect } from "react";
import { useLogin } from "../hooks/useLogin";

const MockAuthContext = createContext();

export const MockAuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loggedIn, setLoggedIn] = useState(false);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true); // ⬅️ new state

  // Restore from localStorage once on mount
  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    const storedLoggedIn = localStorage.getItem("loggedIn") === "true";

    if (storedUser && storedLoggedIn) {
      setUser(JSON.parse(storedUser));
      setLoggedIn(true);
    }
    setLoading(false); // done restoring
  }, []);

  const { login, logout } = useLogin(setUser, setLoggedIn, setError);

  return (
    <MockAuthContext.Provider value={{ user, login, logout, loggedIn, error, loading }}>
      {children}
    </MockAuthContext.Provider>
  );
};

export const useMockAuth = () => useContext(MockAuthContext);
