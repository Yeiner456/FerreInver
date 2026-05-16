import { useState, useEffect, useCallback } from "react";

const API_URL = import.meta.env.VITE_API_URL

const api = {
    getClientes: () =>
        fetch(`${API_URL}/clientes`).then((r) => r.json()),

    getTiposUsuario: () =>
        fetch(`${API_URL}/clientes/tipos`).then((r) => r.json()),

    createCliente: (data) =>
        fetch(`${API_URL}/clientes`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateCliente: (documento, data) => {
        const payload = {
            id_tipo_de_usuario: Number(data.id_tipo_de_usuario),
            nombre: data.nombre,
            correo: data.correo,
            estado: data.estado_inicio_sesion,
            password: data.password || undefined,
            confirmar_password: data.confirmar_password || undefined,
        }
        return fetch(`${API_URL}/clientes/${documento}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        }).then((r) => r.json())
    },

    deactivateCliente: (documento) =>
        fetch(`${API_URL}/clientes/${documento}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = {
    documento: "",
    id_tipo_de_usuario: "",
    nombre: "",
    correo: "",
    password: "",
    confirmar_password: "",
    estado: "activo",
};

function validate(form, isEdit = false) {
    const errors = {};

    if (!isEdit) {
        if (!form.documento || isNaN(form.documento) || Number(form.documento) <= 0)
            errors.documento = "Documento debe ser un número válido.";
        if (String(form.documento).length > 11)
            errors.documento = "Documento no puede tener más de 11 dígitos.";
    }

    if (!form.id_tipo_de_usuario)
        errors.id_tipo_de_usuario = "Seleccione un tipo de usuario.";

    if (!form.nombre || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(form.nombre))
        errors.nombre = "Solo letras y espacios.";
    if (form.nombre.length > 30)
        errors.nombre = "Máximo 30 caracteres.";

    if (!form.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo))
        errors.correo = "Correo inválido.";
    if (form.correo.length > 50)
        errors.correo = "Máximo 50 caracteres.";

    const changingPassword = form.password || form.confirmar_password;
    if (!isEdit || changingPassword) {
        if (!isEdit && !form.password)
            errors.password = "La contraseña es obligatoria.";
        if (form.password && form.password.length < 6)
            errors.password = "Mínimo 6 caracteres.";
        if (form.password && (!/[a-zA-Z]/.test(form.password) || !/[0-9]/.test(form.password)))
            errors.password = "Debe contener letras y números.";
        if (form.password !== form.confirmar_password)
            errors.confirmar_password = "Las contraseñas no coinciden.";
    }

    return errors;
}

function ClienteModal({ cliente, tipos, onClose, onSave }) {
    const isEdit = !!cliente;
    const [form, setForm] = useState(
        isEdit
            ? {
                ...cliente,

                id_tipo_de_usuario: String(cliente.id_tipo_de_usuario),
                password: "",
                confirmar_password: "",
            }
            : emptyForm
    );
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handle = (e) =>
        setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form, isEdit);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateCliente(cliente.documento, form)
                : await api.createCliente(form);
            if (res.success) onSave(res.message);
            else setErrors({ general: res.message || "Error del servidor." });
        } catch {
            setErrors({ general: "No se pudo conectar con la API PHP." });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="modal-box">
                <h2>{isEdit ? "Editar Cliente" : "Nuevo Cliente"}</h2>

                {errors.general && <p className="modal-error">{errors.general}</p>}

                {!isEdit && (
                    <div className="modal-field">
                        <label>Documento</label>
                        <input name="documento" type="number" value={form.documento} onChange={handle} />
                        {errors.documento && <span className="modal-error">{errors.documento}</span>}
                    </div>
                )}

                <div className="modal-field">
                    <label>Tipo de Usuario</label>
                    <select name="id_tipo_de_usuario" value={form.id_tipo_de_usuario} onChange={handle}>
                        <option value="">Seleccionar...</option>
                        {tipos.map((t) => (
                            <option key={t.id_tipo_de_usuario} value={String(t.id_tipo_de_usuario)}>
                                {t.nombre}
                            </option>
                        ))}
                    </select>
                    {errors.id_tipo_de_usuario && <span className="modal-error">{errors.id_tipo_de_usuario}</span>}
                </div>

                <div className="modal-field">
                    <label>Nombre Completo</label>
                    <input name="nombre" type="text" value={form.nombre} onChange={handle} />
                    {errors.nombre && <span className="modal-error">{errors.nombre}</span>}
                </div>

                <div className="modal-field">
                    <label>Correo Electrónico</label>
                    <input name="correo" type="email" value={form.correo} onChange={handle} />
                    {errors.correo && <span className="modal-error">{errors.correo}</span>}
                </div>

                <div className="modal-field">
                    <label>Estado</label>
                    <select name="estado" value={form.estado} onChange={handle}>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <hr className="modal-divider" />
                <p className="modal-hint">
                    {isEdit ? "Deja vacío para no cambiar la contraseña" : "Crea la contraseña"}
                </p>

                <div className="modal-field">
                    <label>Contraseña</label>
                    <input name="password" type="password" value={form.password} onChange={handle} autoComplete="new-password" />
                    {errors.password && <span className="modal-error">{errors.password}</span>}
                </div>

                <div className="modal-field">
                    <label>Confirmar Contraseña</label>
                    <input name="confirmar_password" type="password" value={form.confirmar_password} onChange={handle} autoComplete="new-password" />
                    {errors.confirmar_password && <span className="modal-error">{errors.confirmar_password}</span>}
                </div>

                <div className="modal-footer">
                    <button onClick={onClose}>Cancelar</button>
                    <button onClick={submit} disabled={loading}>
                        {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function ClientesCRUD() {
    const [clientes, setClientes] = useState([]);
    const [tipos, setTipos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [search, setSearch] = useState("");
    const [confirmDeactivate, setConfirmDeactivate] = useState(null);

    const loadClientes = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getClientes();
            if (res.success) setClientes(res.data);
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API PHP.", tipo: "error" });
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadClientes();
        api.getTiposUsuario().then((r) => { if (r.success) setTipos(r.data); });
    }, [loadClientes]);

    const handleSave = (message) => {
        setModal(null);
        setMensaje({ texto: message, tipo: "success" });
        loadClientes();
    };

    const handleDeactivate = async (documento) => {
        try {
            const res = await api.deactivateCliente(documento);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); loadClientes(); }
            else setMensaje({ texto: res.message || "Error al desactivar.", tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API PHP.", tipo: "error" });
        } finally {
            setConfirmDeactivate(null);
        }
    };

    const filtered = clientes.filter((c) =>
        [c.documento, c.nombre, c.correo, c.tipo_usuario]
            .join(" ").toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div>
            <h1>Clientes Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>
                    {mensaje.texto}
                </p>
            )}

            <input
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Buscar por documento, nombre, correo..."
            />
            {" "}
            <button onClick={() => setModal("create")}>+ Nuevo Cliente</button>

            <br /><br />

            {loading ? (
                <p>Cargando clientes...</p>
            ) : filtered.length === 0 ? (
                <p>{search ? "Sin resultados." : "No hay clientes registrados."}</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filtered.map((c) => (
                            <tr key={c.documento}>
                                <td>{c.documento}</td>
                                <td>{c.tipo_usuario?.nombre ?? c.tipo_usuario}</td>
                                <td>{c.nombre}</td>
                                <td>{c.correo}</td>
                                <td>{new Date(c.fecha_registro).toLocaleDateString("es-CO")}</td>
                                <td>{c.estado_inicio_sesion}</td>
                                <td>
                                    <button onClick={() => setModal(c)}>Editar</button>{" "}
                                    {/* Botón desactivar solo si está activo */}
                                    {c.estado_inicio_sesion === "activo" && (
                                        <button onClick={() => setConfirmDeactivate(c)}>Desactivar</button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <ClienteModal
                    cliente={modal === "create" ? null : modal}
                    tipos={tipos}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDeactivate && (
                <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && setConfirmDeactivate(null)}>
                    <div className="modal-box" style={{ maxWidth: 380 }}>
                        <h2>¿Desactivar cliente?</h2>
                        <p className="modal-confirm-text">
                            Vas a desactivar a <strong>{confirmDeactivate.nombre}</strong>.
                        </p>
                        <p className="modal-confirm-sub">El cliente no podrá iniciar sesión.</p>
                        <div className="modal-footer">
                            <button onClick={() => setConfirmDeactivate(null)}>Cancelar</button>
                            <button onClick={() => handleDeactivate(confirmDeactivate.documento)}>
                                Sí, desactivar
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}