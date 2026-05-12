import React, { useState, useEffect, useRef } from 'react'
import ReactDOM from 'react-dom'
import './styles/Nav.css'
import { Link, useLocation } from 'react-router-dom'
import { PerfilMenu } from './Components/Perfilmenu'
import { MiPerfil } from './Components/MiPerfil'
import { MisPedidos } from './Components/MisPedidos'
import { MisCotizaciones } from './Components/MisCotizaciones'
import { Login } from './auth/Login'

export const Nav = () => {
  const [mostrarPerfil, setMostrarPerfil] = useState(false)
  const [mostrarLogin, setMostrarLogin] = useState(false)
  const [mostrarPedidos, setMostrarPedidos] = useState(false)
  const [mostrarCotizaciones, setMostrarCotizaciones] = useState(false)
  const [menuAbierto, setMenuAbierto] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const location = useLocation()
  const navRef = useRef(null)

  const usuarioStr = sessionStorage.getItem('usuario')
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  useEffect(() => {
    if (location.state?.abrirLogin) {
      setTimeout(() => setMostrarLogin(true), 0)
      window.history.replaceState({}, '')
    }
  }, [location.state?.abrirLogin])

  useEffect(() => {
    setMenuAbierto(false)
  }, [location.pathname])

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 10)
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  useEffect(() => {
    document.body.style.overflow = menuAbierto ? 'hidden' : ''
    return () => { document.body.style.overflow = '' }
  }, [menuAbierto])

  useEffect(() => {
    const handleKey = (e) => { if (e.key === 'Escape') setMenuAbierto(false) }
    window.addEventListener('keydown', handleKey)
    return () => window.removeEventListener('keydown', handleKey)
  }, [])

  const toggleMenu = () => setMenuAbierto(prev => !prev)
  const cerrarMenu = () => setMenuAbierto(false)

  return (
    <>
      <div
        className={`nav-overlay ${menuAbierto ? 'active' : ''}`}
        onClick={cerrarMenu}
        aria-hidden="true"
      />

      <nav ref={navRef} className={scrolled ? 'scrolled' : ''}>

        <button
          className={`hamburger ${menuAbierto ? 'open' : ''}`}
          onClick={toggleMenu}
          aria-label={menuAbierto ? 'Cerrar menú' : 'Abrir menú'}
          aria-expanded={menuAbierto}
          aria-controls="nav-links"
        >
          <span />
          <span />
          <span />
        </button>

        <img className="logo" src="/img/logo.webp" alt="logo" />

        <ul id="nav-links" className={`nav-links ${menuAbierto ? 'open' : ''}`}>
          <li className="links">
            <Link className="inicio" to="/inicio" onClick={cerrarMenu}>Inicio</Link>
          </li>
          <li className="links">
            <Link className="productos" to="/tienda-productos" onClick={cerrarMenu}>Productos</Link>
          </li>
          <li className="links">
            <Link className="contactanos" to="/contactanos" onClick={cerrarMenu}>Contáctanos</Link>
          </li>
          <li className="links">
            <Link className="quienes-somos" to="/quienes-somos" onClick={cerrarMenu}>¿Quiénes somos?</Link>
          </li>

          {!usuario && (
            <>
              <li className="links">
                <Link className="registro" to="/register" onClick={cerrarMenu}>Registrarte</Link>
              </li>
              <li className="links">
                <button
                  className="iniciar-sesion"
                  onClick={() => { setMostrarLogin(true); cerrarMenu() }}
                >
                  Iniciar sesión
                </button>
              </li>
            </>
          )}

          {usuario && (
            <li className="links bienvenida">
              Bienvenido, {usuario.nombre}
            </li>
          )}

          {usuario?.tipo_usuario === 'admin' && (
            <li className="links">
              <Link className="admin-panel" to="/admin" onClick={cerrarMenu}>Panel Admin</Link>
            </li>
          )}
        </ul>

        <div className="nav-perfil-wrapper">
          <PerfilMenu
            onAbrirPerfil={() => setMostrarPerfil(true)}
            onAbrirLogin={() => setMostrarLogin(true)}
            onAbrirPedidos={() => setMostrarPedidos(true)}
            onAbrirCotizaciones={() => setMostrarCotizaciones(true)}
          />
        </div>

      </nav>

      {ReactDOM.createPortal(
        <>
          {mostrarPerfil       && <MiPerfil        onCerrar={() => setMostrarPerfil(false)} />}
          {mostrarLogin        && <Login           onCerrar={() => setMostrarLogin(false)} />}
          {mostrarPedidos      && <MisPedidos      onCerrar={() => setMostrarPedidos(false)} />}
          {mostrarCotizaciones && <MisCotizaciones onCerrar={() => setMostrarCotizaciones(false)} />}
        </>,
        document.body
      )}
    </>
  )
}