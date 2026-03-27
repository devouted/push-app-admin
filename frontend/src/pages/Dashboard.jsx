export default function Dashboard() {
	return (
		<div>
			<div className="mb-8">
				<h1 className="text-4xl font-bold text-gray-900 mb-2">Dashboard</h1>
				<p className="text-gray-600">Witaj w panelu CRM</p>
			</div>
			
			<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
				<div className="card bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-xl">
					<div className="card-body">
						<h2 className="card-title text-white/90">Użytkownicy</h2>
						<p className="text-4xl font-bold">124</p>
						<p className="text-white/80 text-sm">Aktywnych użytkowników</p>
					</div>
				</div>
				
				<div className="card bg-gradient-to-br from-cyan-400 to-cyan-600 text-white shadow-xl">
					<div className="card-body">
						<h2 className="card-title text-white/90">Klienci</h2>
						<p className="text-4xl font-bold">89</p>
						<p className="text-white/80 text-sm">Aktywnych klientów</p>
					</div>
				</div>
				
				<div className="card bg-gradient-to-br from-green-400 to-green-600 text-white shadow-xl">
					<div className="card-body">
						<h2 className="card-title text-white/90">Projekty</h2>
						<p className="text-4xl font-bold">42</p>
						<p className="text-white/80 text-sm">W trakcie realizacji</p>
					</div>
				</div>
				
				<div className="card bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-xl">
					<div className="card-body">
						<h2 className="card-title text-white/90">Zadania</h2>
						<p className="text-4xl font-bold">156</p>
						<p className="text-white/80 text-sm">Do wykonania</p>
					</div>
				</div>
			</div>
		</div>
	);
}