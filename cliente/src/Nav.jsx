import React from 'react'
import './Styles/Nav.css'
import { Link } from 'react-router-dom'




export const Nav = () => {
  return (
    <nav>

        <img className='perfil' src="./public/assets/perfil.webp" alt="" />

        <img  className='logo' src="./public/assets/logo.webp" alt="" />
            
            <ul className='nav-links'>

                <li className='links'>
                    <Link className='inicio' to="/admin">Inicio</Link>

                </li>

                <li className='links'> 
                <a className='productos' href="/Producto.jsx">Productos</a>
            </li>

            <li className='links'>
                <a  className='contactanos' href="/contactanos.jsx">Contactanos</a>
            </li>

            <li className='links'>

                <Link className='quienes-somos' to="/quienes-somos">¿Quienes somos?</Link>
            </li >

                <li className='links'>    
                    <a  className='productos' href="">Productos</a>
                </li>

                <li className='links'>
                    <a  className='registro' href="/register.jsx">Registrarte</a>
                </li>
                <li className='links' src="/login" alt="" >
                    <a  className='iniciar-sesion' href="/login.jsx">Iniciar Sesión</a>
                </li>
            </ul>

        </nav>  
  )
}

