import { useState, useEffect } from "react";
import api from "../api/axios";
import { Button, Card } from "../components/ui";
import { useTranslation } from "../context/TranslationContext";

export default function AdminClientsConsumers() {
	const [tab, setTab] = useState("clients");
	const [clients, setClients] = useState([]);
	const [consumers, setConsumers] = useState([]);
	const [consumerTotal, setConsumerTotal] = useState(0);
	const [consumerPage, setConsumerPage] = useState(1);
	const [expandedClient, setExpandedClient] = useState(null);
	const [clientChannels, setClientChannels] = useState({});
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const { t } = useTranslation();
	const limit = 20;

	useEffect(() => {
		if (tab === "clients") fetchClients();
		else fetchConsumers();
	}, [tab, consumerPage]);

	const fetchClients = async () => {
		try {
			setLoading(true);
			const { data } = await api.get("/admin/users");
			setClients(data.filter((u) => u.roles.includes("ROLE_CLIENT")));
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.admin_clients.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const fetchConsumers = async () => {
		try {
			setLoading(true);
			const { data } = await api.get("/admin/consumers", { params: { page: consumerPage, limit } });
			setConsumers(data.items);
			setConsumerTotal(data.total);
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || t('ui.admin_clients.error_loading'));
		} finally {
			setLoading(false);
		}
	};

	const toggleClientChannels = async (clientId) => {
		if (expandedClient === clientId) {
			setExpandedClient(null);
			return;
		}
		if (!clientChannels[clientId]) {
			try {
				const { data } = await api.get("/admin/channels", { params: { limit: 100 } });
				const filtered = data.items.filter((ch) => ch.ownerId === clientId);
				setClientChannels((prev) => ({ ...prev, [clientId]: filtered }));
			} catch {
				setClientChannels((prev) => ({ ...prev, [clientId]: [] }));
			}
		}
		setExpandedClient(clientId);
	};

	const consumerTotalPages = Math.ceil(consumerTotal / limit);

	if (loading && clients.length === 0 && consumers.length === 0) {
		return <div className="flex items-center justify-center min-h-screen"><span className="loading loading-spinner loading-lg" /></div>;
	}

	return (
		<div>
			<h1 className="text-3xl font-bold mb-6">{t('ui.admin_clients.title')}</h1>

			<div className="tabs tabs-boxed mb-6">
				<button type="button" className={`tab ${tab === "clients" ? "tab-active" : ""}`} onClick={() => setTab("clients")}>{t('ui.admin_clients.clients_tab')}</button>
				<button type="button" className={`tab ${tab === "consumers" ? "tab-active" : ""}`} onClick={() => setTab("consumers")}>{t('ui.admin_clients.consumers_tab')}</button>
			</div>

			{error && <div className="alert alert-error mb-4"><span>{error}</span></div>}

			{tab === "clients" && (
				<Card>
					<div className="overflow-x-auto">
						<table className="table">
							<thead>
								<tr>
									<th>ID</th>
									<th>{t('ui.label.email')}</th>
									<th>{t('ui.label.roles')}</th>
									<th className="text-right">{t('ui.channels.actions')}</th>
								</tr>
							</thead>
							<tbody>
								{clients.map((c) => (
									<>
										<tr key={c.id}>
											<td>{c.id}</td>
											<td>{c.email}</td>
											<td>{c.roles.join(", ")}</td>
											<td className="text-right">
												<Button variant="ghost" size="sm" onClick={() => toggleClientChannels(c.id)}>
													{expandedClient === c.id ? t('ui.admin_clients.hide_channels') : t('ui.admin_clients.show_channels')}
												</Button>
											</td>
										</tr>
										{expandedClient === c.id && (
											<tr key={`${c.id}-channels`}>
												<td colSpan={4} className="bg-base-200 p-4">
													{(clientChannels[c.id] || []).length === 0 ? (
														<span className="text-gray-500">{t('ui.message.no_data')}</span>
													) : (
														<table className="table table-sm">
															<thead><tr><th>{t('ui.label.name')}</th><th>{t('ui.channels.status')}</th><th>{t('ui.label.created_at')}</th></tr></thead>
															<tbody>
																{clientChannels[c.id].map((ch) => (
																	<tr key={ch.id}>
																		<td>{ch.name}</td>
																		<td><span className={`badge ${ch.status === "active" ? "badge-success" : ch.status === "blocked" ? "badge-error" : "badge-warning"}`}>{ch.status}</span></td>
																		<td>{ch.createdAt}</td>
																	</tr>
																))}
															</tbody>
														</table>
													)}
												</td>
											</tr>
										)}
									</>
								))}
								{clients.length === 0 && <tr><td colSpan={4} className="text-center text-gray-500">{t('ui.message.no_data')}</td></tr>}
							</tbody>
						</table>
					</div>
				</Card>
			)}

			{tab === "consumers" && (
				<Card>
					<div className="overflow-x-auto">
						<table className="table">
							<thead>
								<tr>
									<th>UUID</th>
									<th>{t('ui.admin_clients.device')}</th>
									<th>{t('ui.admin_clients.os')}</th>
									<th>{t('ui.label.created_at')}</th>
									<th>{t('ui.admin_clients.last_active')}</th>
									<th>{t('ui.admin_clients.subscriptions')}</th>
								</tr>
							</thead>
							<tbody>
								{consumers.map((c) => (
									<tr key={c.id}>
										<td className="text-xs font-mono">{c.id.substring(0, 8)}...</td>
										<td>{c.deviceName || "-"} {c.deviceModel ? `(${c.deviceModel})` : ""}</td>
										<td>{c.deviceOs || "-"} {c.deviceOsVersion || ""}</td>
										<td>{c.createdAt}</td>
										<td>{c.lastActiveAt || "-"}</td>
										<td>{c.activeSubscriptions}</td>
									</tr>
								))}
								{consumers.length === 0 && <tr><td colSpan={6} className="text-center text-gray-500">{t('ui.message.no_data')}</td></tr>}
							</tbody>
						</table>
					</div>

					{consumerTotalPages > 1 && (
						<div className="flex justify-center mt-4 gap-2">
							<Button variant="ghost" size="sm" disabled={consumerPage <= 1} onClick={() => setConsumerPage(consumerPage - 1)}>&laquo;</Button>
							<span className="flex items-center px-3 text-sm">{consumerPage} / {consumerTotalPages}</span>
							<Button variant="ghost" size="sm" disabled={consumerPage >= consumerTotalPages} onClick={() => setConsumerPage(consumerPage + 1)}>&raquo;</Button>
						</div>
					)}
				</Card>
			)}
		</div>
	);
}
