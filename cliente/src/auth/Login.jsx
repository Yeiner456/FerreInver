import { useState, useEffect } from "react";
import { useNavigate, Link } from "react-router-dom";
import "../styles/Login.css";

export const Login = ({ onCerrar }) => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const navigate = useNavigate();

  // Bloquear scroll mientras el modal está abierto
  useEffect(() => {
    document.body.style.overflow = 'hidden'
    return () => { document.body.style.overflow = '' }
  }, [])

  // Si ya hay sesión, redirigir directo
  useEffect(() => {
    const usuario = sessionStorage.getItem("usuario");
    if (usuario) {
      const user = JSON.parse(usuario);
      onCerrar?.()
      navigate(user.tipo_usuario === "admin" ? "/admin" : "/inicio");
    }
  }, []);

  // Cerrar con ESC
  useEffect(() => {
    const handleKey = (e) => { if (e.key === 'Escape') onCerrar?.() }
    document.addEventListener('keydown', handleKey)
    return () => document.removeEventListener('keydown', handleKey)
  }, [])

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch("http://localhost/ferreinver/server/backend-login/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo: email, password }),
      });

      const data = await response.json();

      if (data.success === true) {
        sessionStorage.setItem("usuario", JSON.stringify(data.usuario));
        onCerrar?.()
        if (data.usuario.tipo_usuario === "admin") {
          navigate("/admin");
        } else {
          navigate("/inicio");
        }
      } else {
        alert(data.mensaje);
      }
    } catch (error) {
      console.error(error);
      alert("Error del servidor");
    }
  };

  // Si no viene onCerrar es porque se usa como página (ej: /login directo)
  const esModal = !!onCerrar

  const contenido = (
    <div className="login-card">

      {/* Botón cerrar — solo en modal */}
      {esModal && (
        <button className="login-modal-cerrar" onClick={onCerrar}>
          <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
          </svg>
        </button>
      )}

      <div className="icon-user">
        <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="#00185a">
          <g fill="none" fillRule="evenodd">
            <path d="M24 0v24H0V0h24Z"/>
            <path fill="#00185a" d="M12 2c5.523 0 10 4.477 10 10a9.959 9.959 0 0 1-2.258 6.33A9.978 9.978 0 0 1 12 22c-2.95 0-5.6-1.277-7.43-3.307A9.958 9.958 0 0 1 2 12C2 6.477 6.477 2 12 2Zm0 15c-1.86 0-3.541.592-4.793 1.405A7.965 7.965 0 0 0 12 20a7.965 7.965 0 0 0 4.793-1.595A8.897 8.897 0 0 0 12 17Zm0-13a8 8 0 0 0-6.258 12.984C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984A8 8 0 0 0 12 4Zm0 2a4 4 0 1 1 0 8a4 4 0 0 1 0-8Z"/>
          </g>
        </svg>
      </div>

      <h1>Bienvenido</h1>
      <p className="subtitle">ingresa tu cuenta</p>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="email">Correo electrónico</label>
          <input
            className="login-input"
            type="email"
            id="email"
            placeholder="Ingrese su correo electrónico"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label htmlFor="password">Contraseña</label>
          <input
            className="login-input"
            type="password"
            id="password"
            placeholder="Ingrese su contraseña"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
        </div>

        <div className="forgot-password">
          <Link to="/recuperar" onClick={onCerrar}>¿olvidaste tu contraseña?</Link>
        </div>

        <button className="login-button" type="submit">
          Iniciar Sesión
        </button>
      </form>

      <div className="divider">
        <span></span>
        <span className="dot">o</span>
        <span></span>
      </div>

      <button className="register-button" onClick={() => { onCerrar?.(); navigate("/register") }}>
        Crear Nueva Cuenta
      </button>

    </div>
  )

  // Si es modal → overlay con fondo borroso
  // Si es página → layout normal
  if (esModal) {
    return (
      <div className="login-overlay" onClick={onCerrar}>
        <div className="login-container" onClick={(e) => e.stopPropagation()}>
          {contenido}
        </div>
      </div>
    )
  }

  return (
    <div className="body">
      <div className="login-container">
        {contenido}
      </div>
    </div>
  )
}

export default Login;