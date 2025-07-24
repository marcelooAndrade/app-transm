 <!-- Sidebar -->
        <div class="w-64 bg-card border-r border-border p-4 space-y-6">
            <div class="p-4 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-md flex items-center justify-center">
                        <span class="text-white font-bold text-sm">TM</span>
                    </div>
                    <div>
                        <h2 class="font-semibold text-foreground">TransM</h2>
                        <p class="text-xs text-muted-foreground">Dashboard</p>
                    </div>
                </div>
            </div>

            <nav class="space-y-6">
                <!-- Principal -->
                <div class="space-y-2">
                    <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Principal</h3>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-medium border-r-2 border-primary' : 'text-foreground hover:bg-secondary/50' }}">
                        <i data-lucide="grid-2x2" class="w-4 h-4"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('pedidos.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('pedidos.*') ? 'bg-primary/10 text-primary font-medium border-r-2 border-primary' : 'text-foreground hover:bg-secondary/50' }}">
                        <i data-lucide="database" class="w-4 h-4"></i>
                        Pedidos
                    </a>
                    <a href="{{ route('rotas.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('rotas.*') ? 'bg-primary/10 text-primary font-medium border-r-2 border-primary' : 'text-foreground hover:bg-secondary/50' }}">
                        <i data-lucide="folder" class="w-4 h-4"></i>
                        Rotas
                    </a>
                    <a href="{{ route('tipos-caminhao.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('tipos-caminhao.*') ? 'bg-primary/10 text-primary font-medium border-r-2 border-primary' : 'text-foreground hover:bg-secondary/50' }}">
                        <i data-lucide="file" class="w-4 h-4"></i>
                        Tipos de Caminhão
                    </a>
                </div>

                <!-- Gestão -->
                <div class="space-y-2">
                    <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Gestão</h3>
                    <a href="{{ route('usuarios.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-primary/10 text-primary font-medium border-r-2 border-primary' : 'text-foreground hover:bg-secondary/50' }}">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        Usuários
                    </a>
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-foreground hover:bg-secondary/50">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configurações
                    </a>
                </div>
            </nav>
        </div>
