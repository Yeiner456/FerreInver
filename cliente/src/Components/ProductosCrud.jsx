import { useState, useEffect, useCallback, useRef } from "react";

const API_BASE = "http://localhost/ferreinver/server/productos/api";
const IMG_BASE = "http://localhost/ferreinver/";

const api = {
    getProductos: () =>
        fetch(`${API_BASE}/apiProductos.php`).then((r) => r.json()),

    createProducto: (formData) =>
        fetch(`${API_BASE}/apiProductos.php`, {
            method: "POST",
            body: formData,
        }).then((r) => r.json()),

    updateProducto: (id, formData) =>
    fetch(`${API_BASE}/apiProductos.php?id=${id}&_method=PUT`, {
        method: "POST",   
        body: formData,
    }).then((r) => r.json()),

    deactivateProducto: (id) =>
        fetch(`${API_BASE}/apiProductos.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = { nombre: "", precio: "", descripcion: "" };

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
    const [errors, setErrors]     = useState({});
    const [loading, setLoading]   = useState(false);
    const [imagenFile, setImagenFile] = useState(null);
    const [preview, setPreview]   = useState(
        isEdit && producto.imagen ? IMG_BASE + producto.imagen : null
    );
    const fileInputRef = useRef();

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const handleImagen = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setImagenFile(file);
        setPreview(URL.createObjectURL(file));
    };

    const quitarImagen = () => {
        setImagenFile(null);
        setPreview(isEdit && producto.imagen ? IMG_BASE + producto.imagen : null);
        if (fileInputRef.current) fileInputRef.current.value = "";
    };

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const fd = new FormData();
            fd.append("nombre",      form.nombre);
            fd.append("precio",      form.precio);
            fd.append("descripcion", form.descripcion);
            if (imagenFile) fd.append("imagen", imagenFile);

            const res = isEdit
                ? await api.updateProducto(producto.id_producto, fd)
                : await api.createProducto(fd);

            if (res.success) onSave(res.message);
            else setErrors({ general: res.message });
        } catch {
            setErrors({ general: "No se pudo conectar con la API." });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{ border: "1px solid #ccc", padding: 16, marginTop: 12, maxWidth: 420 }}>
            <h2>{isEdit ? "Editar Producto" : "Nuevo Producto"}</h2>
            {errors.general && <p style={{ color: "red" }}>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={producto.id_producto} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Nombre del Producto</label><br />
                <input name="nombre" type="text" value={form.nombre} onChange={handle} maxLength={30} /><br />
                {errors.nombre && <span style={{ color: "red" }}>{errors.nombre}</span>}
            </div><br />

            <div>
                <label>Precio</label><br />
                <input name="precio" type="number" value={form.precio} onChange={handle} min="1" step="1" /><br />
                {errors.precio && <span style={{ color: "red" }}>{errors.precio}</span>}
            </div><br />

            <div>
                <label>Descripción (opcional)</label><br />
                <textarea name="descripcion" value={form.descripcion} onChange={handle} maxLength={100} rows={3} /><br />
                {errors.descripcion && <span style={{ color: "red" }}>{errors.descripcion}</span>}
            </div><br />

            <div>
                <label>Imagen del Producto (opcional)</label><br />
                <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    onChange={handleImagen}
                /><br />
                {isEdit && <small style={{ color: "#888" }}>Deja vacío para conservar la imagen actual.</small>}

                {preview && (
                    <div style={{ marginTop: 8 }}>
                        <img
                            src={preview}
                            alt="Preview"
                            style={{ width: 120, height: 120, objectFit: "cover", border: "1px solid #ddd", borderRadius: 4 }}
                        />
                        <br />
                        {imagenFile && (
                            <button type="button" onClick={quitarImagen} style={{ fontSize: 12, marginTop: 4 }}>
                                ✕ Quitar imagen nueva
                            </button>
                        )}
                    </div>
                )}
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function ProductosCRUD() {
    const [productos, setProductos]         = useState([]);
    const [loading, setLoading]             = useState(true);
    const [modal, setModal]                 = useState(null);
    const [mensaje, setMensaje]             = useState(null);
    const [confirmDeactivate, setConfirmDeactivate] = useState(null);

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

    const handleDeactivate = async (id) => {
        try {
            const res = await api.deactivateProducto(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); load(); }
            else setMensaje({ texto: res.message, tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmDeactivate(null);
        }
    };

    return (
        <div style={{ padding: 16 }}>
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
                            <th>Imagen</th>
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
                                <td>
                                    {p.imagen
                                        ? <img src={IMG_BASE + p.imagen} alt={p.nombre}
                                               style={{ width: 50, height: 50, objectFit: "cover", borderRadius: 4 }} />
                                        : <span style={{ color: "#aaa", fontSize: 12 }}>Sin imagen</span>
                                    }
                                </td>
                                <td>{p.nombre}</td>
                                <td>$ {Number(p.precio).toLocaleString("es-CO")}</td>
                                <td>{p.descripcion}</td>
                                <td>{p.estado_producto}</td>
                                <td>
                                    <button onClick={() => setModal(p)}>Editar</button>{" "}
                                    <button
                                        onClick={() => setConfirmDeactivate(p)}
                                        disabled={p.estado_producto === "inactivo"}
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
                <ProductoModal
                    producto={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDeactivate && (
                <div style={{ marginTop: 12, border: "1px solid #f99", padding: 12 }}>
                    <p>
                        ¿Desactivar <strong>{confirmDeactivate.nombre}</strong> (ID: {confirmDeactivate.id_producto})?
                        <br />
                        <small>El producto no aparecerá disponible para nuevos pedidos.</small>
                    </p>
                    <button onClick={() => setConfirmDeactivate(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDeactivate(confirmDeactivate.id_producto)}>Sí, desactivar</button>
                </div>
            )}
        </div>
    );
}