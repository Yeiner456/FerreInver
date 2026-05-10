import React, { useEffect, useState, useRef } from 'react'
import '../styles/MisRegistros.css'

const ESTADOS_COLOR = {
  pendiente: { bg: '#fff8e1', color: '#f59e0b' },
  aprobada:  { bg: '#e8f5e9', color: '#22BB48' },
  rechazada: { bg: '#fce4ec', color: '#e53e3e' },
}

// ─── Ticket oculto que se captura para el PDF ─────────────────────────────────
function TicketCotizacion({ cotizacion, usuario, formatPrecio }) {
  const est   = ESTADOS_COLOR[cotizacion.estado] || { bg: '#f5f5f5', color: '#666' }
  const fecha = new Date(cotizacion.fecha).toLocaleDateString('es-CO', {
    day: '2-digit', month: 'long', year: 'numeric',
  })

  return (
    <div className="ticket-wrapper">
      <div className="ticket-header">
        <div className="ticket-header-left">
          <p className="ticket-empresa">FERREINVER SAS</p>
          <h2 className="ticket-titulo">Cotización #{cotizacion.id_cotizacion}</h2>
          <p className="ticket-fecha">Fecha: {fecha}</p>
        </div>
        <span className="ticket-estado" style={{ background: est.bg, color: est.color }}>
          {cotizacion.estado}
        </span>
      </div>

      <div className="ticket-seccion ticket-seccion--gris">
        <p className="ticket-label">Cliente</p>
        <p className="ticket-nombre">{usuario?.nombre}</p>
        <p className="ticket-doc">Doc: {usuario?.documento}</p>
      </div>

      <div className="ticket-seccion">
        <p className="ticket-label">Detalle del invernadero</p>
        <p className="ticket-invernadero-nombre">{cotizacion.invernadero_nombre}</p>
        <div className="ticket-dimensiones">
          {[
            { label: 'Largo', value: `${cotizacion.largo} m` },
            { label: 'Ancho', value: `${cotizacion.ancho} m` },
            { label: 'Area',  value: `${cotizacion.metros_cuadrados} m2` },
          ].map(({ label, value }) => (
            <div className="ticket-dim-card" key={label}>
              <p className="ticket-dim-label">{label}</p>
              <p className="ticket-dim-value">{value}</p>
            </div>
          ))}
        </div>
      </div>

      <div className="ticket-total-row">
        <p className="ticket-total-label">Total cotizado</p>
        <p className="ticket-total-value">{formatPrecio(cotizacion.total)}</p>
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
function BotonDescargar({ cotizacion, usuario, formatPrecio }) {
  const [descargando, setDescargando] = useState(false)
  const ticketRef = useRef(null)
  const { descargar } = useDescargarPDF()

  const handleDescargar = async () => {
    if (descargando) return
    setDescargando(true)
    try {
      await new Promise(r => setTimeout(r, 80))
      await descargar(ticketRef.current, `cotizacion-${cotizacion.id_cotizacion}`)
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
          <TicketCotizacion
            cotizacion={cotizacion}
            usuario={usuario}
            formatPrecio={formatPrecio}
          />
        </div>
      </div>

      <button
        className={`registros-btn-descargar${descargando ? ' registros-btn-descargar--cargando' : ''}`}
        onClick={handleDescargar}
        disabled={descargando}
        title="Descargar PDF de esta cotizacion"
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
export const MisCotizaciones = ({ onCerrar }) => {
  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario    = usuarioStr ? JSON.parse(usuarioStr) : null

  const [cotizaciones, setCotizaciones] = useState([])
  const [cargando, setCargando]         = useState(true)
  const [error, setError]               = useState('')

  useEffect(() => {
    const fetchCotizaciones = async () => {
      try {
        const res  = await fetch(`http://127.0.0.1:8000/api/cotizaciones?documento=${usuario.documento}`)
        const data = await res.json()
        if (!data.success) throw new Error(data.message)
        setCotizaciones(data.data)
      } catch {
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
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              <polyline points="14 2 14 8 20 8"
                stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
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
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                  stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                <polyline points="14 2 14 8 20 8" stroke="currentColor" strokeWidth="1.5" />
              </svg>
              <p>Aun no tienes cotizaciones</p>
            </div>
          )}

          {!cargando && cotizaciones.map((c) => {
            const est = ESTADOS_COLOR[c.estado] || { bg: '#f5f5f5', color: '#666' }
            return (
              <div className="registros-item" key={c.id_cotizacion}>
                <div className="registros-item-top">
                  <span className="registros-item-id">Cotizacion #{c.id_cotizacion}</span>
                  <span className="registros-item-badge" style={{ background: est.bg, color: est.color }}>
                    {c.estado}
                  </span>
                </div>
                <p className="registros-item-invernadero">{c.invernadero_nombre}</p>
                <div className="registros-item-detalle">
                  <span>📐 {c.largo}m x {c.ancho}m ({c.metros_cuadrados} m²)</span>
                  <span>📅 {new Date(c.fecha).toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })}</span>
                </div>
                <div className="registros-item-footer">
                  <div className="registros-item-total">
                    Total: <strong>{formatPrecio(c.total)}</strong>
                  </div>
                  <BotonDescargar
                    cotizacion={c}
                    usuario={usuario}
                    formatPrecio={formatPrecio}
                  />
                </div>
              </div>
            )
          })}
        </div>

      </div>
    </div>
  )
}