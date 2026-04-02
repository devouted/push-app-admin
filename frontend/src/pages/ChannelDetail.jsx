import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../api/axios";
import { Button, Card, Modal } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function ChannelDetail() {
	const { id } = useParams();
	const [channel, setChannel] = useState(null);
	const [showKey, setShowKey] = useState(false);
	const [error, setError] = useState(null);
	const [success, setSuccess] = useState(null);
	const [loading, setLoading] = useState(true);
	const [rotateModal, setRotateModal] = useState(false);
	const [testModal, setTestModal] = useState(false);
	const [testTitle, setTestTitle] = useState("");
	const [testBody, setTestBody] = useState("");
	const navigate = useNavigate();
	const { t } = useTranslation();

	useEffect(() => {
		fetchChannel();
	}, [id]);

	const fetchChannel = async () => {
		try {
			const { data } = await api.get(`/client/channels/${id}`);
			setChannel(data);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const handleRotateKey = async () => {
		try {
			const { data } = await api.post(`/client/channels/${id}/rotate-key`);
			setChannel(data);
			setRotateModal(false);
			setSuccess(t('ui.channels.key_rotated'));
			setTimeout(() => setSuccess(null), 3000);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_rotating'));
		}
	};

	const handleTest = async () => {
		try {
			const payload = {};
			if (testTitle.trim()) payload.title = testTitle;
			if (testBody.trim()) payload.body = testBody;
			await api.post(`/client/channels/${id}/test`, payload);
			setTestModal(false);
			setTestTitle("");
			setTestBody("");
			setSuccess(t('ui.channels.test_sent'));
			setTimeout(() => setSuccess(null), 3000);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_testing'));
		}
	};

	if (loading) {
		return <div className="flex items-center justify-center min-h-screen"><span className="loading loading-spinner loading-lg" /></div>;
	}

	if (!channel) {
		return <div className="alert alert-error"><span>{error}</span></div>;
	}

	return (
		<div>
			<div className="flex justify-between items-center mb-6">
				<h1 className="text-3xl font-bold">{channel.name}</h1>
				<div className="flex gap-2">
					<Button variant="ghost" onClick={() => navigate("/channels")}>{t('ui.button.back')}</Button>
					<Button variant="ghost" onClick={() => navigate(`/channels/${id}/edit`)}>{t('ui.button.edit')}</Button>
					<Button variant="secondary" onClick={() => navigate(`/channels/${id}/notifications`)}>{t('ui.notifications.title')}</Button>
					<Button variant="primary" onClick={() => setTestModal(true)}>{t('ui.channels.test')}</Button>
				</div>
			</div>

			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}
			{success && <div className="alert alert-success mb-4"><span>{success}</span></div>}

			{channel.status === "blocked" && (
				<div className="alert alert-error mb-4">
					<span>{t('ui.channels.blocked_banner')}: {channel.blockedReason}</span>
				</div>
			)}

			<div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
				<Card title={t('ui.channels.metadata')}>
					<div className="space-y-2 text-sm">
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.status')}</span><span className={`badge ${channel.status === "active" ? "badge-success" : channel.status === "blocked" ? "badge-error" : "badge-warning"}`}>{channel.status}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.description')}</span><span>{channel.description || "-"}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.category')}</span><span>{channel.category || "-"}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.language')}</span><span>{channel.language}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.is_public')}</span><span>{channel.isPublic ? t('ui.channels.yes') : t('ui.channels.no')}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.max_subscribers')}</span><span>{channel.maxSubscribers ?? "-"}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.channels.inactivity_timeout')}</span><span>{channel.inactivityTimeoutDays}</span></div>
						<div className="flex justify-between"><span className="text-gray-500">{t('ui.label.created_at')}</span><span>{channel.createdAt}</span></div>
					</div>
				</Card>

				<Card title="API Key" actions={<Button variant="secondary" size="sm" onClick={() => setRotateModal(true)}>{t('ui.channels.rotate_key')}</Button>}>
					<div className="flex items-center gap-2">
						<code className="flex-1 bg-base-200 p-2 rounded text-xs break-all">
							{showKey ? channel.apiKey : "••••••••••••••••••••••••••••••••"}
						</code>
						<Button variant="ghost" size="sm" onClick={() => setShowKey(!showKey)}>
							{showKey ? t('ui.channels.hide') : t('ui.channels.show')}
						</Button>
					</div>
				</Card>
			</div>

			<Modal isOpen={rotateModal} onClose={() => setRotateModal(false)} title={t('ui.channels.rotate_key')} actions={<><Button variant="ghost" onClick={() => setRotateModal(false)}>{t('ui.button.cancel')}</Button><Button variant="error" onClick={handleRotateKey}>{t('ui.channels.rotate_confirm')}</Button></>}>
				<p>{t('ui.channels.rotate_warning')}</p>
			</Modal>

			<Modal isOpen={testModal} onClose={() => setTestModal(false)} title={t('ui.channels.test')} actions={<><Button variant="ghost" onClick={() => setTestModal(false)}>{t('ui.button.cancel')}</Button><Button variant="primary" onClick={handleTest}>{t('ui.channels.send_test')}</Button></>}>
				<div className="space-y-4">
					<div className="form-control">
						<label className="label"><span className="label-text">{t('ui.channels.test_title')}</span></label>
						<input className="input input-bordered w-full" value={testTitle} onChange={(e) => setTestTitle(e.target.value)} placeholder={t('ui.channels.test_title_placeholder')} />
					</div>
					<div className="form-control">
						<label className="label"><span className="label-text">{t('ui.channels.test_body')}</span></label>
						<textarea className="textarea textarea-bordered w-full" value={testBody} onChange={(e) => setTestBody(e.target.value)} placeholder={t('ui.channels.test_body_placeholder')} />
					</div>
				</div>
			</Modal>
		</div>
	);
}
