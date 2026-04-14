import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/recuperar.css";

const BASE_URL = "http://localhost/ferreinver/server/backend-login";

export const RecuperarPassword = () => {
  const navigate = useNavigate();

  // Controla en qué paso estamos: 1, 2 o 3
  const [paso, setPaso] = useState(1);

  // Datos compartidos entre pasos
  const [correo, setCorreo] = useState("");
  const [codigo, setCodigo] = useState("");
  const [nuevaPassword, setNuevaPassword] = useState("");
  const [confirmarPassword, setConfirmarPassword] = useState("");

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  // Enviar código al correo 
  const handleEnviarCodigo = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch(`${BASE_URL}/Enviar_codigo.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo }),
      });
      const data = await res.json();

      if (data.success) {
        setPaso(2);
      } else {
        setError(data.mensaje);
      }
    } catch {
      setError("Error del servidor. Intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  //  Verificar código 
  const handleVerificarCodigo = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch(`${BASE_URL}/verificar_codigo.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo, codigo }),
      });
      const data = await res.json();

      if (data.success) {
        setPaso(3);
      } else {
        setError(data.mensaje);
      }
    } catch {
      setError("Error del servidor. Intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  //  Cambiar contraseña 
  const handleCambiarPassword = async (e) => {
    e.preventDefault();
    setError("");

    if (nuevaPassword !== confirmarPassword) {
      setError("Las contraseñas no coinciden");
      return;
    }
    if (nuevaPassword.length < 8) {
      setError("La contraseña debe tener al menos 8 caracteres");
      return;
    }

    setLoading(true);

    try {
      const res = await fetch(`${BASE_URL}/cambiar_password.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo, codigo, nueva_password: nuevaPassword }),
      });
      const data = await res.json();

      if (data.success) {
        alert(" Contraseña actualizada correctamente");
        navigate("/");
      } else {
        setError(data.mensaje);
      }
    } catch {
      setError("Error del servidor. Intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="body">
      <div className="login-container">
        <div className="login-card">

          <div className="icon-user">
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24">
              <path fill="#fdee1e" d="M17 9V7c0-2.8-2.2-5-5-5S7 4.2 7 7v2c-1.7 0-3 1.3-3 3v7c0 1.7 1.3 3 3 3h10c1.7 0 3-1.3 3-3v-7c0-1.7-1.3-3-3-3zM9 7c0-1.7 1.3-3 3-3s3 1.3 3 3v2H9V7zm4.1 8.5l-.1.1V17c0 .6-.4 1-1 1s-1-.4-1-1v-1.4c-.6-.6-.7-1.5-.1-2.1c.6-.6 1.5-.7 2.1-.1c.6.5.7 1.5.1 2.1z"/>
            </svg>
          </div>

          {/* ─── PASO 1 ─── */}
          {paso === 1 && (
            <>
              <h1>Recuperar contraseña</h1>
              <p className="subtitle">Ingresa tu correo electrónico para recuperar tu contraseña</p>

              <form onSubmit={handleEnviarCodigo}>
                <div className="form-group">
                  <label htmlFor="email">Correo electrónico</label>
                  <input
                    className="login-input"
                    type="email"
                    id="email"
                    placeholder="Ingresa tu correo electrónico"
                    value={correo}
                    onChange={(e) => setCorreo(e.target.value)}
                    required
                  />
                </div>

                <div className="mensaje">
                  <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24">
                    <g fill="none">
                      <circle cx="12" cy="12" r="9.25" stroke="#002FB1" strokeWidth="1.5"/>
                      <path stroke="#002FB1" strokeLinecap="round" strokeWidth="1.5" d="M12 12.438v-5"/>
                      <circle cx="1.25" cy="1.25" r="1.25" fill="#002FB1" transform="matrix(1 0 0 -1 10.75 17.063)"/>
                    </g>
                  </svg>
                  <p>Te enviaremos un correo con un código para restablecer tu contraseña. Revisa también tu carpeta de spam</p>
                </div>

                {error && <p style={{color:"red", fontSize:"13px", textAlign:"left"}}>{error}</p>}

                <button className="login-button" type="submit" disabled={loading}>
                  {loading ? "Enviando..." : "Enviar código"}
                </button>
              </form>
            </>
          )}

          {/* ─── PASO 2 ─── */}
          {paso === 2 && (
            <>
              <h1>Verificar código</h1>
              <p className="subtitle">Ingresa el código de 6 dígitos que enviamos a <strong>{correo}</strong></p>

              <form onSubmit={handleVerificarCodigo}>
                <div className="form-group">
                  <label htmlFor="codigo">Código de verificación</label>
                  <input
                    className="login-input"
                    type="text"
                    id="codigo"
                    placeholder="Ingresa el código de 6 dígitos"
                    value={codigo}
                    onChange={(e) => setCodigo(e.target.value)}
                    maxLength={6}
                    required
                  />
                </div>

                {error && <p style={{color:"red", fontSize:"13px", textAlign:"left"}}>{error}</p>}

                <button className="login-button" type="submit" disabled={loading}>
                  {loading ? "Verificando..." : "Verificar código"}
                </button>
              </form>

              <button className="register-button" onClick={() => { setPaso(1); setError(""); }}>
                ← Volver
              </button>
            </>
          )}

          {/* ─── PASO 3 ─── */}
          {paso === 3 && (
            <>
              <h1>Nueva contraseña</h1>
              <p className="subtitle">Ingresa tu nueva contraseña</p>

              <form onSubmit={handleCambiarPassword}>
                <div className="form-group">
                  <label htmlFor="nuevaPassword">Nueva contraseña</label>
                  <input
                    className="login-input"
                    type="password"
                    id="nuevaPassword"
                    placeholder="Mínimo 8 caracteres"
                    value={nuevaPassword}
                    onChange={(e) => setNuevaPassword(e.target.value)}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="confirmarPassword">Confirmar contraseña</label>
                  <input
                    className="login-input"
                    type="password"
                    id="confirmarPassword"
                    placeholder="Repite tu nueva contraseña"
                    value={confirmarPassword}
                    onChange={(e) => setConfirmarPassword(e.target.value)}
                    required
                  />
                </div>

                {error && <p style={{color:"red", fontSize:"13px", textAlign:"left"}}>{error}</p>}

                <button className="login-button" type="submit" disabled={loading}>
                  {loading ? "Guardando..." : "Cambiar contraseña"}
                </button>
              </form>
            </>
          )}

          {/* Volver al login — visible en paso 1 */}
          {paso === 1 && (
            <button className="register-button" onClick={() => navigate("/")}>
              ← Volver al inicio de sesión
            </button>
          )}

        </div>
      </div>
    </div>
  );
};

export default RecuperarPassword;