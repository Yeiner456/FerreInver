import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'

import Login from './auth/Login'
import Register from './auth/Register'
import { RecuperarPassword } from './auth/RecuperarPassword'
import AdminPanel from './Components/AdminPanel'

import { Nav } from './Nav'
import { HeroSection } from './heroSection'
import { Producto } from './Producto'
import { Footer } from './Footer'
import { QuienesSomos } from './QuienesSomos'
import { InfoFi } from './InfoFi'
import { TipoInvernadero } from './TipoInvernadero'
import { TiendaProductos } from "./tienda-productos"

const RutaPorRol = ({ children, rolRequerido }) => {
  const usuarioStr = sessionStorage.getItem("usuario")
  if (!usuarioStr) return <Navigate to="/login" replace />
  const usuario = JSON.parse(usuarioStr)
  if (usuario.tipo_usuario !== rolRequerido) return <Navigate to="/login" replace />
  return children
}

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

      {/* ── LOGIN ── */}
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/recuperar" element={<RecuperarPassword />} />

      {/* ── LANDING PÚBLICA ── */}
      <Route path="/inicio" element={
        <ClienteLayout>
          <HeroSection />
          <InfoFi />
          <TipoInvernadero />
          <Producto />
        </ClienteLayout>
      } />

      <Route path="/cotizacion" element={
        <ClienteLayout>
          <CotizacionPublica />
        </ClienteLayout>
      } />
          <Route path="/tienda-productos" element={
        <ClienteLayout>
          <TiendaProductos />
        </ClienteLayout>
      } />
      <Route path="/quienes-somos" element={
        <ClienteLayout>
          <QuienesSomos />
        </ClienteLayout>
      } />

      {/* ── ADMIN ── */}
      <Route path="/admin" element={
        <RutaPorRol rolRequerido="admin">
          <AdminPanel />
        </RutaPorRol>
      } />

      {/* ── RUTA RAÍZ → landing ── */}
      <Route path="/" element={<Navigate to="/inicio" replace />} />

      {/* ── RUTA DESCONOCIDA → landing ── */}
      <Route path="*" element={<Navigate to="/inicio" replace />} />

    </Routes>
  )
}