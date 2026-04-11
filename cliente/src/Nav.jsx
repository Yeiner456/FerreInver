import React from 'react'
import './styles/Nav.css'
import { Link } from 'react-router-dom'
import { PerfilMenu } from './Components/PerfilMenu'

export const Nav = () => {

  // Obtener usuario para mostrar/ocultar links de auth
  const usuarioStr = sessionStorage.getItem("usuario")
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  return (
    <nav>
      {/* PerfilMenu reemplaza la imagen de perfil */}
      <PerfilMenu />

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

        {/* Los links de login/registro ahora solo aparecen si no hay usuario */}
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

        {/* El saludo y cerrar sesión ahora viven dentro de PerfilMenu */}
        {usuario && (
          <li className='links bienvenida'>
            Bienvenido, {usuario.nombre}
          </li>
        )}

      </ul>
    </nav>
  )
}