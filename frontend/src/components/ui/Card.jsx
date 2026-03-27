export default function Card({ title, children, actions, className = "" }) {
	return (
		<div className={`card bg-base-100 shadow-xl ${className}`.trim()}>
			<div className="card-body">
				{title && <h2 className="card-title">{title}</h2>}
				{children}
				{actions && <div className="card-actions justify-end">{actions}</div>}
			</div>
		</div>
	);
}
