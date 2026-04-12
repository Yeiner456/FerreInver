import React, { useState } from 'react'
import './styles/Nav.css'
import { Link } from 'react-router-dom'
import { PerfilMenu } from './Components/PerfilMenu'
import { MiPerfil } from './Components/Miperfil'

export const Nav = () => {
  const [mostrarPerfil, setMostrarPerfil] = useState(false)

  const usuarioStr = sessionStorage.getItem("usuario")
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  return (
    <>
      <nav>
        {/* PerfilMenu recibe función para abrir el modal */}
        <PerfilMenu onAbrirPerfil={() => setMostrarPerfil(true)} />

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
                <Link className="iniciar-sesion" to="/login">Iniciar sesión</Link>
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

      {/* Modal de perfil — se renderiza encima de todo */}
      {mostrarPerfil && (
        <MiPerfil onCerrar={() => setMostrarPerfil(false)} />
      )}
    </>
  )
}