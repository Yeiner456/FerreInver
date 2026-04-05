import React from 'react'
import './styles/Nav.css'
import { Link } from 'react-router-dom'

export const Nav = () => {
  return (
    <nav>

        <img className='perfil' src="./public/img/perfil.webp" alt="" />

        <img  className='logo' src="./public/img/logo.webp" alt="" />
            
        <ul className='nav-links'>    

        <li className='links'>
            <Link className='inicio' to="/">Inicio</Link>        
        </li>                

        <li className='links'>
          <a className='productos' href="">Productos</a>
        </li>

        <li className='links'>
          <a className='contactanos' href="">Contáctanos</a>
        </li>

        <li className='links'>
          <Link className='quienes-somos' to="/quienes-somos">¿Quiénes somos?</Link>
        </li>

        <li className='links'>
          <a className='registro' href="">Registro</a>
        </li>

        <li className='links'>
          <a className='iniciar-sesion' href="">Iniciar Sesión</a>
        </li>
      </ul>
    </nav>
  )
}

