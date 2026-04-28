import { useState, useEffect, useCallback } from "react";

// Solo se pueden editar descripcion y cantidad (igual que en el PHP original)

const API_BASE = "http://localhost/FerreInver/server";

const api = {
    getProductosPedidos: () =>
        fetch(`${API_BASE}/productos-pedidos`).then((r) => r.json()),

    // El controller devuelve { success, data: { productos: [...], pedidos: [...] } }
    getSelects: () =>
        fetch(`${API_BASE}/productos-pedidos?selects=1`).then((r) => r.json()),

    createProductoPedido: (data) =>
        fetch(`${API_BASE}/productos-pedidos`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateProductoPedido: (id, data) =>
        fetch(`${API_BASE}/productos-pedidos?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteProductoPedido: (id) =>
        fetch(`${API_BASE}/productos-pedidos?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = { id_producto: "", id_pedido: "", descripcion: "", cantidad: "" };

function validateCreate(form) {
    const errors = {};
    if (!form.id_producto) errors.id_producto = "Seleccione un producto.";
    if (!form.id_pedido) errors.id_pedido = "Seleccione un pedido.";
    if (!form.descripcion) errors.descripcion = "La descripción es obligatoria.";
    if (!/^[A-Za-z0-9\s,.-]+$/.test(form.descripcion))
        errors.descripcion = "Solo letras, números y los caracteres: , . -";
    if (form.descripcion.length > 100) errors.descripcion = "Máximo 100 caracteres.";
    if (!form.cantidad || isNaN(form.cantidad) || Number(form.cantidad) <= 0 || Number(form.cantidad) > 1000)
        errors.cantidad = "La cantidad debe ser entre 1 y 1000.";
    return errors;
}

function validateEdit(form) {
    const errors = {};
    if (!form.descripcion) errors.descripcion = "La descripción es obligatoria.";
    if (!/^[A-Za-z0-9\s,.-]+$/.test(form.descripcion))
        errors.descripcion = "Solo letras, números y los caracteres: , . -";
    if (form.descripcion.length > 100) errors.descripcion = "Máximo 100 caracteres.";
    if (!form.cantidad || isNaN(form.cantidad) || Number(form.cantidad) <= 0 || Number(form.cantidad) > 1000)
        errors.cantidad = "La cantidad debe ser entre 1 y 1000.";
    return errors;
}

function ProductoPedidoModal({ registro, onClose, onSave }) {
    const isEdit = !!registro;
    const [form, setForm] = useState(
        isEdit
            ? { descripcion: registro.descripcion, cantidad: registro.cantidad }
            : emptyForm
    );
    const [selects, setSelects] = useState({ productos: [], pedidos: [] });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!isEdit) {
            api.getSelects().then((res) => {
                // El controller devuelve { success, data: { productos: [...], pedidos: [...] } }
                if (res.success) setSelects({ productos: res.data.productos, pedidos: res.data.pedidos });
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
                ? await api.updateProductoPedido(registro.id, form)
                : await api.createProductoPedido(form);
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
            <h2>{isEdit ? "Editar Producto-Pedido" : "Registrar Producto en Pedido"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={registro.id} disabled /><br /><br />
                    <label>Producto (No editable)</label><br />
                    <input type="text" value={registro.nombre_producto} disabled /><br /><br />
                    <label>Pedido (No editable)</label><br />
                    <input type="text" value={`#${registro.id_pedido}`} disabled /><br /><br />
                </div>
            )}

            {!isEdit && (
                <>
                    <div>
                        <label>Producto</label><br />
                        <select name="id_producto" value={form.id_producto} onChange={handle}>
                            <option value="">-- Selecciona un producto --</option>
                            {selects.productos.map((p) => (
                                <option key={p.id_producto} value={p.id_producto}>{p.nombre}</option>
                            ))}
                        </select><br />
                        {errors.id_producto && <span>{errors.id_producto}</span>}
                    </div><br />

                    <div>
                        <label>Pedido</label><br />
                        <select name="id_pedido" value={form.id_pedido} onChange={handle}>
                            <option value="">-- Selecciona un pedido --</option>
                            {selects.pedidos.map((p) => (
                                <option key={p.id_pedido} value={p.id_pedido}>Pedido #{p.id_pedido}</option>
                            ))}
                        </select><br />
                        {errors.id_pedido && <span>{errors.id_pedido}</span>}
                    </div><br />
                </>
            )}

            <div>
                <label>Descripción</label><br />
                <input name="descripcion" type="text" value={form.descripcion} onChange={handle} maxLength={100} /><br />
                {errors.descripcion && <span>{errors.descripcion}</span>}
            </div><br />

            <div>
                <label>Cantidad</label><br />
                <input name="cantidad" type="number" value={form.cantidad} onChange={handle} min="1" max="1000" /><br />
                {errors.cantidad && <span>{errors.cantidad}</span>}
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function ProductosPedidosCRUD() {
    const [registros, setRegistros] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getProductosPedidos();
            if (res.success) setRegistros(res.data);
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
            const res = await api.deleteProductoPedido(id);
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
            <h1>Productos en Pedidos</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Registrar Producto en Pedido</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : registros.length === 0 ? (
                <p>No hay registros.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Pedido</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {registros.map((r) => (
                            <tr key={r.id}>
                                <td>{r.nombre_producto}</td>
                                <td>#{r.id_pedido}</td>
                                <td>{r.descripcion}</td>
                                <td>{r.cantidad}</td>
                                <td>
                                    <button onClick={() => setModal(r)}>Editar</button>{" "}
                                    <button onClick={() => setConfirmDelete(r)}>Eliminar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <ProductoPedidoModal
                    registro={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>
                        ¿Quitar <strong>{confirmDelete.nombre_producto}</strong> del Pedido #{confirmDelete.id_pedido}?
                    </p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.id)}>Sí, quitar</button>
                </div>
            )}
        </div>
    );
}