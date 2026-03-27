import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "./context/AuthContext";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Users from "./pages/Users";
import UserNew from "./pages/UserNew";
import UserEdit from "./pages/UserEdit";
import ProtectedRoute from "./components/ProtectedRoute";
import Layout from "./components/Layout";

function App() {
	const { isAuthenticated } = useAuth();

	return (
		<Router>
			<Routes>
				<Route 
					path="/login" 
					element={isAuthenticated ? <Navigate to="/dashboard" replace /> : <Login />} 
				/>
				<Route 
					path="/dashboard" 
					element={<ProtectedRoute><Layout><Dashboard /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/users" 
					element={<ProtectedRoute><Layout><Users /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/users/new" 
					element={<ProtectedRoute><Layout><UserNew /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/users/:id/edit" 
					element={<ProtectedRoute><Layout><UserEdit /></Layout></ProtectedRoute>} 
				/>
				<Route path="/" element={<Navigate to="/dashboard" replace />} />
			</Routes>
		</Router>
	);
}

export default App;
