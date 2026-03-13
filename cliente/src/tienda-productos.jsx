import React, { useState, useEffect, useCallback } from 'react';
import './styles/tienda-productos.css';

const API_PRODUCTOS       = 'http://localhost/ferreinver/server/productos/api';
const API_LOGIN           = 'http://localhost/ferreinver/server/auth/api/apiLogin.php';
const API_PEDIDO_COMPLETO = 'http://localhost/ferreinver/server/pedidos/api/apiPedidoCompleto.php';
const IMG_BASE            = 'http://localhost/ferreinver/';

const MEDIOS_PAGO = ['Efectivo', 'Tarjeta Débito', 'Tarjeta Crédito', 'Transferencia', 'PSE', 'Nequi', 'Daviplata'];

const getSession   = () => { try { return JSON.parse(localStorage.getItem('ferreinver_cliente')); } catch { return null; } };
const saveSession  = (c) => localStorage.setItem('ferreinver_cliente', JSON.stringify(c));
const clearSession = () => localStorage.removeItem('ferreinver_cliente');

function ModalLogin({ onClose, onLoginExitoso }) {
  const [form, setForm]     = useState({ documento: '', password: '' });
  const [error, setError]   = useState('');
  const [loading, setLoading] = useState(false);
  const handle = e => setForm(f => ({ ...f, [e.target.name]: e.target.value }));
  const submit = async () => {
    setError('');
    if (!form.documento || !form.password) { setError('Completa todos los campos.'); return; }
    setLoading(true);
    try {
      const res = await fetch(API_LOGIN, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ documento: form.documento, password: form.password }),
      }).then(r => r.json());
      if (res.success) { saveSession(res.cliente); onLoginExitoso(res.cliente); }
      else setError(res.message);
    } catch { setError('No se pudo conectar con el servidor.'); }
    finally { setLoading(false); }
  };
  return (
    <div className="tp-overlay" onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="tp-modal-exito tp-modal-login">
        <button className="tp-modal-close" onClick={onClose}>x</button>
        <div className="tp-login-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="22" fill="#1E12A4" />
            <circle cx="24" cy="18" r="6" fill="white" />
            <path d="M10 38c0-7.7 6.3-14 14-14s14 6.3 14 14" stroke="white" strokeWidth="2.5" fill="none" strokeLinecap="round"/>
          </svg>
        </div>
        <h3>Inicia sesion para continuar</h3>
        <p>Necesitas una cuenta para realizar pedidos en Ferreinver.</p>
        {error && <p className="tp-login-error">{error}</p>}
        <div className="tp-login-fields">
          <div className="tp-login-field">
            <label>Documento</label>
            <input type="number" name="documento" placeholder="Tu numero de documento" value={form.documento} onChange={handle} onKeyDown={e => e.key === 'Enter' && submit()} />
          </div>
          <div className="tp-login-field">
            <label>Contrasena</label>
            <input type="password" name="password" placeholder="Tu contrasena" value={form.password} onChange={handle} onKeyDown={e => e.key === 'Enter' && submit()} />
          </div>
        </div>
        <button className="tp-btn-agregar" onClick={submit} disabled={loading}>{loading ? 'Verificando...' : 'Iniciar sesion'}</button>
        <button className="tp-btn-seguir" onClick={onClose}>Cancelar</button>
      </div>
    </div>
  );
}

function ModalCheckout({ items, cliente, onCerrar, onPedidoConfirmado }) {
  const [medioPago, setMedioPago] = useState('Efectivo');
  const [loading, setLoading]    = useState(false);
  const [error, setError]        = useState('');
  const total = items.reduce((s, it) => s + Number(it.precio) * it.cantidad, 0);
  const confirmar = async () => {
    setError('');
    setLoading(true);
    try {
      const res = await fetch(API_PEDIDO_COMPLETO, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cliente: cliente.documento, medio_pago: medioPago, items: items.map(it => ({ id_producto: it.id_producto, nombre: it.nombre, cantidad: it.cantidad })) }),
      }).then(r => r.json());
      if (res.success) onPedidoConfirmado(res.id_pedido);
      else setError(res.message);
    } catch { setError('No se pudo conectar con el servidor.'); }
    finally { setLoading(false); }
  };
  return (
    <div className="tp-overlay" onClick={e => e.target === e.currentTarget && onCerrar()}>
      <div className="tp-modal-checkout">
        <button className="tp-modal-close" onClick={onCerrar}>x</button>
        <h3 className="tp-checkout-titulo">Resumen del pedido</h3>
        <div className="tp-checkout-cliente">
          <span className="tp-checkout-label">Cliente</span>
          <span className="tp-checkout-val">{cliente.nombre}</span>
          <span className="tp-checkout-doc">Doc. {cliente.documento}</span>
        </div>
        <div className="tp-checkout-items">
          {items.map(it => (
            <div key={it.id_producto} className="tp-checkout-item">
              <div className="tp-checkout-item-img">{it.imagen ? <img src={IMG_BASE + it.imagen} alt={it.nombre} /> : <div className="tp-checkout-item-img-empty" />}</div>
              <div className="tp-checkout-item-info">
                <p className="tp-checkout-item-nombre">{it.nombre}</p>
                <p className="tp-checkout-item-sub">{it.cantidad} x ${Number(it.precio).toLocaleString('es-CO')}</p>
              </div>
              <p className="tp-checkout-item-total">${(Number(it.precio) * it.cantidad).toLocaleString('es-CO')}</p>
            </div>
          ))}
        </div>
        <div className="tp-checkout-total-row"><span>Total</span><span className="tp-checkout-total">${total.toLocaleString('es-CO')}</span></div>
        <div className="tp-checkout-medio">
          <label className="tp-checkout-label">Medio de pago</label>
          <div className="tp-medio-grid">
            {MEDIOS_PAGO.map(m => <button key={m} className={`tp-medio-btn ${medioPago === m ? 'active' : ''}`} onClick={() => setMedioPago(m)}>{m}</button>)}
          </div>
        </div>
        {error && <p className="tp-login-error">{error}</p>}
        <button className="tp-btn-agregar" onClick={confirmar} disabled={loading}>{loading ? 'Procesando...' : 'Confirmar pedido'}</button>
        <button className="tp-btn-seguir" onClick={onCerrar}>Volver al carrito</button>
      </div>
    </div>
  );
}

function ModalPedidoExitoso({ idPedido, onCerrar }) {
  return (
    <div className="tp-overlay">
      <div className="tp-modal-exito">
        <div className="tp-exito-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="22" fill="#22bb48" />
            <polyline points="13,25 21,33 36,16" stroke="white" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
        <h3>Pedido registrado!</h3>
        <p>Tu pedido <strong style={{color:'#22bb48'}}>#{idPedido}</strong> fue creado exitosamente.<br />Ferreinver se pondra en contacto contigo pronto.</p>
        <button className="tp-btn-agregar" onClick={onCerrar}>Seguir comprando</button>
      </div>
    </div>
  );
}

function ModalProducto({ producto, onClose, onAgregar }) {
  const [cantidad, setCantidad] = useState(1);
  const precio = Number(producto.precio);
  const precioOriginal = Math.round(precio * 1.3);
  return (
    <div className="tp-overlay" onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="tp-modal-detalle">
        <button className="tp-modal-close" onClick={onClose}>x</button>
        <div className="tp-modal-img-wrap">
          {producto.imagen ? <img src={IMG_BASE + producto.imagen} alt={producto.nombre} className="tp-modal-img-main" /> : <div className="tp-modal-img-placeholder" />}
          <div className="tp-modal-thumbs">
            {[0,1,2,3].map(i => (
              <div key={i} className={`tp-thumb ${i === 0 ? 'active' : ''}`}>
                {producto.imagen && i === 0 ? <img src={IMG_BASE + producto.imagen} alt="" /> : <div className="tp-thumb-empty" />}
              </div>
            ))}
          </div>
        </div>
        <div className="tp-modal-info">
          <h2 className="tp-modal-nombre">{producto.nombre}</h2>
          <div className="tp-modal-precios">
            <span className="tp-precio-actual">${precio.toLocaleString('es-CO')}</span>
            <span className="tp-precio-original">${precioOriginal.toLocaleString('es-CO')}</span>
          </div>
          <p className="tp-modal-desc">{producto.descripcion}</p>
          <p className="tp-cantidad-label">Cantidad</p>
          <div className="tp-cantidad-ctrl">
            <button onClick={() => setCantidad(c => Math.max(1, c - 1))}>-</button>
            <span>{cantidad}</span>
            <button onClick={() => setCantidad(c => c + 1)}>+</button>
          </div>
          <button className="tp-btn-agregar" onClick={() => onAgregar(producto, cantidad)}>Agregar al carrito</button>
          <div className="tp-badges">
            <span className="tp-badge"><span className="tp-dot tp-dot-blue" />Se recoge en la sede de Ferreinver.</span>
            <span className="tp-badge"><span className="tp-dot tp-dot-green" />Maxima calidad asegurada por parte del proveedor.</span>
          </div>
        </div>
      </div>
    </div>
  );
}

function ModalAgregado({ onIrCarrito, onSeguir }) {
  return (
    <div className="tp-overlay">
      <div className="tp-modal-exito">
        <button className="tp-modal-close" onClick={onSeguir}>x</button>
        <div className="tp-exito-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="22" fill="#22bb48" />
            <polyline points="13,25 21,33 36,16" stroke="white" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
        <h3>Producto anadido correctamente</h3>
        <p>Ingresa a tu carrito para completar el pedido y proceder con el envio!</p>
        <button className="tp-btn-agregar" onClick={onIrCarrito}>Ir al carrito</button>
        <button className="tp-btn-seguir" onClick={onSeguir}>Seguir comprando</button>
      </div>
    </div>
  );
}

function Carrito({ items, sesion, onCambiarCantidad, onCerrar, onFinalizarPedido, onCerrarSesion }) {
  const total = items.reduce((s, it) => s + Number(it.precio) * it.cantidad, 0);
  return (
    <div className="tp-overlay" onClick={e => e.target === e.currentTarget && onCerrar()}>
      <div className="tp-carrito">
        <button className="tp-modal-close" onClick={onCerrar}>x</button>
        <h2 className="tp-carrito-titulo">Carrito de compras</h2>
        {sesion && (
          <div className="tp-carrito-sesion">
            <span>Sesion activa: {sesion.nombre}</span>
            <button className="tp-btn-cerrar-sesion" onClick={onCerrarSesion}>Cerrar sesion</button>
          </div>
        )}
        <div className="tp-carrito-body">
          <div className="tp-carrito-items">
            {items.length === 0
              ? <p className="tp-carrito-vacio">Tu carrito esta vacio.</p>
              : items.map(it => (
                <div key={it.id_producto} className="tp-carrito-item">
                  <div className="tp-carrito-item-img">{it.imagen ? <img src={IMG_BASE + it.imagen} alt={it.nombre} /> : <div className="tp-carrito-item-img-empty" />}</div>
                  <div className="tp-carrito-item-info">
                    <p className="tp-carrito-item-nombre">{it.nombre}</p>
                    <p className="tp-carrito-item-cat">${(Number(it.precio) * it.cantidad).toLocaleString('es-CO')}</p>
                    <div className="tp-cantidad-ctrl tp-cantidad-ctrl--sm">
                      <button onClick={() => onCambiarCantidad(it.id_producto, it.cantidad - 1)}>-</button>
                      <span>{it.cantidad}</span>
                      <button onClick={() => onCambiarCantidad(it.id_producto, it.cantidad + 1)}>+</button>
                    </div>
                  </div>
                </div>
              ))
            }
          </div>
          <div className="tp-carrito-resumen">
            <div className="tp-resumen-box">
              <h4>Resumen del pedido</h4>
              <div className="tp-resumen-row"><span>Subtotal</span><span>${total.toLocaleString('es-CO')}</span></div>
              <p className="tp-resumen-gracias">Muchas gracias por confiar en Ferreinver!</p>
              <hr />
              <div className="tp-resumen-row tp-resumen-total"><span>Total</span><span>${total.toLocaleString('es-CO')}</span></div>
              <button className="tp-btn-agregar" onClick={onFinalizarPedido} disabled={items.length === 0}>Finalizar pedido</button>
              <button className="tp-btn-seguir" onClick={onCerrar}>Seguir comprando</button>
            </div>
            <button className="tp-btn-historial">consultar historial de pedidos realizados</button>
          </div>
        </div>
      </div>
    </div>
  );
}

function TarjetaProducto({ producto, onClick }) {
  return (
    <div className="tp-tarjeta" onClick={() => onClick(producto)}>
      <div className="tp-tarjeta-img">{producto.imagen ? <img src={IMG_BASE + producto.imagen} alt={producto.nombre} /> : <div className="tp-tarjeta-img-empty" />}</div>
      <div className="tp-tarjeta-body">
        <p className="tp-tarjeta-nombre">{producto.nombre}</p>
        <p className="tp-tarjeta-precio">{Number(producto.precio).toLocaleString('es-CO')}</p>
        <button className="tp-tarjeta-btn" onClick={e => { e.stopPropagation(); onClick(producto); }}>Ver producto</button>
      </div>
    </div>
  );
}

export const TiendaProductos = () => {
  const [productos, setProductos] = useState([]);
  const [loading, setLoading]     = useState(true);
  const [error, setError]         = useState(null);
  const [precioMin, setPrecioMin] = useState('');
  const [precioMax, setPrecioMax] = useState('');
  const [filtroAbierto, setFiltroAbierto] = useState(false);
  const [carrito, setCarrito]     = useState([]);
  const [sesion, setSesion]       = useState(getSession);
  const [modal, setModal]         = useState(null);
  const [productoActivo, setProductoActivo] = useState(null);
  const [idPedidoExitoso, setIdPedidoExitoso] = useState(null);

  useEffect(() => {
    fetch(`${API_PRODUCTOS}/apiProductos.php`)
      .then(r => r.json())
      .then(res => {
        if (res.success) setProductos(res.data.filter(p => p.estado_producto === 'activo'));
        else setError('No se pudieron cargar los productos.');
      })
      .catch(() => setError('Error de conexion con el servidor.'))
      .finally(() => setLoading(false));
  }, []);

  const productosFiltrados = productos.filter(p => {
    const precio = Number(p.precio);
    if (precioMin !== '' && precio < Number(precioMin)) return false;
    if (precioMax !== '' && precio > Number(precioMax)) return false;
    return true;
  });

  const agregarAlCarrito = useCallback((producto, cantidad) => {
    setCarrito(prev => {
      const existe = prev.find(it => it.id_producto === producto.id_producto);
      if (existe) return prev.map(it => it.id_producto === producto.id_producto ? { ...it, cantidad: it.cantidad + cantidad } : it);
      return [...prev, { ...producto, cantidad }];
    });
    setProductoActivo(null);
    setModal('agregado');
  }, []);

  const cambiarCantidad = useCallback((id, n) => {
    if (n <= 0) setCarrito(prev => prev.filter(it => it.id_producto !== id));
    else setCarrito(prev => prev.map(it => it.id_producto === id ? { ...it, cantidad: n } : it));
  }, []);

  const handleFinalizarPedido = () => {
    if (!sesion) setModal('login');
    else setModal('checkout');
  };

  const handleLoginExitoso = (cliente) => { setSesion(cliente); setModal('checkout'); };
  const handlePedidoConfirmado = (idPedido) => { setIdPedidoExitoso(idPedido); setCarrito([]); setModal('exitoso'); };
  const cerrarSesion = () => { clearSession(); setSesion(null); };

  const totalItems = carrito.reduce((s, it) => s + it.cantidad, 0);

  return (
    <section className="tp-seccion">
      <div className="tp-header">
        <div className="tp-header-inner">
          <h1 className="tp-titulo">Productos</h1>
          <div className="tp-header-actions">
            {sesion && (
              <span className="tp-sesion-chip">
                {sesion.nombre}
                <button onClick={cerrarSesion} className="tp-sesion-chip-x">x</button>
              </span>
            )}
            <button className="tp-btn-filtro" onClick={() => setFiltroAbierto(f => !f)}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
              Filtrar
            </button>
            <button className="tp-btn-carrito" onClick={() => setModal('carrito')}>
              Carrito
              {totalItems > 0 && <span className="tp-carrito-badge">{totalItems}</span>}
            </button>
          </div>
        </div>
        {filtroAbierto && (
          <div className="tp-filtro-panel">
            <p className="tp-filtro-label">Rango de precio (COP)</p>
            <div className="tp-filtro-row">
              <div className="tp-filtro-input-wrap">
                <label>Minimo</label>
                <input type="number" placeholder="Ej: 5000" value={precioMin} onChange={e => setPrecioMin(e.target.value)} />
              </div>
              <span className="tp-filtro-sep">a</span>
              <div className="tp-filtro-input-wrap">
                <label>Maximo</label>
                <input type="number" placeholder="Ej: 200000" value={precioMax} onChange={e => setPrecioMax(e.target.value)} />
              </div>
              <button className="tp-btn-limpiar" onClick={() => { setPrecioMin(''); setPrecioMax(''); }}>Limpiar</button>
            </div>
          </div>
        )}
      </div>

      {loading && <p className="tp-estado">Cargando productos...</p>}
      {error   && <p className="tp-estado tp-estado--error">{error}</p>}
      {!loading && !error && (
        <>
          <p className="tp-conteo">{productosFiltrados.length} producto{productosFiltrados.length !== 1 ? 's' : ''} {(precioMin || precioMax) ? 'encontrados' : 'disponibles'}</p>
          {productosFiltrados.length === 0
            ? <p className="tp-estado">No hay productos con ese rango de precio.</p>
            : <div className="tp-grid">
                {productosFiltrados.map(p => (
                  <TarjetaProducto key={p.id_producto} producto={p} onClick={prod => { setProductoActivo(prod); setModal('producto'); }} />
                ))}
              </div>
          }
        </>
      )}

      {modal === 'producto' && productoActivo && <ModalProducto producto={productoActivo} onClose={() => setModal(null)} onAgregar={agregarAlCarrito} />}
      {modal === 'agregado' && <ModalAgregado onIrCarrito={() => setModal('carrito')} onSeguir={() => setModal(null)} />}
      {modal === 'carrito' && <Carrito items={carrito} sesion={sesion} onCambiarCantidad={cambiarCantidad} onCerrar={() => setModal(null)} onFinalizarPedido={handleFinalizarPedido} onCerrarSesion={cerrarSesion} />}
      {modal === 'login' && <ModalLogin onClose={() => setModal('carrito')} onLoginExitoso={handleLoginExitoso} />}
      {modal === 'checkout' && <ModalCheckout items={carrito} cliente={sesion} onCerrar={() => setModal('carrito')} onPedidoConfirmado={handlePedidoConfirmado} />}
      {modal === 'exitoso' && <ModalPedidoExitoso idPedido={idPedidoExitoso} onCerrar={() => setModal(null)} />}
    </section>
  );
};