import { Modal, Button } from "./ui";

export default function ConfirmModal({ isOpen, onClose, onConfirm, title, message }) {
	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			title={title}
			actions={
				<>
					<Button variant="ghost" onClick={onClose}>
						Anuluj
					</Button>
					<Button variant="error" onClick={onConfirm}>
						Usuń
					</Button>
				</>
			}
		>
			<p>{message}</p>
		</Modal>
	);
}
