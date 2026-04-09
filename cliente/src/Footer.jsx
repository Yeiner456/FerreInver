import React from 'react'
import './styles/Footer.css'

export const Footer = () => {
  return (
    <footer>

 
  <div className='footer-izquierda'></div>


  <div className='footer-centro'>
    <div className='footer-icons'>
      <a href="https://www.instagram.com/ferreinver_sas/">
        <img className='instagram' src="./public/img/intagram-icon.webp" alt="Instagram" />
      </a>
      <a href="https://www.facebook.com/profile.php?id=61569142257089">
        <img className='facebook' src="./public/img/facebook-icon.webp" alt="Facebook" />
      </a>
    </div>
    <p className='footer-text'>© 2024 FerreInver. Todos los derechos reservados.</p>
    <p className='footer-text'>Contactanos ferreinver@gmail.com</p>
  </div>

  <div className='footer-derecha'>
    <img className='logo-sena' src="./public/img/logo-sena.webp" alt="Logo Sena" />
  </div>

</footer>
  )
}

