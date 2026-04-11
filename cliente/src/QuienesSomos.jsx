import React from 'react'
import './Styles/QuienesSomos.css'

export const QuienesSomos = () => {
  return (
    <main className="qs-page">

      {/* ── HERO ── */}
      <section className="qs-hero">
        <div className="qs-hero-content">
          <span className="qs-tag">Sobre nosotros</span>
          <h1 className="qs-hero-title">Construimos más que<br /><em>estructuras</em></h1>
          <p className="qs-hero-sub">
            Ubicados en El Carmen de Víboral, Antioquia — llevamos soluciones integrales
            en construcción y mantenimiento de invernaderos a todo el departamento.
          </p>
        </div>
        <div className="qs-hero-badge">
          <span className="qs-badge-year">FERREINVER</span>
          <span className="qs-badge-loc">El Carmen de Víboral</span>
        </div>
      </section>

      {/* ── MISIÓN Y VISIÓN ── */}
      <section className="qs-mv">

        <div className="qs-mv-card qs-mision">
          <div className="qs-mv-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="9" stroke="#FFD700" strokeWidth="1.5"/>
              <circle cx="12" cy="12" r="4" stroke="#FFD700" strokeWidth="1.5"/>
              <circle cx="12" cy="12" r="1" fill="#FFD700"/>
              <line x1="12" y1="2" x2="12" y2="5" stroke="#FFD700" strokeWidth="1.5" strokeLinecap="round"/>
              <line x1="12" y1="19" x2="12" y2="22" stroke="#FFD700" strokeWidth="1.5" strokeLinecap="round"/>
              <line x1="2" y1="12" x2="5" y2="12" stroke="#FFD700" strokeWidth="1.5" strokeLinecap="round"/>
              <line x1="19" y1="12" x2="22" y2="12" stroke="#FFD700" strokeWidth="1.5" strokeLinecap="round"/>
            </svg>
          </div>
          <h2 className="qs-mv-label">Misión</h2>
          <p className="qs-mv-text">
            En Ferreinver, nos dedicamos apasionadamente a proporcionar soluciones integrales
            en el ámbito de la construcción, mantenimiento de invernaderos y fabricación de
            elementos para el desarrollo estructural, ofreciendo servicios especializados y
            asesoramiento experto que impulse el éxito de sus proyectos. Creamos un entorno
            laboral en el que nuestro talentoso equipo pueda crecer y prosperar junto a nosotros.
            Valoramos la integridad, la ética y la dedicación, y trabajamos juntos para superar
            las expectativas de nuestros clientes.
          </p>
        </div>

        <div className="qs-mv-divider" />

        <div className="qs-mv-card qs-vision">
          <div className="qs-mv-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" stroke="#1E12A4" strokeWidth="1.5"/>
              <circle cx="12" cy="12" r="3" stroke="#1E12A4" strokeWidth="1.5"/>
              <path d="M12 5V3M12 21v-2M19 12h2M3 12H1" stroke="#1E12A4" strokeWidth="1.5" strokeLinecap="round"/>
            </svg>
          </div>
          <h2 className="qs-mv-label">Visión</h2>
          <p className="qs-mv-text">
            Para el 2028 nos visualizamos como líderes destacados en la industria de la
            construcción y mantenimiento de invernaderos, siendo reconocidos por nuestra
            excelencia, innovación y compromiso inquebrantable con la satisfacción del cliente.
            Nos vemos como arquitectos de sueños y constructores de un futuro sólido, donde
            cada estructura refleje la excelencia en cada detalle.
          </p>
        </div>

      </section>

      {/* ── OBJETIVOS ── */}
      <section className="qs-objetivos">
        <div className="qs-objetivos-header">
          <h2>Nuestros objetivos</h2>
          <p>Lo que nos mueve cada día</p>
        </div>
        <div className="qs-objetivos-grid">
          {[
            { num: "01", texto: "Diseñar una interfaz intuitiva y atractiva que refleje la identidad de FERREINVER." },
            { num: "02", texto: "Presentar la misión y visión de la empresa de manera clara y accesible." },
            { num: "03", texto: "Visualizar el inventario con precios actualizados en tiempo real." },
            { num: "04", texto: "Publicar vacantes disponibles para quienes quieran unirse al equipo." },
            { num: "05", texto: "Facilitar la comunicación rápida y eficiente mediante un formulario de contacto." },
            { num: "06", texto: "Garantizar accesibilidad y compatibilidad en distintos dispositivos." },
          ].map((obj) => (
            <div className="qs-obj-item" key={obj.num}>
              <span className="qs-obj-num">{obj.num}</span>
              <p className="qs-obj-texto">{obj.texto}</p>
            </div>
          ))}
        </div>
      </section>

      {/* ── ALCANCE ── */}
      <section className="qs-alcance">
        <div className="qs-alcance-inner">
          <h2>Alcance del proyecto</h2>
          <p>
            El aplicativo se enfocará en proporcionar información sobre FERREINVER ubicada en
            El Carmen de Víboral, incluyendo su misión, visión, y un catálogo de productos con
            precios actualizados. También contará con una sección de ofertas laborales y un
            formulario de contacto. Además, se implementará una funcionalidad que permitirá a
            los usuarios realizar pedidos de productos directamente desde el aplicativo,
            sin procesar pagos en línea.
          </p>
        </div>
      </section>

    </main>
  )
}