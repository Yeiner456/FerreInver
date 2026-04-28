import { useState, useEffect } from "react";
import "../src/styles/CotizacionesPublicas.css";

const API_BASE = "http://localhost/FerreInver/server";

const api = {
    getSelects: () =>
        fetch(`${API_BASE}/cotizaciones?selects=1`).then((r) => r.json()),
    createCotizacion: (data) =>
        fetch(`${API_BASE}/cotizaciones`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        }).then((r) => r.json()),
};


const PASOS = ["El invernadero", "Dimensiones", "Resumen"];

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

function StepIndicator({ paso }) {
    return (
        <div className="step-indicator">
            {PASOS.map((label, i) => {
                const activo = i === paso;
                const completado = i < paso;
                return (
                    <div key={i} className="step-indicator__item">
                        <div className="step-indicator__node">
                            <div className={[
                                "step-indicator__circle",
                                activo ? "step-indicator__circle--activo" : "",
                                completado ? "step-indicator__circle--completado" : "",
                            ].join(" ")}>
                                {completado ? (
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8L6.5 11.5L13 5" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                ) : i + 1}
                            </div>
                            <span className={[
                                "step-indicator__label",
                                activo ? "step-indicator__label--activo" : "",
                                completado ? "step-indicator__label--completado" : "",
                            ].join(" ")}>
                                {label}
                            </span>
                        </div>
                        {i < PASOS.length - 1 && (
                            <div className={[
                                "step-indicator__line",
                                completado ? "step-indicator__line--completado" : "",
                            ].join(" ")} />
                        )}
                    </div>
                );
            })}
        </div>
    );
}

function FieldError({ msg }) {
    if (!msg) return null;
    return (
        <div className="field-error">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="7" r="6.5" stroke="#E24B4A" strokeWidth="1" />
                <path d="M7 4v3.5" stroke="#E24B4A" strokeWidth="1.5" strokeLinecap="round" />
                <circle cx="7" cy="10" r="0.75" fill="#E24B4A" />
            </svg>
            <span className="field-error__text">{msg}</span>
        </div>
    );
}

function InputField({ label, error, children }) {
    return (
        <div className="field-wrapper">
            <label className="field-label">{label}</label>
            {children}
            <FieldError msg={error} />
        </div>
    );
}

export default function CotizacionPublica() {
    const [paso, setPaso] = useState(0);
    const [form, setForm] = useState(emptyForm);
    const [selects, setSelects] = useState({ clientes: [], invernaderos: [] });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(true);
    const [enviando, setEnviando] = useState(false);
    const [exito, setExito] = useState(false);
    const [errorGeneral, setErrorGeneral] = useState(null);


    const [usuarioSesion, setUsuarioSesion] = useState(null);

    useEffect(() => {

        const raw = sessionStorage.getItem("usuario");
        if (raw) {
            const user = JSON.parse(raw);
            setUsuarioSesion(user);
            setForm((prev) => ({ ...prev, cliente_id: user.documento }));
        } else {
            setErrorGeneral("No hay sesión activa. Por favor inicia sesión.");
        }

        api.getSelects()
            .then((res) => {
                if (res.success) setSelects({ clientes: res.clientes, invernaderos: res.invernaderos });
            })
            .catch(() => {
                setErrorGeneral("No se pudo conectar con el servidor. Intente más tarde.");
            })
            .finally(() => setLoading(false));
    }, []);

    const recalcular = (updatedForm) => {
        const largo = parseFloat(updatedForm.largo) || 0;
        const ancho = parseFloat(updatedForm.ancho) || 0;
        const m2 = largo * ancho;
        const inv = selects.invernaderos.find(
            (i) => String(i.id_invernadero) === String(updatedForm.invernadero_id)
        );
        const precioM2 = inv ? parseFloat(inv.precio_m2) : 0;
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
        setForm(recalcular(updated));
        setErrors((prev) => ({ ...prev, [e.target.name]: undefined }));
    };

    const validarPaso = () => {
        const e = {};
        
        if (paso === 0 && !form.invernadero_id) e.invernadero_id = "Seleccione un invernadero para continuar.";
        if (paso === 1) {
            if (!form.largo || isNaN(form.largo) || Number(form.largo) <= 0)
                e.largo = "El largo debe ser mayor a 0.";
            if (!form.ancho || isNaN(form.ancho) || Number(form.ancho) <= 0)
                e.ancho = "El ancho debe ser mayor a 0.";
        }
        setErrors(e);
        return Object.keys(e).length === 0;
    };

    const siguiente = () => { if (validarPaso()) setPaso((p) => p + 1); };
    const anterior = () => setPaso((p) => p - 1);

    const enviar = async () => {
        setEnviando(true);
        setErrorGeneral(null);
        try {
            const res = await api.createCotizacion(form);
            if (res.success) setExito(true);
            else setErrorGeneral(res.message || "Ocurrió un error al enviar la cotización.");
        } catch {
            setErrorGeneral("No se pudo conectar con el servidor.");
        } finally {
            setEnviando(false);
        }
    };

    const invernaderoSeleccionado = selects.invernaderos.find(
        (i) => String(i.id_invernadero) === String(form.invernadero_id)
    );

    const formatCOP = (n) =>
        Number(n).toLocaleString("es-CO", { style: "currency", currency: "COP", minimumFractionDigits: 2 });

    if (loading) {
        return (
            <div className="loading-wrapper">
                <div className="loading-spinner" />
                <p>Cargando formulario...</p>
            </div>
        );
    }

    if (exito) {
        return (
            <div className="exito-wrapper">
                <div className="exito-icono">
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <path d="M8 18L15 25L28 12" stroke="#1D9E75" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                </div>
                <h2 className="exito-titulo">¡Cotización enviada!</h2>
                <p className="exito-descripcion">
                    Tu solicitud fue registrada exitosamente. Nuestro equipo la revisará y te contactará pronto.
                </p>
                <div className="exito-resumen">
                    <div className="exito-resumen__grid">
                        
                        <span className="exito-resumen__label">Cliente</span>
                        <span className="exito-resumen__valor">{usuarioSesion?.nombre}</span>
                        <span className="exito-resumen__label">Invernadero</span>
                        <span className="exito-resumen__valor">{invernaderoSeleccionado?.nombre}</span>
                        <span className="exito-resumen__label">Dimensiones</span>
                        <span className="exito-resumen__valor">{form.largo} m × {form.ancho} m</span>
                        <span className="exito-resumen__label">Total estimado</span>
                        <span className="exito-resumen__valor exito-resumen__valor--total">{formatCOP(form.total)}</span>
                    </div>
                </div>
                <button
                    className="btn btn--repetir"
                    onClick={() => {
                        setForm({ ...emptyForm, cliente_id: usuarioSesion?.documento ?? "" });
                        setPaso(0);
                        setExito(false);
                    }}
                >
                    Hacer otra cotización
                </button>
            </div>
        );
    }

    return (
        <div className="cotizacion-wrapper">
            <div className="cotizacion-header">
                <div className="cotizacion-header__top">
                    <div className="cotizacion-header__icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <rect x="2" y="2" width="14" height="14" rx="3" stroke="#1D9E75" strokeWidth="1.5" />
                            <path d="M5 7h8M5 10h5" stroke="#1D9E75" strokeWidth="1.5" strokeLinecap="round" />
                        </svg>
                    </div>
                    <h1 className="cotizacion-header__title">Solicitar cotización</h1>
                </div>
                
                <p className="cotizacion-header__subtitle">
                    Hola, <strong>{usuarioSesion?.nombre}</strong>. Completa los datos para recibir tu estimado personalizado.
                </p>
            </div>

            <StepIndicator paso={paso} />

            <div className="cotizacion-card">
                {errorGeneral && (
                    <div className="alerta-error">{errorGeneral}</div>
                )}

                
                {paso === 0 && (
                    <div>
                        <h3 className="paso-titulo">¿Qué invernadero te interesa?</h3>
                        <div className="invernaderos-lista">
                            {selects.invernaderos.map((inv) => {
                                const sel = String(inv.id_invernadero) === String(form.invernadero_id);
                                return (
                                    <label
                                        key={inv.id_invernadero}
                                        className={`invernadero-opcion${sel ? " invernadero-opcion--seleccionado" : ""}`}
                                    >
                                        <input
                                            type="radio"
                                            name="invernadero_id"
                                            value={inv.id_invernadero}
                                            checked={sel}
                                            onChange={handle}
                                            className="invernadero-opcion__radio"
                                        />
                                        <div>
                                            <p className={`invernadero-opcion__nombre${sel ? " invernadero-opcion__nombre--seleccionado" : ""}`}>
                                                {inv.nombre}
                                            </p>
                                            <p className={`invernadero-opcion__precio${sel ? " invernadero-opcion__precio--seleccionado" : ""}`}>
                                                {formatCOP(inv.precio_m2)} / m²
                                            </p>
                                        </div>
                                        {sel && (
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <circle cx="9" cy="9" r="8.5" fill="#1D9E75" />
                                                <path d="M5.5 9L7.5 11L12.5 7" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                            </svg>
                                        )}
                                    </label>
                                );
                            })}
                        </div>
                        <FieldError msg={errors.invernadero_id} />
                    </div>
                )}

                {paso === 1 && (
                    <div>
                        <h3 className="paso-titulo">¿Cuánto espacio necesitas?</h3>
                        <div className="dimensiones-grid">
                            <InputField label="Largo (metros)" error={errors.largo}>
                                <input
                                    name="largo" type="number" value={form.largo}
                                    onChange={handle} min="0.01" step="0.01"
                                    placeholder="Ej: 10"
                                    className="input"
                                />
                            </InputField>
                            <InputField label="Ancho (metros)" error={errors.ancho}>
                                <input
                                    name="ancho" type="number" value={form.ancho}
                                    onChange={handle} min="0.01" step="0.01"
                                    placeholder="Ej: 8"
                                    className="input"
                                />
                            </InputField>
                        </div>

                        {form.metros_cuadrados && (
                            <div className="calculo-preview">
                                <div className="calculo-preview__grid">
                                    {[
                                        { label: "Área total", value: `${form.metros_cuadrados} m²`, destacado: false },
                                        { label: "Valor m²", value: formatCOP(form.valor_m2), destacado: false },
                                        { label: "Total estimado", value: formatCOP(form.total), destacado: true },
                                    ].map(({ label, value, destacado }, i) => (
                                        <div key={i}>
                                            <p className="calculo-preview__label">{label}</p>
                                            <p className={`calculo-preview__value${destacado ? " calculo-preview__value--destacado" : ""}`}>
                                                {value}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {paso === 2 && (
                    <div>
                        <h3 className="paso-titulo">Revisa tu cotización</h3>
                        <div className="resumen-tabla">
                            {[
                                
                                { label: "Cliente", value: usuarioSesion?.nombre },
                                { label: "Invernadero", value: invernaderoSeleccionado?.nombre },
                                { label: "Largo", value: `${form.largo} m` },
                                { label: "Ancho", value: `${form.ancho} m` },
                                { label: "Área total", value: `${form.metros_cuadrados} m²` },
                                { label: "Valor por m²", value: formatCOP(form.valor_m2) },
                            ].map(({ label, value }, i) => (
                                <div key={i} className={`resumen-fila ${i % 2 === 0 ? "resumen-fila--par" : "resumen-fila--impar"}`}>
                                    <span className="resumen-fila__label">{label}</span>
                                    <span className="resumen-fila__valor">{value}</span>
                                </div>
                            ))}
                            <div className="resumen-total">
                                <span className="resumen-total__label">Total estimado</span>
                                <span className="resumen-total__valor">{formatCOP(form.total)}</span>
                            </div>
                        </div>
                        <div className="aviso-estimado">
                            Esta cotización es un estimado sujeto a revisión. Nuestro equipo confirmará los valores finales.
                        </div>
                    </div>
                )}
            </div>

            <div className="cotizacion-nav">
                <button className="btn btn--anterior" onClick={anterior} disabled={paso === 0}>
                    ← Anterior
                </button>
                <span className="cotizacion-nav__contador">Paso {paso + 1} de {PASOS.length}</span>
                {paso < PASOS.length - 1 ? (
                    <button className="btn btn--siguiente" onClick={siguiente}>
                        Siguiente →
                    </button>
                ) : (
                    <button className="btn btn--enviar" onClick={enviar} disabled={enviando}>
                        {enviando ? "Enviando..." : "Enviar cotización"}
                    </button>
                )}
            </div>
        </div>
    );
}