import React, { useEffect, useRef } from 'react'
import "./styles/InfoFi.css"

export const InfoFi = () => {
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
    <section ref={sectionRef} className="infofi">
        <div className='infofi-container'>
            <h2 className='infofi-title'>ESTE ES UNO DE NUESTROS ULTIMOS TRABAJOS </h2>
            <p className='infofi-text'>En el video se puede apreciar uno de nuestros invernaderos en su fase final de construcción,
      donde la estructura principal ya se encuentra completamente instalada. Este proyecto fue
      diseñado para ofrecer un espacio adecuado para el cultivo protegido, permitiendo controlar
      factores como la temperatura, la humedad y la exposición al clima.</p>
        </div>
      
      <video className="corto-proceso" src="./public/img/corto-proceso.mp4" autoPlay muted loop playsInline/>

    </section>
  )
}