import { useState, useEffect } from "react";
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
import "../styles/AdminPanel.css";
import ProductosPedidosCRUD from "./ProductosPedidosCrud";
import NotificacionesCrud from "./NotificacionesCrud";

const NAV_ITEMS = [
    { key: "clientes",        label: "Clientes",           component: ClientesCrud },
    { key: "tipos_usuarios",  label: "Tipos de Usuarios",  component: TiposUsuariosCrud },
    { key: "proveedores",     label: "Proveedores",        component: ProveedoresCrud },
    { key: "productos",       label: "Productos",          component: ProductosCrud },
    { key: "stock",           label: "Stock",              component: StockCrud },
    { key: "pedidos",         label: "Pedidos",            component: PedidosCrud },
    { key: "compras",         label: "Compras",            component: ComprasCrud },
    { key: "invernaderos",    label: "Invernaderos",       component: InvernaderoCRUD },
    { key: "cotizaciones",    label: "Cotizaciones",       component: CotizacionesCrud },
    { key: "productospedidos",label: "ProductosPedidos",   component: ProductosPedidosCRUD },
    { key: "notificaciones",  label: "Notificaciones",     component: NotificacionesCrud },
];

export default function AdminPanel() {
    const [active, setActive] = useState(null);
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const navigate = useNavigate();

    const current = NAV_ITEMS.find((n) => n.key === active);
    const ActiveComponent = current?.component ?? null;

    // Cerrar sidebar con ESC
    useEffect(() => {
        const handleKey = (e) => {
            if (e.key === "Escape") setSidebarOpen(false);
        };
        document.addEventListener("keydown", handleKey);
        return () => document.removeEventListener("keydown", handleKey);
    }, []);

    // Bloquear scroll del body cuando sidebar está abierto en móvil
    useEffect(() => {
        if (sidebarOpen) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
        return () => { document.body.style.overflow = ""; };
    }, [sidebarOpen]);

    const handleNavClick = (key) => {
        setActive(key);
        setSidebarOpen(false); // cerrar sidebar en móvil al seleccionar
    };

    const handleIrInicio = () => {
        navigate("/inicio");
        setSidebarOpen(false);
    };

    return (
        <div className="admin-shell">

            {/* OVERLAY MÓVIL — clic fuera cierra el sidebar */}
            {sidebarOpen && (
                <div
                    className="sidebar-overlay"
                    onClick={() => setSidebarOpen(false)}
                    aria-hidden="true"
                />
            )}

            {/* SIDEBAR */}
            <aside className={`sidebar ${sidebarOpen ? "sidebar--open" : ""}`}>
                <div className="sidebar-logo">
                    <span className="logo-dot" />
                    <span className="logo-text">Ferreinver</span>

                    {/* Botón cerrar (solo visible en móvil dentro del sidebar) */}
                    <button
                        className="sidebar-close-btn"
                        onClick={() => setSidebarOpen(false)}
                        aria-label="Cerrar menú"
                    >
                        <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                            <path d="M18 6L6 18M6 6l12 12"
                                stroke="currentColor" strokeWidth="2"
                                strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                    </button>
                </div>

                <div className="sidebar-section">Navegación</div>
                <ul className="nav-list">
                    <li className="nav-item" onClick={handleIrInicio}>
                        <span className="nav-icon">
                            <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                                <path d="M3 12L12 3l9 9" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M9 21V12h6v9" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                        </span>
                        <span className="nav-label">Ir al Inicio</span>
                    </li>
                </ul>

                <div className="sidebar-section">Módulos</div>
                <ul className="nav-list">
                    {NAV_ITEMS.map((item) => (
                        <li
                            key={item.key}
                            className={`nav-item ${active === item.key ? "active" : ""}`}
                            onClick={() => handleNavClick(item.key)}
                        >
                            <span className="nav-label">{item.label}</span>
                        </li>
                    ))}
                </ul>

                <div className="sidebar-footer">ferreinver © 2026</div>
            </aside>

            {/* MAIN */}
            <div className="main-area">
                <header className="topbar">
                    {/* Botón hamburguesa — solo visible en móvil */}
                    <button
                        className="hamburger-btn"
                        onClick={() => setSidebarOpen(true)}
                        aria-label="Abrir menú"
                    >
                        <span className="hamburger-line" />
                        <span className="hamburger-line" />
                        <span className="hamburger-line" />
                    </button>

                    <div className="topbar-breadcrumb">
                        <span className="topbar-crumb">Admin</span>
                        <span className="topbar-sep">/</span>
                        <span className="topbar-title">{current?.label ?? "Panel"}</span>
                    </div>
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
                                        <span className="wc-label">{item.label}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
}