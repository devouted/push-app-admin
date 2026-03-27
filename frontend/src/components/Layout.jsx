import Header from "./layout/Header";
import Nav from "./layout/Nav";

function Layout({ children }) {
	return (
		<div className="min-h-screen flex flex-col bg-gradient-to-br from-gray-50 to-blue-50">
			<Header />
			<Nav />
			<main className="flex-1 p-8 max-w-7xl w-full mx-auto">
				{children}
			</main>
		</div>
	);
}

export default Layout;
