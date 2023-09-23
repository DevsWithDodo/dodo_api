import React from "react";
import { BrowserRouter } from "react-router-dom";
import { Routes, Route } from "react-router";
import Home from "./Pages/Home";
import PrivacyPolicy from "./Pages/PrivacyPolicy";

export default function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/privacy-policy" element={<PrivacyPolicy />} />
            </Routes>
        </BrowserRouter>
    )
}