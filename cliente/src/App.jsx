import React from 'react'
import { Nav } from './Nav.jsx'
import { HeroSection } from './heroSection.jsx'
import {InfoFi} from "./InfoFi.jsx"
import { TipoInvernadero } from './TipoInvernadero.jsx'
import { Producto } from './Producto.jsx'
import { Footer } from './Footer.jsx'
import { Routes, Route } from 'react-router-dom'
import { QuienesSomos } from './QuienesSomos.jsx'

export default function App() {
  return (
    <div>
      <Nav />
      <Routes>
        <Route path="/" element={
          <>
            <HeroSection />
            <InfoFi/>
            <TipoInvernadero/>
            <Producto />
          </>
        } />
        <Route path="/quienes-somos" element={<QuienesSomos />} />
        <Route path="/login" element={<Login />} />
      </Routes>
      <Footer />
    </div>
  )
}
