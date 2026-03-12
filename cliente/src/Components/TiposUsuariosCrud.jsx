import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://localhost/ferreinver/server/tipos_usuarios/api";

const api = {
    getTipos: () =>
        fetch(`${API_BASE}/apiTipoUsuario.php`).then((r) => r.json()),

    createTipo: (data) =>
        fetch(`${API_BASE}/apiTipoUsuario.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateTipo: (id, data) =>
        fetch(`${API_BASE}/apiTipoUsuario.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteTipo: (id) =>
        fetch(`${API_BASE}/apiTipoUsuario.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = {
    nombre: "",
    estado: "activo",
};

function validate(form) {
    const errors = {};

    if (!form.nombre || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(form.nombre))
        errors.nombre = "Solo letras y espacios.";
    if (form.nombre.length > 30)
        errors.nombre = "Máximo 30 caracteres.";
    if (!form.estado)
        errors.estado = "El estado es obligatorio.";

    return errors;
}

function TipoModal({ tipo, onClose, onSave }) {
    const isEdit = !!tipo;
    const [form, setForm] = useState(isEdit ? { ...tipo } : emptyForm);
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
                ? await api.updateTipo(tipo.id_tipo_de_usuario, form)
                : await api.createTipo(form);
            if (res.success) onSave(res.message);
            else setErrors({ general: res.message || "Error del servidor." });
        } catch {
            setErrors({ general: "No se pudo conectar con la API PHP." });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div>
            <h2>{isEdit ? "Editar Tipo de Usuario" : "Nuevo Tipo de Usuario"}</h2>

            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={tipo.id_tipo_de_usuario} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Nombre</label><br />
                <input
                    name="nombre"
                    type="text"
                    value={form.nombre}
                    onChange={handle}
                    maxLength={30}
                /><br />
                {errors.nombre && <span>{errors.nombre}</span>}
            </div>

            <br />

            <div>
                <label>Estado</label><br />
                <select name="estado" value={form.estado} onChange={handle}>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select><br />
                {errors.estado && <span>{errors.estado}</span>}
            </div>

            <br />
            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function TiposUsuariosCRUD() {
    const [tipos, setTipos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const loadTipos = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getTipos();
            if (res.success) setTipos(res.data);
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API PHP.", tipo: "error" });
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadTipos();
    }, [loadTipos]);

    const handleSave = (message) => {
        setModal(null);
        setMensaje({ texto: message, tipo: "success" });
        loadTipos();
    };

    const handleDelete = async (id) => {
        try {
            const res = await api.deleteTipo(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); loadTipos(); }
            else setMensaje({ texto: res.message || "Error al eliminar.", tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API PHP.", tipo: "error" });
        } finally {
            setConfirmDelete(null);
        }
    };

    return (
        <div>
            <h1>Tipos de Usuario</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>
                    {mensaje.texto}
                </p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Tipo de Usuario</button>

            <br /><br />

            {loading ? (
                <p>Cargando tipos de usuario...</p>
            ) : tipos.length === 0 ? (
                <p>No hay tipos de usuario registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tipos.map((t) => (
                            <tr key={t.id_tipo_de_usuario}>
                                <td>{t.id_tipo_de_usuario}</td>
                                <td>{t.nombre}</td>
                                <td>{t.estado}</td>
                                <td>
                                    <button onClick={() => setModal(t)}>Editar</button>{" "}
                                    <button onClick={() => setConfirmDelete(t)}>Eliminar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <TipoModal
                    tipo={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>
                        ¿Eliminar el tipo <strong>{confirmDelete.nombre}</strong> (ID: {confirmDelete.id_tipo_de_usuario})?
                    </p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.id_tipo_de_usuario)}>Sí, eliminar</button>
                </div>
            )}
        </div>
    );
}