import { Link, useLocation } from "react-router-dom";
import { useTranslation } from "../../context/TranslationContext";

export default function Nav() {
	const location = useLocation();
	const { t } = useTranslation();

	const isActive = (path) => location.pathname === path || location.pathname.startsWith(path);

	return (
		<nav className="bg-white border-b-2 border-gray-300 shadow">
			<ul className="menu menu-horizontal px-8 gap-1">
				<li>
					<Link 
						to="/dashboard" 
						className={`font-medium transition-all ${isActive("/dashboard") ? "bg-blue-600 text-white hover:bg-blue-700" : "text-gray-700 hover:bg-blue-50 hover:text-blue-600"}`}
					>
						{t('ui.nav.dashboard')}
					</Link>
				</li>
				<li>
					<Link 
						to="/users" 
						className={`font-medium transition-all ${isActive("/users") ? "bg-blue-600 text-white hover:bg-blue-700" : "text-gray-700 hover:bg-blue-50 hover:text-blue-600"}`}
					>
						{t('ui.nav.users')}
					</Link>
				</li>
			</ul>
		</nav>
	);
}
