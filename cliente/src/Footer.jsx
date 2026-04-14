import React from 'react'
import './styles/Footer.css'
import { Link } from 'react-router-dom'

export const Footer = () => {
  return (
    <footer>

      <div className="footer-top">

        <div className="footer-col footer-brand-col">
          <img className="footer-logo" src="/img/logo.webp" alt="Ferreinver" />
          <p className="footer-brand-desc">
            Materiales y soluciones para la agricultura. Construimos invernaderos a tu medida en todo Antioquia.
          </p>
          <div className="footer-social">
            <a href="https://www.instagram.com/ferreinver_sas/" target="_blank" rel="noreferrer" className="social-link" aria-label="Instagram">
              <svg viewBox="0 0 24 24"><path d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5a4.25 4.25 0 004.25 4.25h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zm4.25 3.25a5.25 5.25 0 110 10.5 5.25 5.25 0 010-10.5zm0 1.5a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5zm5.5-.875a.875.875 0 110 1.75.875.875 0 010-1.75z"/></svg>
            </a>
            <a href="https://www.facebook.com/profile.php?id=61569142257089" target="_blank" rel="noreferrer" className="social-link" aria-label="Facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.791-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
            </a>
          </div>
        </div>

     
        <div className="footer-col">
          <h4 className="footer-heading">Navegación</h4>
          <ul className="footer-nav">
            <li><Link to="/inicio">Inicio</Link></li>
            <li><Link to="/tienda-productos">Productos</Link></li>
            <li><Link to="/contactanos">Contáctanos</Link></li>
            <li><Link to="/quienes-somos">¿Quiénes somos?</Link></li>
          </ul>
        </div>

    
        <div className="footer-col">
          <h4 className="footer-heading">Contacto</h4>
          <ul className="footer-contact">
            <li>
              <svg viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1v3.5a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.58a1 1 0 01-.25 1.02l-2.2 2.19z"/></svg>
              +57 (4) 555 0100
            </li>
            <li>
              <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
              ferreinver@gmail.com
            </li>
            <li>
              <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
              Carmen de Viboral, Antioquia
            </li>
          </ul>
        </div>

       
        <div className="footer-col footer-sena-col">
          <h4 className="footer-heading">Avalado por</h4>
          <img className="logo-sena" src="./public/img/logo-sena.webp" alt="Logo Sena" />
          <p className="footer-sena-text">Proyecto desarrollado con apoyo del SENA</p>
        </div>

      </div>

      <div className="footer-bottom">
        <p className="footer-copy">© 2026 FerreInver. Todos los derechos reservados.</p>
      </div>

    </footer>
  )
}