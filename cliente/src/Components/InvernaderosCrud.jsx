import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://localhost/ferreinver/server/invernaderos/api";

const api = {
    getInvernaderos: () =>
        fetch(`${API_BASE}/apiInvernaderos.php`).then((r) => r.json()),

    createInvernadero: (data) =>
        fetch(`${API_BASE}/apiInvernaderos.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateInvernadero: (id, data) =>
        fetch(`${API_BASE}/apiInvernaderos.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deactivateInvernadero: (id) =>
        fetch(`${API_BASE}/apiInvernaderos.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = { nombre: "", descripcion: "", precio_m2: "", estado: "activo" };

function validate(form) {
    const errors = {};
    if (!form.nombre) errors.nombre = "El nombre es obligatorio.";
    if (form.nombre.length > 50) errors.nombre = "Máximo 50 caracteres.";
    if (form.descripcion.length > 150) errors.descripcion = "Máximo 150 caracteres.";
    if (!form.precio_m2 || isNaN(form.precio_m2) || Number(form.precio_m2) <= 0)
        errors.precio_m2 = "Debe ser un número mayor a 0.";
    if (!form.estado) errors.estado = "El estado es obligatorio.";
    return errors;
}

function InvernaderoModal({ invernadero, onClose, onSave }) {
    const isEdit = !!invernadero;
    const [form, setForm] = useState(isEdit ? { ...invernadero } : emptyForm);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateInvernadero(invernadero.id_invernadero, form)
                : await api.createInvernadero(form);
            if (res.success) onSave(res.message);
            else setErrors({ general: res.message });
        } catch {
            setErrors({ general: "No se pudo conectar con la API." });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div>
            <h2>{isEdit ? "Editar Invernadero" : "Nuevo Invernadero"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={invernadero.id_invernadero} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Nombre</label><br />
                <input name="nombre" type="text" value={form.nombre} onChange={handle} maxLength={50} /><br />
                {errors.nombre && <span>{errors.nombre}</span>}
            </div><br />

            <div>
                <label>Descripción (opcional)</label><br />
                <textarea name="descripcion" value={form.descripcion} onChange={handle} maxLength={150} rows={3} /><br />
                {errors.descripcion && <span>{errors.descripcion}</span>}
            </div><br />

            <div>
                <label>Precio por m² ($)</label><br />
                <input name="precio_m2" type="number" value={form.precio_m2} onChange={handle} min="0.01" step="0.01" /><br />
                {errors.precio_m2 && <span>{errors.precio_m2}</span>}
            </div><br />

            <div>
                <label>Estado</label><br />
                <select name="estado" value={form.estado} onChange={handle}>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function InvernaderoCRUD() {
    const [invernaderos, setInvernaderos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDeactivate, setConfirmDeactivate] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getInvernaderos();
            if (res.success) setInvernaderos(res.data);
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => { load(); }, [load]);

    const handleSave = (message) => {
        setModal(null);
        setMensaje({ texto: message, tipo: "success" });
        load();
    };

    const handleDeactivate = async (id) => {
        try {
            const res = await api.deactivateInvernadero(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); load(); }
            else setMensaje({ texto: res.message, tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmDeactivate(null);
        }
    };

    return (
        <div>
            <h1>Invernaderos Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Invernadero</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : invernaderos.length === 0 ? (
                <p>No hay invernaderos registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio m²</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {invernaderos.map((inv) => (
                            <tr key={inv.id_invernadero}>
                                <td>{inv.nombre}</td>
                                <td>{inv.descripcion}</td>
                                <td>$ {Number(inv.precio_m2).toLocaleString("es-CO", { minimumFractionDigits: 2 })}</td>
                                <td>{inv.estado}</td>
                                <td>
                                    <button onClick={() => setModal(inv)}>Editar</button>{" "}
                                    <button
                                        onClick={() => setConfirmDeactivate(inv)}
                                        disabled={inv.estado === "inactivo"}
                                    >
                                        Desactivar
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <InvernaderoModal
                    invernadero={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDeactivate && (
                <div>
                    <p>
                        ¿Desactivar invernadero <strong>{confirmDeactivate.nombre}</strong>?
                        <br />
                        <small>El invernadero no aparecerá disponible para nuevas cotizaciones.</small>
                    </p>
                    <button onClick={() => setConfirmDeactivate(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDeactivate(confirmDeactivate.id_invernadero)}>Sí, desactivar</button>
                </div>
            )}
        </div>
    );
}