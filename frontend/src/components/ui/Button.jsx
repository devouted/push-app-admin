export default function Button({ variant = "primary", size = "md", children, className = "", ...props }) {
	const sizeClass = size !== "md" ? `btn-${size}` : "";
	return (
		<button className={`btn btn-${variant} ${sizeClass} ${className}`.trim()} {...props}>
			{children}
		</button>
	);
}
