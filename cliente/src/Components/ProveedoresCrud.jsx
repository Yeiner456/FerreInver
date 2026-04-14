import { useState, useEffect, useCallback } from "react";

// NIT no es editable en el update (es la PK de proveedores)

const API_BASE = "http://localhost/ferreinver/server/proveedores/api";

const api = {
    getProveedores: () =>
        fetch(`${API_BASE}/apiProveedores.php`).then((r) => r.json()),

    createProveedor: (data) =>
        fetch(`${API_BASE}/apiProveedores.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateProveedor: (nit, data) =>
        fetch(`${API_BASE}/apiProveedores.php?nit=${nit}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deactivateProveedor: (nit) =>
        fetch(`${API_BASE}/apiProveedores.php?nit=${nit}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = {
    nit: "",
    correo: "",
    direccion: "",
    telefono: "",
    estado: "activo",
};

function validateCreate(form) {
    const errors = {};
    if (!form.nit || isNaN(form.nit) || Number(form.nit) <= 0)
        errors.nit = "El NIT debe ser un número válido mayor a 0.";
    if (String(form.nit).length > 11)
        errors.nit = "El NIT no puede tener más de 11 dígitos.";
    if (!form.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo))
        errors.correo = "El correo no es válido.";
    if (form.correo.length > 80)
        errors.correo = "El correo no puede exceder 80 caracteres.";
    if (!form.direccion)
        errors.direccion = "La dirección es obligatoria.";
    if (form.direccion.length > 80)
        errors.direccion = "La dirección no puede exceder 80 caracteres.";
    if (!form.telefono || !/^[0-9\s\-)+]+$/.test(form.telefono))
        errors.telefono = "Solo números, espacios, guiones, paréntesis y +.";
    if (form.telefono.length > 20)
        errors.telefono = "El teléfono no puede exceder 20 caracteres.";
    if (form.telefono.replace(/[^0-9]/g, "").length < 7)
        errors.telefono = "El teléfono debe tener al menos 7 dígitos.";
    if (!form.estado)
        errors.estado = "El estado es obligatorio.";
    return errors;
}

function validateEdit(form) {
    const errors = {};
    if (!form.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo))
        errors.correo = "El correo no es válido.";
    if (form.correo.length > 80)
        errors.correo = "El correo no puede exceder 80 caracteres.";
    if (!form.direccion)
        errors.direccion = "La dirección es obligatoria.";
    if (form.direccion.length > 80)
        errors.direccion = "La dirección no puede exceder 80 caracteres.";
    if (!form.telefono || !/^[0-9\s\-)+]+$/.test(form.telefono))
        errors.telefono = "Solo números, espacios, guiones, paréntesis y +.";
    if (form.telefono.length > 20)
        errors.telefono = "El teléfono no puede exceder 20 caracteres.";
    if (form.telefono.replace(/[^0-9]/g, "").length < 7)
        errors.telefono = "El teléfono debe tener al menos 7 dígitos.";
    if (!form.estado)
        errors.estado = "El estado es obligatorio.";
    return errors;
}

function ProveedorModal({ proveedor, onClose, onSave }) {
    const isEdit = !!proveedor;
    const [form, setForm] = useState(
        isEdit
            ? {
                correo: proveedor.correo,
                direccion: proveedor.direccion,
                telefono: proveedor.telefono,
                estado: proveedor.estado,
            }
            : emptyForm
    );
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = isEdit ? validateEdit(form) : validateCreate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateProveedor(proveedor.nit_proveedor, form)
                : await api.createProveedor(form);
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
            <h2>{isEdit ? "Editar Proveedor" : "Nuevo Proveedor"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit ? (
                <div>
                    <label>NIT (No editable)</label><br />
                    <input type="text" value={proveedor.nit_proveedor} disabled /><br /><br />
                </div>
            ) : (
                <div>
                    <label>NIT del Proveedor</label><br />
                    <input name="nit" type="number" value={form.nit} onChange={handle} min="1" /><br />
                    {errors.nit && <span>{errors.nit}</span>}
                </div>
            )}
            <br />

            <div>
                <label>Correo Electrónico</label><br />
                <input name="correo" type="email" value={form.correo} onChange={handle} maxLength={80} /><br />
                {errors.correo && <span>{errors.correo}</span>}
            </div><br />

            <div>
                <label>Dirección</label><br />
                <input name="direccion" type="text" value={form.direccion} onChange={handle} maxLength={80} /><br />
                {errors.direccion && <span>{errors.direccion}</span>}
            </div><br />

            <div>
                <label>Teléfono</label><br />
                <input name="telefono" type="text" value={form.telefono} onChange={handle} maxLength={20} placeholder="Ej: 3001234567" /><br />
                {errors.telefono && <span>{errors.telefono}</span>}
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

export default function ProveedoresCRUD() {
    const [proveedores, setProveedores] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDeactivate, setConfirmDeactivate] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getProveedores();
            if (res.success) setProveedores(res.data);
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

    const handleDeactivate = async (nit) => {
        try {
            const res = await api.deactivateProveedor(nit);
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
            <h1>Proveedores Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Proveedor</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : proveedores.length === 0 ? (
                <p>No hay proveedores registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>NIT</th>
                            <th>Correo</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {proveedores.map((p) => (
                            <tr key={p.nit_proveedor}>
                                <td>{p.nit_proveedor}</td>
                                <td>{p.correo}</td>
                                <td>{p.direccion}</td>
                                <td>{p.telefono}</td>
                                <td>{p.estado}</td>
                                <td>
                                    <button onClick={() => setModal(p)}>Editar</button>{" "}
                                    <button
                                        onClick={() => setConfirmDeactivate(p)}
                                        disabled={p.estado === "inactivo"}
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
                <ProveedorModal
                    proveedor={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDeactivate && (
                <div>
                    <p>
                        ¿Desactivar proveedor({confirmDeactivate.correo})?
                        <br />
                        <small>El proveedor no aparecerá disponible para nuevas compras.</small>
                    </p>
                    <button onClick={() => setConfirmDeactivate(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDeactivate(confirmDeactivate.nit_proveedor)}>Sí, desactivar</button>
                </div>
            )}
        </div>
    );
}