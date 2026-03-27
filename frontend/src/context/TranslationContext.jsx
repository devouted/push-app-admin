import { createContext, useContext, useState, useCallback } from "react";
import api from "../api/axios";

const TranslationContext = createContext({ t: (key) => key, locale: "en", loadTranslations: () => {} });

export function TranslationProvider({ children }) {
	const [locale, setLocale] = useState("en");
	const [translations, setTranslations] = useState({});

	const loadTranslations = useCallback(async (newLocale) => {
		try {
			const { data } = await api.get(`/dictionaries/translations/${newLocale}`);
			setTranslations(data.translations ?? data);
			setLocale(newLocale);
		} catch {
			setLocale(newLocale);
		}
	}, []);

	const t = (key) => translations[key] ?? key;

	return (
		<TranslationContext.Provider value={{ t, locale, loadTranslations }}>
			{children}
		</TranslationContext.Provider>
	);
}

export function useTranslation() {
	return useContext(TranslationContext);
}
