import { useEffect, useRef, useState } from "react";
import "../styles/Contactanos.css"

export function Contactanos() {
  const [toast, setToast] = useState(false);
  const fadeRefs = useRef([]);

  useEffect(() => {
    const io = new IntersectionObserver(
      (entries) => entries.forEach((e) => { if (e.isIntersecting) e.target.classList.add("visible"); }),
      { threshold: 0.1 }
    );
    fadeRefs.current.forEach((el) => el && io.observe(el));
    return () => io.disconnect();
  }, []);

  const addRef = (el) => { if (el && !fadeRefs.current.includes(el)) fadeRefs.current.push(el); };

  // ⚠️ Reemplaza este número por el real de Ferreinver (solo dígitos, con código de país)
  const WHATSAPP_NUMBER = "573133637433";

  function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const nombre   = form.nombre.value.trim();
    const apellido = form.apellido.value.trim();
    const email    = form.email.value.trim();
    const telefono = form.telefono.value.trim();
    const tema     = form.tema.value;
    const mensaje  = form.mensaje.value.trim();

    if (!nombre || !email || !mensaje) {
      alert("Por favor completa al menos nombre, correo y mensaje.");
      return;
    }

    const texto = [
      ` *Nuevo contacto desde ferreinver.com*`,
      ``,
      ` *Nombre:* ${nombre}${apellido ? " " + apellido : ""}`,
      ` *Correo:* ${email}`,
      telefono ? ` *Teléfono:* ${telefono}` : null,
      ` *Tema:* ${tema}`,
      ``,
      ` *Mensaje:*`, 
      mensaje,
    ]
      .filter((l) => l !== null)
      .join("\n");

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(texto)}`;
    window.open(url, "_blank", "noopener,noreferrer");

    form.reset();
    setToast(true);
    setTimeout(() => setToast(false), 5000);
  }

  return (
    <>

      
      <section className="contact-section">

        {/* LEFT: info cards */}
        <div className="info-col fade-up" ref={addRef}>
          <div>
            <h2 className="info-intro">Hablemos sobre tu <span>próximo invernadero</span></h2>
            <p className="info-desc">En Ferreinver llevamos años construyendo soluciones agrícolas a la medida en todo Antioquia. Escríbenos, llámanos o visítanos. Cuéntanos sobre tu invernadero ideal. Nuestro equipo te responde en menos de 24 horas con una propuesta personalizada.</p>
          </div>

          <div className="info-card">
            <div className="info-card-icon">
              <svg viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1v3.5a1 1 0 01-1 1C10.61 21 3 13.39 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.58a1 1 0 01-.25 1.02l-2.2 2.19z"/></svg>
            </div>
            <div className="info-card-body">
              <span className="info-card-label">Teléfono</span>
              <span className="info-card-value">+57 (4) 555 0100</span>
              <span className="info-card-sub">Lunes a viernes · 7 am – 5 pm</span>
            </div>
          </div>

          <div className="info-card">
            <div className="info-card-icon">
              <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </div>
            <div className="info-card-body">
              <span className="info-card-label">Correo electrónico</span>
              <span className="info-card-value">contacto@ferreinver.com</span>
              <span className="info-card-sub">Respondemos en menos de 24 h</span>
            </div>
          </div>

          <div className="info-card">
            <div className="info-card-icon">
              <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </div>
            <div className="info-card-body">
              <span className="info-card-label">Ubicación</span>
              <span className="info-card-value">Carmen de Viboral, Antioquia</span>
              <span className="info-card-sub">Servicio en todo el departamento</span>
            </div>
          </div>

          <div className="info-card">
            <div className="info-card-icon wsp-icon">
              <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.557 4.118 1.531 5.845L.057 23.625a.5.5 0 00.618.618l5.78-1.474A11.953 11.953 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.72 9.72 0 01-4.953-1.354l-.354-.21-3.437.877.892-3.344-.23-.368A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25c5.386 0 9.75 4.365 9.75 9.75 0 5.386-4.364 9.75-9.75 9.75z"/></svg>
            </div>
            <div className="info-card-body">
              <span className="info-card-label">WhatsApp</span>
              <span className="info-card-value">+57 313637433</span>
              <span className="info-card-sub">Respuesta inmediata</span>
            </div>
          </div>

          <div className="social-row">
            <a className="social-btn" href="#">
              <svg viewBox="0 0 24 24"><path d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5a4.25 4.25 0 004.25 4.25h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zm4.25 3.25a5.25 5.25 0 110 10.5 5.25 5.25 0 010-10.5zm0 1.5a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5zm5.5-.875a.875.875 0 110 1.75.875.875 0 010-1.75z"/></svg>
              Instagram
            </a>
            <a className="social-btn" href="#">
              <svg viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.791-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
              Facebook
            </a>
          </div>
        </div>

        {/* RIGHT: form */}
        <div className="form-col fade-up" ref={addRef} style={{ transitionDelay: "0.15s" }}>
          <div className="form-card">
            <h2>Envíanos un mensaje</h2>
            <p className="form-sub">Completa el formulario y te contactaremos a la brevedad.</p>

            <form onSubmit={handleSubmit} noValidate>
              <div className="form-row">
                <div className="field">
                  <label htmlFor="nombre">Nombre</label>
                  <input type="text" id="nombre" name="nombre" placeholder="Juan" />
                </div>
                <div className="field">
                  <label htmlFor="apellido">Apellido</label>
                  <input type="text" id="apellido" name="apellido" placeholder="Pérez" />
                </div>
                <div className="field">
                  <label htmlFor="email">Correo electrónico</label>
                  <input type="email" id="email" name="email" placeholder="juan@correo.com" />
                </div>
                <div className="field">
                  <label htmlFor="telefono">Teléfono</label>
                  <input type="tel" id="telefono" name="telefono" placeholder="+57 300 000 0000" />
                </div>
              </div>

              <div className="field">
                <label>¿En qué podemos ayudarte?</label>
                <div className="topic-grid">
                  {[
                    { id: "t1", value: "cotizacion",  label: " Cotización" },
                    { id: "t2", value: "instalacion", label: " Instalación" },
                    { id: "t3", value: "productos",   label: " Productos" },
                    { id: "t4", value: "soporte",     label: " Soporte" },
                    { id: "t5", value: "visita",      label: " Visita técnica" },
                    { id: "t6", value: "otro",        label: " Otro" },
                  ].map((t, i) => (
                    <div className="topic-pill" key={t.id}>
                      <input type="radio" name="tema" id={t.id} value={t.value} defaultChecked={i === 0} />
                      <label htmlFor={t.id}>{t.label}</label>
                    </div>
                  ))}
                </div>
              </div>

              <div className="field">
                <label htmlFor="mensaje">Mensaje</label>
                <textarea
                  id="mensaje"
                  name="mensaje"
                  placeholder="Cuéntanos sobre tu proyecto: tamaño del invernadero, tipo de cultivo, ubicación..."
                />
              </div>

              <button type="submit" className="btn-submit">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                Enviar mensaje
              </button>
            </form>

            {toast && (
              <div className="toast show">
                ✅ ¡Mensaje enviado! Te contactaremos pronto.
              </div>
            )}
          </div>
        </div>
      </section>

      {/* ── MAP ── */}
      <div className="map-strip fade-up" ref={addRef}>
        <h3>¿Dónde estamos?</h3>
        <p>Carmen de Viboral, Antioquia · Atendemos en todo el departamento</p>
        <div className="map-placeholder">
          <iframe
            title="Ubicación Ferreinver"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31756.95!2d-75.334!3d6.073!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e468d6a6a6a6a6a%3A0x0!2sCarmen+de+Viboral%2C+Antioquia!5e0!3m2!1ses!2sco!4v1"
            allowFullScreen=""
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
          />
        </div>
      </div>
    </>
  );
}