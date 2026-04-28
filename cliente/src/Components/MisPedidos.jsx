import React, { useEffect, useState } from 'react'
import '../styles/MisRegistros.css'

const ESTADOS_COLOR = {
  'pendiente': { bg: '#fff8e1', color: '#f59e0b' },
  'listo para recibir': { bg: '#e8f5e9', color: '#22BB48' },
  'recibido': { bg: '#e3f2fd', color: '#002FB1' },
  'cancelado': { bg: '#fce4ec', color: '#e53e3e' },
}

export const MisPedidos = ({ onCerrar }) => {
  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  const [pedidos, setPedidos] = useState([])
  const [cargando, setCargando] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const fetchPedidos = async () => {
      try {
        const res = await fetch(
          `http://localhost/FerreInver/server/pedidos?documento=${usuario.documento}`
        )
        const data = await res.json()
        if (!data.success) throw new Error(data.mensaje)
        setPedidos(data.data)
      } catch {
        setError('No se pudieron cargar los pedidos.')
      } finally {
        setCargando(false)
      }
    }
    fetchPedidos()
  }, [])

  return (
    <div className="registros-overlay" onClick={onCerrar}>
      <div className="registros-modal" onClick={(e) => e.stopPropagation()}>

        <div className="registros-header">
          <div className="registros-header-icono">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" strokeWidth="1.8" />
              <path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
            </svg>
          </div>
          <div>
            <h2 className="registros-titulo">Mis pedidos</h2>
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
          {cargando && <p className="registros-cargando">Cargando pedidos...</p>}
          {error && <p className="registros-error">{error}</p>}

          {!cargando && !error && pedidos.length === 0 && (
            <div className="registros-vacio">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" strokeWidth="1.5" />
              </svg>
              <p>Aún no tienes pedidos</p>
            </div>
          )}

          {!cargando && pedidos.map((p) => {
            const est = ESTADOS_COLOR[p.estado_pedido] || { bg: '#f5f5f5', color: '#666' }
            return (
              <div className="registros-item" key={p.id_pedido}>
                <div className="registros-item-top">
                  <span className="registros-item-id">Pedido #{p.id_pedido}</span>
                  <span className="registros-item-badge" style={{ background: est.bg, color: est.color }}>
                    {p.estado_pedido}
                  </span>
                </div>
                <div className="registros-item-detalle">
                  <span>📅 {new Date(p.fecha_hora).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                  <span>💳 {p.medio_pago}</span>
                </div>
                {p.productos && (
                  <p className="registros-item-productos">{p.productos}</p>
                )}
              </div>
            )
          })}
        </div>

      </div>
    </div>
  )
}