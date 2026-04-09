import React from 'react'
import './Styles/Nav.css'
import { Link, useNavigate } from 'react-router-dom'

export const Nav = () => {

  const navigate = useNavigate()

  //  DEFINIR USUARIO
  const usuarioStr = sessionStorage.getItem("usuario")
  const usuario = usuarioStr ? JSON.parse(usuarioStr) : null

  //  FUNCIÓN CERRAR SESIÓN
  const cerrarSesion = () => {
    sessionStorage.removeItem("usuario")
    navigate("/")
  }

  return (
    <nav>
      <img className='perfil' src="/assets/perfil.webp" alt="perfil" />
      <img className='logo' src="/assets/logo.webp" alt="logo" />

      <ul className='nav-links'>

        <li className='links'>
          <Link className="inicio" to="/inicio">Inicio</Link>
        </li>

        <li className='links'>
          <Link className="productos" to="/producto">Productos</Link>
        </li>

        <li className='links'>
          <Link className="contactanos" to="/contactanos">Contáctanos</Link>
        </li>

        <li className='links'>
          <Link className="quienes-somos" to="/quienes-somos">¿Quiénes somos?</Link>
        </li>

        {/*  SI NO HAY USUARIO */}
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

        {/*  SI HAY USUARIO */}
        {usuario && (
          <>
            <li className='links bienvenida'>
              Bienvenido, {usuario.nombre}
            </li>

            <li className='links'>
              <button className='cerrar-sesion' onClick={cerrarSesion}>
                Cerrar sesión
              </button>
            </li>
          </>
        )}

      </ul>
    </nav>
  )
}