import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../api/axios";
import { Button, Card } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function ChannelNotifications() {
	const { id } = useParams();
	const [notifications, setNotifications] = useState([]);
	const [total, setTotal] = useState(0);
	const [page, setPage] = useState(1);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [detail, setDetail] = useState(null);
	const navigate = useNavigate();
	const { t } = useTranslation();
	const limit = 20;

	useEffect(() => {
		fetchNotifications();
	}, [page]);

	const fetchNotifications = async () => {
		try {
			setLoading(true);
			const { data } = await api.get(`/client/channels/${id}/notifications`, { params: { page, limit } });
			setNotifications(data.items);
			setTotal(data.total);
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.notifications.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const fetchDetail = async (notifId) => {
		try {
			const { data } = await api.get(`/notifications/${notifId}`);
			setDetail(data);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.notifications.error_loading'));
		}
	};

	const totalPages = Math.ceil(total / limit);

	if (loading && notifications.length === 0) {
		return <div className="flex items-center justify-center min-h-screen"><span className="loading loading-spinner loading-lg" /></div>;
	}

	if (detail) {
		return (
			<div>
				<div className="flex justify-between items-center mb-6">
					<h1 className="text-3xl font-bold">{detail.title}</h1>
					<Button variant="ghost" onClick={() => setDetail(null)}>{t('ui.button.back')}</Button>
				</div>
				<Card>
					<div className="space-y-4">
						<div><span className="text-gray-500 text-sm">{t('ui.notifications.body')}</span><p className="mt-1">{detail.body}</p></div>
						{detail.imageUrl && (
							<div><span className="text-gray-500 text-sm">{t('ui.notifications.image')}</span><img src={detail.imageUrl} alt="" className="mt-1 max-w-md rounded" /></div>
						)}
						{detail.extraData && (
							<div><span className="text-gray-500 text-sm">{t('ui.notifications.extra_data')}</span><pre className="mt-1 bg-base-200 p-2 rounded text-xs overflow-auto">{JSON.stringify(detail.extraData, null, 2)}</pre></div>
						)}
						<div className="text-sm text-gray-500">{t('ui.label.created_at')}: {detail.createdAt}</div>
					</div>
				</Card>
			</div>
		);
	}

	return (
		<div>
			<div className="flex justify-between items-center mb-6">
				<h1 className="text-3xl font-bold">{t('ui.notifications.title')}</h1>
				<Button variant="ghost" onClick={() => navigate(`/channels/${id}`)}>{t('ui.button.back')}</Button>
			</div>

			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}

			<Card>
				<div className="overflow-x-auto">
					<table className="table">
						<thead>
							<tr>
								<th>{t('ui.notifications.notif_title')}</th>
								<th>{t('ui.label.created_at')}</th>
								<th>{t('ui.notifications.type')}</th>
								<th className="text-right">{t('ui.channels.actions')}</th>
							</tr>
						</thead>
						<tbody>
							{notifications.map((n) => (
								<tr key={n.id}>
									<td>{n.title}</td>
									<td>{n.createdAt}</td>
									<td>{n.isTest ? <span className="badge badge-warning">test</span> : <span className="badge badge-info">push</span>}</td>
									<td className="text-right">
										<Button variant="ghost" size="sm" onClick={() => fetchDetail(n.id)}>
											{t('ui.channels.details')}
										</Button>
									</td>
								</tr>
							))}
							{notifications.length === 0 && (
								<tr><td colSpan={4} className="text-center text-gray-500">{t('ui.message.no_data')}</td></tr>
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
		</div>
	);
}
