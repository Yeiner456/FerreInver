import React from 'react'
import './Styles/Nav.css'

export const Nav = () => {
  return (
    <nav>

        <img className='perfil' src="./public/assets/perfil.webp" alt="" />
            
            <ul className='nav-links'>

                <li>
                    <a className='inicio' href="">Inicio</a>
                </li>

                <li>    
                    <a  className='productos' href="">Productos</a>
                </li>

                <li >
                    <a  className='registro' href="">Registro</a>
                </li>
                <li>
                    <a  className='iniciar-sesion' href="">Iniciar Sesión</a>
                </li>
            </ul>

        </nav>  
  )
}

