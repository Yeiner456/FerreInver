import React, { useEffect, useState, useRef } from 'react'
import '../styles/MisRegistros.css'

const ESTADOS_COLOR = {
  'pendiente':           { bg: '#fff8e1', color: '#f59e0b' },
  'listo para recibir':  { bg: '#e8f5e9', color: '#22BB48' },
  'recibido':            { bg: '#e3f2fd', color: '#002FB1' },
  'cancelado':           { bg: '#fce4ec', color: '#e53e3e' },
}

// ─── Helper: garantiza que productos sea siempre un array ─────────────────────
const parsearProductos = (productos) => {
  if (!productos) return []
  if (Array.isArray(productos)) return productos
  if (typeof productos === 'string') {
    try { return JSON.parse(productos) } catch { return [] }
  }
  return []
}

// ─── Ticket oculto que se captura para el PDF ─────────────────────────────────
function TicketPedido({ pedido, usuario }) {
  const est   = ESTADOS_COLOR[pedido.estado_pedido] || { bg: '#f5f5f5', color: '#666' }
  const fecha = new Date(pedido.fecha_hora).toLocaleDateString('es-CO', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
  const hora = new Date(pedido.fecha_hora).toLocaleTimeString('es-CO', {
    hour: '2-digit', minute: '2-digit',
  })

  const productos = parsearProductos(pedido.productos)

  return (
    <div className="ticket-wrapper">
      <div className="ticket-header">
        <div className="ticket-header-left">
          <p className="ticket-empresa">FERREINVER SAS</p>
          <h2 className="ticket-titulo">Pedido #{pedido.id_pedido}</h2>
          <p className="ticket-fecha">{fecha} · {hora}</p>
        </div>
        <span className="ticket-estado" style={{ background: est.bg, color: est.color }}>
          {pedido.estado_pedido}
        </span>
      </div>

      <div className="ticket-seccion ticket-seccion--gris">
        <p className="ticket-label">Cliente</p>
        <p className="ticket-nombre">{usuario?.nombre}</p>
        <p className="ticket-doc">Doc: {usuario?.documento}</p>
      </div>

      <div className="ticket-seccion">
        <p className="ticket-label">Detalle del pedido</p>
        <div className="ticket-pedido-meta">
          <div className="ticket-dim-card">
            <p className="ticket-dim-label">Medio de pago</p>
            <p className="ticket-dim-value">{pedido.medio_pago}</p>
          </div>
          <div className="ticket-dim-card">
            <p className="ticket-dim-label">Estado</p>
            <p className="ticket-dim-value" style={{ color: est.color }}>{pedido.estado_pedido}</p>
          </div>
        </div>
        {productos.length > 0 && (
          <div className="ticket-productos-lista">
            <p className="ticket-label" style={{ marginTop: '14px' }}>Productos</p>
            {productos.map((prod, i) => (
              <div className="ticket-producto-fila" key={i}>
                <span className="ticket-producto-nombre">{prod.nombre}</span>
                {prod.pivot?.cantidad && (
                  <span className="ticket-producto-cantidad">x{prod.pivot.cantidad}</span>
                )}
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="ticket-footer-strip">
        <p>El Carmen de Viboral, Antioquia · ferreinver.com.co</p>
      </div>
    </div>
  )
}

// ─── Hook: carga html2canvas + jsPDF desde CDN bajo demanda ──────────────────
function useDescargarPDF() {
  const descargar = async (nodo, nombreArchivo) => {
    if (!window.html2canvas) {
      await new Promise((res, rej) => {
        const s = document.createElement('script')
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js'
        s.onload = res
        s.onerror = rej
        document.head.appendChild(s)
      })
    }
    if (!window.jspdf) {
      await new Promise((res, rej) => {
        const s = document.createElement('script')
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'
        s.onload = res
        s.onerror = rej
        document.head.appendChild(s)
      })
    }
    const canvas  = await window.html2canvas(nodo, { scale: 2, backgroundColor: '#ffffff', useCORS: true })
    const imgData = canvas.toDataURL('image/png')
    const { jsPDF } = window.jspdf
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: [canvas.width / 2, canvas.height / 2] })
    pdf.addImage(imgData, 'PNG', 0, 0, canvas.width / 2, canvas.height / 2)
    pdf.save(`${nombreArchivo}.pdf`)
  }
  return { descargar }
}

// ─── Botón de descarga individual ────────────────────────────────────────────
function BotonDescargar({ pedido, usuario }) {
  const [descargando, setDescargando] = useState(false)
  const ticketRef = useRef(null)
  const { descargar } = useDescargarPDF()

  const handleDescargar = async () => {
    if (descargando) return
    setDescargando(true)
    try {
      await new Promise(r => setTimeout(r, 80))
      await descargar(ticketRef.current, `pedido-${pedido.id_pedido}`)
    } catch (e) {
      console.error('Error al generar PDF:', e)
      alert('No se pudo generar el PDF. Intenta de nuevo.')
    } finally {
      setDescargando(false)
    }
  }

  return (
    <>
      <div className="ticket-offscreen">
        <div ref={ticketRef}>
          <TicketPedido pedido={pedido} usuario={usuario} />
        </div>
      </div>

      <button
        className={`registros-btn-descargar${descargando ? ' registros-btn-descargar--cargando' : ''}`}
        onClick={handleDescargar}
        disabled={descargando}
        title="Descargar PDF de este pedido"
      >
        {descargando ? (
          <>
            <svg className="registros-btn-spinner" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2.5"
                strokeDasharray="31.4" strokeDashoffset="10" strokeLinecap="round" />
            </svg>
            Generando...
          </>
        ) : (
          <>
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M12 3v13M7 11l5 5 5-5" stroke="currentColor" strokeWidth="2"
                strokeLinecap="round" strokeLinejoin="round" />
              <path d="M3 19h18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
            Descargar PDF
          </>
        )}
      </button>
    </>
  )
}

// ─── Componente principal ─────────────────────────────────────────────────────
export const MisPedidos = ({ onCerrar }) => {
  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario    = usuarioStr ? JSON.parse(usuarioStr) : null

  const [pedidos, setPedidos]   = useState([])
  const [cargando, setCargando] = useState(true)
  const [error, setError]       = useState('')

  useEffect(() => {
    const fetchPedidos = async () => {
      try {
        const res  = await fetch(`http://127.0.0.1:8000/api/pedidos?documento=${usuario.documento}`)
        const data = await res.json()
        if (!data.success) throw new Error(data.message)

        // Normalizar productos en cada pedido al momento de recibirlos
        const pedidosNormalizados = data.data.map((p) => ({
          ...p,
          productos: parsearProductos(p.productos),
        }))
        setPedidos(pedidosNormalizados)
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
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
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
          {error    && <p className="registros-error">{error}</p>}

          {!cargando && !error && pedidos.length === 0 && (
            <div className="registros-vacio">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                  stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" strokeWidth="1.5" />
              </svg>
              <p>Aun no tienes pedidos</p>
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
                {p.productos.length > 0 && (
                  <p className="registros-item-productos">
                    {p.productos.map(prod => `${prod.nombre} x${prod.pivot?.cantidad ?? 1}`).join(', ')}
                  </p>
                )}
                <div className="registros-item-footer">
                  <BotonDescargar pedido={p} usuario={usuario} />
                </div>
              </div>
            )
          })}
        </div>

      </div>
    </div>
  )
}