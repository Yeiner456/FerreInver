import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://localhost/ferreinver/server/cotizaciones/api";

const api = {
    getCotizaciones: () =>
        fetch(`${API_BASE}/apiCotizaciones.php`).then((r) => r.json()),

    // Clientes e invernaderos activos para los selects
    getSelects: () =>
        fetch(`${API_BASE}/apiCotizaciones.php?selects=1`).then((r) => r.json()),

    createCotizacion: (data) =>
        fetch(`${API_BASE}/apiCotizaciones.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updateCotizacion: (id, data) =>
        fetch(`${API_BASE}/apiCotizaciones.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    deleteCotizacion: (id) =>
        fetch(`${API_BASE}/apiCotizaciones.php?id=${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
};

const emptyForm = {
    cliente_id: "",
    invernadero_id: "",
    largo: "",
    ancho: "",
    metros_cuadrados: "",
    valor_m2: "",
    total: "",
    estado: "pendiente",
};

function validate(form) {
    const errors = {};
    if (!form.cliente_id) errors.cliente_id = "Seleccione un cliente.";
    if (!form.invernadero_id) errors.invernadero_id = "Seleccione un invernadero.";
    if (!form.largo || isNaN(form.largo) || Number(form.largo) <= 0)
        errors.largo = "El largo debe ser mayor a 0.";
    if (!form.ancho || isNaN(form.ancho) || Number(form.ancho) <= 0)
        errors.ancho = "El ancho debe ser mayor a 0.";
    if (!form.estado) errors.estado = "El estado es obligatorio.";
    return errors;
}

function CotizacionModal({ cotizacion, onClose, onSave }) {
    const isEdit = !!cotizacion;
    const [form, setForm] = useState(isEdit ? { ...cotizacion } : emptyForm);
    const [selects, setSelects] = useState({ clientes: [], invernaderos: [] });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        api.getSelects().then((res) => {
            if (res.success) setSelects({ clientes: res.clientes, invernaderos: res.invernaderos });
        });
    }, []);

    // Recalcular automáticamente al cambiar largo, ancho o invernadero
    const recalcular = (updatedForm) => {
        const largo = parseFloat(updatedForm.largo) || 0;
        const ancho = parseFloat(updatedForm.ancho) || 0;
        const m2 = largo * ancho;

        const inv = selects.invernaderos.find(
            (i) => String(i.id_invernadero) === String(updatedForm.invernadero_id)
        );
        const precioM2 = inv ? parseFloat(inv.precio_m2) : parseFloat(updatedForm.valor_m2) || 0;
        const total = m2 * precioM2;

        return {
            ...updatedForm,
            metros_cuadrados: m2 > 0 ? m2.toFixed(2) : "",
            valor_m2: precioM2 > 0 ? precioM2.toFixed(2) : "",
            total: total > 0 ? total.toFixed(2) : "",
        };
    };

    const handle = (e) => {
        const updated = { ...form, [e.target.name]: e.target.value };
        const recalculated = recalcular(updated);
        setForm(recalculated);
    };

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updateCotizacion(cotizacion.id_cotizacion, form)
                : await api.createCotizacion(form);
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
            <h2>{isEdit ? "Editar Cotización" : "Nueva Cotización"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID (No editable)</label><br />
                    <input type="text" value={cotizacion.id_cotizacion} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Cliente</label><br />
                <select name="cliente_id" value={form.cliente_id} onChange={handle}>
                    <option value="">-- Seleccione un cliente --</option>
                    {selects.clientes.map((cl) => (
                        <option key={cl.documento} value={cl.documento}>
                            {cl.nombre} (Doc: {cl.documento})
                        </option>
                    ))}
                </select><br />
                {errors.cliente_id && <span>{errors.cliente_id}</span>}
            </div><br />

            <div>
                <label>Invernadero</label><br />
                <select name="invernadero_id" value={form.invernadero_id} onChange={handle}>
                    <option value="">-- Seleccione un invernadero --</option>
                    {selects.invernaderos.map((inv) => (
                        <option key={inv.id_invernadero} value={inv.id_invernadero}>
                            {inv.nombre} ($ {Number(inv.precio_m2).toLocaleString("es-CO", { minimumFractionDigits: 2 })}/m²)
                        </option>
                    ))}
                </select><br />
                {errors.invernadero_id && <span>{errors.invernadero_id}</span>}
            </div><br />

            <div>
                <label>Largo (metros)</label><br />
                <input name="largo" type="number" value={form.largo} onChange={handle} min="0.01" step="0.01" /><br />
                {errors.largo && <span>{errors.largo}</span>}
            </div><br />

            <div>
                <label>Ancho (metros)</label><br />
                <input name="ancho" type="number" value={form.ancho} onChange={handle} min="0.01" step="0.01" /><br />
                {errors.ancho && <span>{errors.ancho}</span>}
            </div><br />

            <div>
                <label>Metros Cuadrados (calculado automáticamente)</label><br />
                <input type="number" value={form.metros_cuadrados} readOnly /><br />
            </div><br />

            <div>
                <label>Valor por m² (según invernadero)</label><br />
                <input type="number" value={form.valor_m2} readOnly /><br />
            </div><br />

            <div>
                <label>Total ($)</label><br />
                <input type="number" value={form.total} readOnly /><br />
            </div><br />

            <div>
                <label>Estado</label><br />
                <select name="estado" value={form.estado} onChange={handle}>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function CotizacionesCRUD() {
    const [cotizaciones, setCotizaciones] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmDelete, setConfirmDelete] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getCotizaciones();
            if (res.success) setCotizaciones(res.data);
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
            const res = await api.deleteCotizacion(id);
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
            <h1>Cotizaciones</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nueva Cotización</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : cotizaciones.length === 0 ? (
                <p>No hay cotizaciones registradas.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Invernadero</th>
                            <th>Largo</th>
                            <th>Ancho</th>
                            <th>m²</th>
                            <th>Valor m²</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cotizaciones.map((c) => (
                            <tr key={c.id_cotizacion}>
                                <td>{c.id_cotizacion}</td>
                                <td>{c.cliente_nombre}</td>
                                <td>{c.invernadero_nombre}</td>
                                <td>{Number(c.largo).toFixed(2)}</td>
                                <td>{Number(c.ancho).toFixed(2)}</td>
                                <td>{Number(c.metros_cuadrados).toFixed(2)}</td>
                                <td>$ {Number(c.valor_m2).toLocaleString("es-CO", { minimumFractionDigits: 2 })}</td>
                                <td>$ {Number(c.total).toLocaleString("es-CO", { minimumFractionDigits: 2 })}</td>
                                <td>{c.fecha}</td>
                                <td>{c.estado}</td>
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
                <CotizacionModal
                    cotizacion={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmDelete && (
                <div>
                    <p>
                        ¿Eliminar cotización ID <strong>{confirmDelete.id_cotizacion}</strong> de{" "}
                        <strong>{confirmDelete.cliente_nombre}</strong>?
                    </p>
                    <button onClick={() => setConfirmDelete(null)}>Cancelar</button>{" "}
                    <button onClick={() => handleDelete(confirmDelete.id_cotizacion)}>Sí, eliminar</button>
                </div>
            )}
        </div>
    );
}