// src/contexts/MockAuthContext.jsx
import { createContext, useContext, useState } from "react";

const MockAuthContext = createContext();

export const MockAuthProvider = ({ children }) => {
  const [user, setUser] = useState( { id: 1, isAdmin: false }); 

  const login = (mockUser) => {
    setUser(mockUser);
  };

  const logout = () => {
    setUser(null);
  };

  return (
    <MockAuthContext.Provider value={{ user, login, logout, setUser }}>
      {children}
    </MockAuthContext.Provider>
  );
};

export const useMockAuth = () => useContext(MockAuthContext);
