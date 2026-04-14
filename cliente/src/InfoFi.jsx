import React, { useEffect, useRef } from 'react'
import "./styles/InfoFi.css"

export const InfoFi = () => {
  const sectionRef = useRef();

  useEffect(() => {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        entry.target.classList.toggle('visible', entry.isIntersecting);
      });
    }, { threshold: 0.15 });

    if (sectionRef.current) observer.observe(sectionRef.current);
    return () => observer.disconnect();
  }, []);

  return (
    <section ref={sectionRef} className="infofi">

      <div className="infofi-inner">

        <div className="infofi-container">
          <span className="infofi-pill">Nuestro proceso</span>
          <h2 className="infofi-title">
            Así construimos<br />
            <em>tu invernadero</em>
          </h2>
          <p className="infofi-text">
            En el video se puede apreciar uno de nuestros invernaderos en su fase final de construcción,
            donde la estructura principal ya se encuentra completamente instalada. Este proyecto fue
            diseñado para ofrecer un espacio adecuado para el cultivo protegido, permitiendo controlar
            factores como la temperatura, la humedad y la exposición al clima.
          </p>

          <div className="infofi-stats">
            <div className="infofi-stat">
              <span className="stat-num">10+</span>
              <span className="stat-label">Años de experiencia</span>
            </div>
            <div className="infofi-stat">
              <span className="stat-num">200+</span>
              <span className="stat-label">Proyectos completados</span>
            </div>
            <div className="infofi-stat">
              <span className="stat-num">100%</span>
              <span className="stat-label">Clientes satisfechos</span>
            </div>
          </div>
        </div>

        <div className="infofi-media">
          <div className="infofi-media-wrap">
            <video
              className="corto-proceso"
              src="./public/img/corto-proceso.mp4"
              autoPlay muted loop playsInline
            />
            <div className="infofi-media-badge">
              <span>▶</span> Proceso real de construcción
            </div>
          </div>
        </div>

      </div>
    </section>
  )
}