import React, { useEffect, useState } from 'react'
import '../styles/MisRegistros.css'

const ESTADOS_COLOR = {
  'pendiente':  { bg: '#fff8e1', color: '#f59e0b' },
  'aprobada':   { bg: '#e8f5e9', color: '#22BB48' },
  'rechazada':  { bg: '#fce4ec', color: '#e53e3e' },
}

export const MisCotizaciones = ({ onCerrar }) => {
  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  const [cotizaciones, setCotizaciones] = useState([])
  const [cargando, setCargando]         = useState(true)
  const [error, setError]               = useState('')

  useEffect(() => {
    const fetchCotizaciones = async () => {
      try {
        const res = await fetch(
          `http://localhost/FerreInver/server/cotizaciones?documento=${usuario.documento}`
        )
        const data = await res.json()
        if (!data.success) throw new Error(data.mensaje)
        setCotizaciones(data.data)
      } catch  {
        setError('No se pudieron cargar las cotizaciones.')
      } finally {
        setCargando(false)
      }
    }
    fetchCotizaciones()
  }, [])

  const formatPrecio = (val) =>
    new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val)

  return (
    <div className="registros-overlay" onClick={onCerrar}>
      <div className="registros-modal" onClick={(e) => e.stopPropagation()}>

        <div className="registros-header">
          <div className="registros-header-icono">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              <polyline points="14 2 14 8 20 8" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
              <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
            </svg>
          </div>
          <div>
            <h2 className="registros-titulo">Mis cotizaciones</h2>
            <p className="registros-subtitulo">{usuario?.nombre}</p>
          </div>
          <button className="registros-cerrar" onClick={onCerrar}>
            <svg viewBox="0 0 24 24" fill="none">
              <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
              <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </button>
        </div>

        <div className="registros-body">
          {cargando && <p className="registros-cargando">Cargando cotizaciones...</p>}
          {error    && <p className="registros-error">{error}</p>}

          {!cargando && !error && cotizaciones.length === 0 && (
            <div className="registros-vacio">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                <polyline points="14 2 14 8 20 8" stroke="currentColor" strokeWidth="1.5" />
              </svg>
              <p>Aún no tienes cotizaciones</p>
            </div>
          )}

          {!cargando && cotizaciones.map((c) => {
            const est = ESTADOS_COLOR[c.estado] || { bg: '#f5f5f5', color: '#666' }
            return (
              <div className="registros-item" key={c.id_cotizacion}>
                <div className="registros-item-top">
                  <span className="registros-item-id">Cotización #{c.id_cotizacion}</span>
                  <span className="registros-item-badge" style={{ background: est.bg, color: est.color }}>
                    {c.estado}
                  </span>
                </div>
                <p className="registros-item-invernadero">{c.invernadero_nombre}</p>
                <div className="registros-item-detalle">
                  <span>📐 {c.largo}m × {c.ancho}m ({c.metros_cuadrados} m²)</span>
                  <span>📅 {new Date(c.fecha).toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })}</span>
                </div>
                <div className="registros-item-total">
                  Total: <strong>{formatPrecio(c.total)}</strong>
                </div>
              </div>
            )
          })}
        </div>

      </div>
    </div>
  )
}