import { useState, useEffect, useCallback } from "react";

const API_URL = import.meta.env.VITE_API_URL

const MEDIOS_PAGO    = ["Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Transferencia", "PSE", "Nequi", "Daviplata"];
const ESTADOS_PEDIDO = ["pendiente", "recibido", "listo para recibir", "cancelado"];

const api = {
    getPedidos: () =>
        fetch(`${API_URL}/pedidos`).then((r) => r.json()),

    getClientes: () =>
        fetch(`${API_URL}/pedidos?selects=1`).then((r) => r.json()),

    createPedido: (data) =>
        fetch(`${API_URL}/pedidos`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    updatePedido: (id, data) =>
        fetch(`${API_URL}/pedidos/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),

    cancelPedido: (id) =>
        fetch(`${API_URL}/pedidos/${id}`, {
            method: "DELETE",
        }).then((r) => r.json()),
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
        <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && onClose()}>
            <div className="modal-box">

                <h2>{isEdit ? "Editar Pedido" : "Nuevo Pedido"}</h2>
                {errors.general && <p style={{ color: "#ff6b6b", fontSize: 12 }}>{errors.general}</p>}

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
                    {errors.id_cliente && <span style={{ color: "#ff6b6b", fontSize: 12 }}>{errors.id_cliente}</span>}
                </div><br />

                <div>
                    <label>Medio de Pago</label><br />
                    <select name="medio_pago" value={form.medio_pago} onChange={handle}>
                        <option value="">-- Seleccione un medio de pago --</option>
                        {MEDIOS_PAGO.map((m) => (
                            <option key={m} value={m}>{m}</option>
                        ))}
                    </select><br />
                    {errors.medio_pago && <span style={{ color: "#ff6b6b", fontSize: 12 }}>{errors.medio_pago}</span>}
                </div><br />

                <div>
                    <label>Estado del Pedido</label><br />
                    <select name="estado_pedido" value={form.estado_pedido} onChange={handle}>
                        {ESTADOS_PEDIDO.map((e) => (
                            <option key={e} value={e}>{e}</option>
                        ))}
                    </select><br />
                    {errors.estado_pedido && <span style={{ color: "#ff6b6b", fontSize: 12 }}>{errors.estado_pedido}</span>}
                </div><br />

                <div className="modal-footer">
                    <button onClick={onClose}>Cancelar</button>
                    <button onClick={submit} disabled={loading}>
                        {loading ? "Guardando..." : isEdit ? "Actualizar" : "Registrar"}
                    </button>
                </div>

            </div>
        </div>
    );
}

export default function PedidosCRUD() {
    const [pedidos, setPedidos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modal, setModal] = useState(null);
    const [mensaje, setMensaje] = useState(null);
    const [confirmCancelar, setConfirmCancelar] = useState(null);
    const [filtroFecha, setFiltroFecha] = useState("");

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
            const res = await api.cancelPedido(id);
            if (res.success) { setMensaje({ texto: res.message, tipo: "success" }); load(); }
            else setMensaje({ texto: res.message, tipo: "error" });
        } catch {
            setMensaje({ texto: "No se pudo conectar con la API.", tipo: "error" });
        } finally {
            setConfirmCancelar(null);
        }
    };

    const pedidosFiltrados = filtroFecha
        ? pedidos.filter((p) => p.fecha_hora?.startsWith(filtroFecha))
        : pedidos;

    return (
        <div>
            <h1>Pedidos Registrados</h1>

            {mensaje && (
                <p style={{ color: mensaje.tipo === "success" ? "green" : "red" }}>{mensaje.texto}</p>
            )}

            <button onClick={() => setModal("create")}>+ Nuevo Pedido</button>
            <br /><br />

            <div>
                <label>Filtrar por fecha: </label>
                <input
                    type="date"
                    value={filtroFecha}
                    onChange={(e) => setFiltroFecha(e.target.value)}
                />
                {filtroFecha && (
                    <button onClick={() => setFiltroFecha("")}>✕ Limpiar</button>
                )}
            </div>
            <br />

            {loading ? (
                <p>Cargando...</p>
            ) : pedidosFiltrados.length === 0 ? (
                <p>{filtroFecha ? "No hay pedidos en esa fecha." : "No hay pedidos registrados."}</p>
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
                        {pedidosFiltrados.map((p) => (
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
                <div className="modal-overlay" onClick={(e) => e.target === e.currentTarget && setConfirmCancelar(null)}>
                    <div className="modal-box" style={{ maxWidth: 380 }}>
                        <h2>¿Cancelar pedido?</h2>
                        <p style={{ color: "var(--text)", fontSize: 13, marginBottom: 6 }}>
                            Vas a cancelar el pedido de{" "}
                            <strong>{confirmCancelar.cliente?.nombre || "este cliente"}</strong>.
                        </p>
                        <p style={{ color: "var(--muted)", fontSize: 12, marginBottom: 8 }}>
                            El estado cambiará a "cancelado" y no podrá revertirse desde aquí.
                        </p>
                        <div className="modal-footer">
                            <button onClick={() => setConfirmCancelar(null)}>Cerrar</button>
                            <button onClick={() => handleCancelar(confirmCancelar.id_pedido)}>Sí, cancelar</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}