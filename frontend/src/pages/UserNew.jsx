import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import { Button, Input, Card } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

const AVAILABLE_ROLES = [
	{ value: "ROLE_USER", label: "User" },
	{ value: "ROLE_ADMIN", label: "Admin" },
];

export default function UserNew() {
	const [formData, setFormData] = useState({ email: "", password: "", roles: ["ROLE_USER"] });
	const [error, setError] = useState(null);
	const [validationErrors, setValidationErrors] = useState({});
	const [loading, setLoading] = useState(false);
	const navigate = useNavigate();
	const { t } = useTranslation();

	const validateForm = () => {
		const errors = {};
		
		if (!formData.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
			errors.email = t('ui.validation.invalid_email');
		}
		
		if (formData.password.length < 6) {
			errors.password = t('ui.validation.password_min_length');
		}
		
		setValidationErrors(errors);
		return Object.keys(errors).length === 0;
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		
		if (!validateForm()) return;
		
		setLoading(true);
		try {
			await api.post("/admin/users", formData);
			navigate("/users");
		} catch (err) {
			setError(err.response?.data?.message || t('ui.users.error_creating'));
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

	return (
		<div>
			<h1 className="text-3xl font-bold mb-6">{t('ui.users.new_title')}</h1>

			{error && (
				<div className="alert alert-error mb-4">
					<span>{error}</span>
				</div>
			)}

			<Card>
				<form onSubmit={handleSubmit} className="space-y-4">
					<Input
						type="email"
						label={t('ui.label.email')}
						value={formData.email}
						onChange={(e) => setFormData({ ...formData, email: e.target.value })}
						error={validationErrors.email}
						required
					/>

					<Input
						type="password"
						label={t('ui.label.password')}
						value={formData.password}
						onChange={(e) => setFormData({ ...formData, password: e.target.value })}
						error={validationErrors.password}
						required
					/>

					<div className="form-control">
						<label className="label">
							<span className="label-text">{t('ui.label.roles')}</span>
						</label>
						<div className="space-y-2">
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
					</div>

					<div className="flex gap-4">
						<Button type="submit" variant="primary" disabled={loading}>
							{loading ? t('ui.button.saving') : t('ui.button.save')}
						</Button>
						<Button type="button" variant="ghost" onClick={() => navigate("/users")}>
							{t('ui.button.cancel')}
						</Button>
					</div>
				</form>
			</Card>
		</div>
	);
}
