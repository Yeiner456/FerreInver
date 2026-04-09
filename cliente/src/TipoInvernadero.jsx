import React, { useEffect, useRef } from 'react'
import "./Styles/TipoInvernadero.css"

export const TipoInvernadero = () => {
  const sectionRef = useRef();

  useEffect(() => {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    });

    if (sectionRef.current) {
      observer.observe(sectionRef.current);
    }

    return () => observer.disconnect();
  }, []);

  return (
    <section ref={sectionRef} className='tipoinvernadero'>

        <h2>TIPOS DE INVERNADEROS</h2>

        <div className='tipoinvernadero-img'>

            <div className='invernadero-metalico'>
                <img src="./public/img/invernadero-metalico.webp" alt="" />
                <p>Invernadero con estructura metálica resistente, ideal para brindar
                estabilidad, durabilidad y protección a los cultivos.</p>
            </div>

            <div className='invernadero-tipocapilla'>
                <img src="./public/img/invernadero-tipocapilla.webp" alt="" />
                <p>Diseño tipo capilla que permite una mejor ventilación y entrada de luz,
                favoreciendo el crecimiento saludable de los cultivos.</p>
            </div>

        </div>

    </section>
  )
}
