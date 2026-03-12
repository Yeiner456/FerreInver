import { useState, useEffect, useCallback } from "react";

// Nota: proveedor e id_producto NO son editables en el update
// Solo se pueden cambiar cantidad y descripcion al editar

const API_BASE = "http://localhost/ferreinver/server/compras/api";

const api = {
    getCompras: () =>
        fetch(`${API_BASE}/apiCompras.php`).then((r) => r.json()),

    getSelects: () =>
        fetch(`${API_BASE}/apiCompras.php?selects=1`, { method: "POST" }).then((r) => r.json()),

    createCompra: (data) =>
        fetch(`${API_BASE}/apiCompras.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateCompra: (id, data) =>
        fetch(`${API_BASE}/apiCompras.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteCompra: (id) =>
        fetch(`${API_BASE}/apiCompras.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = { cantidad: "", descripcion: "", id_producto: "", id_proveedor: "" };

function validateCreate(form) {
    const errors = {};
    if (!form.cantidad || isNaN(form.cantidad) || Number(form.cantidad) <= 0)
        errors.cantidad = "La cantidad debe ser un número mayor a 0.";
    if (!form.descripcion) errors.descripcion = "La descripción es obligatoria.";
    if (!/^[a-zA-Z0-9\s]+$/.test(form.descripcion))
        errors.descripcion = "Solo letras, números y espacios.";
    if (form.descripcion.length > 150) errors.descripcion = "Máximo 150 caracteres.";
    if (!form.id_producto) errors.id_producto = "Seleccione un producto.";
    if (!form.id_proveedor) errors.id_proveedor = "Seleccione un proveedor.";
    return errors;
}

function validateEdit(form) {
    const errors = {};
    if (!form.cantidad || isNaN(form.cantidad) || Number(form.cantidad) <= 0)
        errors.cantidad = "La cantidad debe ser un número mayor a 0.";
    if (!form.descripcion) errors.descripcion = "La descripción es obligatoria.";
    if (!/^[a-zA-Z0-9\s]+$/.test(form.descripcion))
        errors.descripcion = "Solo letras, números y espacios.";
    if (form.descripcion.length > 150) errors.descripcion = "Máximo 150 caracteres.";
    return errors;
}

function CompraModal({ compra, onClose, onSave }) {
    const isEdit = !!compra;
    const [form, setForm] = useState(
        isEdit
            ? { cantidad: compra.cantidad, descripcion: compra.descripcion }
            : emptyForm
    );
    const [selects, setSelects] = useState({ productos: [], proveedores: [] });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!isEdit) {
            api.getSelects().then((res) => {
                if (res.success) setSelects({ productos: res.productos, proveedores: res.proveedores });
            });
        }
    }, [isEdit]);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = isEdit ? validateEdit(form) : validateCreate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateCompra(compra.id_compra, form)
                : await api.createCompra(form);
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
            <h2>{isEdit ? "Editar Compra" : "Nueva Compra"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID Compra (No editable)</label><br />
                    <input type="text" value={compra.id_compra} disabled /><br /><br />
                    <label>Producto (No editable)</label><br />
                    <input type="text" value={compra.nombre_producto} disabled /><br /><br />
                    <label>Proveedor (No editable)</label><br />
                    <input type="text" value={compra.correo_proveedor} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Cantidad</label><br />
                <input name="cantidad" type="number" value={form.cantidad} onChange={handle} min="1" /><br />
                {errors.cantidad && <span>{errors.cantidad}</span>}
            </div><br />

            <div>
                <label>Descripción</label><br />
                <input name="descripcion" type="text" value={form.descripcion} onChange={handle} maxLength={150} /><br />
                {errors.descripcion && <span>{errors.descripcion}</span>}
            </div><br />

            {!isEdit && (
                <>
                    <div>
                        <label>Producto</label><br />
                        <select name="id_producto" value={form.id_producto} onChange={handle}>
                            <option value="">-- Seleccione un producto --</option>
                            {selects.productos.map((p) => (
                                <option key={p.id_producto} value={p.id_producto}>{p.nombre}</option>
                            ))}
                        </select><br />
                        {errors.id_producto && <span>{errors.id_producto}</span>}
                    </div><br />

                    <div>
                        <label>Proveedor</label><br />
                        <select name="id_proveedor" value={form.id_proveedor} onChange={handle}>
                            <option value="">-- Seleccione un proveedor --</option>
                            {selects.proveedores.map((pv) => (
                                <option key={pv.nit_proveedor} value={pv.nit_proveedor}>{pv.correo}</option>
                            ))}
                        </select><br />
                        {errors.id_proveedor && <span>{errors.id_proveedor}</span>}
                    </div><br />
                </>
            )}

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function ComprasCRUD() {
    const [compras, setCompras] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getCompras();
            if (res.success) setCompras(res.data);
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

    const handleDelete = async (id) => {
        try {
            const res = await api.deleteCompra(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); load(); }
            else setMensaje({ texto: res.message, tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmDelete(null);
        }
    };

    return (
        <div>
            <h1>Compras Registradas</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nueva Compra</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : compras.length === 0 ? (
                <p>No hay compras registradas.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cantidad</th>
                            <th>Descripción</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {compras.map((c) => (
                            <tr key={c.id_compra}>
                                <td>{c.id_compra}</td>
                                <td>{c.cantidad}</td>
                                <td>{c.descripcion}</td>
                                <td>{c.nombre_producto}</td>
                                <td>{c.correo_proveedor}</td>
                                <td>
                                    <button onClick={() => setModal(c)}>Editar</button>{" "}
                                    <button onClick={() => setConfirmDelete(c)}>Eliminar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <CompraModal
                    compra={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>¿Eliminar compra ID <strong>{confirmDelete.id_compra}</strong>?</p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.id_compra)}>Sí, eliminar</button>
                </div>
            )}
        </div>
    );
}