import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://localhost/FerreInver/server";

const api = {
    getStocks: () =>
        fetch(`${API_BASE}/stocks`).then((r) => r.json()),

    getProductos: () =>
        fetch(`${API_BASE}/stocks?selects=1`).then((r) => r.json()),

    createStock: (data) =>
        fetch(`${API_BASE}/stocks`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateStock: (id, data) =>
        fetch(`${API_BASE}/stocks?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteStock: (id) =>
        fetch(`${API_BASE}/stocks?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = { id_producto: "", cantidad: "" };

function validate(form) {
    const errors = {};
    if (!form.id_producto) errors.id_producto = "Seleccione un producto.";
    if (form.cantidad === "" || isNaN(form.cantidad) || Number(form.cantidad) < 0)
        errors.cantidad = "La cantidad debe ser un número mayor o igual a 0.";
    if (Number(form.cantidad) % 1 !== 0)
        errors.cantidad = "La cantidad debe ser un número entero.";
    return errors;
}

function StockModal({ stock, onClose, onSave }) {
    const isEdit = !!stock;
    const [form, setForm] = useState(
        isEdit
            ? { id_producto: stock.id_producto, cantidad: stock.cantidad }
            : emptyForm
    );
    const [productos, setProductos] = useState([]);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        api.getProductos().then((res) => {
            // El controller devuelve { success, data: { productos: [...] } }
            if (res.success) setProductos(res.data.productos);
        });
    }, []);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateStock(stock.id_stock, form)
                : await api.createStock(form);
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
            <h2>{isEdit ? "Editar Stock" : "Nuevo Stock"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID Stock (No editable)</label><br />
                    <input type="text" value={stock.id_stock} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Producto</label><br />
                <select name="id_producto" value={form.id_producto} onChange={handle}>
                    <option value="">-- Seleccione un producto --</option>
                    {productos.map((p) => (
                        <option key={p.id_producto} value={p.id_producto}>{p.nombre}</option>
                    ))}
                </select><br />
                {errors.id_producto && <span>{errors.id_producto}</span>}
            </div><br />

            <div>
                <label>Cantidad en Stock</label><br />
                <input name="cantidad" type="number" value={form.cantidad} onChange={handle} min="0" step="1" /><br />
                {errors.cantidad && <span>{errors.cantidad}</span>}
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function StocksCRUD() {
    const [stocks, setStocks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getStocks();
            if (res.success) setStocks(res.data);
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
            const res = await api.deleteStock(id);
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
            <h1>Stocks Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Stock</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : stocks.length === 0 ? (
                <p>No hay stocks registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>ID Stock</th>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Valor Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {stocks.map((s) => (
                            <tr key={s.id_stock}>
                                <td>{s.id_stock}</td>
                                <td>{s.nombre_producto}</td>
                                <td>$ {Number(s.precio).toLocaleString("es-CO")}</td>
                                <td>{s.cantidad}</td>
                                <td>$ {(Number(s.precio) * Number(s.cantidad)).toLocaleString("es-CO")}</td>
                                <td>
                                    <button onClick={() => setModal(s)}>Editar</button>{" "}
                                    <button onClick={() => setConfirmDelete(s)}>Eliminar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <StockModal
                    stock={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>
                        ¿Eliminar stock ID <strong>{confirmDelete.id_stock}</strong> ({confirmDelete.nombre_producto})?
                    </p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.id_stock)}>Sí, eliminar</button>
                </div>
            )}
        </div>
    );
}