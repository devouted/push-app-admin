import { useEffect, useState } from "react";
import api from "../../api/axios";
import { useAuth } from "../../context/AuthContext";
import { useTranslation } from "../../context/TranslationContext";

const LOCALE_LABELS = {
	en: "🇬🇧 EN",
	pl: "🇵🇱 PL",
};

export default function LanguageSwitch() {
	const { isAuthenticated } = useAuth();
	const { locale, loadTranslations } = useTranslation();
	const [locales, setLocales] = useState([]);
	const [open, setOpen] = useState(false);

	useEffect(() => {
		if (!isAuthenticated) return;
		Promise.all([
			api.get("/dictionaries/locales"),
			api.get("/users/me"),
		]).then(([localesRes, userRes]) => {
			setLocales(localesRes.data.locales ?? []);
			loadTranslations(userRes.data.locale ?? "en");
		});
	}, [isAuthenticated]);

	const changeLocale = async (next) => {
		setOpen(false);
		if (next === locale) return;
		await api.patch("/users/me/locale", { locale: next });
		loadTranslations(next);
	};

	if (!isAuthenticated || locales.length === 0) return null;

	return (
		<div className="relative">
			<button
				onClick={() => setOpen(!open)}
				className="btn btn-sm bg-white/20 hover:bg-white/30 text-white border-white/40 hover:border-white/60"
			>
				{LOCALE_LABELS[locale] ?? locale}
				<svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
				</svg>
			</button>
			{open && (
				<>
					<div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
					<ul className="absolute right-0 top-full mt-1 z-50 bg-white rounded shadow-lg border min-w-[100px]">
						{locales.map((loc) => (
							<li key={loc}>
								<button
									onClick={() => changeLocale(loc)}
									className={`w-full text-left px-4 py-2 text-sm hover:bg-blue-50 ${loc === locale ? "font-bold text-blue-600" : "text-gray-700"}`}
								>
									{LOCALE_LABELS[loc] ?? loc}
								</button>
							</li>
						))}
					</ul>
				</>
			)}
		</div>
	);
}
