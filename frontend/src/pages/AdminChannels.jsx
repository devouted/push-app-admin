import { useState, useEffect } from "react";
import api from "../api/axios";
import { Button, Card, Modal, Input } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function AdminChannels() {
	const [channels, setChannels] = useState([]);
	const [total, setTotal] = useState(0);
	const [page, setPage] = useState(1);
	const [statusFilter, setStatusFilter] = useState("");
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [success, setSuccess] = useState(null);
	const [blockModal, setBlockModal] = useState({ isOpen: false, channelId: null, channelName: "" });
	const [blockReason, setBlockReason] = useState("");
	const [unblockModal, setUnblockModal] = useState({ isOpen: false, channelId: null, channelName: "" });
	const { t } = useTranslation();
	const limit = 20;

	useEffect(() => {
		fetchChannels();
	}, [page, statusFilter]);

	const fetchChannels = async () => {
		try {
			setLoading(true);
			const params = { page, limit };
			if (statusFilter) params.status = statusFilter;
			const { data } = await api.get("/admin/channels", { params });
			setChannels(data.items);
			setTotal(data.total);
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.admin_channels.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const handleBlock = async () => {
		try {
			await api.patch(`/admin/channels/${blockModal.channelId}/block`, { reason: blockReason });
			setBlockModal({ isOpen: false, channelId: null, channelName: "" });
			setBlockReason("");
			setSuccess(t('ui.admin_channels.blocked'));
			setTimeout(() => setSuccess(null), 3000);
			fetchChannels();
		} catch (err) {
			setError(err.response?.data?.message || t('ui.admin_channels.error_blocking'));
			setBlockModal({ isOpen: false, channelId: null, channelName: "" });
		}
	};

	const handleUnblock = async () => {
		try {
			await api.patch(`/admin/channels/${unblockModal.channelId}/unblock`);
			setUnblockModal({ isOpen: false, channelId: null, channelName: "" });
			setSuccess(t('ui.admin_channels.unblocked'));
			setTimeout(() => setSuccess(null), 3000);
			fetchChannels();
		} catch (err) {
			setError(err.response?.data?.message || t('ui.admin_channels.error_unblocking'));
			setUnblockModal({ isOpen: false, channelId: null, channelName: "" });
		}
	};

	const totalPages = Math.ceil(total / limit);

	const statusBadge = (status) => {
		const cls = { active: "badge-success", blocked: "badge-error", inactive: "badge-warning" };
		return <span className={`badge ${cls[status] || "badge-ghost"}`}>{status}</span>;
	};

	if (loading && channels.length === 0) {
		return <div className="flex items-center justify-center min-h-screen"><span className="loading loading-spinner loading-lg" /></div>;
	}

	return (
		<div>
			<div className="flex justify-between items-center mb-6">
				<h1 className="text-3xl font-bold">{t('ui.admin_channels.title')}</h1>
				<select className="select select-bordered" value={statusFilter} onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}>
					<option value="">{t('ui.admin_channels.all_statuses')}</option>
					<option value="active">active</option>
					<option value="blocked">blocked</option>
					<option value="inactive">inactive</option>
				</select>
			</div>

			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}
			{success && <div className="alert alert-success mb-4"><span>{success}</span></div>}

			<Card>
				<div className="overflow-x-auto">
					<table className="table">
						<thead>
							<tr>
								<th>{t('ui.label.name')}</th>
								<th>{t('ui.admin_channels.owner')}</th>
								<th>{t('ui.channels.status')}</th>
								<th>{t('ui.admin_channels.blocked_reason')}</th>
								<th>{t('ui.label.created_at')}</th>
								<th className="text-right">{t('ui.channels.actions')}</th>
							</tr>
						</thead>
						<tbody>
							{channels.map((ch) => (
								<tr key={ch.id} className={ch.status === "blocked" ? "bg-red-50" : ""}>
									<td>{ch.name}</td>
									<td>{ch.ownerEmail}</td>
									<td>{statusBadge(ch.status)}</td>
									<td className="max-w-xs truncate">{ch.blockedReason || "-"}</td>
									<td>{ch.createdAt}</td>
									<td className="text-right">
										{ch.status !== "blocked" ? (
											<Button variant="error" size="sm" onClick={() => setBlockModal({ isOpen: true, channelId: ch.id, channelName: ch.name })}>
												{t('ui.admin_channels.block')}
											</Button>
										) : (
											<Button variant="success" size="sm" onClick={() => setUnblockModal({ isOpen: true, channelId: ch.id, channelName: ch.name })}>
												{t('ui.admin_channels.unblock')}
											</Button>
										)}
									</td>
								</tr>
							))}
							{channels.length === 0 && (
								<tr><td colSpan={6} className="text-center text-gray-500">{t('ui.message.no_data')}</td></tr>
							)}
						</tbody>
					</table>
				</div>

				{totalPages > 1 && (
					<div className="flex justify-center mt-4 gap-2">
						<Button variant="ghost" size="sm" disabled={page <= 1} onClick={() => setPage(page - 1)}>&laquo;</Button>
						<span className="flex items-center px-3 text-sm">{page} / {totalPages}</span>
						<Button variant="ghost" size="sm" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>&raquo;</Button>
					</div>
				)}
			</Card>

			<Modal isOpen={blockModal.isOpen} onClose={() => { setBlockModal({ isOpen: false, channelId: null, channelName: "" }); setBlockReason(""); }} title={`${t('ui.admin_channels.block')}: ${blockModal.channelName}`} actions={<><Button variant="ghost" onClick={() => { setBlockModal({ isOpen: false, channelId: null, channelName: "" }); setBlockReason(""); }}>{t('ui.button.cancel')}</Button><Button variant="error" onClick={handleBlock} disabled={blockReason.length < 3}>{t('ui.admin_channels.block')}</Button></>}>
				<Input label={t('ui.admin_channels.reason')} value={blockReason} onChange={(e) => setBlockReason(e.target.value)} error={blockReason.length > 0 && blockReason.length < 3 ? t('ui.admin_channels.reason_min') : null} />
			</Modal>

			<Modal isOpen={unblockModal.isOpen} onClose={() => setUnblockModal({ isOpen: false, channelId: null, channelName: "" })} title={`${t('ui.admin_channels.unblock')}: ${unblockModal.channelName}`} actions={<><Button variant="ghost" onClick={() => setUnblockModal({ isOpen: false, channelId: null, channelName: "" })}>{t('ui.button.cancel')}</Button><Button variant="success" onClick={handleUnblock}>{t('ui.admin_channels.unblock')}</Button></>}>
				<p>{t('ui.admin_channels.unblock_confirm')}</p>
			</Modal>
		</div>
	);
}
