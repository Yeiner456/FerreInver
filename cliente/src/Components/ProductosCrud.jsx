import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://localhost/ferreinver/server/productos/api";

const api = {
    getProductos: () =>
        fetch(`${API_BASE}/apiProductos.php`).then((r) => r.json()),

    createProducto: (data) =>
        fetch(`${API_BASE}/apiProductos.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateProducto: (id, data) =>
        fetch(`${API_BASE}/apiProductos.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteProducto: (id) =>
        fetch(`${API_BASE}/apiProductos.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = {
    nombre: "",
    precio: "",
    descripcion: "",
};

function validate(form) {
    const errors = {};
    if (!form.nombre) errors.nombre = "El nombre es obligatorio.";
    if (form.nombre.length > 30) errors.nombre = "Máximo 30 caracteres.";
    if (!form.precio || isNaN(form.precio) || Number(form.precio) <= 0)
        errors.precio = "El precio debe ser un número mayor a 0.";
    if (Number(form.precio) % 1 !== 0)
        errors.precio = "El precio debe ser un número entero.";
    if (form.descripcion.length > 100)
        errors.descripcion = "Máximo 100 caracteres.";
    return errors;
}

function ProductoModal({ producto, onClose, onSave }) {
    const isEdit = !!producto;
    const [form, setForm] = useState(
        isEdit
            ? { nombre: producto.nombre, precio: producto.precio, descripcion: producto.descripcion }
            : emptyForm
    );
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateProducto(producto.ID_producto, form)
                : await api.createProducto(form);
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
            <h2>{isEdit ? "Editar Producto" : "Nuevo Producto"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={producto.id_producto} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Nombre del Producto</label><br />
                <input name="nombre" type="text" value={form.nombre} onChange={handle} maxLength={30} /><br />
                {errors.nombre && <span>{errors.nombre}</span>}
            </div><br />

            <div>
                <label>Precio</label><br />
                <input name="precio" type="number" value={form.precio} onChange={handle} min="1" step="1" /><br />
                {errors.precio && <span>{errors.precio}</span>}
            </div><br />

            <div>
                <label>Descripción (opcional)</label><br />
                <textarea
                    name="descripcion"
                    value={form.descripcion}
                    onChange={handle}
                    maxLength={100}
                    rows={3}
                    placeholder="Producto de ferreinver disponible"
                /><br />
                {errors.descripcion && <span>{errors.descripcion}</span>}
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function ProductosCRUD() {
    const [productos, setProductos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getProductos();
            if (res.success) setProductos(res.data);
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
            const res = await api.deleteProducto(id);
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
            <h1>Productos Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Producto</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : productos.length === 0 ? (
                <p>No hay productos registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {productos.map((p) => (
                            <tr key={p.id_producto}>
                                <td>{p.id_producto}</td>
                                <td>{p.nombre}</td>
                                <td>$ {Number(p.precio).toLocaleString("es-CO")}</td>
                                <td>{p.descripcion}</td>
                                <td>{p.estado_producto}</td>
                                <td>
                                    <button onClick={() => setModal(p)}>Editar</button>{" "}
                                    <button onClick={() => setConfirmDelete(p)}>Eliminar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <ProductoModal
                    producto={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>¿Eliminar producto <strong>{confirmDelete.nombre}</strong> (ID: {confirmDelete.id_producto})?</p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.ID_producto)}>Sí, eliminar</button>
                </div>
            )}
        </div>
    );
}