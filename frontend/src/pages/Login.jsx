import { useState } from "react";
import { useAuth } from "../context/AuthContext";
import { useNavigate } from "react-router-dom";
import { Button, Input, Card } from "../components/ui";

export default function Login() {
	const [email, setEmail] = useState("");
	const [password, setPassword] = useState("");
	const [errors, setErrors] = useState({});
	const [loading, setLoading] = useState(false);
	const { login } = useAuth();
	const navigate = useNavigate();

	const validateForm = () => {
		const newErrors = {};
		
		if (!email) {
			newErrors.email = "Email jest wymagany";
		} else if (!/\S+@\S+\.\S+/.test(email)) {
			newErrors.email = "Nieprawidłowy format email";
		}
		
		if (!password) {
			newErrors.password = "Hasło jest wymagane";
		} else if (password.length < 6) {
			newErrors.password = "Hasło musi mieć minimum 6 znaków";
		}
		
		return newErrors;
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		
		const formErrors = validateForm();
		if (Object.keys(formErrors).length > 0) {
			setErrors(formErrors);
			return;
		}
		
		setLoading(true);
		setErrors({});
		
		try {
			await login(email, password);
			navigate("/dashboard");
		} catch (error) {
			setErrors({ 
				api: error.response?.data?.message || "Błąd logowania" 
			});
		} finally {
			setLoading(false);
		}
	};

	return (
		<div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 py-12 px-4">
			<Card className="w-full max-w-md shadow-2xl">
				<div className="text-center mb-8">
					<div className="inline-block p-3 bg-blue-600 rounded-full mb-4">
						<svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
						</svg>
					</div>
					<h2 className="text-3xl font-bold text-gray-800">
						Zaloguj się do konta
					</h2>
					<p className="text-gray-500 mt-2">Witaj ponownie!</p>
				</div>
				
				<form onSubmit={handleSubmit} className="space-y-4">
					<Input
						type="email"
						label="Email"
						placeholder="Adres email"
						value={email}
						onChange={(e) => setEmail(e.target.value)}
						error={errors.email}
					/>
					
					<Input
						type="password"
						label="Hasło"
						placeholder="Hasło"
						value={password}
						onChange={(e) => setPassword(e.target.value)}
						error={errors.password}
					/>

					{errors.api && (
						<div className="alert alert-error shadow-lg">
							<svg xmlns="http://www.w3.org/2000/svg" className="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
							<span>{errors.api}</span>
						</div>
					)}

					<Button
						type="submit"
						variant="primary"
						disabled={loading}
						className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-lg"
					>
						{loading ? "Logowanie..." : "Zaloguj się"}
					</Button>
				</form>
			</Card>
		</div>
	);
}