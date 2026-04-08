import React from 'react'
import './Styles/Nav.css'
import { Link } from 'react-router-dom'
// Nav actualizado: link Productos apunta a /tienda-productos, duplicado eliminado




export const Nav = () => {
  return (
    <nav>

        <img className='perfil' src="./public/assets/perfil.webp" alt="" />

        <img  className='logo' src="./public/assets/logo.webp" alt="" />
            
            <ul className='nav-links'>

                <li className='links'>
                    <Link className='inicio' to="/">Inicio</Link>

                </li>

                <li className='links'> 
                <Link className='productos' to="/tienda-productos">Productos</Link>
            </li>

            <li className='links'>
                <a  className='contactanos' href="">Contactanos</a>
            </li>

            <li className='links'>

                <Link className='quienes-somos' to="/quienes-somos">¿Quienes somos?</Link>
            </li >

                <li className='links'>
                    <a  className='registro' href="">Registro</a>
                </li>
                <li className='links'>
                    <a  className='iniciar-sesion' href="">Iniciar Sesión</a>
                </li>
            </ul>

        </nav>  
  )
}

