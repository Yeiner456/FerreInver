import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'

import Login from './auth/Login'
import Register from './auth/Register'
import { RecuperarPassword } from './auth/RecuperarPassword'
import AdminPanel from './Components/AdminPanel'


//  Ruta protegida si no hay usuario en sessionStorage, redirige al login
const RutaProtegida = ({ children }) => {
    const usuario = sessionStorage.getItem("usuario")
    return usuario ? children : <Navigate to="/" replace />
}

export const ProjectApp = () => {
    return (
        <Routes>
            {/* Rutas públicas */}
            <Route path="/" element={<Login />} />
            <Route path="/register" element={<Register />} />

            {/* Rutas protegidas */}
            <Route path="/admin" element={
                <RutaProtegida>
                    <AdminPanel/>
                </RutaProtegida>
            } />

            <Route path="/recuperar" element={
                <RecuperarPassword />
                } />

            {/* Cualquier ruta desconocida redirige al login */}
            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    )
}
