import React, { useEffect, useState } from 'react'
import './styles/Producto.css'

const IMG_BASE = 'http://localhost/FerreInver/'

function ModalProducto({ producto, onClose, formatPrecio }) {
  // Cerrar con Escape
  useEffect(() => {
    const handler = (e) => e.key === 'Escape' && onClose()
    window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [onClose])

  return (
    <div className="prod-overlay" onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="prod-modal">
        <button className="prod-modal-close" onClick={onClose}>✕</button>

        <div className="prod-modal-img-wrap">
          {producto.imagen
            ? <img src={IMG_BASE + producto.imagen} alt={producto.nombre} className="prod-modal-img" />
            : <div className="prod-modal-img-placeholder">
                <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-8.5-5.5l-2.51 3.01L7 14l-4 5h18l-5.5-7.5z"/></svg>
                <span>Sin imagen</span>
              </div>
          }
          <div className="prod-modal-badge">Disponible</div>
        </div>

        <div className="prod-modal-info">
          <h2 className="prod-modal-nombre">{producto.nombre}</h2>
          <p className="prod-modal-desc">{producto.descripcion}</p>
          <div className="prod-modal-precio-wrap">
            <span className="prod-modal-precio">{formatPrecio(producto.precio)}</span>
          </div>
          <button className="prod-modal-btn-cerrar" onClick={onClose}>Cerrar</button>
        </div>
      </div>
    </div>
  )
}

export const Producto = () => {
  const [productos, setProductos]       = useState([])
  const [loading, setLoading]           = useState(true)
  const [error, setError]               = useState(null)
  const [productoActivo, setProductoActivo] = useState(null)

  useEffect(() => {
    fetch('http://localhost/FerreInver/server/productos')
      .then(res => {
        if (!res.ok) throw new Error('Error al cargar productos')
        return res.json()
      })
      .then(data => {
        const lista = Array.isArray(data)
          ? data
          : Array.isArray(data.productos)
          ? data.productos
          : Array.isArray(data.data)
          ? data.data
          : []
        setProductos(lista.filter(p => p.estado_producto === 'activo'))
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  const formatPrecio = (precio) =>
    new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(precio)

  return (
    <section className="seccion-productos">

      <div className="producto-header">
        <span className="producto-pill">Catálogo</span>
        <h2 className="producto-title">Nuestros <span>Productos</span></h2>
        <p className="producto-sub">Materiales y soluciones de calidad para tu invernadero</p>
      </div>

      {loading && (
        <div className="productos-estado">
          <div className="spinner" />
          <p>Cargando productos...</p>
        </div>
      )}

      {error && (
        <div className="productos-estado error">
          <span>⚠️</span>
          <p>{error}</p>
        </div>
      )}

      {!loading && !error && productos.length === 0 && (
        <div className="productos-estado">
          <span>📦</span>
          <p>No hay productos disponibles por el momento.</p>
        </div>
      )}

      {!loading && !error && productos.length > 0 && (
        <div className="tarjetas">
          {productos.map(prod => (
            <div className="tarjeta-producto" key={prod.id_producto} onClick={() => setProductoActivo(prod)} style={{ cursor: 'pointer' }}>

              <div className="tarjeta-img-wrap">
                {prod.imagen
                  ? <img
                      src={`${IMG_BASE}${prod.imagen}`}
                      alt={prod.nombre}
                      className="tarjeta-img"
                    />
                  : <div className="tarjeta-img-placeholder">
                      <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-8.5-5.5l-2.51 3.01L7 14l-4 5h18l-5.5-7.5z"/></svg>
                      <span>Sin imagen</span>
                    </div>
                }
                <div className="tarjeta-badge">Disponible</div>
              </div>

              <div className="tarjeta-body">
                <h3 className="tarjeta-nombre">{prod.nombre}</h3>
                <p className="tarjeta-desc">{prod.descripcion}</p>
                <div className="tarjeta-footer">
                  <span className="tarjeta-precio">{formatPrecio(prod.precio)}</span>
                </div>
              </div>

            </div>
          ))}
        </div>
      )}

      {productoActivo && (
        <ModalProducto
          producto={productoActivo}
          formatPrecio={formatPrecio}
          onClose={() => setProductoActivo(null)}
        />
      )}

    </section>
  )
}