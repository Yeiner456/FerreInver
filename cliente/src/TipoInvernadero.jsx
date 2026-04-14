import React, { useEffect, useRef } from 'react'
import "./styles/TipoInvernadero.css"

export const TipoInvernadero = () => {
  const sectionRef = useRef();

  useEffect(() => {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        entry.target.classList.toggle('visible', entry.isIntersecting);
      });
    }, { threshold: 0.1 });

    if (sectionRef.current) observer.observe(sectionRef.current);
    return () => observer.disconnect();
  }, []);

  return (
    <section ref={sectionRef} className='tipoinvernadero'>

      <div className="tipoinvernadero-header">
        <span className="tipo-pill">Nuestros diseños</span>
        <h2>Tipos de <em>Invernaderos</em></h2>
        <p className="tipo-sub">Diseñados para adaptarse a cada tipo de cultivo y terreno</p>
      </div>

      <div className='tipoinvernadero-img'>

        <div className='invernadero-card invernadero-metalico'>
          <div className="invernadero-img-wrap">
            <img src="./public/img/invernadero-metalico.webp" alt="Invernadero metálico" />
            <div className="invernadero-tag">Más popular</div>
          </div>
          <div className="invernadero-info">
            <h3>Estructura Metálica</h3>
            <p>Invernadero con estructura metálica resistente, ideal para brindar
            estabilidad, durabilidad y protección a los cultivos en cualquier condición climática.</p>
            <ul className="invernadero-features">
              <li>Alta durabilidad</li>
              <li>Resistente al viento</li>
              <li>Bajo mantenimiento</li>
            </ul>
          </div>
        </div>

        <div className='invernadero-card invernadero-tipocapilla'>
          <div className="invernadero-img-wrap">
            <img src="./public/img/invernadero-tipocapilla.webp" alt="Invernadero tipo capilla" />
          </div>
          <div className="invernadero-info">
            <h3>Tipo Capilla</h3>
            <p>Diseño tipo capilla que permite una mejor ventilación y entrada de luz,
            favoreciendo el crecimiento saludable de los cultivos con circulación de aire natural.</p>
            <ul className="invernadero-features">
              <li>Mejor ventilación</li>
              <li>Máxima luminosidad</li>
              <li>Diseño optimizado</li>
            </ul>
          </div>
        </div>

      </div>

    </section>
  )
}