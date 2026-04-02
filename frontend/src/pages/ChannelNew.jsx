import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import { Button, Input, Card } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function ChannelNew() {
	const [formData, setFormData] = useState({
		name: "", description: "", category: "", icon: "",
		language: "pl", isPublic: true, maxSubscribers: "", inactivityTimeoutDays: 7,
	});
	const [error, setError] = useState(null);
	const [validationErrors, setValidationErrors] = useState({});
	const [loading, setLoading] = useState(false);
	const navigate = useNavigate();
	const { t } = useTranslation();

	const validateForm = () => {
		const errors = {};
		if (!formData.name.trim()) errors.name = t('ui.channels.name_required');
		setValidationErrors(errors);
		return Object.keys(errors).length === 0;
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		if (!validateForm()) return;
		setLoading(true);
		try {
			const payload = {
				name: formData.name,
				description: formData.description || null,
				category: formData.category || null,
				icon: formData.icon || null,
				language: formData.language,
				isPublic: formData.isPublic,
				maxSubscribers: formData.maxSubscribers ? Number(formData.maxSubscribers) : null,
				inactivityTimeoutDays: Number(formData.inactivityTimeoutDays),
			};
			await api.post("/client/channels", payload);
			navigate("/channels");
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_creating'));
		} finally {
			setLoading(false);
		}
	};

	const set = (field) => (e) => setFormData({ ...formData, [field]: e.target.value });

	return (
		<div>
			<h1 className="text-3xl font-bold mb-6">{t('ui.channels.new_title')}</h1>
			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}
			<Card>
				<form onSubmit={handleSubmit} className="space-y-4">
					<Input label={t('ui.label.name')} value={formData.name} onChange={set("name")} error={validationErrors.name} required />
					<Input label={t('ui.channels.description')} value={formData.description} onChange={set("description")} />
					<Input label={t('ui.channels.category')} value={formData.category} onChange={set("category")} />
					<Input label={t('ui.channels.icon')} value={formData.icon} onChange={set("icon")} />
					<Input label={t('ui.channels.language')} value={formData.language} onChange={set("language")} />
					<div className="form-control">
						<label className="label cursor-pointer justify-start gap-2">
							<input type="checkbox" checked={formData.isPublic} onChange={(e) => setFormData({ ...formData, isPublic: e.target.checked })} className="checkbox" />
							<span className="label-text">{t('ui.channels.is_public')}</span>
						</label>
					</div>
					<Input type="number" label={t('ui.channels.max_subscribers')} value={formData.maxSubscribers} onChange={set("maxSubscribers")} />
					<Input type="number" label={t('ui.channels.inactivity_timeout')} value={formData.inactivityTimeoutDays} onChange={set("inactivityTimeoutDays")} />
					<div className="flex gap-4">
						<Button type="submit" variant="primary" disabled={loading}>
							{loading ? t('ui.button.saving') : t('ui.button.save')}
						</Button>
						<Button type="button" variant="ghost" onClick={() => navigate("/channels")}>
							{t('ui.button.cancel')}
						</Button>
					</div>
				</form>
			</Card>
		</div>
	);
}
