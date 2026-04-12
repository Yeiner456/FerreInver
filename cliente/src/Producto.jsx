import React, { useEffect, useState } from 'react'
import './styles/Producto.css'

export const Producto = () => {
  const [productos, setProductos] = useState([])
  const [loading, setLoading]   = useState(true)
  const [error, setError]       = useState(null)

  useEffect(() => {
    fetch('http://localhost/FerreInver/server/productos/api/apiProductos.php')
      .then(res => {
        if (!res.ok) throw new Error('Error al cargar productos')
        return res.json()
      })
      .then(data => {
        // Manejar distintos formatos de respuesta
        const lista = Array.isArray(data)
          ? data
          : Array.isArray(data.productos)
          ? data.productos
          : Array.isArray(data.data)
          ? data.data
          : []
        const activos = lista.filter(p => p.estado_producto === 'activo')
        setProductos(activos)
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
            <div className="tarjeta-producto" key={prod.id_producto}>

              <div className="tarjeta-img-wrap">
                {prod.imagen
                  ? <img
                      src={`http://localhost/FerreInver/${prod.imagen}`}
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
                  <button className="tarjeta-btn">Ver más</button>
                </div>
              </div>

            </div>
          ))}
        </div>
      )}

    </section>
  )
}