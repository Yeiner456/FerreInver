import { useState, useEffect, useCallback } from "react";
import "../styles/Notificaciones.css";

const API_URL = import.meta.env.VITE_API_URL

const api = {
    getNotificaciones: () =>
        fetch(`${API_URL}/notificaciones`).then((r) => r.json()),

    createNotificacion: (data) =>
        fetch(`${API_URL}/notificaciones`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateNotificacion: (id, data) =>
        fetch(`${API_URL}/notificaciones/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    marcarLeida: (id) =>
        fetch(`${API_URL}/notificaciones/${id}/marcar-leida`, {
            method: "PATCH",
        }).then((r) => r.json()),

    deleteNotificacion: (id) =>
        fetch(`${API_URL}/notificaciones/${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const TIPOS = ["info", "alerta", "pedido", "cotizacion", "sistema"];

const TIPO_LABELS = {
    info:       "Info",
    alerta:     "Alerta",
    pedido:     "Pedido",
    cotizacion: "Cotización",
    sistema:    "Sistema",
};

const emptyForm = {
    documento_cliente: "",
    titulo: "",
    mensaje: "",
    tipo: "info",
};

function formatFecha(fechaStr) {
    if (!fechaStr) return "—";
    const d = new Date(fechaStr);
    return (
        d.toLocaleDateString("es-CO", {
            day: "2-digit", month: "short", year: "numeric",
        }) +
        " · " +
        d.toLocaleTimeString("es-CO", { hour: "2-digit", minute: "2-digit" })
    );
}

function validate(form) {
    const errors = {};
    if (!form.documento_cliente || isNaN(form.documento_cliente) || Number(form.documento_cliente) <= 0)
        errors.documento_cliente = "Documento válido requerido.";
    if (!form.titulo || form.titulo.trim().length === 0)
        errors.titulo = "El título es obligatorio.";
    if (form.titulo.length > 100)
        errors.titulo = "Máximo 100 caracteres.";
    if (!form.mensaje || form.mensaje.trim().length === 0)
        errors.mensaje = "El mensaje es obligatorio.";
    if (!form.tipo)
        errors.tipo = "Seleccione un tipo.";
    return errors;
}

function NotificacionModal({ notificacion, onClose, onSave }) {
    const isEdit = !!notificacion;

    const [form, setForm] = useState(
        isEdit
            ? {
                  titulo: notificacion.titulo,
                  mensaje: notificacion.mensaje,
                  tipo: notificacion.tipo,
                  documento_cliente: notificacion.documento_cliente,
              }
            : emptyForm
    );

    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handle = (e) =>
        setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateNotificacion(notificacion.id_notificacion, {
                      titulo: form.titulo,
                      mensaje: form.mensaje,
                      tipo: form.tipo,
                  })
                : await api.createNotificacion(form);
            if (res.success) onSave(res.message);
            else setErrors({ general: res.message });
        } catch {
            setErrors({ general: "No se pudo conectar con la API." });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="modal-box">
                <div className="nc-modal-header">
                    <h2 className="nc-modal-title">
                        {isEdit ? "✏️ Editar Notificación" : "🔔 Nueva Notificación"}
                    </h2>
                    <button className="nc-modal-close" onClick={onClose}>✕</button>
                </div>

                {errors.general && (
                    <div className="nc-error-banner">{errors.general}</div>
                )}

                <div className="nc-modal-body">
                    {!isEdit && (
                        <div className="nc-field">
                            <label className="nc-label">Documento del Cliente</label>
                            <input
                                className={`nc-input${errors.documento_cliente ? " nc-input--error" : ""}`}
                                name="documento_cliente"
                                type="number"
                                value={form.documento_cliente}
                                onChange={handle}
                            />
                            {errors.documento_cliente && (
                                <span className="nc-error-text">{errors.documento_cliente}</span>
                            )}
                        </div>
                    )}

                    {isEdit && (
                        <div className="nc-field">
                            <label className="nc-label">Cliente</label>
                            <input
                                className="nc-input nc-input--disabled"
                                value={notificacion.cliente?.nombre ?? notificacion.documento_cliente}
                                disabled
                            />
                        </div>
                    )}

                    <div className="nc-field">
                        <label className="nc-label">Tipo</label>
                        <select className="nc-input" name="tipo" value={form.tipo} onChange={handle}>
                            {TIPOS.map((t) => (
                                <option key={t} value={t}>{TIPO_LABELS[t] ?? t}</option>
                            ))}
                        </select>
                    </div>

                    <div className="nc-field">
                        <label className="nc-label">Título</label>
                        <input
                            className={`nc-input${errors.titulo ? " nc-input--error" : ""}`}
                            name="titulo"
                            value={form.titulo}
                            onChange={handle}
                        />
                        {errors.titulo && <span className="nc-error-text">{errors.titulo}</span>}
                    </div>

                    <div className="nc-field">
                        <label className="nc-label">Mensaje</label>
                        <textarea
                            className={`nc-input nc-textarea${errors.mensaje ? " nc-input--error" : ""}`}
                            name="mensaje"
                            value={form.mensaje}
                            onChange={handle}
                        />
                        {errors.mensaje && <span className="nc-error-text">{errors.mensaje}</span>}
                    </div>
                </div>

                <div className="nc-modal-footer">
                    <button className="nc-btn-secondary" onClick={onClose}>Cancelar</button>
                    <button className="nc-btn-primary" onClick={submit} disabled={loading}>
                        {loading ? "Guardando..." : isEdit ? "Actualizar" : "Crear"}
                    </button>
                </div>
            </div>
        </div>
    );
}

function ConfirmModal({ notificacion, onClose, onConfirm }) {
    return (
        <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="modal-box" style={{ maxWidth: 380 }}>
                <div className="nc-modal-header">
                    <h2 className="nc-modal-title">🗑️ Eliminar Notificación</h2>
                    <button className="nc-modal-close" onClick={onClose}>✕</button>
                </div>
                <p className="nc-confirm-text">
                    ¿Eliminar <strong>"{notificacion.titulo}"</strong>?
                </p>
                <div className="modal-footer">
                    <button className="nc-btn-secondary" onClick={onClose}>Cancelar</button>
                    <button className="nc-btn-primary nc-btn-primary--danger" onClick={onConfirm}>
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function NotificacionesCrud() {
    const [notificaciones, setNotificaciones] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [search, setSearch] = useState("");
    const [filtroFecha, setFiltroFecha] = useState("");
    const [marcando, setMarcando] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getNotificaciones();
            if (res.success) setNotificaciones(res.data);
        } catch {
            setMensaje({ texto: "Error con la API", tipo: "error" });
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => { load(); }, [load]);

    useEffect(() => {
        if (!mensaje) return;
        const t = setTimeout(() => setMensaje(null), 3500);
        return () => clearTimeout(t);
    }, [mensaje]);

    const handleMarcarLeida = async (n) => {
        if (n.leido) return;
        setMarcando(n.id_notificacion);
        try {
            const res = await api.marcarLeida(n.id_notificacion);
            if (res.success) {
                setMensaje({ texto: "Notificación marcada como leída.", tipo: "success" });
                setNotificaciones((prev) =>
                    prev.map((x) =>
                        x.id_notificacion === n.id_notificacion ? { ...x, leido: 1 } : x
                    )
                );
            } else {
                setMensaje({ texto: res.message, tipo: "error" });
            }
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setMarcando(null);
        }
    };

    const handleDelete = async (id) => {
        try {
            const res = await api.deleteNotificacion(id);
            if (res.success) {
                setMensaje({ texto: res.message, tipo: "success" });
                load();
            } else {
                setMensaje({ texto: res.message, tipo: "error" });
            }
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmDelete(null);
        }
    };

    const filtradas = notificaciones
        .filter((n) => {
            const q = search.toLowerCase();
            return (
                n.titulo.toLowerCase().includes(q) ||
                n.mensaje.toLowerCase().includes(q) ||
                (n.cliente?.nombre ?? "").toLowerCase().includes(q)
            );
        })
        .filter((n) => filtroFecha ? n.fecha?.startsWith(filtroFecha) : true);

    const noLeidas = notificaciones.filter((n) => !n.leido).length;

    return (
        <div className="nc-page">

            <div className="nc-page-header">
                <div className="nc-page-header-left">
                    <h1 className="nc-page-title">Notificaciones</h1>
                    {noLeidas > 0 && (
                        <span className="nc-badge-sinleer">{noLeidas} sin leer</span>
                    )}
                </div>
                <button className="nc-btn-primary" onClick={() => setModal("create")}>
                    + Nueva
                </button>
            </div>

            {mensaje && (
                <div className={`nc-toast nc-toast--${mensaje.tipo}`}>
                    {mensaje.texto}
                </div>
            )}

            <div className="nc-search-wrap">
                <input
                    className="nc-search-input"
                    placeholder="Buscar por título, mensaje o cliente..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
                <input
                    type="date"
                    value={filtroFecha}
                    onChange={(e) => setFiltroFecha(e.target.value)}
                    style={{ marginLeft: 8 }}
                />
                {filtroFecha && (
                    <button onClick={() => setFiltroFecha("")} style={{ marginLeft: 4 }}>
                        ✕ Limpiar fecha
                    </button>
                )}
            </div>

            {loading ? (
                <div className="nc-empty-state">Cargando...</div>
            ) : filtradas.length === 0 ? (
                <div className="nc-empty-state">
                    {filtroFecha ? "No hay notificaciones en esa fecha." : "No hay notificaciones."}
                </div>
            ) : (
                <div className="nc-table-wrapper">
                    <table className="nc-table">
                        <thead>
                            <tr>
                                <th className="nc-th">Tipo</th>
                                <th className="nc-th">Título</th>
                                <th className="nc-th">Mensaje</th>
                                <th className="nc-th">Cliente</th>
                                <th className="nc-th">Fecha y hora</th>
                                <th className="nc-th">Estado</th>
                                <th className="nc-th">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filtradas.map((n) => {
                                const leida = !!n.leido;
                                return (
                                    <tr
                                        key={n.id_notificacion}
                                        className={leida ? "nc-row--leida" : "nc-row--noleida"}
                                    >
                                        <td className="nc-td">
                                            <span className={`nc-tipo-badge nc-tipo--${n.tipo}`}>
                                                {TIPO_LABELS[n.tipo] ?? n.tipo}
                                            </span>
                                        </td>
                                        <td className={`nc-td nc-td-titulo nc-td-titulo--${leida ? "leido" : "noleido"}`}>
                                            {n.titulo}
                                        </td>
                                        <td className="nc-td nc-td-mensaje">{n.mensaje}</td>
                                        <td className="nc-td">
                                            <div className="nc-cliente-nombre">{n.cliente?.nombre ?? "—"}</div>
                                            <div className="nc-cliente-doc">{n.documento_cliente}</div>
                                        </td>
                                        <td className="nc-td nc-fecha">{formatFecha(n.fecha)}</td>
                                        <td className="nc-td">
                                            <span className={`nc-estado-badge nc-estado--${leida ? "leida" : "noleida"}`}>
                                                {leida ? "✓ Leída" : "● Sin leer"}
                                            </span>
                                        </td>
                                        <td className="nc-td nc-td-acciones">
                                            {!leida && (
                                                <button
                                                    className="nc-btn-action nc-btn-action--leida"
                                                    onClick={() => handleMarcarLeida(n)}
                                                    disabled={marcando === n.id_notificacion}
                                                    title="Marcar como leída"
                                                >
                                                    {marcando === n.id_notificacion ? "..." : "✓"}
                                                </button>
                                            )}
                                            <button
                                                className="nc-btn-action"
                                                onClick={() => setModal(n)}
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>
                                            <button
                                                className="nc-btn-action nc-btn-action--danger"
                                                onClick={() => setConfirmDelete(n)}
                                                title="Eliminar"
                                            >
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}

            {modal && (
                <NotificacionModal
                    notificacion={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={(msg) => {
                        setModal(null);
                        setMensaje({ texto: msg, tipo: "success" });
                        load();
                    }}
                />
            )}

            {confirmDelete && (
                <ConfirmModal
                    notificacion={confirmDelete}
                    onClose={() => setConfirmDelete(null)}
                    onConfirm={() => handleDelete(confirmDelete.id_notificacion)}
                />
            )}
        </div>
    );
}