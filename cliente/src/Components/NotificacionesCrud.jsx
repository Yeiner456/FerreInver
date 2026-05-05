import { useState, useEffect, useCallback } from "react";


const API_BASE = "http://127.0.0.1:8000/api";

const api = {
    getNotificaciones: () =>
        fetch(`${API_BASE}/notificaciones`).then((r) => r.json()),

    createNotificacion: (data) =>
        fetch(`${API_BASE}/notificaciones`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateNotificacion: (id, data) =>
        fetch(`${API_BASE}/notificaciones/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    marcarLeida: (id) =>
        fetch(`${API_BASE}/notificaciones/${id}/marcar-leida`, {
            method: "PATCH",
        }).then((r) => r.json()),

    deleteNotificacion: (id) =>
        fetch(`${API_BASE}/notificaciones/${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const TIPOS = ["info", "alerta", "pedido", "cotizacion", "sistema"];

const TIPO_STYLES = {
    info: { bg: "#e3f2fd", color: "#1565c0", label: "Info" },
    alerta: { bg: "#fff8e1", color: "#f59e0b", label: "Alerta" },
    pedido: { bg: "#e8f5e9", color: "#2e7d32", label: "Pedido" },
    cotizacion: { bg: "#f3e5f5", color: "#7b1fa2", label: "Cotización" },
    sistema: { bg: "#fce4ec", color: "#c62828", label: "Sistema" },
};

const emptyForm = {
    documento_cliente: "",
    titulo: "",
    mensaje: "",
    tipo: "info",
};

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

/* ================= MODAL ================= */

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
        if (Object.keys(errs).length) {
            setErrors(errs);
            return;
        }

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
        <div className="overlay">
            <div className="modal">
                <div className="modalHeader">
                    <h2 className="modalTitle">
                        {isEdit ? "✏️ Editar Notificación" : "🔔 Nueva Notificación"}
                    </h2>
                    <button className="closeBtn" onClick={onClose}>✕</button>
                </div>

                {errors.general && (
                    <div className="errorBanner">{errors.general}</div>
                )}

                <div className="formGrid">
                    {!isEdit && (
                        <div className="fieldFull">
                            <label className="label">Documento del Cliente</label>
                            <input
                                className={`input ${errors.documento_cliente ? "inputError" : ""}`}
                                name="documento_cliente"
                                type="number"
                                value={form.documento_cliente}
                                onChange={handle}
                            />
                            {errors.documento_cliente && (
                                <span className="errorText">{errors.documento_cliente}</span>
                            )}
                        </div>
                    )}

                    <div className="fieldFull">
                        <label className="label">Tipo</label>
                        <select
                            className="input"
                            name="tipo"
                            value={form.tipo}
                            onChange={handle}
                        >
                            {TIPOS.map((t) => (
                                <option key={t} value={t}>
                                    {TIPO_STYLES[t]?.label ?? t}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="fieldFull">
                        <label className="label">Título</label>
                        <input
                            className={`input ${errors.titulo ? "inputError" : ""}`}
                            name="titulo"
                            value={form.titulo}
                            onChange={handle}
                        />
                        {errors.titulo && (
                            <span className="errorText">{errors.titulo}</span>
                        )}
                    </div>

                    <div className="fieldFull">
                        <label className="label">Mensaje</label>
                        <textarea
                            className={`input ${errors.mensaje ? "inputError" : ""}`}
                            name="mensaje"
                            value={form.mensaje}
                            onChange={handle}
                        />
                        {errors.mensaje && (
                            <span className="errorText">{errors.mensaje}</span>
                        )}
                    </div>
                </div>

                <div className="modalFooter">
                    <button className="btnSecondary" onClick={onClose}>Cancelar</button>
                    <button className="btnPrimary" onClick={submit} disabled={loading}>
                        {loading ? "Guardando..." : isEdit ? "Actualizar" : "Crear"}
                    </button>
                </div>
            </div>
        </div>
    );
}

/* ================= CONFIRM DELETE ================= */

function ConfirmModal({ notificacion, onClose, onConfirm }) {
    return (
        <div className="overlay">
            <div className="modal small">
                <div className="modalHeader">
                    <h2 className="modalTitle small">🗑️ Eliminar Notificación</h2>
                    <button className="closeBtn" onClick={onClose}>✕</button>
                </div>

                <p className="confirmText">
                    ¿Eliminar <strong>"{notificacion.titulo}"</strong>?
                </p>

                <div className="modalFooter">
                    <button className="btnSecondary" onClick={onClose}>Cancelar</button>
                    <button className="btnDanger" onClick={onConfirm}>
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}

/* ================= CRUD ================= */

export default function NotificacionesCrud() {
    const [notificaciones, setNotificaciones] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [search, setSearch] = useState("");
    const [filtroLeido, setFiltroLeido] = useState("todos");

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

    return (
        <div className="container">
            <div className="pageHeader">
                <h1 className="pageTitle">Notificaciones</h1>

                <button className="btnPrimary" onClick={() => setModal("create")}>
                    + Nueva Notificación
                </button>
            </div>

            {mensaje && (
                <div className="toast">{mensaje.texto}</div>
            )}

            <div className="toolbar">
                <input
                    className="searchInput"
                    placeholder="Buscar..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
            </div>

            {loading ? (
                <div className="emptyState">Cargando...</div>
            ) : (
                <div className="tableWrapper">
                    <table className="table">
                        <thead>
                            <tr>
                                <th className="th">Título</th>
                                <th className="th">Mensaje</th>
                                <th className="th">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {notificaciones.map((n) => (
                                <tr key={n.id_notificacion}>
                                    <td className="td">{n.titulo}</td>
                                    <td className="td">{n.mensaje}</td>
                                    <td className="td">
                                        <button
                                            className="btnAction"
                                            onClick={() => setModal(n)}
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            className="btnAction danger"
                                            onClick={() => setConfirmDelete(n)}
                                        >
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {modal && (
                <NotificacionModal
                    notificacion={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={() => {
                        setModal(null);
                        load();
                    }}
                />
            )}

            {confirmDelete && (
                <ConfirmModal
                    notificacion={confirmDelete}
                    onClose={() => setConfirmDelete(null)}
                    onConfirm={() => {
                        setConfirmDelete(null);
                        load();
                    }}
                />
            )}
        </div>
    );
}