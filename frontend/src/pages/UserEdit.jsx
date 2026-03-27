import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "../api/axios";
import { Button, Input, Card } from "../components/ui";

const AVAILABLE_ROLES = [
	{ value: "ROLE_USER", label: "User" },
	{ value: "ROLE_ADMIN", label: "Admin" },
];

export default function UserEdit() {
	const { id } = useParams();
	const [formData, setFormData] = useState({ email: "", password: "", roles: [] });
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [validationErrors, setValidationErrors] = useState({});
	const navigate = useNavigate();

	useEffect(() => {
		fetchUser();
	}, [id]);

	const fetchUser = async () => {
		try {
			const response = await api.get(`/admin/users/${id}`);
			setFormData({ email: response.data.email, password: "", roles: response.data.roles });
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || "Błąd podczas pobierania użytkownika");
		} finally {
			setLoading(false);
		}
	};

	const validateForm = () => {
		const errors = {};
		
		if (!formData.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
			errors.email = "Nieprawidłowy format email";
		}
		
		if (formData.password && formData.password.length < 6) {
			errors.password = "Hasło musi mieć minimum 6 znaków";
		}
		
		setValidationErrors(errors);
		return Object.keys(errors).length === 0;
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		
		if (!validateForm()) return;
		
		setLoading(true);
		try {
			const payload = { email: formData.email };
			if (formData.password) payload.password = formData.password;
			await api.put(`/admin/users/${id}`, payload);
			navigate("/users");
		} catch (err) {
			setError(err.response?.data?.message || "Błąd podczas aktualizacji użytkownika");
			setLoading(false);
		}
	};

	const handleRolesSubmit = async () => {
		setLoading(true);
		try {
			await api.post(`/admin/users/${id}/roles`, { roles: formData.roles });
			setError(null);
			alert("Role zostały zaktualizowane");
		} catch (err) {
			setError(err.response?.data?.message || "Błąd podczas aktualizacji ról");
		} finally {
			setLoading(false);
		}
	};

	const toggleRole = (role) => {
		setFormData(prev => ({
			...prev,
			roles: prev.roles.includes(role)
				? prev.roles.filter(r => r !== role)
				: [...prev.roles, role]
		}));
	};

	if (loading) {
		return (
			<div className="flex items-center justify-center min-h-screen">
				<span className="loading loading-spinner loading-lg"></span>
			</div>
		);
	}

	return (
		<div>
			<h1 className="text-3xl font-bold mb-6">Edycja użytkownika #{id}</h1>

			{error && (
				<div className="alert alert-error mb-4">
					<span>{error}</span>
				</div>
			)}

			<Card>
				<form onSubmit={handleSubmit} className="space-y-4">
					<Input
						type="email"
						label="Email"
						value={formData.email}
						onChange={(e) => setFormData({ ...formData, email: e.target.value })}
						error={validationErrors.email}
						required
					/>

					<Input
						type="password"
						label="Nowe hasło (opcjonalne)"
						value={formData.password}
						onChange={(e) => setFormData({ ...formData, password: e.target.value })}
						error={validationErrors.password}
					/>

					<div className="form-control">
						<label className="label">
							<span className="label-text">Role</span>
						</label>
						<div className="space-y-2 mb-3">
							{AVAILABLE_ROLES.map(role => (
								<label key={role.value} className="label cursor-pointer justify-start gap-2">
									<input
										type="checkbox"
										checked={formData.roles.includes(role.value)}
										onChange={() => toggleRole(role.value)}
										className="checkbox"
									/>
									<span className="label-text">{role.label}</span>
								</label>
							))}
						</div>
						<Button
							type="button"
							variant="success"
							size="sm"
							onClick={handleRolesSubmit}
							disabled={loading}
						>
							Zapisz role
						</Button>
					</div>

					<div className="flex gap-4">
						<Button type="submit" variant="primary" disabled={loading}>
							{loading ? "Zapisywanie..." : "Zapisz"}
						</Button>
						<Button type="button" variant="ghost" onClick={() => navigate("/users")}>
							Anuluj
						</Button>
					</div>
				</form>
			</Card>
		</div>
	);
}
