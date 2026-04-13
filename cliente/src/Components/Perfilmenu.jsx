import React, { useState, useRef, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import '../styles/Perfilmenu.css'

export const PerfilMenu = ({ onAbrirPerfil }) => {
  const [abierto, setAbierto] = useState(false)
  const menuRef = useRef(null)
  const navigate = useNavigate()

  // Obtener usuario de sessionStorage (campos de tabla clientes)
  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  // Cerrar al hacer clic fuera
  useEffect(() => {
    const handleClickFuera = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        setAbierto(false)
      }
    }
    document.addEventListener('mousedown', handleClickFuera)
    return () => document.removeEventListener('mousedown', handleClickFuera)
  }, [])

  const cerrarSesion = () => {
    sessionStorage.removeItem('usuario')
    setAbierto(false)
    navigate('/')
  }

  // Iniciales del nombre
  const obtenerIniciales = (nombre) => {
    if (!nombre) return '?'
    return nombre
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2)
  }

  const esActivo = usuario?.estado_inicio_sesion === 'activo'

  return (
    <div className="perfil-menu" ref={menuRef}>

      {/* Botón disparador */}
      <button
        className={`perfil-trigger ${abierto ? 'activo' : ''}`}
        onClick={() => setAbierto(!abierto)}
        aria-label="Menú de perfil"
      >
        {usuario ? (
          <span className="perfil-iniciales">
            {obtenerIniciales(usuario.nombre)}
          </span>
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

      {/* Dropdown */}
      {abierto && (
        <div className="perfil-dropdown">
          {usuario ? (
            <>
              <div className="perfil-header">
                <div className="perfil-avatar-grande">
                  {obtenerIniciales(usuario.nombre)}
                </div>
                <div className="perfil-info">
                  <p className="perfil-nombre">{usuario.nombre}</p>
                  {usuario.correo && (
                    <p className="perfil-correo">{usuario.correo}</p>
                  )}
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
                  <button
                    className="perfil-opcion"
                    onClick={() => { onAbrirPerfil(); setAbierto(false) }}
                  >
                    <svg viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.8" />
                      <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                    </svg>
                    Mi perfil
                  </button>
                </li>
                <li>
                  <button
                    className="perfil-opcion perfil-opcion--cerrar"
                    onClick={cerrarSesion}
                  >
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
              <button
                className="perfil-btn-login"
                onClick={() => { navigate('/login'); setAbierto(false) }}
              >
                Iniciar sesión
              </button>
              <button
                className="perfil-btn-registro"
                onClick={() => { navigate('/register'); setAbierto(false) }}
              >
                Crear cuenta
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}