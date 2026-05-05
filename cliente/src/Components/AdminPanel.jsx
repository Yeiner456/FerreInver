import { useState } from "react";
import { useNavigate } from "react-router-dom";
import ClientesCrud from "./ClientesCrud";
import ComprasCrud from "./ComprasCrud";
import CotizacionesCrud from "./CotizacionesCrud";
import InvernaderoCRUD from "./InvernaderosCrud";
import PedidosCrud from "./PedidosCrud";
import ProductosCrud from "./ProductosCrud";
import ProveedoresCrud from "./ProveedoresCrud";
import StockCrud from "./StockCrud";
import TiposUsuariosCrud from "./TiposUsuariosCrud";
import "../styles/AdminPanel.css"
import ProductosPedidosCRUD from "./ProductosPedidosCrud";
import NotificacionesCrud from "./NotificacionesCrud";


const NAV_ITEMS = [
    { key: "clientes", label: "Clientes", component: ClientesCrud },
    { key: "tipos_usuarios", label: "Tipos de Usuarios", component: TiposUsuariosCrud },
    { key: "proveedores", label: "Proveedores", component: ProveedoresCrud },
    { key: "productos", label: "Productos", component: ProductosCrud },
    { key: "stock", label: "Stock", component: StockCrud },
    { key: "pedidos", label: "Pedidos", component: PedidosCrud },
    { key: "compras", label: "Compras", component: ComprasCrud },
    { key: "invernaderos", label: "Invernaderos", component: InvernaderoCRUD },
    { key: "cotizaciones", label: "Cotizaciones", component: CotizacionesCrud },
    { key: "productospedidos", label: "ProductosPedidos", component: ProductosPedidosCRUD  },
    { key: "notificaciones", label: "Notificaciones", component: NotificacionesCrud  },
];

export default function AdminPanel() {
    const [active, setActive] = useState(null);
    const navigate = useNavigate();

    const current = NAV_ITEMS.find((n) => n.key === active);
    const ActiveComponent = current?.component ?? null;

    return (
        <>
            <div className="admin-shell">

                {/* SIDEBAR */}
                <aside className="sidebar">
                    <div className="sidebar-logo">
                        
                        <span className="logo-text">Ferreinver</span>
                    </div>
                    <div className="sidebar-section">Navegación</div>
                    <ul className="nav-list">
                        <li className="nav-item" onClick={() => navigate("/inicio")}>
                            <span className="nav-icon"></span>
                            <span className="nav-label">Ir al Inicio</span>
                        </li>
                    </ul>

                    <div className="sidebar-section">Módulos</div>

                    <ul className="nav-list">
                        {NAV_ITEMS.map((item) => (
                            <li
                                key={item.key}
                                className={`nav-item ${active === item.key ? "active" : ""}`}
                                onClick={() => setActive(item.key)}
                            >
                                
                                <span className="nav-label">{item.label}</span>
                            </li>
                        ))}
                    </ul>

                    <div className="sidebar-footer">
                        ferreinver © 2026
                    </div>
                </aside>

                {/* MAIN */}
                <div className="main-area">
                    <header className="topbar">
                        <span className="topbar-crumb">Admin</span>
                        <span className="topbar-sep">/</span>
                        <span className="topbar-title">{current?.label ?? "Panel"}</span>
                        
                    </header>

                    <main className="content-area">
                        {ActiveComponent ? (
                            <ActiveComponent />
                        ) : (
                            <div className="welcome-screen">
                                <h1 className="welcome-title">
                                    Panel <span>Admin</span>
                                </h1>
                                <p className="welcome-sub">
                                    Selecciona un módulo en el menú lateral para comenzar a gestionar los datos.
                                </p>
                                <div className="welcome-grid">
                                    {NAV_ITEMS.map((item) => (
                                        <div
                                            key={item.key}
                                            className="welcome-card"
                                            onClick={() => setActive(item.key)}
                                        >
                                            <span className="wc-icon">{item.icon}</span>
                                            <span className="wc-label">{item.label}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </main>
                </div>

            </div>
        </>
    );
}