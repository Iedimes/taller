@extends('layouts.app')

@section('title', 'Dashboard - Taller Mecánico')

@section('styles')
    <style>
        /* GRID LAYOUT: 4 columnas exactas */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            width: 100%;
            margin-bottom: 40px;
        }

        /* Responsive: Ajuste para pantallas más pequeñas */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ESTILO UNIFICADO DE TARJETAS (Stats y Gestión iguales) */
        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;

            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            min-height: 160px;

            border-left: 5px solid #ccc;
            /* Color base */
            text-decoration: none;
            color: inherit;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* ELEMENTOS INTERNOS */
        .dashboard-card .icon {
            font-size: 36px;
            margin-bottom: 15px;
            display: inline-block;
            width: fit-content;
        }

        .dashboard-card h3,
        .dashboard-card h2 {
            font-size: 18px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 10px;
        }

        /* Subtítulo específico para Stats (más pequeño) */
        .stat-type h3 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
        }

        .dashboard-card .value {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            line-height: 1.2;
        }

        .dashboard-card p {
            font-size: 14px;
            color: #718096;
            line-height: 1.5;
        }

        .section-title {
            font-size: 22px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <h1><span>🔧</span> Taller Mecánico</h1>
        <div class="user-info">
            <span class="user-name" id="userName">Cargando...</span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <div id="loader" style="text-align: center; padding: 40px;">
            <div class="spinner"
                style="border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto;">
            </div>
            <p style="margin-top:20px">Cargando...</p>
        </div>

        <div id="content" style="display: none;">

            <h2 class="section-title">📊 Estadísticas Generales</h2>
            <div class="dashboard-grid" id="statsGrid"></div>

            <h2 class="section-title">🚀 Gestión</h2>
            <div class="dashboard-grid">
                <a href="{{ route('customers') }}" class="dashboard-card" style="border-left-color: #667eea;">
                    <div class="icon">👥</div>
                    <h2>Clientes</h2>
                    <p>Gestionar clientes y contactos</p>
                </a>

                <a href="{{ route('vehicles') }}" class="dashboard-card" style="border-left-color: #f093fb;">
                    <div class="icon">🚗</div>
                    <h2>Vehículos</h2>
                    <p>Administrar flotas y vehículos</p>
                </a>

                <a href="{{ route('parts') }}" class="dashboard-card" style="border-left-color: #4facfe;">
                    <div class="icon">🔩</div>
                    <h2>Repuestos</h2>
                    <p>Control de inventario de repuestos</p>
                </a>

                <a href="{{ route('work-orders') }}" class="dashboard-card" style="border-left-color: #43e97b;">
                    <div class="icon">📋</div>
                    <h2>Órdenes</h2>
                    <p>Gestión de órdenes de trabajo</p>
                </a>

                <a href="{{ route('reports') }}" class="dashboard-card" style="border-left-color: #fa709a;">
                    <div class="icon">📈</div>
                    <h2>Reportes</h2>
                    <p>Analíticas y rendimiento</p>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');

        if (!token) window.location.href = '{{ route('login') }}';
        document.getElementById('userName').textContent = user.name || 'Usuario';

        async function loadDashboard() {
            try {
                const data = await apiRequest('reports/dashboard');

                const stats = [{
                        icon: '👥',
                        title: 'Clientes',
                        value: data.summary.total_customers,
                        color: '#667eea'
                    },
                    {
                        icon: '🚗',
                        title: 'Vehículos',
                        value: data.summary.total_vehicles,
                        color: '#f093fb'
                    },
                    {
                        icon: '📋',
                        title: 'Pendientes',
                        value: data.work_orders.pending,
                        color: '#f1c40f'
                    },
                    {
                        icon: '⚙️',
                        title: 'En Progreso',
                        value: data.work_orders.in_progress,
                        color: '#43e97b'
                    },
                    {
                        icon: '💰',
                        title: 'Utilidad Total',
                        value: formatMoney(data.financial.total_profit_all_time),
                        color: '#27ae60'
                    },
                    {
                        icon: '📊',
                        title: 'Mes Actual',
                        value: formatMoney(data.financial.total_profit_this_month),
                        color: '#2980b9'
                    },
                    {
                        icon: '🔩',
                        title: 'Repuestos',
                        value: data.summary.total_parts,
                        color: '#4facfe'
                    },
                    {
                        icon: '⚠️',
                        title: 'Stock Bajo',
                        value: data.summary.low_stock_parts,
                        color: '#e74c3c'
                    }
                ];

                const statsGrid = document.getElementById('statsGrid');
                statsGrid.innerHTML = stats.map(stat => `
                <div class="dashboard-card stat-type" style="border-left-color: ${stat.color}">
                    <div class="icon">${stat.icon}</div>
                    <h3>${stat.title}</h3>
                    <div class="value">${stat.value}</div>
                </div>
                `).join('');

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loader').innerHTML = `<p style="color:red">Error al cargar datos</p>`;
            }
        }

        const style = document.createElement('style');
        style.innerHTML = `
          @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        `;
        document.head.appendChild(style);

        loadDashboard();
    </script>
@endsection
