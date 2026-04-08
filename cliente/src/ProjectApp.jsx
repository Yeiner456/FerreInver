import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'

import Login from './auth/Login'
import Register from './auth/Register'
import { RecuperarPassword } from './auth/RecuperarPassword'
import AdminPanel from './Components/AdminPanel'

// Componentes del cliente (de tu compañero)
import { Nav } from './Nav'
import { HeroSection } from './heroSection'
import { Producto } from './Producto'
import { Footer } from './Footer'
import { QuienesSomos } from './QuienesSomos'

// ✅ Ruta protegida: verifica que haya sesión
const RutaProtegida = ({ children }) => {
  const usuario = sessionStorage.getItem("usuario")
  return usuario ? children : <Navigate to="/" replace />
}

// ✅ Ruta protegida por rol
const RutaPorRol = ({ children, rolRequerido }) => {
  const usuarioStr = sessionStorage.getItem("usuario")
  if (!usuarioStr) return <Navigate to="/" replace />
  const usuario = JSON.parse(usuarioStr)
  if (usuario.tipo_usuario !== rolRequerido) return <Navigate to="/" replace />
  return children
}

// ✅ Layout del cliente: Nav + contenido + Footer
const ClienteLayout = ({ children }) => (
  <div>
    <Nav />
    {children}
    <Footer />
  </div>
)

export const ProjectApp = () => {
  return (
    <Routes>

      {/* ── Rutas públicas (sin Nav/Footer) ── */}
      <Route path="/" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/recuperar" element={<RecuperarPassword />} />

      {/* ── Ruta admin ── */}
      <Route path="/admin" element={
        <RutaPorRol rolRequerido="admin">
          <AdminPanel />
        </RutaPorRol>
      } />

      {/* ── Rutas cliente (con Nav y Footer) ── */}
      <Route path="/inicio" element={
        <RutaPorRol rolRequerido="cliente">
          <ClienteLayout>
            <HeroSection />
            <Producto />
          </ClienteLayout>
        </RutaPorRol>
      } />

      <Route path="/quienes-somos" element={
        <RutaPorRol rolRequerido="cliente">
          <ClienteLayout>
            <QuienesSomos />
          </ClienteLayout>
        </RutaPorRol>
      } />

      {/* Ruta desconocida → login */}
      <Route path="*" element={<Navigate to="/" replace />} />

    </Routes>
  )
}