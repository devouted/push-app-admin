import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./styles/main.scss";
import App from "./App.jsx";
import { AuthProvider } from "./context/AuthContext.jsx";
import { TranslationProvider } from "./context/TranslationContext.jsx";

createRoot(document.getElementById("root")).render(
	<StrictMode>
		<AuthProvider>
			<TranslationProvider>
				<App />
			</TranslationProvider>
		</AuthProvider>
	</StrictMode>,
);
