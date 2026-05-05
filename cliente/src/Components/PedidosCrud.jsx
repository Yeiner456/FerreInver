import { useState, useEffect, useCallback } from "react";

const API_BASE = "http://127.0.0.1:8000/api";

const MEDIOS_PAGO    = ["Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Transferencia", "PSE", "Nequi", "Daviplata"];
const ESTADOS_PEDIDO = ["pendiente", "recibido", "listo para recibir", "cancelado"];

const api = {
    getPedidos: () =>
        fetch(`${API_BASE}/pedidos`).then((r) => r.json()),

    // Clientes para el select — el controller devuelve { success, data: { clientes: [...] } }
    getClientes: () =>
        fetch(`${API_BASE}/pedidos?selects=1`).then((r) => r.json()),

    createPedido: (data) =>
        fetch(`${API_BASE}/pedidos`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    // Pedido completo con items (carrito del cliente)
    createPedidoCompleto: (data) =>
        fetch(`${API_BASE}/pedidos/completo`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updatePedido: (id, data) =>
        fetch(`${API_BASE}/pedidos/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    cancelPedido: (id) =>
        fetch(`${API_BASE}/pedidos/${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),


    // Vista cliente: pedidos por documento
    getPedidosByCliente: (documento) =>
        fetch(`${API_BASE}/pedidos?documento=${documento}`).then((r) => r.json()),
};

const emptyForm = {
    id_cliente: "",
    medio_pago: "",
    estado_pedido: "pendiente",
};

function validate(form) {
    const errors = {};
    if (!form.id_cliente) errors.id_cliente = "Seleccione un cliente.";
    if (!form.medio_pago) errors.medio_pago = "Seleccione un medio de pago.";
    if (!form.estado_pedido) errors.estado_pedido = "Seleccione un estado.";
    return errors;
}

function PedidoModal({ pedido, onClose, onSave }) {
    const isEdit = !!pedido;
    const [form, setForm] = useState(
        isEdit
            ? { id_cliente: pedido.id_cliente, medio_pago: pedido.medio_pago, estado_pedido: pedido.estado_pedido }
            : emptyForm
    );
    const [clientes, setClientes] = useState([]);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        api.getClientes().then((res) => {
            // El controller devuelve { success, data: { clientes: [...] } }
            if (res.success) setClientes(res.data.clientes);
        });
    }, []);

    const handle = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

    const submit = async () => {
        const errs = validate(form);
        if (Object.keys(errs).length) { setErrors(errs); return; }
        setLoading(true);
        try {
            const res = isEdit
                ? await api.updatePedido(pedido.id_pedido, form)
                : await api.createPedido(form);
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
            <h2>{isEdit ? "Editar Pedido" : "Nuevo Pedido"}</h2>
            {errors.general && <p>{errors.general}</p>}

            {isEdit && (
                <div>
                    <label>ID Pedido (No editable)</label><br />
                    <input type="text" value={pedido.id_pedido} disabled /><br /><br />
                    <label>Fecha y Hora (No editable)</label><br />
                    <input type="text" value={pedido.fecha_hora} disabled /><br /><br />
                </div>
            )}

            <div>
                <label>Cliente</label><br />
                <select name="id_cliente" value={form.id_cliente} onChange={handle}>
                    <option value="">-- Seleccione un cliente --</option>
                    {clientes.map((c) => (
                        <option key={c.documento} value={c.documento}>
                            {c.nombre} - {c.correo}
                        </option>
                    ))}
                </select><br />
                {errors.id_cliente && <span>{errors.id_cliente}</span>}
            </div><br />

            <div>
                <label>Medio de Pago</label><br />
                <select name="medio_pago" value={form.medio_pago} onChange={handle}>
                    <option value="">-- Seleccione un medio de pago --</option>
                    {MEDIOS_PAGO.map((m) => (
                        <option key={m} value={m}>{m}</option>
                    ))}
                </select><br />
                {errors.medio_pago && <span>{errors.medio_pago}</span>}
            </div><br />

            <div>
                <label>Estado del Pedido</label><br />
                <select name="estado_pedido" value={form.estado_pedido} onChange={handle}>
                    {ESTADOS_PEDIDO.map((e) => (
                        <option key={e} value={e}>{e}</option>
                    ))}
                </select><br />
                {errors.estado_pedido && <span>{errors.estado_pedido}</span>}
            </div><br />

            <button onClick={onClose}>Cancelar</button>{" "}
            <button onClick={submit} disabled={loading}>
                {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
            </button>
        </div>
    );
}

export default function PedidosCRUD() {
    const [pedidos, setPedidos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmCancelar, setConfirmCancelar] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getPedidos();
            if (res.success) setPedidos(res.data);
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

    const handleCancelar = async (id) => {
        try {
            const res = await api.cancelarPedido(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); load(); }
            else setMensaje({ texto: res.message, tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmCancelar(null);
        }
    };

    return (
        <div>
            <h1>Pedidos Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Pedido</button>
            <br /><br />

            {loading ? (
                <p>Cargando...</p>
            ) : pedidos.length === 0 ? (
                <p>No hay pedidos registrados.</p>
            ) : (
                <table border="1" cellPadding="8">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Fecha y Hora</th>
                            <th>Medio de Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {pedidos.map((p) => (
                            <tr key={p.id_pedido}>
                                <td>{p.cliente?.nombre || "N/A"}</td>
                                <td>{p.cliente?.correo || "N/A"}</td>
                                <td>{p.fecha_hora}</td>
                                <td>{p.medio_pago}</td>
                                <td>{p.estado_pedido}</td>
                                <td>
                                    <button onClick={() => setModal(p)}>Editar</button>{" "}
                                    <button
                                        onClick={() => setConfirmCancelar(p)}
                                        disabled={p.estado_pedido === "cancelado"}
                                    >
                                        Cancelar
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}

            {modal && (
                <PedidoModal
                    pedido={modal === "create" ? null : modal}
                    onClose={() => setModal(null)}
                    onSave={handleSave}
                />
            )}

            {confirmCancelar && (
                <div>
                    <p>
                        ¿Cancelar pedido de{" "}
                        <strong>{confirmCancelar.nombre_cliente}</strong>?
                        <br />
                        <small>El estado cambiará a "cancelado" y no podrá revertirse desde aquí.</small>
                    </p>
                    <button onClick={() => setConfirmCancelar(null)}>Cerrar</button>{" "}
                    <button onClick={() => handleCancelar(confirmCancelar.id_pedido)}>Sí, cancelar</button>
                </div>
            )}
        </div>
    );
}