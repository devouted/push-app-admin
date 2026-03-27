export default function Modal({ isOpen, onClose, title, children, actions }) {
	if (!isOpen) return null;

	return (
		<dialog className="modal modal-open">
			<div className="modal-box">
				{title && <h3 className="font-bold text-lg">{title}</h3>}
				<div className="py-4">{children}</div>
				{actions && <div className="modal-action">{actions}</div>}
			</div>
			<form method="dialog" className="modal-backdrop" onClick={onClose}>
				<button type="button">close</button>
			</form>
		</dialog>
	);
}
