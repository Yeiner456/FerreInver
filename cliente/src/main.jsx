import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import { ProjectApp } from './ProjectApp'
import './Styles/main.css'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <ProjectApp />
    </BrowserRouter>
  </StrictMode>
)