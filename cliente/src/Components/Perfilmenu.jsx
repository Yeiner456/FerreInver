import React, { useState, useRef, useEffect } from 'react'
import ReactDOM from 'react-dom'
import { useNavigate } from 'react-router-dom'
import '../styles/Perfilmenu.css'

export const PerfilMenu = ({ onAbrirPerfil, onAbrirLogin, onAbrirPedidos, onAbrirCotizaciones }) => {
  const [abierto, setAbierto] = useState(false)
  const [dropdownPos, setDropdownPos] = useState({})
  const triggerRef = useRef(null)
  const dropdownRef = useRef(null)
  const navigate = useNavigate()

  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  // Calcular posición del dropdown relativa al botón trigger
  useEffect(() => {
    if (abierto && triggerRef.current) {
      const rect = triggerRef.current.getBoundingClientRect()
      const esMobil = window.innerWidth <= 480

      if (esMobil) {
        setDropdownPos({ mobile: true })
      } else {
        setDropdownPos({
          top: rect.bottom + 8,           // ← sin window.scrollY
          right: window.innerWidth - rect.right,
        })
      }
    }
  }, [abierto])

  // Cerrar al hacer click fuera (solo desktop)
  useEffect(() => {
    const handleClickFuera = (e) => {
      if (window.innerWidth <= 480) return
      const dentroTrigger = triggerRef.current?.contains(e.target)
      const dentroDropdown = dropdownRef.current?.contains(e.target)
      if (!dentroTrigger && !dentroDropdown) {
        setAbierto(false)
      }
    }
    document.addEventListener('mousedown', handleClickFuera)
    return () => document.removeEventListener('mousedown', handleClickFuera)
  }, [])

  // Bloquear scroll en móvil
  useEffect(() => {
    if (window.innerWidth <= 480) {
      document.body.style.overflow = abierto ? 'hidden' : ''
    }
    return () => { document.body.style.overflow = '' }
  }, [abierto])

  const cerrarSesion = () => {
    sessionStorage.removeItem('usuario')
    setAbierto(false)
    navigate('/')
  }

  const obtenerIniciales = (nombre) => {
    if (!nombre) return '?'
    return nombre.split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2)
  }

  const esActivo = usuario?.estado_inicio_sesion === 'activo'

  const dropdownStyle = dropdownPos.mobile
    ? {} // el CSS lo posiciona como bottom sheet en móvil
    : {
      position: 'fixed',
      top: dropdownPos.top,
      right: dropdownPos.right,
    }

  const contenidoDropdown = (
    <div className="perfil-dropdown" ref={dropdownRef} style={dropdownStyle} onMouseDown={(e) => e.stopPropagation()}>
      {usuario ? (
        <>
          <div className="perfil-header">
            <div className="perfil-avatar-grande">
              {obtenerIniciales(usuario.nombre)}
            </div>
            <div className="perfil-info">
              <p className="perfil-nombre">{usuario.nombre}</p>
              {usuario.correo && <p className="perfil-correo">{usuario.correo}</p>}
              <span className={`perfil-badge ${esActivo ? 'activo' : 'inactivo'}`}>
                {esActivo ? '● Activo' : '● Inactivo'}
              </span>
            </div>
          </div>

          {usuario.documento && (
            <div className="perfil-dato">
              <span className="perfil-dato-label">Documento</span>
              <span className="perfil-dato-valor">{usuario.documento}</span>
            </div>
          )}

          <hr className="perfil-divider" />

          <ul className="perfil-opciones">
            <li>
              <button className="perfil-opcion" onClick={() => { onAbrirPerfil?.(); setAbierto(false) }}>
                <svg viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.8" />
                  <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                </svg>
                Mi perfil
              </button>
            </li>

            <li>
              <button className="perfil-opcion" onClick={() => { onAbrirPedidos?.(); setAbierto(false) }}>
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                  <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                  <path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
                Mis pedidos
              </button>
            </li>

            <li>
              <button className="perfil-opcion" onClick={() => { onAbrirCotizaciones?.(); setAbierto(false) }}>
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                  <polyline points="14 2 14 8 20 8" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                  <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                  <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                  <polyline points="10 9 9 9 8 9" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                </svg>
                Mis cotizaciones
              </button>
            </li>

            <li>
              <button className="perfil-opcion perfil-opcion--cerrar" onClick={cerrarSesion}>
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                  <polyline points="16 17 21 12 16 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                  <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                </svg>
                Cerrar sesión
              </button>
            </li>
          </ul>
        </>
      ) : (
        <div className="perfil-no-sesion">
          <div className="perfil-icono-grande">
            <svg viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.8" />
              <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
            </svg>
          </div>
          <p className="perfil-no-sesion-texto">No has iniciado sesión</p>
          <p className="perfil-no-sesion-sub">Accede a tu cuenta para ver tu perfil</p>
          <button className="perfil-btn-login" onClick={() => { onAbrirLogin?.(); setAbierto(false) }}>
            Iniciar sesión
          </button>
          <button className="perfil-btn-registro" onClick={() => { navigate('/register'); setAbierto(false) }}>
            Crear cuenta
          </button>
        </div>
      )}
    </div>
  )

  return (
    <div className="perfil-menu">

      {/* Botón disparador */}
      <button
        ref={triggerRef}
        className={`perfil-trigger ${abierto ? 'activo' : ''}`}
        onClick={() => setAbierto(!abierto)}
        aria-label="Menú de perfil"
      >
        {usuario ? (
          <span className="perfil-iniciales">{obtenerIniciales(usuario.nombre)}</span>
        ) : (
          <svg className="perfil-icono-svg" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.8" />
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
          </svg>
        )}
        {usuario && (
          <span className={`perfil-estado-dot ${esActivo ? 'activo' : 'inactivo'}`} />
        )}
      </button>

      {/* Portal: overlay + dropdown en body para evitar cualquier clipping o z-index roto */}
      {abierto && ReactDOM.createPortal(
        <>
          <div
            className="perfil-overlay"
            onMouseDown={() => setAbierto(false)}
          />
          {contenidoDropdown}
        </>,
        document.body
      )}

    </div>
  )
}