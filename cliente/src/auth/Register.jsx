import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Register.css";

export const Register = () => {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    nombre: "",
    email: "",
    documento: "",
    password: "",
  });

  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    if (form.password.length < 8) {
      setError("La contraseña debe tener al menos 8 caracteres");
      return;
    }

    setLoading(true);

    try {
      const response = await fetch("http://localhost/ferreinver/server/backend-login/Register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nombre: form.nombre,
          correo: form.email,
          documento: form.documento,
          password: form.password,
        }),
      });

      const data = await response.json();

      if (data.success) {
        alert("✅ Cuenta creada correctamente. Ahora puedes iniciar sesión.");
        navigate("/inicio");
      } else {
        setError(data.mensaje || "Error al crear la cuenta");
      }
    } catch (err) {
      console.error(err);
      setError("Error del servidor. Intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register-page-wrapper">
      <div className="card">

        {/* ── Botón volver ── */}
        <button className="volver-btn" onClick={() => navigate(-1)}>
          <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
            <path d="M19 12H5M5 12l7 7M5 12l7-7"
              stroke="currentColor" strokeWidth="2"
              strokeLinecap="round" strokeLinejoin="round" />
          </svg>
          Volver
        </button>

        <div className="card-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="#00185a">
            <g fill="none" fillRule="evenodd">
              <path d="M24 0v24H0V0h24Z" />
              <path fill="#00185a" d="M12 2c5.523 0 10 4.477 10 10a9.959 9.959 0 0 1-2.258 6.33A9.978 9.978 0 0 1 12 22c-2.95 0-5.6-1.277-7.43-3.307A9.958 9.958 0 0 1 2 12C2 6.477 6.477 2 12 2Zm0 15c-1.86 0-3.541.592-4.793 1.405A7.965 7.965 0 0 0 12 20a7.965 7.965 0 0 0 4.793-1.595A8.897 8.897 0 0 0 12 17Zm0-13a8 8 0 0 0-6.258 12.984C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984A8 8 0 0 0 12 4Zm0 2a4 4 0 1 1 0 8a4 4 0 0 1 0-8Zm0 2a2 2 0 1 0 0 4a2 2 0 0 0 0-4Z" />
            </g>
          </svg>
          <h1>Crear cuenta</h1>
        </div>

        <div className="card-body">
          <form onSubmit={handleSubmit}>

            <div className="form-group">
              <label htmlFor="nombre">Nombre completo</label>
              <input
                type="text"
                id="nombre"
                name="nombre"
                placeholder="Ingresa tu nombre completo"
                value={form.nombre}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="email">Correo electrónico</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Ingresa tu correo electrónico"
                value={form.email}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="documento">Documento</label>
              <input
                type="text"
                id="documento"
                name="documento"
                placeholder="Documento de identidad"
                value={form.documento}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="password">Contraseña</label>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Ingresa tu contraseña..."
                value={form.password}
                onChange={handleChange}
                required
              />
            </div>

            {error && <p className="password-hint">{error}</p>}

            <button className="btn-submit" type="submit" disabled={loading}>
              {loading ? "Creando cuenta..." : "Crear cuenta"}
            </button>

          </form>

          <div className="login-link">
            <span>¿Ya tienes una cuenta?</span>
            <a onClick={() => navigate("/inicio", { state: { abrirLogin: true } })} style={{ cursor: "pointer" }}>
              Iniciar sesión
            </a>
          </div>
        </div>

      </div>
    </div>
  );
};

export default Register;