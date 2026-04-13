import React, { useState, useEffect } from 'react'
import './styles/Nav.css'
import { Link, useLocation } from 'react-router-dom'
import { PerfilMenu } from './Components/PerfilMenu'
import { MiPerfil } from './Components/Miperfil'
import { Login } from './auth/Login'

export const Nav = () => {
  const [mostrarPerfil, setMostrarPerfil] = useState(false)
  const [mostrarLogin, setMostrarLogin]   = useState(false)
  const location = useLocation()

  const usuarioStr = sessionStorage.getItem("usuario")
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

 useEffect(() => {
  if (location.state?.abrirLogin) {
    setTimeout(() => setMostrarLogin(true), 0)  // ✅ diferir un tick
    window.history.replaceState({}, '')
  }
}, [location.state])

  return (
    <>
      <nav>
        <PerfilMenu
          onAbrirPerfil={() => setMostrarPerfil(true)}
          onAbrirLogin={() => setMostrarLogin(true)}
        />

        <img className='logo' src="/img/logo.webp" alt="logo" />

        <ul className='nav-links'>
          <li className='links'>
            <Link className="inicio" to="/inicio">Inicio</Link>
          </li>
          <li className='links'>
            <Link className="productos" to="/tienda-productos">Productos</Link>
          </li>
          <li className='links'>
            <Link className="contactanos" to="/contactanos">Contáctanos</Link>
          </li>
          <li className='links'>
            <Link className="quienes-somos" to="/quienes-somos">¿Quiénes somos?</Link>
          </li>

          {!usuario && (
            <>
              <li className='links'>
                <Link className="registro" to="/register">Registrarte</Link>
              </li>
              <li className='links'>
                <button className="iniciar-sesion" onClick={() => setMostrarLogin(true)}>
                  Iniciar sesión
                </button>
              </li>
            </>
          )}

          {usuario && (
            <li className='links bienvenida'>
              Bienvenido, {usuario.nombre}
            </li>
          )}

          {usuario && usuario.tipo_usuario === "admin" && (
            <li className='links'>
              <Link className="admin-panel" to="/admin">Panel Admin</Link>
            </li>
          )}
        </ul>
      </nav>

      {mostrarPerfil && <MiPerfil onCerrar={() => setMostrarPerfil(false)} />}
      {mostrarLogin  && <Login    onCerrar={() => setMostrarLogin(false)} />}
    </>
  )
}