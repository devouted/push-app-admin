import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import ConfirmModal from "../components/ConfirmModal";
import { Button, Card } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function Channels() {
	const [channels, setChannels] = useState([]);
	const [total, setTotal] = useState(0);
	const [page, setPage] = useState(1);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [success, setSuccess] = useState(null);
	const [deleteModal, setDeleteModal] = useState({ isOpen: false, channelId: null });
	const navigate = useNavigate();
	const { t } = useTranslation();
	const limit = 20;

	useEffect(() => {
		fetchChannels();
	}, [page]);

	const fetchChannels = async () => {
		try {
			setLoading(true);
			const response = await api.get("/client/channels", { params: { page, limit } });
			setChannels(response.data.items);
			setTotal(response.data.total);
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const handleDeleteClick = (id) => {
		setDeleteModal({ isOpen: true, channelId: id });
	};

	const handleDeleteConfirm = async () => {
		try {
			await api.delete(`/client/channels/${deleteModal.channelId}`);
			setDeleteModal({ isOpen: false, channelId: null });
			setSuccess(t('ui.channels.deleted'));
			setTimeout(() => setSuccess(null), 3000);
			fetchChannels();
		} catch (err) {
			setError(err.response?.data?.message || t('ui.channels.error_deleting'));
			setDeleteModal({ isOpen: false, channelId: null });
		}
	};

	const totalPages = Math.ceil(total / limit);

	const statusBadge = (status) => {
		const cls = {
			active: "badge-success",
			blocked: "badge-error",
			inactive: "badge-warning",
		};
		return <span className={`badge ${cls[status] || "badge-ghost"}`}>{status}</span>;
	};

	if (loading && channels.length === 0) {
		return (
			<div className="flex items-center justify-center min-h-screen">
				<span className="loading loading-spinner loading-lg" />
			</div>
		);
	}

	return (
		<div>
			<div className="flex justify-between items-center mb-6">
				<h1 className="text-3xl font-bold">{t('ui.channels.title')}</h1>
				<Button variant="primary" onClick={() => navigate("/channels/new")}>
					{t('ui.channels.add')}
				</Button>
			</div>

			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}
			{success && <div className="alert alert-success mb-4"><span>{success}</span></div>}

			<Card>
				<div className="overflow-x-auto">
					<table className="table">
						<thead>
							<tr>
								<th>{t('ui.label.name')}</th>
								<th>{t('ui.channels.status')}</th>
								<th>{t('ui.label.created_at')}</th>
								<th className="text-right">{t('ui.channels.actions')}</th>
							</tr>
						</thead>
						<tbody>
							{channels.map((ch) => (
								<tr key={ch.id}>
									<td>{ch.name}</td>
									<td>{statusBadge(ch.status)}</td>
									<td>{ch.createdAt}</td>
									<td className="text-right">
										<Button variant="ghost" size="sm" onClick={() => navigate(`/channels/${ch.id}`)} className="mr-2">
											{t('ui.channels.details')}
										</Button>
										<Button variant="ghost" size="sm" onClick={() => navigate(`/channels/${ch.id}/edit`)} className="mr-2">
											{t('ui.button.edit')}
										</Button>
										<Button variant="error" size="sm" onClick={() => handleDeleteClick(ch.id)}>
											{t('ui.button.delete')}
										</Button>
									</td>
								</tr>
							))}
							{channels.length === 0 && (
								<tr><td colSpan={4} className="text-center text-gray-500">{t('ui.message.no_data')}</td></tr>
							)}
						</tbody>
					</table>
				</div>

				{totalPages > 1 && (
					<div className="flex justify-center mt-4 gap-2">
						<Button variant="ghost" size="sm" disabled={page <= 1} onClick={() => setPage(page - 1)}>
							&laquo;
						</Button>
						<span className="flex items-center px-3 text-sm">
							{page} / {totalPages}
						</span>
						<Button variant="ghost" size="sm" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>
							&raquo;
						</Button>
					</div>
				)}
			</Card>

			<ConfirmModal
				isOpen={deleteModal.isOpen}
				onClose={() => setDeleteModal({ isOpen: false, channelId: null })}
				onConfirm={handleDeleteConfirm}
				title={t('ui.channels.delete_title')}
				message={t('ui.channels.delete_confirm')}
			/>
		</div>
	);
}
