import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "./context/AuthContext";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Users from "./pages/Users";
import UserNew from "./pages/UserNew";
import UserEdit from "./pages/UserEdit";
import Channels from "./pages/Channels";
import ChannelNew from "./pages/ChannelNew";
import ChannelEdit from "./pages/ChannelEdit";
import ChannelDetail from "./pages/ChannelDetail";
import ChannelNotifications from "./pages/ChannelNotifications";
import AdminChannels from "./pages/AdminChannels";
import AdminClientsConsumers from "./pages/AdminClientsConsumers";
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
				<Route 
					path="/channels" 
					element={<ProtectedRoute><Layout><Channels /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/channels/new" 
					element={<ProtectedRoute><Layout><ChannelNew /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/channels/:id/edit" 
					element={<ProtectedRoute><Layout><ChannelEdit /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/channels/:id" 
					element={<ProtectedRoute><Layout><ChannelDetail /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/channels/:id/notifications" 
					element={<ProtectedRoute><Layout><ChannelNotifications /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/admin/channels" 
					element={<ProtectedRoute><Layout><AdminChannels /></Layout></ProtectedRoute>} 
				/>
				<Route 
					path="/admin/clients" 
					element={<ProtectedRoute><Layout><AdminClientsConsumers /></Layout></ProtectedRoute>} 
				/>
				<Route path="/" element={<Navigate to="/dashboard" replace />} />
			</Routes>
		</Router>
	);
}

export default App;
