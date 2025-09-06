import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import Customer from "./pages/Customer";
import Admin from "./pages/Admin";
import "./App.css";
import Layout from "./components/commons/Layout";

function App() {
  const queryClient = new QueryClient();

  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <Routes>
          {/* Layout wrapper */}
          <Route element={<Layout />}>
            <Route path="/customer" element={<Customer />} />
            <Route path="/admin" element={<Admin />} />
          </Route>
        </Routes>
      </Router>
    </QueryClientProvider>
  );
}

export default App;
