import React, { useState, useEffect } from 'react'
import '../styles/MiPerfil.css'

const API_URL = 'http://127.0.0.1:8000/api';

export const MiPerfil = ({ onCerrar }) => {

  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  const [nombre, setNombre] = useState(usuario?.nombre || '')
  const [editando, setEditando] = useState(false)
  const [nombreTemporal, setNombreTemporal] = useState(usuario?.nombre || '')
  const [guardado, setGuardado] = useState(false)
  const [error, setError] = useState('')
  const [cargando, setCargando] = useState(false)

  // Cerrar con ESC
  useEffect(() => {
    const handleKey = (e) => { if (e.key === 'Escape') onCerrar() }
    document.addEventListener('keydown', handleKey)
    return () => document.removeEventListener('keydown', handleKey)
  }, [])

  // Bloquear scroll del body mientras el modal está abierto
  useEffect(() => {
    document.body.style.overflow = 'hidden'
    return () => { document.body.style.overflow = '' }
  }, [])

  if (!usuario) return null

  const obtenerIniciales = (nombre) => {
    if (!nombre) return '?'
    return nombre.split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2)
  }

  const esActivo = usuario.estado_inicio_sesion === 'activo'

  const handleEditar = () => {
    setNombreTemporal(nombre)
    setEditando(true)
    setError('')
    setGuardado(false)
  }

  const handleCancelar = () => {
    setEditando(false)
    setError('')
  }

  const handleGuardar = async () => {
    const trimmed = nombreTemporal.trim()
    if (!trimmed) { setError('El nombre no puede estar vacío.'); return }
    if (trimmed.length < 2) { setError('El nombre debe tener al menos 2 caracteres.'); return }

    setCargando(true)
    setError('')

    try {
      const res = await fetch(`${API_URL}/clientes/${usuario.documento}/nombre`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre: trimmed }),
      })
      const data = await res.json()
      if (!data.success) throw new Error(data.message || 'Error desconocido')

      const usuarioActualizado = { ...usuario, nombre: trimmed }
      sessionStorage.setItem('usuario', JSON.stringify(usuarioActualizado))

      setNombre(trimmed)
      setEditando(false)
      setGuardado(true)
      setTimeout(() => setGuardado(false), 3000)
    } catch (err) {
      setError(`No se pudo guardar: ${err.message}`)
    } finally {
      setCargando(false)
    }
  }

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') handleGuardar()
    if (e.key === 'Escape') handleCancelar()
  }

  return (
    // Overlay — clic fuera cierra el modal
    <div className="miperfil-overlay" onClick={onCerrar}>

      {/* Card — detener propagación para no cerrar al hacer clic dentro */}
      <div className="miperfil-card" onClick={(e) => e.stopPropagation()}>

        {/* Cabecera */}
        <div className="miperfil-header">
          <button className="miperfil-volver" onClick={onCerrar}>
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M18 6L6 18M6 6l12 12"
                stroke="currentColor" strokeWidth="2"
                strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            Cerrar
          </button>

          <div className="miperfil-avatar">{obtenerIniciales(nombre)}</div>
          <h1 className="miperfil-titulo">Mi perfil</h1>
          <span className={`miperfil-estado ${esActivo ? 'activo' : 'inactivo'}`}>
            {esActivo ? '● Cuenta activa' : '● Cuenta inactiva'}
          </span>
        </div>

        {/* Body */}
        <div className="miperfil-body">

          {guardado && (
            <div className="miperfil-toast miperfil-toast--ok">
              ✓ Nombre actualizado correctamente
            </div>
          )}

          {/* Nombre */}
          <div className="miperfil-campo">
            <label className="miperfil-label">
              <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.8" />
                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
              </svg>
              Nombre completo
            </label>

            {editando ? (
              <div className="miperfil-editar-grupo">
                <input
                  className={`miperfil-input ${error ? 'error' : ''}`}
                  type="text"
                  value={nombreTemporal}
                  onChange={(e) => setNombreTemporal(e.target.value)}
                  onKeyDown={handleKeyDown}
                  autoFocus
                  maxLength={30}
                  placeholder="Tu nombre completo"
                  disabled={cargando}
                />
                {error && <p className="miperfil-error">{error}</p>}
                <div className="miperfil-acciones">
                  <button className="miperfil-btn-guardar" onClick={handleGuardar} disabled={cargando}>
                    {cargando ? <span className="miperfil-spinner" /> : 'Guardar'}
                  </button>
                  <button className="miperfil-btn-cancelar" onClick={handleCancelar} disabled={cargando}>
                    Cancelar
                  </button>
                </div>
              </div>
            ) : (
              <div className="miperfil-valor-grupo">
                <span className="miperfil-valor">{nombre}</span>
                <button className="miperfil-btn-editar" onClick={handleEditar}>
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                      stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                      stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                  Editar
                </button>
              </div>
            )}
          </div>

          {/* Correo */}
          <div className="miperfil-campo">
            <label className="miperfil-label">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
                  stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <polyline points="22,6 12,13 2,6" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
              </svg>
              Correo electrónico
            </label>
            <div className="miperfil-valor-grupo">
              <span className="miperfil-valor">{usuario.correo}</span>
              <span className="miperfil-readonly-badge">No editable</span>
            </div>
          </div>

          {/* Documento */}
          {usuario.documento && (
            <div className="miperfil-campo">
              <label className="miperfil-label">
                <svg viewBox="0 0 24 24" fill="none">
                  <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" strokeWidth="1.8" />
                  <path d="M8 10h8M8 14h5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                </svg>
                Número de documento
              </label>
              <div className="miperfil-valor-grupo">
                <span className="miperfil-valor">{usuario.documento}</span>
                <span className="miperfil-readonly-badge">No editable</span>
              </div>
            </div>
          )}

        </div>
      </div>
    </div>
  )
}