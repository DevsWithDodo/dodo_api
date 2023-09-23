import "@/css/index.scss";
import axios from "axios";
import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";

axios.defaults.withCredentials = true;

createRoot(document.getElementById('app')!).render(<App />);