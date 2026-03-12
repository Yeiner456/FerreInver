import React from 'react'
import { Nav } from './Nav.jsx'
import { HeroSection } from './heroSection.jsx'
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
            <Producto />
          </>
        } />
        <Route path="/quienes-somos" element={<QuienesSomos />} />
      </Routes>
      <Footer />
    </div>
  )
}
