import { createContext, useContext, useState } from "react";
import api from "../api/axios";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
	const [token, setToken] = useState(localStorage.getItem("token"));

	const login = async (email, password) => {
		const { data } = await api.post("/auth/login", { email, password });
		localStorage.setItem("token", data.token);
		setToken(data.token);
	};

	const logout = () => {
		localStorage.removeItem("token");
		setToken(null);
	};

	return (
		<AuthContext.Provider value={{ token, login, logout, isAuthenticated: !!token }}>
			{children}
		</AuthContext.Provider>
	);
}

export function useAuth() {
	return useContext(AuthContext);
}
