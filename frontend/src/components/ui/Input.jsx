export default function Input({ type = "text", label, error, className = "", ...props }) {
	return (
		<div className="form-control w-full">
			{label && (
				<label className="label">
					<span className="label-text">{label}</span>
				</label>
			)}
			<input type={type} className={`input input-bordered w-full ${error ? "input-error" : ""} ${className}`.trim()} {...props} />
			{error && (
				<label className="label">
					<span className="label-text-alt text-error">{error}</span>
				</label>
			)}
		</div>
	);
}
