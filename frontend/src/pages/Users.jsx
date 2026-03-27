import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import ConfirmModal from "../components/ConfirmModal";
import { Button, Card } from "../components/ui";

export default function Users() {
	const [users, setUsers] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [success, setSuccess] = useState(null);
	const [deleteModal, setDeleteModal] = useState({ isOpen: false, userId: null });
	const navigate = useNavigate();

	useEffect(() => {
		fetchUsers();
	}, []);

	const fetchUsers = async () => {
		try {
			setLoading(true);
			const response = await api.get("/admin/users");
			setUsers(response.data);
			setError(null);
		} catch (err) {
			setError(err.response?.data?.message || "Błąd podczas pobierania użytkowników");
		} finally {
			setLoading(false);
		}
	};

	const handleDeleteClick = (id) => {
		setDeleteModal({ isOpen: true, userId: id });
	};

	const handleDeleteConfirm = async () => {
		try {
			await api.delete(`/admin/users/${deleteModal.userId}`);
			setDeleteModal({ isOpen: false, userId: null });
			setSuccess("Użytkownik został usunięty");
			setTimeout(() => setSuccess(null), 3000);
			fetchUsers();
		} catch (err) {
			setError(err.response?.data?.message || "Błąd podczas usuwania użytkownika");
			setDeleteModal({ isOpen: false, userId: null });
		}
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
			<div className="flex justify-between items-center mb-6">
				<h1 className="text-3xl font-bold">Użytkownicy</h1>
				<Button variant="primary" onClick={() => navigate("/users/new")}>
					Dodaj użytkownika
				</Button>
			</div>

			{error && (
				<div className="alert alert-error mb-4">
					<span>{error}</span>
				</div>
			)}

			{success && (
				<div className="alert alert-success mb-4">
					<span>{success}</span>
				</div>
			)}

			<Card>
				<div className="overflow-x-auto">
					<table className="table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Email</th>
								<th>Role</th>
								<th className="text-right">Akcje</th>
							</tr>
						</thead>
						<tbody>
							{users.map((user) => (
								<tr key={user.id}>
									<td>{user.id}</td>
									<td>{user.email}</td>
									<td>{user.roles.join(", ")}</td>
									<td className="text-right">
										<Button
											variant="ghost"
											size="sm"
											onClick={() => navigate(`/users/${user.id}/edit`)}
											className="mr-2"
										>
											Edytuj
										</Button>
										<Button
											variant="error"
											size="sm"
											onClick={() => handleDeleteClick(user.id)}
										>
											Usuń
										</Button>
									</td>
								</tr>
							))}
						</tbody>
					</table>
				</div>
			</Card>

			<ConfirmModal
				isOpen={deleteModal.isOpen}
				onClose={() => setDeleteModal({ isOpen: false, userId: null })}
				onConfirm={handleDeleteConfirm}
				title="Usuń użytkownika"
				message="Czy na pewno chcesz usunąć tego użytkownika?"
			/>
		</div>
	);
}