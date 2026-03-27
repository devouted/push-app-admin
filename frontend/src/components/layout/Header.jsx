import { useNavigate } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";
import LanguageSwitch from "./LanguageSwitch";

export default function Header() {
	const navigate = useNavigate();
	const { logout } = useAuth();

	const handleLogout = () => {
		logout();
		navigate("/login");
	};

	return (
		<header className="navbar bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg">
			<div className="flex-1">
				<h1 className="text-2xl font-bold">CRM Admin Panel</h1>
			</div>
			<div className="flex-none gap-2">
				<LanguageSwitch />
				<button 
					onClick={handleLogout}
					className="btn btn-sm bg-white/20 hover:bg-white/30 text-white border-white/40 hover:border-white/60"
				>
					Wyloguj
				</button>
			</div>
		</header>
	);
}
