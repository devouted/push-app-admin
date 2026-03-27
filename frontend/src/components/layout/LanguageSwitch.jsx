import { useState, useEffect } from "react";
import api from "../../api/axios";
import { useAuth } from "../../context/AuthContext";
import { useTranslation } from "../../context/TranslationContext";

export default function LanguageSwitch() {
	const { isAuthenticated } = useAuth();
	const { locale, loadTranslations } = useTranslation();
	const [currentLocale, setCurrentLocale] = useState("en");

	useEffect(() => {
		if (!isAuthenticated) return;
		api.get("/users/me").then(({ data }) => {
			const l = data.locale ?? "en";
			setCurrentLocale(l);
			loadTranslations(l);
		});
	}, [isAuthenticated]);

	const toggle = async () => {
		const next = currentLocale === "en" ? "pl" : "en";
		await api.patch("/users/me/locale", { locale: next });
		setCurrentLocale(next);
		loadTranslations(next);
	};

	if (!isAuthenticated) return null;

	return (
		<button
			onClick={toggle}
			className="btn btn-sm bg-white/20 hover:bg-white/30 text-white border-white/40 hover:border-white/60"
			title="Switch language"
		>
			{currentLocale === "en" ? "🇵🇱 PL" : "🇬🇧 EN"}
		</button>
	);
}
