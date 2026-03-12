import React, { useRef, useState, useEffect } from "react";
import { data } from "./componets/data.js";
import "./Styles/HeroSection.css";

export const HeroSection = () => {
  const listRef = useRef();
  const [currentIndex, setCurrentIndex] = useState(0);

useEffect(() => {
  const timer = setInterval(() => {
    setCurrentIndex(curr => curr === data.length - 1 ? 0 : curr + 1);
  }, 10000);

  return () => clearInterval(timer);
}, []);

  const scrollToImage = (direction) => {
    if (direction === "prev") {
      setCurrentIndex((curr) => (curr === 0 ? data.length - 1 : curr - 1));
    } else {
      setCurrentIndex((curr) => (curr === data.length - 1 ? 0 : curr + 1));
    }
  };

  const goToSlide = (slideIndex) => {
    setCurrentIndex(slideIndex);
  };

  return (
    <div className="main-container">
      <div className="slider-container">
        <div className="leftArrow" onClick={() => scrollToImage("prev")}>
          &#10092;
        </div>
        <div className="rightArrow" onClick={() => scrollToImage("next")}>
          &#10093;
        </div>
        <div className="container-images">
          <div className="hero-cta">
            <ul ref={listRef}>
              {data.map((item) => {
                return (
                  <li
                    key={item.id}
                    className={item.id === data[currentIndex].id ? "active" : ""}
                  >
                    <img
                      src={item.imgUrl}
                      style={{ width: "100%", height: "100%", objectFit: "cover" }}
                    />
                    <div className="hero-overlay">
                      <h1 className="hero-title">
                        Construimos invernaderos a tu medida
                      </h1>
                      <p className="hero-subtitle">
                        Para todo el departamento de Antioquia
                      </p>
                      <button className="btn-cotizar">¡Cotiza ahora!</button>
                    </div>
                  </li>
                );
              })}
            </ul>
          </div>
        </div>
        <div className="dots-container">
          {data.map((_, idx) => (
            <div
              key={idx}
              className={`dot-container-item ${idx === currentIndex ? "active" : ""}`}
              onClick={() => goToSlide(idx)}
            >
              &#9865;
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};